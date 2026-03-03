<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Tinkerette &mdash; {{ $pageTitle }}</title>
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

        .header-user {
            color: var(--muted);
            font-size: 11px;
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
        {{-- Terminal chrome --}}
        <div class="header">
            <div class="header-left">
                <div class="dots">
                    <span class="r"></span>
                    <span class="y"></span>
                    <span class="g"></span>
                </div>
                <span class="header-title">Tinkerette &mdash; php artisan</span>
            </div>
            <span class="header-user">{{ auth()->user()->email }}</span>
            <span class="exit-code {{ $exitCode === 0 ? 'ok' : 'fail' }}">
                exit: {{ $exitCode }}
            </span>
        </div>

        {{-- Quick links --}}
        <div class="quick-links">
            @foreach ($quickCommands as $qc)
                <a href="?{{ $qc === 'list' ? '' : 'cmd=' . urlencode($qc) }}">{{ $qc }}</a>
            @endforeach
        </div>

        {{-- Command input --}}
        <div class="cmd-bar">
            <span class="prompt">$</span>
            <span class="prompt" style="color:var(--muted)">php artisan</span>
            <form method="get" action="{{ route('tinkerette') }}">
                <input type="text"
                       name="cmd"
                       value="{{ $escapedCmd }}"
                       placeholder="type a command..."
                       autocomplete="off"
                       autofocus>
                <button type="submit">Run</button>
            </form>
        </div>

        {{-- Breadcrumb --}}
        <div class="breadcrumb">
            <a href="{{ route('tinkerette') }}">artisan</a>
            @if ($command !== '')
                <span class="sep">&rsaquo;</span>
                @php
                    $segments = explode(':', $command);
                    $accumulated = '';
                    $lastIdx = count($segments) - 1;
                @endphp
                @foreach ($segments as $i => $segment)
                    @php $accumulated .= ($i > 0 ? ':' : '') . $segment; @endphp
                    @if ($i === $lastIdx)
                        <span class="current">{{ $segment }}</span>
                    @else
                        <a href="{{ route('tinkerette', ['cmd' => 'list ' . $accumulated]) }}">{{ $segment }}</a>
                        <span class="sep">&rsaquo;</span>
                    @endif
                @endforeach
            @endif
        </div>

        {{-- Output --}}
        <div class="output">{!! $renderedOutput !!}</div>

        <div class="footer">
            Tinkerette &middot; artisan web interface &middot; {{ now()->format('Y-m-d H:i:s') }}
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var input = document.querySelector('.cmd-bar input[type="text"]');
            if (input) {
                input.focus();
                input.select();
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === '/' && document.activeElement.tagName !== 'INPUT') {
                e.preventDefault();
                document.querySelector('.cmd-bar input[type="text"]').focus();
            }
        });
    </script>
</body>
</html>
