import re

with open('resources/views/down.blade.php', 'r') as f:
    content = f.read()

# Fix mobile view responsiveness
mobile_css = """        @media (max-width: 768px) {
            .down-head {
                flex-direction: column;
                text-align: center;
            }

            .down-brand {
                flex: 1 1 100%;
                justify-content: center;
                flex-direction: column;
            }

            .down-text {
                margin-top: 1rem;
            }

            .countdown-card {
                min-width: 100%;
            }

            .fun-message {
                grid-column: 1 / -1;
            }

            /* Slower rotation on mobile for better performance */
            .logo-3d-rotating {
                animation: rotate3D-slow 10s linear infinite;
            }

            /* Reduce glow intensity on mobile */
            .logo-glow {
                opacity: 0.4;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }"""

content = re.sub(r'        @media \(max-width: 768px\) \{.*?(?=        /\* Medium screens optimization \*/)', mobile_css + '\n\n', content, flags=re.DOTALL)

with open('resources/views/down.blade.php', 'w') as f:
    f.write(content)
