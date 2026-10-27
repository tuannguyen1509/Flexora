/* Dependency-free behavior fixture. DOM double; not a browser integration test. */
function runFLXSkipLinkTests(source) {
  var count = 0;
  function check(value, label) { if (!value) throw new Error(label); count++; }
  function element(tag, id) {
    return {
      tag: tag, id: id || '', hidden: false, attrs: {}, events: {}, focusCalls: [],
      hasAttribute: function (key) { return Object.prototype.hasOwnProperty.call(this.attrs, key); },
      setAttribute: function (key, value) { this.attrs[key] = value; },
      removeAttribute: function (key) { delete this.attrs[key]; },
      addEventListener: function (name, callback) { (this.events[name] || (this.events[name] = [])).push(callback); },
      focus: function (options) { this.focusCalls.push(options); }
    };
  }
  function fixture(links, main, other, ready) {
    var doc = {
      links: links, main: main, other: other || {}, readyState: ready || 'complete', events: {},
      querySelectorAll: function () { return this.links; },
      querySelector: function (selector) { return selector === 'main' ? this.main : this.other[selector] || null; },
      getElementById: function (id) {
        var nodes = [this.main].concat(Object.keys(this.other).map(function (key) { return doc.other[key]; }));
        return nodes.filter(function (node) { return node && node.id === id; })[0] || null;
      },
      addEventListener: function (name, callback) { (this.events[name] || (this.events[name] = [])).push(callback); }
    };
    var win = {};
    function load() { new Function('window', 'document', source)(win, doc); }
    load();
    return { doc: doc, win: win, load: load };
  }
  function click(link) {
    var event = { prevented: false, preventDefault: function () { this.prevented = true; } };
    (link.events.click || []).forEach(function (callback) { callback(event); });
    return event;
  }
  var main = element('main');
  var noLink = fixture([], main);
  check(main.id === '' && main.focusCalls.length === 0, 'no link: no target mutation or focus');
  var absentLink = element('a'); absentLink.attrs.href = '#flx-main-content';
  var noMain = fixture([absentLink], null);
  check(absentLink.hidden && !absentLink.hasAttribute('href'), 'no main: hidden link without broken href');
  check(click(absentLink).prevented, 'no main: synthetic click cannot navigate');
  var link = element('a'); main = element('main', 'content-main');
  var existing = fixture([link], main);
  check(link.attrs.href === '#content-main' && !link.hidden, 'existing main ID reused');
  check(!click(link).prevented, 'native fragment navigation preserved');
  check(main.attrs.tabindex === '-1' && main.focusCalls.length === 1, 'click adds tabindex and focuses main');
  check(main.focusCalls[0].preventScroll === true, 'focus does not cause a second scroll');
  main.attrs.tabindex = '0'; click(link);
  check(main.attrs.tabindex === '0', 'existing tabindex preserved');
  existing.win.FLXSkipLink.init(existing.doc);
  existing.win.FLXSkipLink.init(existing.doc);
  existing.load();
  check(link.events.click.length === 1, 'multiple init and repeated script execution do not duplicate listener');
  click(link);
  check(main.focusCalls.length === 3, 'one focus per click after repeated initialization');
  var fresh = element('main'); var freshLink = element('a');
  fixture([freshLink], fresh);
  check(fresh.id === 'flx-main-content' && freshLink.attrs.href === '#flx-main-content', 'missing ID gets FLX default');
  var collision = element('div', 'flx-main-content'); var collisionMain = element('main');
  var collisionLink = element('a'); fixture([collisionLink], collisionMain, { '#content': collision });
  check(collisionMain.id === 'flx-main-content-2', 'generated ID avoids existing node collision');
  check(collisionLink.attrs.href === '#flx-main-content-2', 'href follows unique generated ID');
  var role = element('section', 'role-main'); var roleLink = element('a');
  fixture([roleLink], null, { '[role="main"]': role });
  check(roleLink.attrs.href === '#role-main', 'role main fallback');
  ['#content', '#primary'].forEach(function (selector) {
    var target = element('div', 'fallback'); var fallbackLink = element('a'); var other = {}; other[selector] = target;
    fixture([fallbackLink], null, other);
    check(fallbackLink.attrs.href === '#fallback', selector + ' fallback');
  });
  var priorityLink = element('a'); fixture([priorityLink], element('main', 'preferred'), { '#content': element('div', 'outer') });
  check(priorityLink.attrs.href === '#preferred', 'main wins over generic wrapper');
  var specialLink = element('a'); fixture([specialLink], element('main', 'main:example space'));
  check(specialLink.attrs.href === '#main%3Aexample%20space', 'fragment encodes existing ID safely');
  existing.doc.main = null; existing.win.FLXSkipLink.init(existing.doc);
  check(link.hidden && !link.hasAttribute('href'), 'target removal safely hides link on reinit');
  var replacement = element('main', 'replacement'); existing.doc.main = replacement; existing.win.FLXSkipLink.init(existing.doc);
  click(link);
  check(!link.hidden && replacement.focusCalls.length === 1 && link.events.click.length === 1, 'reinit updates target without duplicate bindings');
  var waitingLink = element('a'); var waiting = fixture([waitingLink], null, {}, 'loading');
  waiting.doc.main = element('main', 'ready'); waiting.doc.events.DOMContentLoaded[0]();
  check(waitingLink.attrs.href === '#ready' && !waitingLink.hidden, 'DOM ready resolves later main');
  var oldTarget = element('main', 'old'); var oldLink = element('a');
  oldTarget.focus = function (options) { if (options) throw new Error('options unsupported'); this.focusCalls.push(null); };
  fixture([oldLink], oldTarget); click(oldLink);
  check(oldTarget.focusCalls.length === 1, 'focus fallback for browser without options support');
  var second = element('a'); var multiMain = element('main', 'shared'); var multi = fixture([element('a'), second], multiMain);
  check(multi.doc.links[0].attrs.href === second.attrs.href, 'multiple links resolve the same target');
  return { assertions: count, environment: 'DOM fixture (not browser)' };
}
if (typeof module !== 'undefined' && module.exports) {
  module.exports = runFLXSkipLinkTests;
  if (require.main === module) {
    var fs = require('fs');
    var result = runFLXSkipLinkTests(fs.readFileSync(require('path').join(__dirname, '../flexora/assets/accessibility/skip-link.js'), 'utf8'));
    process.stdout.write('PASS skip-link JS: ' + result.assertions + ' assertions (DOM fixture)\n');
  }
}
