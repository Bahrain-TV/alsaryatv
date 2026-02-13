import { chromium } from 'playwright';
import { mkdir, writeFile } from 'node:fs/promises';

async function test() {
  const browser = await chromium.launch();
  const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });
  
  await page.goto('http://127.0.0.1:8000', { waitUntil: 'networkidle' });
  
  // Take a screenshot
  await mkdir('/tmp/debug-screenshots', { recursive: true });
  await page.screenshot({ path: '/tmp/debug-screenshots/home-page.png', fullPage: true });
  console.log('Screenshot saved to /tmp/debug-screenshots/home-page.png');
  
  // Check the HTML
  const html = await page.content();
  
  // Look for the registration form in the HTML
  if (html.includes('registration-form')) {
    console.log('Found "registration-form" in HTML');
  }
  
  if (html.includes('<form')) {
    console.log('Found <form tag in HTML');
  }
  
  if (html.includes('سجّل الآن')) {
    console.log('Found "سجّل الآن" button text');
  }
  
  // Find what's between the main-container divs
  const startIndex = html.indexOf('class="main-container"');
  if (startIndex !== -1) {
    const endIndex = html.indexOf('</div>', startIndex + 500);
    const snippet = html.substring(startIndex, Math.min(endIndex + 10, startIndex + 2000));
    console.log('Main container snippet:');
    console.log(snippet.substring(0, 500));
  }
  
  await browser.close();
}

test().catch(console.error);
