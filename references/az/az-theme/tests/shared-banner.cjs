// Run with NODE_PATH pointing to php-parser and jsdom (test-only dependencies).
const fs = require('node:fs');
const path = require('node:path');
const assert = require('node:assert/strict');
const { JSDOM } = require('jsdom');
const Parser = require('php-parser');
const root = path.resolve(__dirname, '..');
const parser = new Parser({ parser: { phpVersion: '7.4', suppressErrors: false } });
for (const file of ['inc/core/shared-banner.php', 'inc/bootstrap.php', 'header.php', 'page.php', 'page-contact.php', 'page-neutral.php', 'page-goc-chuyen-gia.php', 'archive.php', 'archive-product.php', 'front-page.php']) {
  parser.parseCode(fs.readFileSync(path.join(root, file), 'utf8'), file);
}
console.log('PASS PHP syntax: 10 files (PHP 7.4 grammar)');
function fixture(reduce = false) {
  const dom = new JSDOM(`<header class="az-site-header"></header><section class="az-shared-banner" data-autoplay="1" data-overlay-header="1"><img data-slide="0"><img data-slide="1" style="display:none"><div class="az-shared-banner__controls" hidden><button data-direction="-1"></button><span data-slide-status></span><button data-direction="1"></button><button data-pause></button></div></section>`, { runScripts: 'outside-only', pretendToBeVisual: true });
  const w = dom.window; let tick = null;
  w.matchMedia = () => ({ matches: reduce, addEventListener() {} });
  w.setInterval = fn => { tick = fn; return 1; }; w.clearInterval = () => { tick = null; };
  w.eval(fs.readFileSync(path.join(root, 'js/shared-banner.js'), 'utf8'));
  return { w, doc: w.document, tick: () => tick };
}
const test = fixture();
const { doc, w } = test;
assert.equal(doc.querySelector('.az-shared-banner__controls').hidden, false);
assert.ok(doc.body.classList.contains('az-banner-overlay-ready'));
assert.ok(test.tick()); test.tick()();
assert.equal(doc.querySelector('[data-slide-status]').textContent, '2 / 2');
doc.querySelector('[data-direction="1"]').click();
assert.equal(doc.querySelector('[data-slide-status]').textContent, '1 / 2');
doc.querySelector('[data-direction="-1"]').click();
assert.equal(doc.querySelector('[data-slide-status]').textContent, '2 / 2');
doc.querySelector('[data-pause]').click(); assert.equal(test.tick(), null);
doc.querySelector('[data-pause]').click(); assert.ok(test.tick());
doc.querySelector('.az-shared-banner').dispatchEvent(new w.Event('mouseenter')); assert.equal(test.tick(), null);
doc.querySelector('.az-shared-banner').dispatchEvent(new w.Event('mouseleave')); assert.ok(test.tick());
doc.querySelector('[data-direction="1"]').focus(); assert.equal(test.tick(), null);
doc.querySelector('[data-direction="1"]').blur(); assert.ok(test.tick());
assert.equal(fixture(true).tick(), null);
console.log('PASS slider: wrap, manual, autoplay, pause, hover, focus, reduced motion; header overlay init');
const css = fs.readFileSync(path.join(root, 'css/shared-banner.css'), 'utf8');
assert.ok(!/#[a-f\d]{3,8}\b|rgba?\(/i.test(css), 'No hardcoded colour values');
assert.ok(css.includes('--az-container') && css.includes('--az-mobile-gutter'));
console.log('PASS shared colour and layout tokens');
const admin = new JSDOM(`<div class="az-banner-editor"><input data-az-banner-images value="10,20"><div data-az-banner-preview><img data-id="10"><img data-id="20"></div><button data-az-banner-pick></button><button data-az-banner-clear></button></div>`, { runScripts: 'outside-only' });
admin.window.eval(fs.readFileSync(path.join(root, 'assets/admin/js/az-banner-editor.js'), 'utf8'));
const ad = admin.window.document;
ad.querySelector('[aria-label="Đưa ảnh về sau"]').click();
assert.equal(ad.querySelector('input').value, '20,10');
ad.querySelector('[aria-label="Xóa ảnh"]').click();
assert.equal(ad.querySelector('input').value, '10');
ad.querySelector('[data-az-banner-clear]').click();
assert.equal(ad.querySelector('input').value, '');
assert.equal(ad.querySelectorAll('img').length, 0);
console.log('PASS admin image reorder, remove, clear');
