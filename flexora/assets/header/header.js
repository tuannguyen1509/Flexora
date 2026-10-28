(function () {
    'use strict';
    function init(root) {
        (root || document).querySelectorAll('[data-flx-header]').forEach(function (header) {
            if (header.hasAttribute('data-flx-enhanced')) return;
            var nav = header.querySelector('[data-flx-navigation]'), toggle = header.querySelector('[data-flx-menu-toggle]');
            if (!nav || !toggle || !nav.id || toggle.getAttribute('aria-controls') !== nav.id) return;
            var desktop = window.matchMedia('(min-width: 901px)'), disclosures = [];
            function position(item) {
                item.menu.style.transform = '';
                if (!item.menu.hidden && desktop.matches && item.menu.parentElement.parentElement === nav.firstElementChild) {
                    var rect = item.menu.getBoundingClientRect(), width = document.documentElement.clientWidth;
                    var left = Math.max(20, Math.min(rect.left, width - rect.width - 20));
                    item.menu.style.transform = 'translateX(' + (left - rect.left) + 'px)';
                }
            }
            function substate(item, open) {
                item.button.setAttribute('aria-expanded', String(open)); item.menu.hidden = !open;
                var label = nav.getAttribute(open ? 'data-flx-submenu-close-label' : 'data-flx-submenu-open-label') || (open ? 'Close submenu' : 'Open submenu');
                item.button.setAttribute('aria-label', label + ': ' + item.label);
                position(item);
                if (!open) {
                    disclosures.forEach(function (child) {
                        if (item.menu.contains(child.button)) substate(child, false);
                    });
                }
            }
            function closeSubs() { disclosures.forEach(function (item) { substate(item, false); }); }
            function menuState(open, restore) {
                toggle.setAttribute('aria-expanded', String(open)); nav.hidden = !desktop.matches && !open;
                if (!open) closeSubs();
                if (restore) toggle.focus();
            }
            nav.querySelectorAll('li').forEach(function (li, index) {
                var menu = Array.from(li.children).find(function (child) { return child.matches('ul'); });
                var link = Array.from(li.children).find(function (child) { return child.matches('a'); });
                if (!menu || !link) return;
                menu.id = nav.id + '-submenu-' + index;
                var button = document.createElement('button'); button.type = 'button'; button.className = 'flx-nav__disclosure'; button.textContent = '⌄';
                button.setAttribute('aria-controls', menu.id); link.after(button);
                var item = {button: button, menu: menu, label: link.textContent.trim()}; disclosures.push(item); substate(item, false);
                button.addEventListener('click', function () { substate(item, button.getAttribute('aria-expanded') !== 'true'); });
                li.addEventListener('mouseenter', function () { if (desktop.matches) substate(item, true); });
                li.addEventListener('mouseleave', function () { if (desktop.matches && !li.contains(document.activeElement)) substate(item, false); });
                link.addEventListener('focus', function () { if (desktop.matches) substate(item, true); });
                li.addEventListener('focusout', function (event) { if (desktop.matches && !li.contains(event.relatedTarget)) substate(item, false); });
            });
            nav.querySelectorAll('a[href]').forEach(function (link) {
                link.addEventListener('click', function () {
                    if (!desktop.matches && toggle.getAttribute('aria-expanded') === 'true') menuState(false, true);
                });
            });
            toggle.addEventListener('click', function () { menuState(toggle.getAttribute('aria-expanded') !== 'true', false); });
            header.addEventListener('keydown', function (event) {
                if (event.key !== 'Escape') return;
                var parent = event.target.closest('li');
                while (parent && nav.contains(parent)) {
                    var item = disclosures.find(function (entry) { return entry.menu.parentElement === parent && entry.button.getAttribute('aria-expanded') === 'true'; });
                    if (item) { event.preventDefault(); event.stopPropagation(); substate(item, false); item.button.focus(); return; }
                    parent = parent.parentElement.closest('li');
                }
                if (!desktop.matches && toggle.getAttribute('aria-expanded') === 'true') { event.preventDefault(); menuState(false, true); }
            });
            function resize() {
                var focused = nav.contains(document.activeElement);
                toggle.hidden = desktop.matches; menuState(false, false);
                if (!desktop.matches && focused) toggle.focus();
                if (desktop.matches && document.activeElement === toggle) { var first = nav.querySelector('a'); if (first) first.focus(); }
            }
            if (desktop.addEventListener) desktop.addEventListener('change', resize); else desktop.addListener(resize);
            window.addEventListener('resize', function () { disclosures.forEach(position); });
            header.setAttribute('data-flx-enhanced', ''); resize();
        });
    }
    window.FLXHeader = {init: init};
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', function () { init(document); }); else init(document);
}());
