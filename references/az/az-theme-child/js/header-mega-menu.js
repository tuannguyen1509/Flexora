(function () {
    'use strict';
    var menus = [];
    var hover = window.matchMedia('(hover: hover) and (pointer: fine)');
    document.querySelectorAll('[data-az-mega-toggle]').forEach(function (toggle) {
        var root = toggle.closest('.az-mega-menu');
        var panel = document.getElementById(toggle.getAttribute('aria-controls'));
        if (!root || !panel) { return; }
        var dismissed = false;
        function position() {
            panel.style.setProperty('--az-mega-offset', '0px');
            var rect = panel.getBoundingClientRect();
            var left = Math.max(16, Math.min(rect.left, document.documentElement.clientWidth - rect.width - 16));
            panel.style.setProperty('--az-mega-offset', (left - rect.left) + 'px');
        }
        function close() {
            root.classList.remove('is-open');
            toggle.setAttribute('aria-expanded', 'false');
        }
        function open() {
            menus.forEach(function (menu) { if (menu.root !== root) { menu.close(); } });
            dismissed = false;
            root.classList.add('is-open');
            toggle.setAttribute('aria-expanded', 'true');
            position();
        }
        root.classList.add('az-mega-menu--ready');
        menus.push({ root: root, close: close, position: position });
        root.addEventListener('pointerover', function (event) {
            if (hover.matches && !toggle.contains(event.target) && !root.contains(event.relatedTarget)) { open(); }
        });
        root.addEventListener('pointerleave', function () { if (!root.contains(document.activeElement)) { close(); } });
        root.addEventListener('focusin', function (event) { if (!dismissed && event.target !== toggle) { open(); } });
        root.addEventListener('focusout', function (event) { if (!root.contains(event.relatedTarget)) { close(); dismissed = false; } });
        toggle.addEventListener('click', function () {
            if (root.classList.contains('is-open')) { close(); dismissed = true; }
            else { open(); }
        });
        root.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && root.classList.contains('is-open')) {
                event.preventDefault(); event.stopPropagation(); close(); dismissed = true; toggle.focus();
            } else if ((event.key === 'ArrowDown' || event.key === 'ArrowUp') && (event.target === toggle || event.target === root.querySelector(':scope > a'))) {
                event.preventDefault(); open();
                var links = panel.querySelectorAll('a');
                var link = event.key === 'ArrowDown' ? links[0] : links[links.length - 1];
                if (link) { link.focus(); }
            }
        });
    });
    document.addEventListener('click', function (event) {
        menus.forEach(function (menu) { if (!menu.root.contains(event.target)) { menu.close(); } });
    });
    window.addEventListener('resize', function () {
        menus.forEach(function (menu) { if (menu.root.classList.contains('is-open')) { menu.position(); } });
    });
})();
