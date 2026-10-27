/** Isolated scroll/click/reduced-motion behavior checks. */
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');
const source = fs.readFileSync(path.join(__dirname, '../js/az-site-effects.js'), 'utf8');
let checks = 0;
function check(condition, label) { assert.ok(condition, label); checks++; console.log(`PASS ${label}`); }
function fixture({ present = true, loading = false, reduceMotion = false, scrollY = 0, height = 2000 } = {}) {
  const classes = new Set();
  const events = {};
  const docEvents = {};
  const clicks = {};
  const button = {
    classList: {
      add(name) { classes.add(name); },
      toggle(name, on) { if (on) classes.add(name); else classes.delete(name); }
    },
    addEventListener(name, callback) { clicks[name] = callback; }
  };
  const window = {
    scrollY,
    matchMedia() { return { matches: reduceMotion }; },
    addEventListener(name, callback, options) { events[name] = { callback, options }; },
    scrollTo(value) { this.destination = value; }
  };
  const document = {
    scrollingElement: { scrollTop: 0, scrollHeight: height, clientHeight: 800 },
    readyState: loading ? 'loading' : 'complete',
    querySelector(selector) { return present && selector === '[data-az-back-to-top]' ? button : null; },
    addEventListener(name, callback) { docEvents[name] = callback; }
  };
  vm.runInNewContext(source, { window, document });
  return { window, document, classes, events, docEvents, clicks };
}
const f = fixture();
check(!f.classes.has('is-visible'), 'button is hidden at top of page');
check(f.classes.has('is-enhanced'), 'script activates conditional visibility only after initialization');
f.window.scrollY = 361;
f.events.scroll.callback();
check(f.classes.has('is-visible'), 'button appears when scrolling down');
check(f.events.scroll.options.passive, 'scroll listener is passive');
let prevented = false;
f.clicks.click({ preventDefault() { prevented = true; } });
check(f.window.destination.top === 0 && f.window.destination.behavior === 'smooth', 'click smoothly scrolls to top');
check(prevented, 'enhanced click replaces native anchor jump with smooth scrolling');
f.window.scrollY = 0;
f.events.scroll.callback();
check(!f.classes.has('is-visible'), 'button hides again at top');
const reduced = fixture({ reduceMotion: true });
reduced.clicks.click({ preventDefault() {} });
check(reduced.window.destination.behavior === 'auto', 'reduced-motion preference skips animated scrolling');
const deferred = fixture({ loading: true });
check(!deferred.events.scroll, 'initialization waits for DOM readiness');
check(!deferred.classes.has('is-enhanced'), 'delayed script does not apply hidden styling to native link');
deferred.docEvents.DOMContentLoaded();
check(!!deferred.events.scroll && !!deferred.clicks.click, 'DOMContentLoaded initializes scrolling');
check(!fixture({ present: false }).events.scroll, 'disabled button attaches no scroll listener');
const restored = fixture({ scrollY: 700 });
check(restored.classes.has('is-visible'), 'initializes as visible on a restored scrolled page');
restored.window.scrollY = 0;
restored.events.pageshow.callback();
check(!restored.classes.has('is-visible'), 'pageshow refreshes visibility after browser scroll restoration');
const short = fixture({ height: 1000, scrollY: 200 });
check(short.classes.has('is-visible'), 'button appears at footer even when page scroll range is below 360px');
short.window.scrollY = 100;
short.document.scrollingElement.scrollHeight = 2000;
short.events.resize.callback();
check(!short.classes.has('is-visible'), 'resize recalculates threshold for changed page height');
const fallback = fixture();
delete fallback.window.scrollY;
fallback.document.scrollingElement.scrollTop = 500;
fallback.events.scroll.callback();
check(fallback.classes.has('is-visible'), 'scrolling element offset works when scrollY is unavailable');
console.log(`${checks} checks passed.`);
