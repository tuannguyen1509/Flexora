/**
 * Behavioral cases ported/adapted from references/az/az-theme/tests/mobile-menu.cjs.
 * Real Chromium DOM runs the production FLX behavior, not a reimplementation.
 * Modal wrapping/scroll-lock/style-variant cases are explicitly deferred in the reuse audit.
 */
const fs = require('node:fs');
const { chromium } = require('playwright');
const base = '/workspace/';
const source = fs.readFileSync(base + 'flexora/assets/header/header.js', 'utf8');
const css = fs.readFileSync(base + 'flexora/assets/header/header.css', 'utf8');
let browser, assertions = 0;
const results = [];
function check(value, label) { if (!value) throw Error(label); assertions++; results.push(label); }
function markup(mode = 'nested') {
    const nested = mode === 'nested' ? '<ul class="sub-menu"><li><a href="#destination">Child</a><ul class="sub-menu"><li><a href="#destination">Grandchild</a></li></ul></li></ul>' : '';
    const sibling = mode === 'nested' ? '<ul class="sub-menu"><li><a href="#destination">Other child</a></li></ul>' : '';
    const nav = '<nav class="flx-nav" data-flx-navigation id="flx-test-nav" data-flx-submenu-open-label="Mở menu con" data-flx-submenu-close-label="Đóng menu con"><ul class="flx-nav__list"><li><a href="#destination">Parent</a>' + nested + '</li><li><a href="#destination">Sibling</a>' + sibling + '</li></ul></nav>';
    return '<!doctype html><html><head><style>' + css + '</style></head><body><header class="flx-header" data-flx-header><div class="flx-header__inner flx-container"><a href="#destination">Home</a><button type="button" data-flx-menu-toggle hidden aria-expanded="false" aria-controls="' + (mode === 'mismatch' ? 'missing' : 'flx-test-nav') + '">Menu</button>' + (mode === 'missing' ? '' : nav) + '</div></header><main id="destination" style="min-height:1000px">Destination</main></body></html>';
}
(async function () {
    browser = await chromium.launch({ headless: true });
    const page = await browser.newPage({ viewport: { width: 390, height: 844 } });
    const errors = [];
    page.on('pageerror', error => errors.push(error.message));
    async function load(mode, script = true) { await page.setContent(markup(mode)); if (script) await page.addScriptTag({ content: source }); }
    await load('missing');
    check(await page.locator('[data-flx-header]').getAttribute('data-flx-enhanced') === null, 'missing navigation: safe no-op');
    check(await page.locator('[data-flx-menu-toggle]').isHidden(), 'missing navigation: no dead toggle');
    await load('mismatch');
    check(await page.locator('[data-flx-navigation]').isVisible(), 'missing controls target: retain visible no-JS navigation');
    check(await page.locator('[data-flx-header]').getAttribute('data-flx-enhanced') === null, 'mismatched controls target: no enhancement');
    await load('one-level');
    const toggle = page.locator('[data-flx-menu-toggle]'), nav = page.locator('[data-flx-navigation]');
    check(await page.locator('.flx-nav__disclosure').count() === 0, 'one-level menu: no fabricated submenu controls');
    await toggle.focus(); await page.keyboard.press('Enter');
    check(await nav.isVisible(), 'one-level menu: keyboard opens');
    await page.keyboard.press('Escape');
    check(await nav.isHidden() && await toggle.evaluate(el => document.activeElement === el), 'one-level menu: Escape closes and returns focus');
    await load('nested');
    const buttons = page.locator('.flx-nav__disclosure'), menus = page.locator('.sub-menu');
    check(await toggle.getAttribute('aria-expanded') === 'false', 'PORT closed trigger starts collapsed');
    check(await buttons.count() === 3 && await menus.evaluateAll(nodes => nodes.every(el => el.hidden)), 'PORT each nested branch starts collapsed with own toggle');
    check(await buttons.evaluateAll(nodes => new Set(nodes.map(el => el.getAttribute('aria-controls'))).size === nodes.length && nodes.every(el => document.getElementById(el.getAttribute('aria-controls')))), 'PORT nested controls reference unique targets');
    check(await buttons.first().getAttribute('aria-label') === 'Mở menu con: Parent', 'PORT translated collapsed label');
    await toggle.focus(); await page.keyboard.press('Space');
    check(await nav.isVisible() && await toggle.getAttribute('aria-expanded') === 'true', 'PORT root open state matches visibility');
    await buttons.first().focus(); await page.keyboard.press('Enter');
    check(await menus.nth(0).isVisible() && await nav.isVisible(), 'PORT submenu expands without closing root');
    check(await buttons.first().getAttribute('aria-label') === 'Đóng menu con: Parent', 'PORT expanded label changes');
    check(await menus.nth(1).isHidden(), 'PORT nested branch remains collapsed until opened');
    await buttons.nth(1).focus(); await page.keyboard.press('Space');
    check(await menus.nth(1).isVisible() && await buttons.nth(1).getAttribute('aria-expanded') === 'true', 'PORT supports a second nested navigation level');
    await page.keyboard.press('Tab');
    check(await page.locator('a').filter({ hasText: 'Grandchild' }).evaluate(el => document.activeElement === el), 'nested destination keyboard reachable');
    await page.keyboard.press('Escape');
    check(await menus.nth(1).isHidden() && await menus.nth(0).isVisible(), 'Escape closes innermost branch before root');
    check(await buttons.nth(1).evaluate(el => document.activeElement === el), 'Escape returns nested disclosure focus');
    await buttons.nth(1).click(); await buttons.first().click();
    check(await menus.nth(0).isHidden() && await menus.nth(1).isHidden(), 'PORT parent re-collapse hides nested branches');
    check(await buttons.nth(1).getAttribute('aria-expanded') === 'false', 'closing ancestor resets descendant expanded state');
    check(await buttons.first().getAttribute('aria-label') === 'Mở menu con: Parent', 'PORT collapsed label restored');
    await buttons.first().click(); await buttons.nth(2).click();
    check(await menus.nth(0).isVisible() && await menus.nth(2).isVisible(), 'independent sibling branches retain separate state');
    await buttons.nth(1).click();
    await page.locator('a').filter({ hasText: 'Grandchild' }).click();
    check(await nav.isHidden() && await toggle.getAttribute('aria-expanded') === 'false', 'PORT nested destination closes mobile navigation');
    check(await nav.evaluate(el => !el.contains(document.activeElement)), 'PORT destination close does not strand focus inside hidden navigation');
    check(await buttons.evaluateAll(nodes => nodes.every(el => el.getAttribute('aria-expanded') === 'false')), 'destination close resets all branches');
    // Inspect a real cancelable event without overriding native navigation.
    check(await page.evaluate(() => { const event = new MouseEvent('click', { bubbles: true, cancelable: true }); document.querySelector('.flx-nav a').dispatchEvent(event); return !event.defaultPrevented; }), 'link activation is not intercepted');
    await page.addScriptTag({ content: source });
    await page.evaluate(() => { FLXHeader.init(document); FLXHeader.init(document); });
    check(await buttons.count() === 3, 'repeated script/init: no duplicate disclosure controls');
    await toggle.click();
    check(await nav.isVisible() && await toggle.getAttribute('aria-expanded') === 'true', 'repeated init: one click opens once');
    await toggle.click();
    check(await nav.isHidden(), 'repeated init: one click closes once');
    await toggle.click(); await buttons.first().click();
    await page.setViewportSize({ width: 1024, height: 900 });
    await page.waitForFunction(() => document.querySelector('[data-flx-menu-toggle]').hidden);
    check(await nav.isVisible() && await toggle.getAttribute('aria-expanded') === 'false', 'PORT desktop breakpoint clears mobile open state');
    check(await buttons.evaluateAll(nodes => nodes.every(el => el.getAttribute('aria-expanded') === 'false')), 'resize clears nested branch state');
    // Port the Child viewport clamp arithmetic to an ordinary, non-mega submenu.
    await page.locator('.flx-nav__list').evaluate(el => { el.style.justifyContent = 'flex-end'; });
    await page.locator('.flx-nav__list > li').last().locator(':scope > a').focus();
    check(await menus.nth(2).evaluate(el => { const rect = el.getBoundingClientRect(); return rect.left >= 19 && rect.right <= document.documentElement.clientWidth - 19; }), 'ordinary submenu clamped to viewport');
    await page.setViewportSize({ width: 960, height: 900 });
    await page.waitForFunction(() => { const el = document.querySelectorAll('.sub-menu')[2], rect = el.getBoundingClientRect(); return el.hidden || rect.right <= document.documentElement.clientWidth - 19; });
    check(await menus.nth(2).isVisible(), 'same-breakpoint resize retains open submenu');
    check(await menus.nth(2).evaluate(el => el.getBoundingClientRect().right <= document.documentElement.clientWidth - 19), 'same-breakpoint resize repositions submenu');
    await page.setViewportSize({ width: 390, height: 844 });
    await page.waitForFunction(() => document.querySelector('[data-flx-navigation]').hidden);
    check(await nav.isHidden() && await toggle.isVisible(), 'PORT returning to mobile starts closed');
    await load('nested', false);
    check(await nav.isVisible() && await menus.evaluateAll(nodes => nodes.every(el => getComputedStyle(el).display !== 'none')), 'no-JS mobile nested navigation remains visible');
    check(await toggle.isHidden(), 'no-JS does not display inert toggle');
    check(errors.length === 0, 'no script errors across malformed, simple and nested fixtures');
    fs.writeFileSync(base + 'local/results/phase08-header-regression.json', JSON.stringify({ assertions, results, kind: 'isolated Chromium behavior fixtures; not WordPress integration' }, null, 2));
    console.log('PASS Header reuse regression: ' + assertions + ' browser assertions');
}()).catch(error => { console.error(error); process.exitCode = 1; }).finally(async () => { if (browser) await browser.close(); });
