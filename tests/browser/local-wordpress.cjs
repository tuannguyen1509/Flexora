const http = require('http');
const { chromium } = require('playwright');
const origin = process.env.FLX_URL || 'http://localhost:8090';
const port = Number(new URL(origin).port || 80);
const theme = process.env.FLX_TEST_THEME || 'flexora';
let count = 0;
function check(value, message) { if (!value) throw new Error(message); count++; }

// Container localhost transport only; every response comes from the real WP service.
const proxy = http.createServer((request, response) => {
  const upstream = http.request({ hostname: 'wordpress', port: 80, path: request.url, method: request.method, headers: request.headers }, result => {
    response.writeHead(result.statusCode, result.headers);
    result.pipe(response);
  });
  upstream.on('error', error => { response.writeHead(502); response.end(error.message); });
  request.pipe(upstream);
});
(async () => {
  await new Promise(resolve => proxy.listen(port, '127.0.0.1', resolve));
  const browser = await chromium.launch({ headless: true });
  try {
    const page = await browser.newPage();
    const errors = [];
    const consoleErrors = [];
    const responses = [];
    page.on('pageerror', error => errors.push(error.message));
    page.on('console', message => { if (message.type() === 'error') consoleErrors.push(message.text()); });
    page.on('response', response => responses.push({ url: response.url(), status: response.status() }));
    const response = await page.goto(origin + '/?flx_probe=1', { waitUntil: 'networkidle' });
    check(response.status() === 200, 'homepage HTTP200');
    check((await page.title()).includes('Flexora Development'), 'real WordPress site title');
    check(await page.locator('.flx-skip-link').count() === 1, 'exactly one skip link');
    check(await page.locator('main').count() === 1, 'semantic main present');
    check(await page.evaluate(() => typeof window.FLXSkipLink.init === 'function'), 'actual behavior script executed');
    check(await page.locator('.flx-skip-link').getAttribute('href') === '#flx-main-content', 'link targets actual main');
    const before = await page.locator('.flx-skip-link').boundingBox();
    check(before.y + before.height <= 0, 'unfocused link outside viewport');
    await page.keyboard.press('Tab');
    check(await page.locator('.flx-skip-link').evaluate(element => document.activeElement === element), 'first keyboard tab focuses skip link');
    const focused = await page.locator('.flx-skip-link').boundingBox();
    check(focused.y >= 0, 'focused link visible in viewport');
    check(await page.locator('.flx-skip-link').evaluate(element => getComputedStyle(element).outlineStyle === 'solid'), 'focus ring style actually loaded');
    await page.keyboard.press('Enter');
    check(await page.locator('main').evaluate(element => document.activeElement === element), 'keyboard activation focuses real main');
    check(await page.locator('main').getAttribute('tabindex') === '-1', 'main made programmatically focusable');
    check(responses.filter(item => item.url.includes('/flexora/assets/accessibility/skip-link.css') && item.status === 200).length === 1, 'CSS requested successfully once');
    check(responses.filter(item => item.url.includes('/flexora/assets/accessibility/skip-link.js') && item.status === 200).length === 1, 'JS requested successfully once');
    check(errors.length === 0, 'no uncaught JS errors: ' + errors.join(';'));
    check(consoleErrors.length === 0, 'no console errors: ' + consoleErrors.join(';'));
    const html = await page.content();
    const match = html.match(/FLX_LOCAL_PROBE ([A-Za-z0-9+/=]+)/);
    check(!!match, 'real server probe present');
    const probe = JSON.parse(Buffer.from(match[1], 'base64').toString());
    check(probe.stylesheet === theme, 'browser used requested active theme');
    await page.emulateMedia({ reducedMotion: 'reduce' });
    check(await page.evaluate(() => getComputedStyle(document.documentElement).scrollBehavior === 'auto'), 'reduced motion actual computed scroll behavior');
    process.stdout.write(`PASS browser ${theme}: ${count} assertions; Chromium ${browser.version()}\n`);
  } finally { await browser.close(); }
})().catch(error => { console.error(error); process.exitCode = 1; }).finally(() => proxy.close());
