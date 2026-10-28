(function () {
    'use strict';
    var root = document.querySelector('.az-contact--expand');
    if (!root) { return; }
    var button = root.querySelector('.az-contact__toggle');
    var links = root.querySelector('.az-contact__links');
    function setOpen(open, restoreFocus) {
        links.hidden = !open;
        button.setAttribute('aria-expanded', String(open));
        button.setAttribute('aria-label', open ? 'Đóng danh sách liên hệ' : 'Mở danh sách liên hệ');
        if (restoreFocus) { button.focus(); }
    }
    setOpen(false);
    button.addEventListener('click', function () { setOpen(links.hidden); });
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && !links.hidden) { setOpen(false, root.contains(document.activeElement)); }
    });
    document.addEventListener('click', function (event) {
        if (!root.contains(event.target)) { setOpen(false); }
    });
    root.addEventListener('focusout', function (event) {
        if (!root.contains(event.relatedTarget)) { setOpen(false); }
    });
})();
