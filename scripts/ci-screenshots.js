/**
 * CI Screenshots - Captures screenshots of registration forms and dashboard.
 *
 * Usage:
 *   node scripts/ci-screenshots.js [--base-url http://localhost:8000] [--output-dir screenshots]
 *
 * Captures:
 *   1. Individual registration form (home page with registration enabled)
 *   2. Family registration form (after toggling)
 *   3. Registration page (/register) - individual form
 *   4. Registration page (/register) - family form
 *   5. Dashboard (authenticated)
 */
import { chromium } from 'playwright';
import { mkdir } from 'node:fs/promises';
import path from 'node:path';

const argv = process.argv.slice(2);

const getArg = (flag) => {
  const idx = argv.indexOf(flag);
  return idx !== -1 ? argv[idx + 1] ?? null : null;
};

const BASE_URL = (getArg('--base-url') || process.env.APP_URL || 'http://127.0.0.1:8000').replace(/\/$/, '');
const OUTPUT_DIR = path.resolve(getArg('--output-dir') || 'screenshots');

const waitForServer = async (url, maxRetries = 30, intervalMs = 2000) => {
  for (let i = 0; i < maxRetries; i++) {
    try {
      const res = await fetch(url, { method: 'GET', redirect: 'manual' });
      if (res.status > 0) return true;
    } catch {
      // Server not ready yet
    }
    await new Promise((r) => setTimeout(r, intervalMs));
  }
  return false;
};

const screenshots = [];

const capture = async (page, name, description) => {
  const filePath = path.join(OUTPUT_DIR, `${name}.png`);
  await page.screenshot({ path: filePath, fullPage: true });
  screenshots.push({ name, description, path: filePath });
  console.log(`  [ok] ${description} -> ${name}.png`);
};

const main = async () => {
  console.log(`\nCI Screenshots`);
  console.log(`  Base URL:   ${BASE_URL}`);
  console.log(`  Output dir: ${OUTPUT_DIR}\n`);

  // Wait for server to be ready
  console.log('Waiting for server...');
  const serverReady = await waitForServer(BASE_URL);
  if (!serverReady) {
    console.error('Server did not start within timeout. Aborting.');
    process.exit(1);
  }
  console.log('Server is ready.\n');

  await mkdir(OUTPUT_DIR, { recursive: true });

  const browser = await chromium.launch({ args: ['--no-sandbox', '--disable-setuid-sandbox'] });
  const context = await browser.newContext({ viewport: { width: 1440, height: 900 } });
  const page = await context.newPage();

  let passed = 0;
  let failed = 0;

  const run = async (description, fn) => {
    try {
      await fn();
      passed++;
    } catch (err) {
      failed++;
      console.error(`  [FAIL] ${description}: ${err.message}`);
      // Take a failure screenshot if possible
      try {
        await page.screenshot({ path: path.join(OUTPUT_DIR, `FAIL-${Date.now()}.png`), fullPage: true });
      } catch {
        // ignore
      }
    }
  };

  // ─── 1. Home page - Individual Form ───
  await run('Home page - Individual registration form', async () => {
    await page.goto(`${BASE_URL}/`, { waitUntil: 'networkidle', timeout: 30000 });
    // Wait for preloader to finish (max 5s)
    await page.waitForTimeout(4500);
    await capture(page, '01-home-individual-form', 'Home page - Individual registration form');
  });

  // ─── 2. Home page - Family Form (toggle) ───
  await run('Home page - Family registration form', async () => {
    // Click family toggle button
    const familyBtn = page.locator('#toggleFamily');
    if (await familyBtn.count() > 0) {
      await familyBtn.click();
      await page.waitForTimeout(1000);
      await capture(page, '02-home-family-form', 'Home page - Family registration form (toggled)');
    } else {
      // Fallback: try the welcome page family toggle IDs
      const altFamilyBtn = page.locator('#family-toggle');
      if (await altFamilyBtn.count() > 0) {
        await altFamilyBtn.click();
        await page.waitForTimeout(1000);
      }
      await capture(page, '02-home-family-form', 'Home page - Family registration form (toggled)');
    }
  });

  // ─── 3. /register page - Individual Form ───
  await run('Register page - Individual form', async () => {
    await page.goto(`${BASE_URL}/register`, { waitUntil: 'networkidle', timeout: 30000 });
    await page.waitForTimeout(1000);
    await capture(page, '03-register-individual-form', 'Register page - Individual form');
  });

  // ─── 4. /register page - Family Form (toggle) ───
  await run('Register page - Family form', async () => {
    const familyBtn = page.locator('#toggleFamily');
    if (await familyBtn.count() > 0) {
      await familyBtn.click();
      await page.waitForTimeout(1000);
    }
    await capture(page, '04-register-family-form', 'Register page - Family form (toggled)');
  });

  // ─── 5. Dashboard (authenticated) ───
  await run('Dashboard (authenticated)', async () => {
    // Navigate to login
    await page.goto(`${BASE_URL}/login`, { waitUntil: 'networkidle', timeout: 30000 });

    const emailField = page.locator('input[name="email"]');
    const passwordField = page.locator('input[name="password"]');

    const email = process.env.ADMIN_EMAIL || 'admin@alsarya.tv';
    const password = process.env.ADMIN_PASSWORD || 'password';

    if (await emailField.count() > 0 && await passwordField.count() > 0) {
      await emailField.fill(email);
      await passwordField.fill(password);

      const submitBtn = page.locator('button[type="submit"]').first();
      if (await submitBtn.count() > 0) {
        await submitBtn.click();
      } else {
        await page.keyboard.press('Enter');
      }
      await page.waitForTimeout(2000);
    }

    // Navigate to dashboard
    await page.goto(`${BASE_URL}/dashboard`, { waitUntil: 'networkidle', timeout: 30000 });
    await page.waitForTimeout(1000);
    await capture(page, '05-dashboard', 'Dashboard (authenticated)');
  });

  await browser.close();

  // ─── Summary ───
  console.log(`\n${'='.repeat(50)}`);
  console.log(`Screenshots: ${screenshots.length} captured`);
  console.log(`Results: ${passed} passed, ${failed} failed`);
  console.log(`Output: ${OUTPUT_DIR}`);
  console.log(`${'='.repeat(50)}\n`);

  if (screenshots.length > 0) {
    console.log('Files:');
    for (const s of screenshots) {
      console.log(`  - ${s.name}.png  (${s.description})`);
    }
  }

  if (failed > 0) {
    process.exit(1);
  }
};

main().catch((err) => {
  console.error('Fatal error:', err);
  process.exit(1);
});
