import { mkdir } from 'node:fs/promises';
import path from 'node:path';
import { chromium } from 'playwright';

const argv = process.argv.slice(2);

const getArg = (flag) => {
  const idx = argv.indexOf(flag);
  return idx !== -1 ? argv[idx + 1] ?? null : null;
};

const hasFlag = (flag) => argv.includes(flag);

const DEFAULT_PRODUCTION_URL = 'https://alsarya.tv';
const DEFAULT_LOCAL_URL = 'http://127.0.0.1:8000';

const resolvedBaseUrl = getArg('--base-url')
  || process.env.VISUAL_REPORT_BASE_URL
  || (hasFlag('--local') ? DEFAULT_LOCAL_URL : DEFAULT_PRODUCTION_URL);

const BASE_URL = resolvedBaseUrl.replace(/\/$/, '');
const OUTPUT_DIR = path.resolve(getArg('--output-dir') || 'screenshots/visual-report');
const PDF_PATH = path.resolve(getArg('--pdf') || path.join(OUTPUT_DIR, 'visual-report.pdf'));

const ADMIN_EMAIL = getArg('--email') || process.env.ADMIN_EMAIL || 'admin@alsarya.tv';
const ADMIN_PASSWORD = getArg('--password') || process.env.ADMIN_PASSWORD || 'password';

const waitForServer = async (url, maxRetries = 30, intervalMs = 2000) => {
  for (let i = 0; i < maxRetries; i += 1) {
    try {
      const response = await fetch(url, { method: 'GET', redirect: 'manual' });
      if (response.status > 0) {
        return true;
      }
    } catch {
      // retry
    }
    await new Promise((resolve) => setTimeout(resolve, intervalMs));
  }

  return false;
};

const toFileUrl = (absolutePath) => `file://${absolutePath.split(path.sep).join('/')}`;

const captureScreenshot = async (page, destination, title) => {
  await page.waitForLoadState('networkidle', { timeout: 30000 });
  await page.waitForTimeout(1200);
  await page.screenshot({ path: destination, fullPage: true });
  console.log(`  [ok] ${title} -> ${path.basename(destination)}`);
};

const tryLogin = async (page) => {
  await page.goto(`${BASE_URL}/login`, { waitUntil: 'domcontentloaded', timeout: 30000 });
  await page.waitForTimeout(800);

  const emailInput = page.locator('input[name="email"]');
  const passwordInput = page.locator('input[name="password"]');

  if ((await emailInput.count()) === 0 || (await passwordInput.count()) === 0) {
    return;
  }

  await emailInput.fill(ADMIN_EMAIL);
  await passwordInput.fill(ADMIN_PASSWORD);

  const submitButton = page.locator('button[type="submit"]').first();
  if ((await submitButton.count()) > 0) {
    await submitButton.click();
  } else {
    await page.keyboard.press('Enter');
  }

  await page.waitForTimeout(1800);
};

const buildPdf = async (page, items) => {
  const sections = items
    .map((item) => {
      const imageUrl = toFileUrl(item.path);
      return `
        <section class="sheet">
          <h1>${item.title}</h1>
          <img src="${imageUrl}" alt="${item.title}" />
        </section>
      `;
    })
    .join('');

  const html = `
    <!doctype html>
    <html>
      <head>
        <meta charset="utf-8" />
        <style>
          @page { size: A4 landscape; margin: 14mm; }
          body { margin: 0; font-family: Arial, sans-serif; }
          .sheet {
            page-break-after: always;
            break-after: page;
            display: flex;
            flex-direction: column;
            height: calc(100vh - 28mm);
          }
          .sheet:last-child {
            page-break-after: auto;
            break-after: auto;
          }
          h1 {
            margin: 0 0 8px;
            font-size: 20px;
          }
          img {
            width: 100%;
            height: calc(100% - 32px);
            object-fit: contain;
            border: 1px solid #ddd;
          }
        </style>
      </head>
      <body>
        ${sections}
      </body>
    </html>
  `;

  await page.setContent(html, { waitUntil: 'load' });
  await page.pdf({ path: PDF_PATH, printBackground: true, preferCSSPageSize: true });
};

const main = async () => {
  console.log('\nVisual Report PDF');
  console.log(`  Base URL:   ${BASE_URL}`);
  console.log(`  Output dir: ${OUTPUT_DIR}`);
  console.log(`  PDF file:   ${PDF_PATH}\n`);

  const serverReady = await waitForServer(BASE_URL);
  if (!serverReady) {
    console.error('Server is not reachable. Start the app and run again.');
    process.exit(1);
  }

  await mkdir(OUTPUT_DIR, { recursive: true });

  const browser = await chromium.launch({ args: ['--no-sandbox', '--disable-setuid-sandbox'] });
  const context = await browser.newContext({ viewport: { width: 1600, height: 1000 } });
  const page = await context.newPage();

  const captured = [];

  try {
    // 1) Main registration form
    const registrationPath = path.join(OUTPUT_DIR, '01-registration-main.png');
    await page.goto(`${BASE_URL}/?skip-splash=true`, { waitUntil: 'domcontentloaded', timeout: 30000 });
    await captureScreenshot(page, registrationPath, 'Main Registration Form');
    captured.push({ title: 'Main Registration Form', path: registrationPath });

    // 2) Login for protected pages
    await tryLogin(page);

    // 3) Dashboard
    const dashboardPath = path.join(OUTPUT_DIR, '02-dashboard.png');
    await page.goto(`${BASE_URL}/dashboard`, { waitUntil: 'domcontentloaded', timeout: 30000 });
    await captureScreenshot(page, dashboardPath, 'Dashboard');
    captured.push({ title: 'Dashboard', path: dashboardPath });

    // 4) Admin panel
    const adminPath = path.join(OUTPUT_DIR, '03-admin.png');
    await page.goto(`${BASE_URL}/admin`, { waitUntil: 'domcontentloaded', timeout: 30000 });
    await captureScreenshot(page, adminPath, 'Admin Panel');
    captured.push({ title: 'Admin Panel', path: adminPath });

    // 5) Compose PDF
    const pdfPage = await context.newPage();
    await buildPdf(pdfPage, captured);
    await pdfPage.close();

    console.log('\nDone.');
    console.log(`  Screenshots: ${captured.length}`);
    console.log(`  PDF: ${PDF_PATH}\n`);
  } finally {
    await browser.close();
  }
};

main().catch((error) => {
  console.error('Fatal error:', error?.message ?? error);
  process.exit(1);
});
