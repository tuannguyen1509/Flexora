(function () {
    'use strict';
    var seen = new Set(), limit = 10;
    document.addEventListener('mouseover', function (event) {
        if (!(event.target instanceof Element)) { return; }
        var a = event.target.closest('a');
        var connection = navigator.connection;
        if (!a || a.hasAttribute('download') || a.target === '_blank' || (connection && (connection.saveData || /2g/.test(connection.effectiveType)))) { return; }
        var url = new URL(a.href, location.href);
        if (url.origin !== location.origin || !/^https?:$/.test(url.protocol) || url.hash || url.search || url.pathname === location.pathname || /\/(wp-admin|wp-login\.php|cart|checkout|my-account|gio-hang|thanh-toan|tai-khoan)(\/|$)/.test(url.pathname) || seen.has(url.href) || seen.size >= limit) { return; }
        seen.add(url.href);
        var link = document.createElement('link');
        link.rel = 'prefetch'; link.href = url.href; document.head.appendChild(link);
    }, { passive: true });
})();
