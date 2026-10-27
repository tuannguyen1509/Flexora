(function () {
    'use strict';
    document.querySelectorAll('[data-az-social-contact]').forEach(function (root) {
        var button = root.querySelector('.az-contact__toggle');
        var links = root.querySelector('.az-contact__links');
        if (!button || !links) { return; }
        function setOpen(open, restoreFocus) {
            links.hidden = !open;
            button.setAttribute('aria-expanded', String(open));
            button.setAttribute('aria-label', open ? 'Đóng các kênh liên hệ' : 'Mở các kênh liên hệ');
            if (restoreFocus) { button.focus(); }
        }
        setOpen(false);
        button.addEventListener('click', function () {
            var open = links.hidden;
            setOpen(open);
            // Tab order begins in the panel when opened with Enter/Space.
            if (open && document.activeElement === button) { links.querySelector('a').focus(); }
        });
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !links.hidden) { setOpen(false, root.contains(document.activeElement)); }
        });
        document.addEventListener('click', function (event) {
            if (!root.contains(event.target)) { setOpen(false); }
        });
        root.addEventListener('focusout', function (event) {
            if (!root.contains(event.relatedTarget)) { setOpen(false); }
        });
    });
})();
