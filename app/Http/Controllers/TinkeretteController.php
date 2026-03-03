<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

/**
 * Tinkerette — Artisan Web Interface
 *
 * Browser-based UI for running `php artisan` commands.
 * Restricted to authenticated users in non-production environments.
 */
class TinkeretteController extends Controller
{
    /** Commands that must never be executed from the web. */
    private const BLOCKED_COMMANDS = [
        // Dangerous state changes
        'down', 'up', 'env',
        'migrate:fresh', 'migrate:reset', 'migrate:rollback',
        'db:wipe', 'db:seed',
        'key:generate',
        'config:clear', 'cache:clear', 'route:clear', 'view:clear',
        // Interactive / long-running
        'tinker',
        'queue:listen', 'queue:work',
        'pail',
        'serve',
        'reverb:start',
        'schedule:work',
        'octane:start',
        'horizon', 'horizon:work',
    ];

    /** Quick-access commands shown as toolbar buttons. */
    private const QUICK_COMMANDS = [
        'list', 'about', 'route:list', 'migrate:status',
        'config:show', 'schedule:list', 'event:list', 'queue:failed',
    ];

    /** Maximum execution time per command (seconds). */
    private const MAX_EXECUTION_SECONDS = 30;

    /** ANSI compound color codes → CSS hex. */
    private const ANSI_COMPOUND_MAP = [
        '0;30' => '#4e4e4e', '1;30' => '#7c7c7c',
        '0;31' => '#e06c75', '1;31' => '#ff8b94',
        '0;32' => '#98c379', '1;32' => '#b5e890',
        '0;33' => '#e5c07b', '1;33' => '#ffd68a',
        '0;34' => '#61afef', '1;34' => '#7ec8f2',
        '0;35' => '#c678dd', '1;35' => '#d898ed',
        '0;36' => '#56b6c2', '1;36' => '#79dce8',
        '0;37' => '#dcdfe4', '1;37' => '#ffffff',
    ];

    /** ANSI single color codes → CSS hex. */
    private const ANSI_SINGLE_MAP = [
        '30' => '#4e4e4e', '31' => '#e06c75', '32' => '#98c379',
        '33' => '#e5c07b', '34' => '#61afef', '35' => '#c678dd',
        '36' => '#56b6c2', '37' => '#dcdfe4',
        '90' => '#7c7c7c', '91' => '#ff8b94', '92' => '#b5e890',
        '93' => '#ffd68a', '94' => '#7ec8f2', '95' => '#d898ed',
        '96' => '#79dce8', '97' => '#ffffff',
    ];

    /** ANSI base-16 palette (indices 0–15). */
    private const ANSI_BASE16 = [
        '#000000', '#800000', '#008000', '#808000',
        '#000080', '#800080', '#008080', '#c0c0c0',
        '#808080', '#ff0000', '#00ff00', '#ffff00',
        '#0000ff', '#ff00ff', '#00ffff', '#ffffff',
    ];

    // -----------------------------------------------------------------
    // Route handler
    // -----------------------------------------------------------------

    public function __invoke(Request $request): Response
    {
        if (app()->isProduction()) {
            abort(403, 'Tinkerette is disabled in production.');
        }

        $command = $this->validateCommand($request->query('cmd', ''));

        [$output, $exitCode] = $this->execute($command);

        Log::info('Tinkerette command executed', [
            'user'      => $request->user()?->email,
            'command'   => $command ?: 'list',
            'exit_code' => $exitCode,
        ]);

        $renderedOutput = $this->renderOutput($output, $command);

        return response()->view('tinkerette', [
            'command'        => $command,
            'escapedCmd'     => e($command),
            'renderedOutput' => $renderedOutput,
            'exitCode'       => $exitCode,
            'pageTitle'      => $command !== '' ? "artisan {$command}" : 'artisan list',
            'quickCommands'  => self::QUICK_COMMANDS,
        ]);
    }

    // -----------------------------------------------------------------
    // Command validation
    // -----------------------------------------------------------------

    private function validateCommand(string $raw): string
    {
        $command = trim($raw);

        if ($command === '') {
            return '';
        }

        // Strict allowlist: alphanumerics, colons, dashes, underscores,
        // spaces, equals, dots, commas. No quotes, no slashes.
        if (! preg_match('/^[a-zA-Z0-9:_\-\s=.,]+$/', $command)) {
            abort(400, 'Invalid command characters.');
        }

        foreach (self::BLOCKED_COMMANDS as $blocked) {
            if ($command === $blocked || str_starts_with($command, $blocked . ' ')) {
                abort(403, "Command blocked: {$blocked}");
            }
        }

        return $command;
    }

    // -----------------------------------------------------------------
    // Execution
    // -----------------------------------------------------------------

    /** @return array{0: string, 1: int} [output, exitCode] */
    private function execute(string $command): array
    {
        $artisan = base_path('artisan');
        $php = PHP_BINARY ?: 'php';
        $args = $command !== '' ? $command : 'list';

        // escapeshellcmd neutralises any remaining metacharacters
        $fullCmd = sprintf(
            '%s %s %s --no-interaction --ansi 2>&1',
            escapeshellarg($php),
            escapeshellarg($artisan),
            escapeshellcmd($args),
        );

        set_time_limit(self::MAX_EXECUTION_SECONDS);

        $lines = [];
        $exitCode = 0;
        exec($fullCmd, $lines, $exitCode);

        return [implode("\n", $lines), $exitCode];
    }

    // -----------------------------------------------------------------
    // Output rendering
    // -----------------------------------------------------------------

    private function renderOutput(string $raw, string $command): string
    {
        $html = $this->ansiToHtml($raw);

        return ($command === '' || $command === 'list')
            ? $this->linkifyListOutput($html)
            : $this->linkifyGenericOutput($html);
    }

    /** Convert ANSI escape sequences to styled <span> tags. */
    private function ansiToHtml(string $text): string
    {
        $text = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $result = preg_replace_callback(
            '/\x1b\[([0-9;]*)m/',
            function (array $matches): string {
                $code = $matches[1];

                if ($code === '0' || $code === '') {
                    return '</span>';
                }

                if (isset(self::ANSI_COMPOUND_MAP[$code])) {
                    return '<span style="color:' . self::ANSI_COMPOUND_MAP[$code] . '">';
                }

                if (preg_match('/38;5;(\d+)/', $code, $m256)) {
                    return '<span style="color:' . $this->ansi256ToHex((int) $m256[1]) . '">';
                }

                if ($code === '1') {
                    return '<span style="font-weight:bold">';
                }
                if ($code === '2') {
                    return '<span style="opacity:0.7">';
                }
                if ($code === '4') {
                    return '<span style="text-decoration:underline">';
                }

                $parts = explode(';', $code);
                $color = null;
                $bold = false;

                foreach ($parts as $p) {
                    if ($p === '1') {
                        $bold = true;
                    }
                    if (isset(self::ANSI_SINGLE_MAP[$p])) {
                        $color = self::ANSI_SINGLE_MAP[$p];
                    }
                }

                if ($color) {
                    $style = 'color:' . $color;
                    if ($bold) {
                        $style .= ';font-weight:bold';
                    }

                    return '<span style="' . $style . '">';
                }

                return '<span>';
            },
            $text,
        );

        return preg_replace('/\x1b\[[0-9;]*[A-Za-z]/', '', $result);
    }

    /** Convert a 256-color ANSI index to a CSS hex colour. */
    private function ansi256ToHex(int $n): string
    {
        if ($n < 16) {
            return self::ANSI_BASE16[$n];
        }

        if ($n >= 232) {
            $v = 8 + ($n - 232) * 10;

            return sprintf('#%02x%02x%02x', $v, $v, $v);
        }

        $n -= 16;
        $b = $n % 6;
        $n = intdiv($n, 6);
        $g = $n % 6;
        $r = intdiv($n, 6);
        $f = fn (int $v): int => $v === 0 ? 0 : 55 + $v * 40;

        return sprintf('#%02x%02x%02x', $f($r), $f($g), $f($b));
    }

    // -----------------------------------------------------------------
    // Linkification
    // -----------------------------------------------------------------

    /** In `artisan list` output, make command names clickable. */
    private function linkifyListOutput(string $html): string
    {
        return preg_replace_callback(
            '/^([ ]{2,})(<span[^>]*>)?([a-z][a-z0-9]*(?::[a-z0-9\-]+)*)(<\/span>)?([ ]{2,}.*)$/m',
            function (array $m): string {
                $indent = $m[1];
                $cmd = $m[3];
                $rest = $m[5];

                if (in_array($cmd, self::BLOCKED_COMMANDS, true)) {
                    return $indent . ($m[2] ?? '') . $cmd . ($m[4] ?? '') . $rest;
                }

                $url = '?cmd=' . urlencode($cmd);

                return $indent . '<a href="' . $url . '" class="cmd-link">' . $cmd . '</a>' . $rest;
            },
            $html,
        );
    }

    /** In generic output, linkify anything matching a command pattern. */
    private function linkifyGenericOutput(string $html): string
    {
        return preg_replace_callback(
            '/\b([a-z][a-z0-9]*:[a-z0-9\-]+(?::[a-z0-9\-]+)*)\b/',
            function (array $m): string {
                $cmd = $m[1];

                if (in_array($cmd, self::BLOCKED_COMMANDS, true)) {
                    return $cmd;
                }

                $url = '?cmd=' . urlencode($cmd);

                return '<a href="' . $url . '" class="cmd-link">' . $cmd . '</a>';
            },
            $html,
        );
    }
}
