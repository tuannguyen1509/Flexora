/** Isolated keyboard/navigation behavior checks; no browser or dependencies. */
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const source = fs.readFileSync(path.join(__dirname, '../js/mobile-menu.js'), 'utf8');
let checks = 0;
function check(condition, message) {
  assert.ok(condition, message);
  checks++;
  console.log(`PASS ${message}`);
}
function fixture(style) {
  const document = { activeElement: null };
  function element(tag = 'div') {
    const listeners = {};
    const attributes = {};
    const classes = new Set();
    return {
      tagName: tag.toUpperCase(), children: [], parentNode: null, hidden: false, textContent: '',
      insertBefore(child, before) {
        const index = this.children.indexOf(before);
        this.children.splice(index < 0 ? this.children.length : index, 0, child);
        child.parentNode = this;
      },
      matches(selector) {
        return selector === 'a' ? tag === 'a' : selector === 'ul.sub-menu' && tag === 'ul' && classes.has('sub-menu');
      },
      closest(selector) {
        for (let item = this; item; item = item.parentNode) {
          if (selector === '[hidden]' && item.hidden) return item;
        }
        return null;
      },
      addEventListener(type, callback) { (listeners[type] ??= []).push(callback); },
      fire(type, detail = {}) {
        const event = { ...detail, prevented: false, preventDefault() { this.prevented = true; } };
        for (const callback of listeners[type] ?? []) callback(event);
        return event;
      },
      focus() { document.activeElement = this; },
      setAttribute(name, value) { attributes[name] = value; },
      getAttribute(name) { return attributes[name]; },
      classList: {
        add(name) { classes.add(name); },
        contains(name) { return classes.has(name); },
        remove(name) { classes.delete(name); },
        toggle(name, enabled) { if (enabled) classes.add(name); else classes.delete(name); }
      }
    };
  }
  const menu = element();
  const summary = element();
  const panel = element();
  const close = element();
  const shade = element();
  const logo = element('a');
  const first = element('a');
  const nested = element('a');
  const grandchild = element('a');
  const last = element('a');
  first.textContent = 'Góc chuyên gia';
  nested.textContent = 'Kiến thức cà phê';
  const parentItem = element('li');
  const nestedItem = element('li');
  const leafItem = element('li');
  const submenu = element('ul');
  const childSubmenu = element('ul');
  submenu.classList.add('sub-menu');
  childSubmenu.classList.add('sub-menu');
  parentItem.insertBefore(first);
  parentItem.insertBefore(submenu);
  submenu.insertBefore(nestedItem);
  nestedItem.insertBefore(nested);
  nestedItem.insertBefore(childSubmenu);
  childSubmenu.insertBefore(grandchild);
  leafItem.insertBefore(last);
  const screen = element();
  screen.matches = true;
  document.documentElement = element();
  document.createElement = element;
  document.querySelector = () => menu;
  menu.classList.toggle(`az-mobile-menu--${style}`, true);
  menu.querySelector = selector => selector === 'summary' ? summary : panel;
  menu.querySelectorAll = () => [close, shade];
  panel.querySelector = selector => selector === '.az-mobile-menu__close' ? close : logo;
  panel.dataset = { submenuOpenLabel: 'Mở menu con', submenuCloseLabel: 'Đóng menu con' };
  const treeItems = () => [parentItem, nestedItem].flatMap(item => item.children.filter(child => child.tagName === 'BUTTON'));
  panel.querySelectorAll = selector => {
    if (selector === 'li') return [parentItem, nestedItem, leafItem];
    if (selector === 'nav a[href]') return [first, nested, grandchild, last];
    return [logo, close, first, ...treeItems(), nested, grandchild, last];
  };
  let opened = false;
  Object.defineProperty(menu, 'open', {
    get() { return opened; },
    set(value) { opened = value; menu.fire('toggle'); }
  });
  vm.runInNewContext(source, { document, matchMedia: () => screen });
  return { menu, summary, panel, close, shade, logo, first, nested, grandchild, last, screen, document, submenu, childSubmenu, treeItems,
    locked: () => document.documentElement.classList.contains('az-mobile-menu-open') };
}
const branded = fixture('brand_right');
check(branded.summary.getAttribute('aria-expanded') === 'false', 'closed trigger starts collapsed');
branded.menu.open = true;
check(branded.locked() && branded.summary.getAttribute('aria-expanded') === 'true', 'drawer locks page scroll and updates expanded state');
check(branded.document.activeElement === branded.close, 'branded drawer focuses close control on opening');
const [rightToggle, rightNestedToggle] = branded.treeItems();
check(branded.submenu.hidden && branded.childSubmenu.hidden && branded.treeItems().length === 2, 'right drawer starts with nested branches collapsed');
rightToggle.fire('click');
check(branded.menu.open && !branded.submenu.hidden && rightToggle.getAttribute('aria-expanded') === 'true', 'right drawer submenu expands without closing the drawer');
check(rightToggle.getAttribute('aria-label') === 'Đóng menu con: Góc chuyên gia', 'right drawer submenu has translated expanded label');
rightNestedToggle.fire('click');
check(!branded.childSubmenu.hidden && rightNestedToggle.getAttribute('aria-controls') === branded.childSubmenu.id, 'right drawer supports a second nested level');
rightNestedToggle.fire('click');
rightToggle.fire('click');
check(branded.submenu.hidden && branded.childSubmenu.hidden && rightToggle.getAttribute('aria-expanded') === 'false', 'right drawer branches collapse again');
branded.last.focus();
check(branded.menu.fire('keydown', { key: 'Tab' }).prevented && branded.document.activeElement === branded.logo, 'Tab wraps from last link to first panel control');
check(branded.menu.fire('keydown', { key: 'Tab', shiftKey: true }).prevented && branded.document.activeElement === branded.last, 'Shift+Tab wraps to final link');
check(branded.menu.fire('keydown', { key: 'Escape' }).prevented && !branded.menu.open && !branded.locked(), 'Escape closes drawer and unlocks page');
check(branded.document.activeElement === branded.summary, 'closing restores focus to menu trigger');
for (const [label, control] of [['close button', branded.close], ['shade', branded.shade], ['navigation link', branded.first], ['nested menu link', branded.nested]]) {
  branded.menu.open = true;
  control.fire('click');
  check(!branded.menu.open && !branded.locked(), `${label} closes drawer and unlocks scroll`);
}
branded.menu.open = true;
branded.screen.matches = false;
branded.screen.fire('change');
check(!branded.menu.open && !branded.locked(), 'desktop breakpoint closes drawer and removes scroll lock');
for (const style of ['left', 'right', 'dropdown']) {
  const existing = fixture(style);
  existing.menu.open = true;
  existing.first.fire('click');
  check(existing.menu.open, `${style} retains previous link behavior`);
  check(existing.locked() === (style !== 'dropdown'), `${style} retains previous scroll behavior`);
}
const coffee = fixture('brand_dropdown');
coffee.menu.open = true;
check(coffee.locked() && coffee.document.activeElement === coffee.close, 'coffee overlay locks scroll and focuses close button');
const [parentToggle, nestedToggle] = coffee.treeItems();
check(coffee.treeItems().length === 2 && coffee.submenu.hidden && coffee.childSubmenu.hidden, 'each nested branch starts collapsed with its own toggle');
check(parentToggle.getAttribute('aria-controls') === coffee.submenu.id && nestedToggle.getAttribute('aria-controls') === coffee.childSubmenu.id && coffee.submenu.id !== coffee.childSubmenu.id, 'nested controls reference unique submenu IDs');
check(parentToggle.getAttribute('aria-expanded') === 'false' && parentToggle.getAttribute('aria-label') === 'Mở menu con: Góc chuyên gia', 'submenu button has collapsed state and parent label');
const originalCoffeeQuery = coffee.panel.querySelectorAll;
coffee.panel.querySelectorAll = selector => selector === 'a[href],button:not([disabled])'
  ? [coffee.logo, coffee.close, coffee.first, parentToggle, coffee.nested, nestedToggle, coffee.grandchild]
  : originalCoffeeQuery(selector);
parentToggle.focus();
check(coffee.menu.fire('keydown', { key: 'Tab' }).prevented && coffee.document.activeElement === coffee.logo, 'hidden submenu links are excluded when final branch is collapsed');
coffee.panel.querySelectorAll = originalCoffeeQuery;
parentToggle.fire('click');
check(coffee.menu.open && !coffee.submenu.hidden && parentToggle.getAttribute('aria-expanded') === 'true', 'submenu toggle expands its branch without closing overlay');
check(parentToggle.getAttribute('aria-label') === 'Đóng menu con: Góc chuyên gia' && coffee.childSubmenu.hidden, 'expanded label updates while nested branch stays collapsed');
nestedToggle.fire('click');
check(!coffee.childSubmenu.hidden && nestedToggle.getAttribute('aria-expanded') === 'true', 'supports a second level of nested navigation');
parentToggle.fire('click');
check(coffee.submenu.hidden && parentToggle.getAttribute('aria-expanded') === 'false', 'submenu toggle collapses branch again');
coffee.last.focus();
check(coffee.menu.fire('keydown', { key: 'Tab' }).prevented && coffee.document.activeElement === coffee.logo, 'coffee overlay wraps Tab across visible controls');
parentToggle.fire('click');
coffee.grandchild.fire('click');
check(!coffee.menu.open && !coffee.locked(), 'nested destination closes coffee menu and restores page scroll');
coffee.menu.open = true;
coffee.close.fire('click');
check(!coffee.menu.open && coffee.document.activeElement === coffee.summary, 'coffee close button restores trigger focus');
coffee.menu.open = true;
coffee.shade.fire('click');
check(!coffee.menu.open && !coffee.locked(), 'clicking outside coffee panel closes overlay');
coffee.menu.open = true;
check(coffee.menu.fire('keydown', { key: 'Escape' }).prevented && !coffee.menu.open, 'Escape closes coffee overlay');
coffee.menu.open = true;
coffee.screen.matches = false;
coffee.screen.fire('change');
check(!coffee.menu.open && !coffee.locked(), 'desktop breakpoint dismisses coffee overlay');
console.log(`${checks} checks passed`);
