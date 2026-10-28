(function () {
    'use strict';

    function syncStickyHeader() {
        document.querySelectorAll('[data-az-sticky-header]').forEach(function (header) {
            var show = window.scrollY > 140;
            header.classList.toggle('is-visible', show);
            header.setAttribute('aria-hidden', show ? 'false' : 'true');
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        syncStickyHeader();
        window.addEventListener('scroll', syncStickyHeader, { passive: true });
    });
}());
