import { readFile, writeFile, mkdir } from 'node:fs/promises';
import path from 'node:path';

const argv = process.argv.slice(2);

const getArg = (flag) => {
    const idx = argv.indexOf(flag);
    return idx !== -1 ? argv[idx + 1] ?? null : null;
};

const ROOT_DIR = path.resolve(getArg('--output-dir') || 'artifacts/dashboard-audit');
const MANIFEST_PATH = path.resolve(getArg('--manifest') || path.join(ROOT_DIR, 'manifest.json'));
const OUTPUT_HTML = path.resolve(getArg('--html') || path.join(ROOT_DIR, 'dashboard-functionalities-audit.html'));

const escapeHtml = (value) => String(value)
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#39;');

const main = async () => {
    const raw = await readFile(MANIFEST_PATH, 'utf8');
    const manifest = JSON.parse(raw);

    const items = (manifest.scenes || []).filter((scene) => scene.status === 'captured');

    const cards = items.map((scene) => {
        const relativeImage = path.relative(path.dirname(OUTPUT_HTML), scene.path).split(path.sep).join('/');
        return `
        <article class="card">
            <div class="card-header">
                <span class="badge">#${scene.order}</span>
                <span class="path">${escapeHtml(scene.url)}</span>
            </div>
            <h2 class="title-en">${escapeHtml(scene.titleEn)}</h2>
            <h3 class="title-ar" dir="rtl">${escapeHtml(scene.titleAr)}</h3>
            <p class="desc-en">${escapeHtml(scene.descEn)}</p>
            <p class="desc-ar" dir="rtl">${escapeHtml(scene.descAr)}</p>
            <img src="${escapeHtml(relativeImage)}" alt="${escapeHtml(scene.titleEn)}" loading="lazy" />
        </article>
        `;
    }).join('\n');

    const html = `<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>AlSarya TV Dashboard Functionalities Audit</title>
    <style>
        :root { color-scheme: dark; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Inter, Arial, sans-serif;
            background: #0b1220;
            color: #e5e7eb;
            line-height: 1.6;
        }
        .container { max-width: 1380px; margin: 0 auto; padding: 28px 20px 56px; }
        .hero {
            padding: 20px;
            border: 1px solid #334155;
            border-radius: 16px;
            background: linear-gradient(160deg, #0f172a, #111827);
            margin-bottom: 24px;
        }
        .hero h1 { margin: 0 0 8px; font-size: 30px; }
        .hero p { margin: 6px 0; color: #cbd5e1; }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(430px, 1fr));
            gap: 16px;
        }
        .card {
            border: 1px solid #374151;
            border-radius: 16px;
            background: #111827;
            overflow: hidden;
            padding: 14px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25);
        }
        .card-header {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            align-items: center;
            margin-bottom: 8px;
        }
        .badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 42px;
            height: 24px;
            border-radius: 999px;
            background: #1d4ed8;
            font-size: 12px;
            font-weight: 700;
        }
        .path {
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: 12px;
            color: #93c5fd;
            overflow-wrap: anywhere;
        }
        .title-en { margin: 0; font-size: 20px; color: #f9fafb; }
        .title-ar { margin: 4px 0 0; font-size: 20px; color: #fef08a; font-family: Tahoma, Arial, sans-serif; }
        .desc-en, .desc-ar { margin: 8px 0; color: #d1d5db; }
        .desc-ar { font-family: Tahoma, Arial, sans-serif; }
        img {
            display: block;
            width: 100%;
            border-radius: 12px;
            border: 1px solid #475569;
            margin-top: 12px;
            background: #020617;
        }
        .footer { margin-top: 24px; color: #94a3b8; font-size: 13px; }
    </style>
</head>
<body>
    <div class="container">
        <header class="hero">
            <h1>AlSarya TV Dashboard Functionalities Audit</h1>
            <p dir="rtl">توثيق شامل لوظائف لوحة التحكم مع لقطات شاشة ثنائية اللغة (عربي / إنجليزي).</p>
            <p><strong>Generated at:</strong> ${escapeHtml(manifest.generatedAt || new Date().toISOString())}</p>
            <p><strong>Base URL:</strong> ${escapeHtml(manifest.baseUrl || '')}</p>
            <p><strong>Total Captured Screens:</strong> ${items.length}</p>
        </header>

        <section class="grid">
            ${cards}
        </section>

        <p class="footer">Generated by scripts/build-dashboard-audit-html.js</p>
    </div>
</body>
</html>`;

    await mkdir(path.dirname(OUTPUT_HTML), { recursive: true });
    await writeFile(OUTPUT_HTML, html, 'utf8');

    console.log(`HTML report created: ${OUTPUT_HTML}`);
};

main().catch((error) => {
    console.error('Failed to build HTML report:', error.message);
    process.exit(1);
});
