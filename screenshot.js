import { chromium } from 'playwright';

const url = process.argv[2] || 'http://127.0.0.1:8000';
const browser = await chromium.launch();
const page = await browser.newPage();
await page.goto(url, { waitUntil: 'networkidle', timeout: 30000 });
await page.screenshot({ path: 'screenshot.png', fullPage: true });
await browser.close();
console.log(`Screenshot taken: screenshot.png (${url})`);
