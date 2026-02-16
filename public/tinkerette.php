<?php

/**
 * Tinkerette - Artisan Web Interface
 *
 * A single-file web UI that mirrors `php artisan` in the browser.
 * Commands appear as clickable links; output is rendered in a terminal-style page.
 *
 * SECURITY: This file should NEVER be deployed to production.
 * Protect it with IP allowlisting or remove it entirely before going live.
 */

// ---------------------------------------------------------------------------
// Configuration
// ---------------------------------------------------------------------------

// Allowed IPs (empty array = allow all — only for local dev)
$allowedIps = ['127.0.0.1', '::1'];

// Commands that should never be clickable / executable from the web
$blockedCommands = [
    'down', 'up', 'env',
    'tinker',               // interactive REPL — cannot work in web context
    'queue:listen',         // long-running
    'queue:work',           // long-running
    'pail',                 // long-running log tail
    'serve',                // starts dev server
    'reverb:start',         // long-running websocket
    'schedule:work',        // long-running
    'octane:start',         // long-running
    'horizon',              // long-running
    'horizon:work',         // long-running
];

// Maximum execution time for any single command (seconds)
$maxExecutionTime = 30;

// ---------------------------------------------------------------------------
// Resolve paths
// ---------------------------------------------------------------------------

$publicDir = __DIR__;
$basePath = dirname($publicDir);          // project root
$artisanBin = $basePath.'/artisan';

// ---------------------------------------------------------------------------
// Production kill-switch — Tinkerette must never run in production
// ---------------------------------------------------------------------------

$appEnv = 'production'; // default to the safest assumption

// Read APP_ENV from .env without bootstrapping Laravel
$envFile = $basePath.'/.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#')) {
            continue;
        }
        if (preg_match('/^APP_ENV\s*=\s*(.+)$/', trim($line), $m)) {
            $appEnv = trim($m[1], " \t\n\r\0\x0B\"'");
            break;
        }
    }
}

// Also honour the real environment variable (set by the web server / container)
$appEnv = getenv('APP_ENV') ?: $appEnv;

if ($appEnv === 'production') {
    http_response_code(403);
    exit('Tinkerette is disabled in production.');
}

// ---------------------------------------------------------------------------
// Security gate
// ---------------------------------------------------------------------------

$clientIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

if (! empty($allowedIps) && ! in_array($clientIp, $allowedIps, true)) {
    http_response_code(403);
    exit('Forbidden');
}

if (! file_exists($artisanBin)) {
    http_response_code(500);
    exit('artisan not found at: '.htmlspecialchars($artisanBin));
}

$phpBin = PHP_BINARY ?: 'php';

// ---------------------------------------------------------------------------
// Determine what to run
// ---------------------------------------------------------------------------

$command = trim($_GET['cmd'] ?? '');

// Sanitise: only allow alphanumeric, colons, dashes, underscores, spaces, equals, quotes, dots, commas, slashes
if ($command !== '' && ! preg_match('/^[a-zA-Z0-9:_\-\s=\'".,\/]+$/', $command)) {
    http_response_code(400);
    exit('Invalid command characters.');
}

// Block dangerous commands
foreach ($blockedCommands as $blocked) {
    if ($command === $blocked || str_starts_with($command, $blocked.' ')) {
        http_response_code(403);
        exit('Command blocked: '.htmlspecialchars($blocked));
    }
}

// Prevent shell escapes — the command is passed as a single argument string
// We prepend --no-interaction to avoid prompts that would hang
$fullCmd = sprintf(
    '%s %s %s --no-interaction --ansi 2>&1',
    escapeshellarg($phpBin),
    escapeshellarg($artisanBin),
    $command !== '' ? $command : 'list'
);

// ---------------------------------------------------------------------------
// Execute
// ---------------------------------------------------------------------------

set_time_limit($maxExecutionTime);

$output = '';
$exitCode = 0;
exec($fullCmd, $lines, $exitCode);
$output = implode("\n", $lines);

// ---------------------------------------------------------------------------
// Parse: detect artisan command names and make them clickable
// ---------------------------------------------------------------------------

/**
 * Given raw artisan output (with ANSI codes), return HTML with commands as links.
 *
 * Artisan `list` output has lines like:
 *   make:model          Create a new Eloquent model class
 *   migrate:fresh       Drop all tables and re-run all migrations
 *
 * Namespace headers look like:
 *  cache
 *  config
 *
 * We detect command patterns (word:word or single-word commands indented)
 * and wrap them in <a> tags.
 */
function renderOutput(string $raw, string $currentCommand, array $blockedCommands): string
{
    // Convert ANSI color codes to HTML spans
    $html = ansiToHtml($raw);

    // If we are on the list view (no command or "list"), linkify command names
    if ($currentCommand === '' || $currentCommand === 'list') {
        return linkifyListOutput($html, $blockedCommands);
    }

    // For "help <cmd>" output, linkify any "command:subcommand" patterns
    // For other outputs, linkify recognized artisan command patterns in context
    return linkifyGenericOutput($html, $blockedCommands);
}

/**
 * Convert ANSI escape codes to <span> with inline styles.
 */
function ansiToHtml(string $text): string
{
    $text = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

    // Map ANSI codes to CSS colors
    $colorMap = [
        '0;30' => '#4e4e4e', '1;30' => '#7c7c7c',  // black
        '0;31' => '#e06c75', '1;31' => '#ff8b94',  // red
        '0;32' => '#98c379', '1;32' => '#b5e890',  // green
        '0;33' => '#e5c07b', '1;33' => '#ffd68a',  // yellow
        '0;34' => '#61afef', '1;34' => '#7ec8f2',  // blue
        '0;35' => '#c678dd', '1;35' => '#d898ed',  // magenta
        '0;36' => '#56b6c2', '1;36' => '#79dce8',  // cyan
        '0;37' => '#dcdfe4', '1;37' => '#ffffff',  // white
    ];

    // Replace ANSI sequences with spans
    // Pattern: ESC[ (params) m
    $result = preg_replace_callback(
        '/\x1b\[([0-9;]*)m/',
        function ($matches) use ($colorMap) {
            $code = $matches[1];
            if ($code === '0' || $code === '') {
                return '</span>';
            }
            // Try direct mapping
            if (isset($colorMap[$code])) {
                return '<span style="color:'.$colorMap[$code].'">';
            }
            // Handle 38;5;N (256-color foreground)
            if (preg_match('/38;5;(\d+)/', $code, $m256)) {
                $c = ansi256ToHex((int) $m256[1]);

                return '<span style="color:'.$c.'">';
            }
            // Bold
            if ($code === '1') {
                return '<span style="font-weight:bold">';
            }
            // Dim
            if ($code === '2') {
                return '<span style="opacity:0.7">';
            }
            // Underline
            if ($code === '4') {
                return '<span style="text-decoration:underline">';
            }
            // Single color codes (e.g. 32 for green)
            $singleMap = [
                '30' => '#4e4e4e', '31' => '#e06c75', '32' => '#98c379',
                '33' => '#e5c07b', '34' => '#61afef', '35' => '#c678dd',
                '36' => '#56b6c2', '37' => '#dcdfe4',
                '90' => '#7c7c7c', '91' => '#ff8b94', '92' => '#b5e890',
                '93' => '#ffd68a', '94' => '#7ec8f2', '95' => '#d898ed',
                '96' => '#79dce8', '97' => '#ffffff',
            ];
            // Try compound codes like "1;32"
            $parts = explode(';', $code);
            $color = null;
            $bold = false;
            foreach ($parts as $p) {
                if ($p === '1') {
                    $bold = true;
                }
                if (isset($singleMap[$p])) {
                    $color = $singleMap[$p];
                }
            }
            if ($color) {
                $style = 'color:'.$color;
                if ($bold) {
                    $style .= ';font-weight:bold';
                }

                return '<span style="'.$style.'">';
            }

            return '<span>';
        },
        $text
    );

    // Strip any remaining raw ESC chars
    $result = preg_replace('/\x1b\[[0-9;]*[A-Za-z]/', '', $result);

    return $result;
}

/**
 * Convert ANSI 256-color index to hex.
 */
function ansi256ToHex(int $n): string
{
    // Standard 16 colors
    $base16 = [
        '#000000', '#800000', '#008000', '#808000', '#000080', '#800080', '#008080', '#c0c0c0',
        '#808080', '#ff0000', '#00ff00', '#ffff00', '#0000ff', '#ff00ff', '#00ffff', '#ffffff',
    ];
    if ($n < 16) {
        return $base16[$n];
    }
    if ($n >= 232) {
        $v = 8 + ($n - 232) * 10;

        return sprintf('#%02x%02x%02x', $v, $v, $v);
    }
    $n -= 16;
    $b = $n % 6;
    $n = (int) ($n / 6);
    $g = $n % 6;
    $r = (int) ($n / 6);
    $f = fn ($v) => $v === 0 ? 0 : 55 + $v * 40;

    return sprintf('#%02x%02x%02x', $f($r), $f($g), $f($b));
}

/**
 * In `artisan list` output, command names appear as:
 *   "  command:name       Description text"
 * We linkify those.
 */
function linkifyListOutput(string $html, array $blockedCommands): string
{
    // Match lines that look like "  command:sub   Description..."
    // Also handles single-word commands like "  about   Display basic info..."
    // The command part may be wrapped in <span> tags from ANSI conversion
    return preg_replace_callback(
        '/^([ ]{2,})(<span[^>]*>)?([a-z][a-z0-9]*(?::[a-z0-9\-]+)*)(<\/span>)?([ ]{2,}.*)$/m',
        function ($m) use ($blockedCommands) {
            $indent = $m[1];
            $spanOpen = $m[2] ?? '';
            $cmd = $m[3];
            $spanClose = $m[4] ?? '';
            $rest = $m[5];

            if (in_array($cmd, $blockedCommands, true)) {
                return $indent.$spanOpen.$cmd.$spanClose.$rest;
            }

            $url = '?cmd='.urlencode($cmd);
            $link = '<a href="'.$url.'" class="cmd-link">'.$cmd.'</a>';

            return $indent.$link.$rest;
        },
        $html
    );
}

/**
 * In generic output, linkify anything that looks like an artisan command (word:word).
 */
function linkifyGenericOutput(string $html, array $blockedCommands): string
{
    return preg_replace_callback(
        '/\b([a-z][a-z0-9]*:[a-z0-9\-]+(?::[a-z0-9\-]+)*)\b/',
        function ($m) use ($blockedCommands) {
            $cmd = $m[1];
            if (in_array($cmd, $blockedCommands, true)) {
                return $cmd;
            }
            $url = '?cmd='.urlencode($cmd);

            return '<a href="'.$url.'" class="cmd-link">'.$cmd.'</a>';
        },
        $html
    );
}

// ---------------------------------------------------------------------------
// Render
// ---------------------------------------------------------------------------

$renderedOutput = renderOutput($output, $command, $blockedCommands);
$pageTitle = $command !== '' ? "artisan $command" : 'artisan list';
$escapedCmd = htmlspecialchars($command, ENT_QUOTES, 'UTF-8');

?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Tinkerette &mdash; <?= $pageTitle ?></title>
    <style>
        :root {
            --bg:       #1e1e2e;
            --surface:  #181825;
            --text:     #cdd6f4;
            --muted:    #6c7086;
            --green:    #a6e3a1;
            --blue:     #89b4fa;
            --red:      #f38ba8;
            --yellow:   #f9e2af;
            --border:   #313244;
            --hover-bg: #313244;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'JetBrains Mono', 'Fira Code', 'SF Mono', 'Cascadia Code', 'Consolas', monospace;
            font-size: 13px;
            line-height: 1.6;
            min-height: 100vh;
        }

        .wrapper {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Header */
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 8px 8px 0 0;
            gap: 12px;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .dots {
            display: flex;
            gap: 6px;
        }
        .dots span {
            width: 12px; height: 12px;
            border-radius: 50%;
            display: inline-block;
        }
        .dots .r { background: #ff5f57; }
        .dots .y { background: #febc2e; }
        .dots .g { background: #28c840; }

        .header-title {
            color: var(--muted);
            font-size: 12px;
        }

        /* Command bar */
        .cmd-bar {
            display: flex;
            align-items: center;
            background: var(--surface);
            border-left: 1px solid var(--border);
            border-right: 1px solid var(--border);
            padding: 8px 16px;
            gap: 8px;
        }

        .cmd-bar .prompt {
            color: var(--green);
            white-space: nowrap;
            user-select: none;
        }

        .cmd-bar form {
            flex: 1;
            display: flex;
        }

        .cmd-bar input[type="text"] {
            flex: 1;
            background: transparent;
            border: none;
            outline: none;
            color: var(--text);
            font-family: inherit;
            font-size: inherit;
            caret-color: var(--green);
        }

        .cmd-bar input[type="text"]::placeholder {
            color: var(--muted);
        }

        .cmd-bar button {
            background: var(--border);
            color: var(--text);
            border: none;
            padding: 4px 12px;
            border-radius: 4px;
            font-family: inherit;
            font-size: 12px;
            cursor: pointer;
        }
        .cmd-bar button:hover {
            background: var(--hover-bg);
            color: var(--green);
        }

        /* Breadcrumb / nav */
        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: var(--surface);
            border-left: 1px solid var(--border);
            border-right: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
            font-size: 12px;
            flex-wrap: wrap;
        }

        .breadcrumb a {
            color: var(--blue);
            text-decoration: none;
        }
        .breadcrumb a:hover {
            text-decoration: underline;
        }
        .breadcrumb .sep {
            color: var(--muted);
        }
        .breadcrumb .current {
            color: var(--yellow);
        }

        /* Output */
        .output {
            background: var(--bg);
            border: 1px solid var(--border);
            border-top: none;
            border-radius: 0 0 8px 8px;
            padding: 16px;
            overflow-x: auto;
            white-space: pre-wrap;
            word-wrap: break-word;
            min-height: 200px;
        }

        .output .cmd-link {
            color: var(--blue);
            text-decoration: none;
            border-bottom: 1px dotted var(--blue);
            transition: color 0.15s, border-color 0.15s;
        }
        .output .cmd-link:hover {
            color: var(--green);
            border-color: var(--green);
        }

        /* Exit code badge */
        .exit-code {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
        }
        .exit-code.ok   { background: #28c84022; color: var(--green); }
        .exit-code.fail { background: #ff5f5722; color: var(--red); }

        /* Quick-access buttons */
        .quick-links {
            display: flex;
            gap: 6px;
            padding: 10px 16px;
            background: var(--surface);
            border-left: 1px solid var(--border);
            border-right: 1px solid var(--border);
            flex-wrap: wrap;
        }

        .quick-links a {
            color: var(--muted);
            text-decoration: none;
            font-size: 11px;
            padding: 3px 8px;
            border: 1px solid var(--border);
            border-radius: 4px;
            transition: all 0.15s;
        }
        .quick-links a:hover {
            color: var(--text);
            border-color: var(--blue);
            background: var(--hover-bg);
        }

        /* Footer */
        .footer {
            text-align: center;
            padding: 16px;
            color: var(--muted);
            font-size: 11px;
        }

        /* Responsive */
        @media (max-width: 640px) {
            .wrapper { padding: 8px; }
            .output { padding: 10px; font-size: 12px; }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Terminal chrome -->
        <div class="header">
            <div class="header-left">
                <div class="dots">
                    <span class="r"></span>
                    <span class="y"></span>
                    <span class="g"></span>
                </div>
                <span class="header-title">Tinkerette &mdash; php artisan</span>
            </div>
            <span class="exit-code <?= $exitCode === 0 ? 'ok' : 'fail' ?>">
                exit: <?= $exitCode ?>
            </span>
        </div>

        <!-- Quick links -->
        <div class="quick-links">
            <a href="?">list</a>
            <a href="?cmd=about">about</a>
            <a href="?cmd=route:list">route:list</a>
            <a href="?cmd=migrate:status">migrate:status</a>
            <a href="?cmd=config:show">config:show</a>
            <a href="?cmd=schedule:list">schedule:list</a>
            <a href="?cmd=event:list">event:list</a>
            <a href="?cmd=queue:failed">queue:failed</a>
        </div>

        <!-- Command input -->
        <div class="cmd-bar">
            <span class="prompt">$</span>
            <span class="prompt" style="color:var(--muted)">php artisan</span>
            <form method="get" action="">
                <input type="text"
                       name="cmd"
                       value="<?= $escapedCmd ?>"
                       placeholder="type a command..."
                       autocomplete="off"
                       autofocus>
                <button type="submit">Run</button>
            </form>
        </div>

        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="?">artisan</a>
            <?php if ($command !== '') { ?>
                <span class="sep">&rsaquo;</span>
                <?php
                // Build breadcrumb segments for namespaced commands like make:model
                $parts = explode(':', $command);
                $accumulated = '';
                $last = count($parts) - 1;
                foreach ($parts as $i => $part) {
                    $accumulated .= ($i > 0 ? ':' : '').$part;
                    if ($i === $last) { ?>
                        <span class="current"><?= htmlspecialchars($part) ?></span>
                    <?php } else { ?>
                        <a href="?cmd=<?= urlencode('list '.$accumulated) ?>"><?= htmlspecialchars($part) ?></a>
                        <span class="sep">&rsaquo;</span>
                    <?php }
                    }
                ?>
            <?php } ?>
        </div>

        <!-- Output -->
        <div class="output"><?= $renderedOutput ?></div>

        <div class="footer">
            Tinkerette &middot; artisan web interface &middot; <?= date('Y-m-d H:i:s') ?>
        </div>
    </div>

    <script>
        // Auto-focus the input and select all text on page load
        document.addEventListener('DOMContentLoaded', function() {
            var input = document.querySelector('.cmd-bar input[type="text"]');
            if (input) {
                input.focus();
                input.select();
            }
        });

        // Keyboard shortcut: / to focus input
        document.addEventListener('keydown', function(e) {
            if (e.key === '/' && document.activeElement.tagName !== 'INPUT') {
                e.preventDefault();
                document.querySelector('.cmd-bar input[type="text"]').focus();
            }
        });
    </script>
</body>
</html>
