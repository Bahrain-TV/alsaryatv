<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Finder\Finder;

class BrandingMakerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'branding:maker 
                            {image? : Path to the new logo image (optional if just investigating)} 
                            {--replace : Actually perform the replacement}
                            {--model=gemma3:1b : Ollama model to use for code analysis}
                            {--path= : Specific path to search in (defaults to resources/views and public)}
                            {--no-ai : Skip Ollama entirely and use heuristic-only detection}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Investigate and update branding (logos) across a project. Generic & portable.';

    /**
     * Image-related regex pattern — matches <img src="...">, background-image: url(...), asset('...logo...'), etc.
     */
    private const IMG_PATTERN = '/(<img\s+[^>]*src\s*=\s*[\'"]|background(-image)?\s*:\s*url\s*\(|asset\s*\(\s*[\'"][^)]*logo|src\s*=\s*[\'"][^"\']*\.(png|jpg|jpeg|svg|webp|gif))/i';

    /**
     * Files to ignore.
     */
    protected array $ignoreFolders = ['node_modules', 'vendor', 'storage', '.git', 'framework', 'cache'];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $newImagePath = $this->argument('image');
        $shouldReplace = $this->option('replace');
        $model = $this->option('model');
        $searchPath = $this->option('path');
        $noAi = $this->option('no-ai');

        $this->info("🔍 Starting Branding Investigation...");

        // 1. Check Ollama availability (skip if --no-ai)
        if (!$noAi && !$this->checkOllama()) {
            $this->warn("⚠️  Ollama is not accessible at http://localhost:11434. Falling back to heuristic-only mode.");
            $noAi = true;
        }

        // 2. Identify branding locations
        $locations = $this->identifyBrandingLocations($searchPath, $model, $noAi);

        if (empty($locations)) {
            $this->warn("⚠️ No branding locations identified.");
            return self::SUCCESS;
        }

        $this->newLine();
        $this->info("✅ Identified " . count($locations) . " file(s) with branding:");
        $totalMatches = 0;
        foreach ($locations as $file => $matches) {
            $this->newLine();
            $this->line("  📄 <info>{$file}</info>");
            foreach ($matches as $match) {
                $totalMatches++;
                $shortSnippet = $this->truncate(trim($match['snippet']), 120);
                $this->line("     Line {$match['line']}: <comment>{$shortSnippet}</comment>");
                $this->line("     ↳ {$match['reason']}");
            }
        }
        $this->newLine();
        $this->info("   Total matches: {$totalMatches}");

        // 3. Replacement phase
        if ($shouldReplace && $newImagePath) {
            if (!File::exists($newImagePath)) {
                $this->error("❌ New image file not found: {$newImagePath}");
                return self::FAILURE;
            }

            if (!$this->confirm("Replace all identified branding references with the new logo?", true)) {
                $this->info("Aborted.");
                return self::SUCCESS;
            }

            $this->info("\n🚀 Starting Replacement...");
            $this->performReplacement($locations, $newImagePath, $model, $noAi);
            $this->info("\n✅ Branding update completed!");
        } elseif ($shouldReplace && !$newImagePath) {
            $this->error("❌ Replacement requested but no new image path provided.");
            return self::FAILURE;
        } else {
            $this->newLine();
            $this->info("💡 To apply changes, re-run with --replace:");
            $this->line("   <comment>php artisan branding:maker {$newImagePath} --replace</comment>");
        }

        return self::SUCCESS;
    }

    /**
     * Check if Ollama is running.
     */
    private function checkOllama(): bool
    {
        try {
            $response = Http::timeout(2)->get('http://localhost:11434/api/tags');
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Identify branding locations using heuristics and optionally Ollama verification.
     */
    private function identifyBrandingLocations(?string $path, string $model, bool $noAi): array
    {
        $searchPaths = $path ? [base_path($path)] : [
            resource_path('views'),
            app_path('View/Components'),
        ];

        // Filter to existing paths
        $searchPaths = array_filter($searchPaths, fn($p) => File::exists($p));

        if (empty($searchPaths)) {
            return [];
        }

        $finder = new Finder();
        $finder->files()
            ->in($searchPaths)
            ->exclude($this->ignoreFolders)
            ->name(['*.blade.php', '*.css', '*.scss', '*.vue', '*.html'])
            ->contains('/logo/i'); // Primary filter: only files that mention "logo"

        $results = [];
        $ollamaCalls = 0;
        $maxOllamaCalls = 10; // Cap Ollama calls to keep things fast

        $this->withProgressBar($finder, function ($file) use (&$results, $model, $noAi, &$ollamaCalls, $maxOllamaCalls) {
            $content = $file->getContents();
            $filename = $file->getFilename();
            $lines = explode("\n", $content);

            // Determine if this file is obviously a logo file
            $isLogoFile = (bool) preg_match('/(logo|brand)/i', $filename);

            $seen = []; // Deduplicate
            foreach ($lines as $index => $line) {
                // Only look at lines that reference an image path + logo keyword together
                $hasImageRef = (bool) preg_match(self::IMG_PATTERN, $line);
                $hasLogoKeyword = stripos($line, 'logo') !== false;

                if (!$hasImageRef && !$hasLogoKeyword) {
                    continue;
                }

                // Build context window (3 lines)
                $start = max(0, $index - 1);
                $end = min(count($lines) - 1, $index + 1);
                $block = implode("\n", array_slice($lines, $start, $end - $start + 1));

                // Deduplicate overlapping blocks
                $blockKey = md5($block);
                if (isset($seen[$blockKey])) {
                    continue;
                }
                $seen[$blockKey] = true;

                // Decision: does this block contain a branding image?
                if ($hasImageRef && $hasLogoKeyword) {
                    // High confidence — both image pattern and "logo" keyword → no AI needed
                    $results[$file->getRelativePathname()][] = [
                        'snippet' => $block,
                        'line' => $index + 1,
                        'reason' => '🎯 High confidence: Image reference + "logo" keyword found.',
                        'full_path' => $file->getRealPath(),
                    ];
                } elseif ($isLogoFile && $hasImageRef) {
                    // File named *logo* and has an image ref → very likely
                    $results[$file->getRelativePathname()][] = [
                        'snippet' => $block,
                        'line' => $index + 1,
                        'reason' => '🎯 File is a logo component with image reference.',
                        'full_path' => $file->getRealPath(),
                    ];
                } elseif (!$noAi && $ollamaCalls < $maxOllamaCalls && $hasImageRef) {
                    // Ambiguous — ask Ollama
                    $ollamaCalls++;
                    $analysis = $this->ollamaAnalyze($block, $model);
                    if ($analysis['is_logo']) {
                        $results[$file->getRelativePathname()][] = [
                            'snippet' => $block,
                            'line' => $index + 1,
                            'reason' => '🤖 AI: ' . $analysis['reason'],
                            'full_path' => $file->getRealPath(),
                        ];
                    }
                }
            }
        });

        return $results;
    }

    /**
     * Analyze a code snippet using Ollama.
     */
    private function ollamaAnalyze(string $snippet, string $model): array
    {
        $prompt = "Does this code display a website logo? Respond JSON: {\"is_logo\":true/false,\"reason\":\"short\"}\n```\n{$snippet}\n```";

        try {
            $response = Http::timeout(15)->post('http://localhost:11434/api/generate', [
                'model' => $model,
                'prompt' => $prompt,
                'stream' => false,
                'format' => 'json',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $content = json_decode($data['response'] ?? '{}', true);
                if (isset($content['is_logo'])) {
                    return $content;
                }
            }
        } catch (\Exception $e) {
        }

        return ['is_logo' => false, 'reason' => ''];
    }

    /**
     * Perform the actual replacement.
     */
    private function performReplacement(array $locations, string $newImagePath, string $model, bool $noAi): void
    {
        // 1. Copy new image into public/images/branding/
        $extension = pathinfo($newImagePath, PATHINFO_EXTENSION);
        $brandingDir = public_path('images/branding');
        if (!File::exists($brandingDir)) {
            File::makeDirectory($brandingDir, 0755, true);
        }

        $targetFileName = 'logo.' . $extension;
        $targetPath = $brandingDir . '/' . $targetFileName;
        $publicAsset = 'images/branding/' . $targetFileName;

        File::copy($newImagePath, $targetPath);
        $this->info("  ✓ Saved new logo to: <comment>public/{$publicAsset}</comment>");

        // 2. Extract all unique image paths from matched snippets using regex
        $replacementMap = $this->buildReplacementMap($locations, $publicAsset, $model, $noAi);

        if (empty($replacementMap)) {
            $this->warn("  ⚠️ Could not identify any image paths to replace.");
            return;
        }

        $this->info("  Found " . count($replacementMap) . " unique image path(s) to replace:");
        foreach ($replacementMap as $old => $new) {
            $this->line("    <fg=red>{$old}</> → <fg=green>{$new}</>");
        }

        // 3. Apply replacements to all matched files
        $updatedFiles = [];
        foreach ($locations as $filename => $matches) {
            $fullPath = $matches[0]['full_path'];
            $content = File::get($fullPath);
            $original = $content;

            foreach ($replacementMap as $oldPath => $newPath) {
                $content = str_replace($oldPath, $newPath, $content);
            }

            if ($content !== $original) {
                File::put($fullPath, $content);
                $updatedFiles[] = $filename;
                $this->line("  ✓ Updated <info>{$filename}</info>");
            }
        }

        $this->newLine();
        $this->info("  Updated " . count($updatedFiles) . " file(s).");
    }

    /**
     * Build a map of old image paths → new image path using regex extraction.
     */
    private function buildReplacementMap(array $locations, string $newAssetPath, string $model, bool $noAi): array
    {
        $oldPaths = [];

        foreach ($locations as $filename => $matches) {
            foreach ($matches as $match) {
                $snippet = $match['snippet'];

                // Extract image paths using regex
                // Match asset('...'), src="...", url(...)
                $patterns = [
                    '/asset\s*\(\s*[\'"]([^\'"]+)[\'\"]\s*\)/i',       // asset('images/logo.png')
                    '/src\s*=\s*[\'"]([^\'"]+\.(png|jpg|jpeg|svg|webp|gif))[\'\"]/i', // src="images/logo.png"
                    '/url\s*\(\s*[\'"]?([^\'")\s]+\.(png|jpg|jpeg|svg|webp|gif))[\'"]?\s*\)/i', // url(images/logo.png)
                ];

                foreach ($patterns as $pattern) {
                    if (preg_match_all($pattern, $snippet, $m)) {
                        foreach ($m[1] as $path) {
                            if (stripos($path, 'logo') !== false || stripos($path, 'brand') !== false) {
                                $oldPaths[$path] = true;
                            }
                        }
                    }
                }
            }
        }

        // Build replacement map: each old path → new asset path
        $map = [];
        foreach (array_keys($oldPaths) as $old) {
            $map[$old] = $newAssetPath;
        }

        return $map;
    }

    /**
     * Truncate a string for display.
     */
    private function truncate(string $text, int $length): string
    {
        $text = str_replace(["\n", "\r", "\t"], ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        return strlen($text) > $length ? substr($text, 0, $length) . '…' : $text;
    }
}
