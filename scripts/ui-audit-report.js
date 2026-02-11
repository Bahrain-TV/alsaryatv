import { chromium } from 'playwright';
import { execSync } from 'node:child_process';
import { mkdir, writeFile } from 'node:fs/promises';
import path from 'node:path';

const argv = process.argv.slice(2);

const getArgValue = (flag) => {
  const index = argv.indexOf(flag);
  if (index === -1) {
    return null;
  }
  return argv[index + 1] ?? null;
};

const normalizeFontFamily = (value) => {
  if (!value) {
    return null;
  }
  const first = value.split(',')[0] ?? '';
  return first.replace(/['"]/g, '').trim();
};

const formatTimestamp = (date) => {
  const pad = (value) => String(value).padStart(2, '0');
  return [
    date.getFullYear(),
    pad(date.getMonth() + 1),
    pad(date.getDate()),
    pad(date.getHours()),
    pad(date.getMinutes()),
    pad(date.getSeconds()),
  ].join('-');
};

const formatDuration = (ms) => {
  const totalSeconds = Math.max(0, Math.floor(ms / 1000));
  const days = Math.floor(totalSeconds / 86400);
  const hours = Math.floor((totalSeconds % 86400) / 3600);
  const minutes = Math.floor((totalSeconds % 3600) / 60);
  return `${days}d ${hours}h ${minutes}m`;
};

const withTimeout = async (promise, ms) => {
  const controller = new AbortController();
  const timeout = setTimeout(() => controller.abort(), ms);
  try {
    return await promise(controller.signal);
  } finally {
    clearTimeout(timeout);
  }
};

const probeUrl = async (url) => {
  try {
    const response = await withTimeout(
      (signal) => fetch(url, { method: 'GET', redirect: 'manual', signal }),
      2000
    );
    return {
      ok: true,
      status: response.status,
    };
  } catch (error) {
    return {
      ok: false,
      status: null,
      error,
    };
  }
};

const findBaseUrl = async () => {
  const envUrl = process.env.APP_URL || process.env.BASE_URL;
  if (envUrl) {
    const url = envUrl.endsWith('/') ? envUrl.slice(0, -1) : envUrl;
    const result = await probeUrl(url);
    if (result.ok) {
      return { url, status: result.status, source: 'env' };
    }
  }

  const candidatePorts = [
    process.env.APP_PORT,
    '8000',
    '8001',
    '8080',
    '5173',
    '3000',
  ].filter(Boolean);

  for (const port of candidatePorts) {
    const url = `http://127.0.0.1:${port}`;
    const result = await probeUrl(url);
    if (result.ok) {
      return { url, status: result.status, source: `port:${port}` };
    }
  }

  return null;
};

const collectFontSnapshot = async (page) => {
  return page.evaluate(() => {
    const selectors = [
      'body',
      'h1',
      'h2',
      'h3',
      'p',
      'button',
      'input',
      'label',
      'a',
      'nav',
      'table',
      'th',
      'td',
    ];

    const entries = selectors.map((selector) => {
      const element = document.querySelector(selector);
      if (!element) {
        return [selector, null];
      }
      const styles = window.getComputedStyle(element);
      return [selector, {
        fontFamily: styles.fontFamily,
        fontSize: styles.fontSize,
        fontWeight: styles.fontWeight,
      }];
    });

    return Object.fromEntries(entries);
  });
};

const pickDominantFont = (fonts) => {
  const counts = new Map();
  for (const font of fonts) {
    if (!font) {
      continue;
    }
    counts.set(font, (counts.get(font) ?? 0) + 1);
  }
  let dominant = null;
  let max = 0;
  for (const [font, count] of counts.entries()) {
    if (count > max) {
      dominant = font;
      max = count;
    }
  }
  return dominant;
};

const bucketByMonth = (dates) => {
  const buckets = new Map();
  for (const date of dates) {
    const key = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`;
    buckets.set(key, (buckets.get(key) ?? 0) + 1);
  }
  return [...buckets.entries()].sort((a, b) => a[0].localeCompare(b[0]));
};

const renderDistributionTable = (rows) => {
  if (!rows.length) {
    return '<p class="muted">No data available.</p>';
  }

  const maxValue = Math.max(...rows.map(([, count]) => count));
  const renderBar = (value) => {
    const percent = maxValue === 0 ? 0 : Math.round((value / maxValue) * 100);
    return `<div class="bar"><span style="width:${percent}%"></span></div>`;
  };

  return `
    <table>
      <thead>
        <tr>
          <th>Month</th>
          <th>Count</th>
          <th>Distribution</th>
        </tr>
      </thead>
      <tbody>
        ${rows.map(([month, count]) => `
          <tr>
            <td>${month}</td>
            <td>${count}</td>
            <td>${renderBar(count)}</td>
          </tr>
        `).join('')}
      </tbody>
    </table>
  `;
};

const getGitCommitStats = () => {
  try {
    // Quote the pretty format so the pipe characters are passed to git, not the shell
    const output = execSync("git log --pretty='%H|%cI|%s'", { encoding: 'utf8' }).trim();
    if (!output) {
      return null;
    }
    const commits = output.split('\n').map((line) => {
      const [hash, dateIso, message] = line.split('|');
      return {
        hash,
        date: new Date(dateIso),
        message,
      };
    }).filter((entry) => !Number.isNaN(entry.date.getTime()));

    if (!commits.length) {
      return null;
    }

    const latest = commits[0];
    const earliest = commits[commits.length - 1];
    const durationMs = latest.date.getTime() - earliest.date.getTime();
    const distribution = bucketByMonth(commits.map((entry) => entry.date));

    return {
      total: commits.length,
      earliest: earliest.date.toISOString(),
      latest: latest.date.toISOString(),
      duration: formatDuration(durationMs),
      distribution,
    };
  } catch (error) {
    return { error: error?.message ?? String(error) };
  }
};

const fetchGitHubItems = async ({ query, token, maxPages = 2 }) => {
  const headers = {
    Accept: 'application/vnd.github+json',
  };
  if (token) {
    headers.Authorization = `Bearer ${token}`;
  }

  const items = [];
  for (let page = 1; page <= maxPages; page += 1) {
    const response = await fetch(
      `https://api.github.com/search/issues?q=${encodeURIComponent(query)}&per_page=100&page=${page}`,
      { headers }
    );
    if (!response.ok) {
      throw new Error(`GitHub API error (${response.status})`);
    }
    const payload = await response.json();
    if (!payload.items?.length) {
      break;
    }
    items.push(...payload.items);
    if (payload.items.length < 100) {
      break;
    }
  }

  return items;
};

const getGitHubStats = async ({ notes }) => {
  const repoSlug = process.env.GITHUB_REPOSITORY || 'Bahrain-TV/alsaryatv';
  const token = process.env.GITHUB_TOKEN || process.env.GH_TOKEN || process.env.GITHUB_PAT;
  const maxPages = Number(process.env.GITHUB_MAX_PAGES || 2);

  if (!token) {
    notes.push('GitHub token not provided. PR/issue distribution may be limited or skipped.');
  }

  try {
    const [owner, repo] = repoSlug.split('/');
    if (!owner || !repo) {
      throw new Error('Invalid repository slug');
    }

    const prItems = await fetchGitHubItems({
      query: `repo:${owner}/${repo} is:pr`,
      token,
      maxPages,
    });

    const issueItems = await fetchGitHubItems({
      query: `repo:${owner}/${repo} is:issue`,
      token,
      maxPages,
    });

    const prDates = prItems.map((item) => new Date(item.created_at)).filter((date) => !Number.isNaN(date.getTime()));
    const issueDates = issueItems.map((item) => new Date(item.created_at)).filter((date) => !Number.isNaN(date.getTime()));

    return {
      repo: repoSlug,
      prs: {
        total: prItems.length,
        distribution: bucketByMonth(prDates),
      },
      issues: {
        total: issueItems.length,
        distribution: bucketByMonth(issueDates),
      },
    };
  } catch (error) {
    notes.push(`GitHub data fetch failed: ${error?.message ?? String(error)}`);
    return null;
  }
};

const renderReport = (report) => {
  const escapeHtml = (value) => String(value)
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#39;');

  const renderFontRows = (fonts) => Object.entries(fonts)
    .map(([selector, details]) => {
      if (!details) {
        return `<tr><td>${escapeHtml(selector)}</td><td colspan="3">Not found</td></tr>`;
      }
      return `
        <tr>
          <td>${escapeHtml(selector)}</td>
          <td>${escapeHtml(details.fontFamily)}</td>
          <td>${escapeHtml(details.fontSize)}</td>
          <td>${escapeHtml(details.fontWeight)}</td>
        </tr>
      `;
    }).join('');

  const renderPageCards = (pages) => pages.map((page) => {
    const statusClass = page.status === 'ok' ? 'ok' : 'warn';
    const fontsTable = page.fontSnapshot
      ? `
        <table>
          <thead>
            <tr>
              <th>Selector</th>
              <th>Font family</th>
              <th>Font size</th>
              <th>Weight</th>
            </tr>
          </thead>
          <tbody>
            ${renderFontRows(page.fontSnapshot)}
          </tbody>
        </table>
      `
      : '<p class="muted">No font snapshot collected.</p>';

    const screenshotMarkup = page.screenshot
      ? `<img src="${escapeHtml(page.screenshot)}" alt="${escapeHtml(page.title)}">`
      : '<p class="muted">No screenshot.</p>';

    const errorMarkup = page.error
      ? `<p class="error">${escapeHtml(page.error)}</p>`
      : '';

    const fontSummary = page.fontSummary
      ? `<p><strong>Dominant font:</strong> ${escapeHtml(page.fontSummary.dominant || 'Unknown')}</p>
         <p><strong>Unique fonts:</strong> ${escapeHtml(page.fontSummary.unique.join(', ') || 'None')}</p>
         <p><strong>Inconsistencies:</strong> ${escapeHtml(page.fontSummary.inconsistencies.join(', ') || 'None')}</p>`
      : '';

    return `
      <section class="card ${statusClass}">
        <header>
          <h3>${escapeHtml(page.title)}</h3>
          <p class="muted">${escapeHtml(page.url)}</p>
          <p><strong>Status:</strong> ${escapeHtml(page.status)}</p>
          ${errorMarkup}
        </header>
        <div class="grid">
          <div class="snapshot">
            ${screenshotMarkup}
          </div>
          <div class="fonts">
            ${fontSummary}
            ${fontsTable}
          </div>
        </div>
      </section>
    `;
  }).join('');

  const activity = report.activity;
  const commitSummary = activity?.commits
    ? `
      <div class="stack">
        <p><strong>Total commits:</strong> ${activity.commits.total}</p>
        <p><strong>First commit:</strong> ${escapeHtml(activity.commits.earliest)}</p>
        <p><strong>Last commit:</strong> ${escapeHtml(activity.commits.latest)}</p>
        <p><strong>Duration:</strong> ${escapeHtml(activity.commits.duration)}</p>
      </div>
      <h4>Commit distribution</h4>
      ${renderDistributionTable(activity.commits.distribution)}
    `
    : '<p class="muted">Commit history unavailable.</p>';

  const prSummary = activity?.github?.prs
    ? `
      <p><strong>PRs captured:</strong> ${activity.github.prs.total}</p>
      <h4>PR distribution</h4>
      ${renderDistributionTable(activity.github.prs.distribution)}
    `
    : '<p class="muted">PR data unavailable.</p>';

  const issueSummary = activity?.github?.issues
    ? `
      <p><strong>Issues captured:</strong> ${activity.github.issues.total}</p>
      <h4>Issue distribution</h4>
      ${renderDistributionTable(activity.github.issues.distribution)}
    `
    : '<p class="muted">Issue data unavailable.</p>';

  return `
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>UI Audit Report</title>
  <style>
    :root {
      color-scheme: light;
      --bg: #f6f4ef;
      --card: #ffffff;
      --text: #1f2124;
      --muted: #6c7178;
      --border: #e4dfd7;
      --accent: #3a6b5e;
      --warn: #d9480f;
    }
    body {
      margin: 0;
      font-family: "IBM Plex Sans", "Segoe UI", sans-serif;
      background: var(--bg);
      color: var(--text);
    }
    header.report {
      padding: 32px 48px 16px;
      border-bottom: 1px solid var(--border);
      background: linear-gradient(120deg, #f4ede2, #f0f7f2);
    }
    header.report h1 {
      margin: 0 0 8px;
      font-size: 32px;
    }
    header.report p {
      margin: 4px 0;
      color: var(--muted);
    }
    main {
      padding: 24px 48px 48px;
      display: flex;
      flex-direction: column;
      gap: 24px;
    }
    section.card {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: 16px;
      padding: 20px;
      box-shadow: 0 12px 30px rgba(31, 33, 36, 0.08);
    }
    section.card.ok {
      border-left: 6px solid var(--accent);
    }
    section.card.warn {
      border-left: 6px solid var(--warn);
    }
    section.card header h3 {
      margin: 0 0 4px;
      font-size: 22px;
    }
    section.card header p {
      margin: 4px 0;
      color: var(--muted);
    }
    .grid {
      display: grid;
      grid-template-columns: minmax(320px, 1fr) minmax(280px, 0.8fr);
      gap: 24px;
      align-items: start;
      margin-top: 16px;
    }
    .snapshot img {
      width: 100%;
      border-radius: 12px;
      border: 1px solid var(--border);
      background: #fff;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 14px;
    }
    th, td {
      text-align: left;
      padding: 8px 10px;
      border-bottom: 1px solid var(--border);
    }
    th {
      background: #f2efe9;
    }
    .bar {
      width: 100%;
      height: 8px;
      background: #efe9df;
      border-radius: 999px;
      overflow: hidden;
    }
    .bar span {
      display: block;
      height: 100%;
      background: var(--accent);
    }
    .stack {
      display: grid;
      gap: 6px;
      margin-bottom: 12px;
    }
    h4 {
      margin: 18px 0 8px;
    }
    .muted {
      color: var(--muted);
      font-size: 14px;
    }
    .error {
      color: var(--warn);
      font-weight: 600;
    }
    @media (max-width: 960px) {
      header.report, main {
        padding: 20px;
      }
      .grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
  <header class="report">
    <h1>UI Audit Report</h1>
    <p><strong>Base URL:</strong> ${escapeHtml(report.baseUrl)}</p>
    <p><strong>Detected port source:</strong> ${escapeHtml(report.portSource)}</p>
    <p><strong>Start time:</strong> ${escapeHtml(report.startedAt)}</p>
    <p><strong>End time:</strong> ${escapeHtml(report.finishedAt)}</p>
  </header>
  <main>
    <section class="card">
      <header>
        <h3>Summary</h3>
        <p class="muted">Pages captured: ${report.pages.length}</p>
        <p class="muted">Admin pages captured: ${report.pages.filter((page) => page.group === 'admin').length}</p>
        <p class="muted">Front end pages captured: ${report.pages.filter((page) => page.group === 'front-end').length}</p>
        <p class="muted">Warnings: ${report.pages.filter((page) => page.status !== 'ok').length}</p>
        ${report.notes.length ? `<p><strong>Notes:</strong> ${escapeHtml(report.notes.join(' | '))}</p>` : ''}
      </header>
    </section>
    <section class="card">
      <header>
        <h3>Activity Timeline</h3>
        <p class="muted">Commits, PRs, and issues distribution across the repository timeline.</p>
      </header>
      <div class="grid">
        <div>
          <h4>Commits</h4>
          ${commitSummary}
        </div>
        <div>
          <h4>PRs and Issues</h4>
          ${prSummary}
          ${issueSummary}
        </div>
      </div>
    </section>
    ${renderPageCards(report.pages)}
  </main>
</body>
</html>
  `;
};

const summarizeFonts = (fontSnapshot) => {
  if (!fontSnapshot) {
    return null;
  }

  const normalized = Object.values(fontSnapshot)
    .map((entry) => normalizeFontFamily(entry?.fontFamily))
    .filter(Boolean);

  const unique = [...new Set(normalized)];
  const dominant = pickDominantFont(normalized);
  const inconsistencies = unique.filter((font) => font !== dominant);

  return {
    dominant,
    unique,
    inconsistencies,
  };
};

const capturePage = async ({ page, url, title, outputDir, group }) => {
  const record = {
    title,
    url,
    group,
    status: 'ok',
    screenshot: null,
    fontSnapshot: null,
    fontSummary: null,
    error: null,
  };

  try {
    await page.goto(url, { waitUntil: 'networkidle', timeout: 30000 });
    await page.waitForTimeout(500);
    const safeName = title.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
    const screenshotPath = path.join(outputDir, `${safeName || 'page'}.png`);
    await page.screenshot({ path: screenshotPath, fullPage: true });
    record.screenshot = path.relative(process.cwd(), screenshotPath);
    const fontSnapshot = await collectFontSnapshot(page);
    record.fontSnapshot = fontSnapshot;
    record.fontSummary = summarizeFonts(fontSnapshot);
  } catch (error) {
    record.status = 'error';
    record.error = error?.message ?? String(error);
  }

  return record;
};

const maybeLoginToAdmin = async ({ page, baseUrl }) => {
  const adminUrl = `${baseUrl}/admin`;
  await page.goto(adminUrl, { waitUntil: 'domcontentloaded', timeout: 30000 });

  const hasEmailField = await page.locator('input[name="email"]').count();
  if (!hasEmailField) {
    return { loggedIn: true, loginNeeded: false };
  }

  const email = process.env.ADMIN_EMAIL;
  const password = process.env.ADMIN_PASSWORD;
  if (!email || !password) {
    return { loggedIn: false, loginNeeded: true };
  }

  await page.fill('input[name="email"]', email);
  await page.fill('input[name="password"]', password);

  const submitButton = page.locator('button[type="submit"], button:has-text("Login"), button:has-text("Sign in")');
  if (await submitButton.count()) {
    await submitButton.first().click();
  } else {
    await page.keyboard.press('Enter');
  }

  await page.waitForTimeout(1000);
  return { loggedIn: true, loginNeeded: true };
};

const collectAdminLinks = async ({ page, baseUrl }) => {
  const links = await page.$$eval('a[href]', (anchors) => anchors
    .map((anchor) => anchor.getAttribute('href'))
    .filter(Boolean));

  const normalized = new Set();
  for (const link of links) {
    if (link.startsWith('http')) {
      if (link.startsWith(baseUrl)) {
        normalized.add(link);
      }
      continue;
    }
    if (!link.startsWith('/')) {
      continue;
    }
    normalized.add(`${baseUrl}${link}`);
  }

  return [...normalized].filter((link) => link.includes('/admin'));
};

const main = async () => {
  const baseUrlArg = getArgValue('--base-url');
  const outputRoot = getArgValue('--output-dir') || 'reports/ui-audit';

  const baseResult = baseUrlArg
    ? { url: baseUrlArg.replace(/\/$/, ''), status: null, source: 'arg' }
    : await findBaseUrl();

  if (!baseResult) {
    console.error('No running server detected. Set APP_URL or use --base-url.');
    process.exit(1);
  }

  const baseUrl = baseResult.url;
  const startedAt = new Date();
  const outputDir = path.resolve(outputRoot, formatTimestamp(startedAt));
  const screenshotDir = path.join(outputDir, 'screenshots');
  await mkdir(screenshotDir, { recursive: true });

  const notes = [];

  const browser = await chromium.launch();
  const context = await browser.newContext({ viewport: { width: 1440, height: 900 } });
  const page = await context.newPage();

  const frontEndPages = [
    { title: 'Splash', path: '/splash' },
    { title: 'Home', path: '/' },
    { title: 'Welcome', path: '/welcome' },
    { title: 'Family Registration', path: '/family' },
    { title: 'Registration Form', path: '/register' },
    { title: 'Privacy Policy', path: '/privacy' },
    { title: 'CSRF Test Page', path: '/csrf-test' },
    { title: 'Caller Create', path: '/callers/create' },
  ];

  // Helper function to submit a form and capture the success page
  const submitRegistrationForm = async (page, baseUrl, formType) => {
    const pages = [];

    try {
      // First, log in as a test user to ensure registration form is visible
      console.log(`  Logging in test user for ${formType} form submission...`);
      await page.goto(`${baseUrl}/login`, { waitUntil: 'networkidle', timeout: 30000 });

      const emailInput = page.locator('input[name="email"]');
      const passwordInput = page.locator('input[name="password"]');
      const loginBtn = page.locator('button[type="submit"]');

      if (await emailInput.count() > 0 && await passwordInput.count() > 0) {
        await emailInput.waitFor({ state: 'visible' });
        await passwordInput.waitFor({ state: 'visible' });
        await emailInput.fill('test@example.com');
        await passwordInput.fill('password123');

        if (await loginBtn.count() > 0) {
          const navigationPromise = page.waitForNavigation({ waitUntil: 'networkidle' }).catch(() => null);
          await loginBtn.click();
          await navigationPromise;
          await page.waitForTimeout(1000);
        }
      }

      // Now navigate to the home page
      await page.goto(`${baseUrl}/`, { waitUntil: 'networkidle', timeout: 30000 });
      await page.waitForTimeout(1000);

      // Wait for the registration form to be visible
      const registrationForm = page.locator('.registration-form');
      if (await registrationForm.count() === 0) {
        throw new Error('Registration form not found on page');
      }
      await registrationForm.waitFor({ state: 'visible' });

      // Toggle to family if needed
      if (formType === 'family') {
        const familyBtn = page.locator('#family-toggle');
        if (await familyBtn.count() > 0) {
          await familyBtn.click();
          await page.waitForTimeout(1500);
        }
      }

      // Fill form fields using Playwright locators
      const nameInput = page.locator('#name');
      const cprInput = page.locator('#cpr');
      const phoneInput = page.locator('#phone_number');

      // Wait for inputs to be visible
      if (await nameInput.count() > 0) {
        await nameInput.waitFor({ state: 'visible' });
        await nameInput.fill('أحمد محمد');
      }

      if (await cprInput.count() > 0) {
        await cprInput.waitFor({ state: 'visible' });
        const cpr = formType === 'family' ? '98765432109' : '12345678901';
        await cprInput.fill(cpr);
      }

      if (await phoneInput.count() > 0) {
        await phoneInput.waitFor({ state: 'visible' });
        await phoneInput.fill('+97366123456');
      }

      // Fill family fields if family registration
      if (formType === 'family') {
        const familyNameInput = page.locator('#family_name');
        const familyMembersInput = page.locator('#family_members');

        if (await familyNameInput.count() > 0) {
          await familyNameInput.waitFor({ state: 'visible' });
          await familyNameInput.fill('عائلة أحمد');
        }
        if (await familyMembersInput.count() > 0) {
          await familyMembersInput.waitFor({ state: 'visible' });
          await familyMembersInput.fill('4');
        }
      }

      // Submit the form
      const submitBtn = page.locator('button[type="submit"]');
      if (await submitBtn.count() > 0) {
        await submitBtn.waitFor({ state: 'visible' });
        // Wait for potential navigation
        const navigationPromise = page.waitForNavigation({ waitUntil: 'networkidle' }).catch(() => null);
        await submitBtn.click();
        await navigationPromise;
        await page.waitForTimeout(1500);
      } else {
        throw new Error('Submit button not found on form');
      }

      // Capture the success page
      const screenshotPath = path.join(screenshotDir, `success-page-${formType}.png`);
      await page.screenshot({ path: screenshotPath, fullPage: true });
      const fontSnapshot = await collectFontSnapshot(page);

      pages.push({
        title: `Front End - Success Page (${formType === 'family' ? 'Family' : 'Individual'} Registration)`,
        url: page.url(),
        group: 'front-end',
        status: 'ok',
        screenshot: path.relative(process.cwd(), screenshotPath),
        fontSnapshot: fontSnapshot,
        fontSummary: summarizeFonts(fontSnapshot),
        error: null,
      });
    } catch (error) {
      pages.push({
        title: `Front End - Success Page (${formType === 'family' ? 'Family' : 'Individual'} Registration)`,
        url: `${baseUrl}/success`,
        group: 'front-end',
        status: 'error',
        screenshot: null,
        fontSnapshot: null,
        fontSummary: null,
        error: error?.message ?? String(error),
      });
    }

    return pages;
  };

  const adminPaths = [
    { title: 'Admin Home', path: '/admin' },
    { title: 'Dashboard', path: '/dashboard' },
    { title: 'Winners', path: '/winners' },
    { title: 'Families', path: '/families' },
    { title: 'Callers Resource', path: '/admin/callers' },
    { title: 'Callers Winners', path: '/admin/callers/winners' },
  ];

  const pages = [];

  for (const pageInfo of frontEndPages) {
    pages.push(await capturePage({
      page,
      url: `${baseUrl}${pageInfo.path}`,
      title: `Front End - ${pageInfo.title}`,
      outputDir: screenshotDir,
      group: 'front-end',
    }));
  }

  // Add form submission tests
  console.log('Capturing form submission screenshots...');
  const individualSubmissionPages = await submitRegistrationForm(page, baseUrl, 'individual');
  pages.push(...individualSubmissionPages);

  const familySubmissionPages = await submitRegistrationForm(page, baseUrl, 'family');
  pages.push(...familySubmissionPages);

  const adminLogin = await maybeLoginToAdmin({ page, baseUrl });
  if (!adminLogin.loggedIn) {
    notes.push('Admin login required. Provide ADMIN_EMAIL and ADMIN_PASSWORD to include admin pages.');
  }

  if (adminLogin.loggedIn) {
    const adminLinks = await collectAdminLinks({ page, baseUrl });
    const adminQueue = [...adminPaths.map((item) => `${baseUrl}${item.path}`), ...adminLinks];
    const uniqueAdminQueue = [...new Set(adminQueue)];

    for (const adminUrl of uniqueAdminQueue) {
      const title = adminUrl.replace(baseUrl, '').replace('/', '') || 'admin';
      pages.push(await capturePage({
        page,
        url: adminUrl,
        title: `Admin - ${title}`,
        outputDir: screenshotDir,
        group: 'admin',
      }));
    }
  }

  const commitStats = getGitCommitStats();
  if (commitStats?.error) {
    notes.push(`Git history unavailable: ${commitStats.error}`);
  }
  const githubStats = await getGitHubStats({ notes });

  await browser.close();

  const finishedAt = new Date();
  const report = {
    baseUrl,
    portSource: baseResult.source ?? 'unknown',
    startedAt: startedAt.toISOString(),
    finishedAt: finishedAt.toISOString(),
    pages,
    notes,
    activity: {
      commits: commitStats && !commitStats.error ? commitStats : null,
      github: githubStats,
    },
  };

  const reportHtml = renderReport(report);
  const reportPath = path.join(outputDir, 'report.html');
  await writeFile(reportPath, reportHtml, 'utf8');

  console.log(`Report generated at ${reportPath}`);
};

main().catch((error) => {
  console.error(error);
  process.exit(1);
});
