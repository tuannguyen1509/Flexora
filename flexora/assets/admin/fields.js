/* Shared lifecycle, no provider knowledge in child consumers. */
(function () {
    'use strict';
    window.FLXFields = {
        emit: function (name, node) { document.dispatchEvent(new CustomEvent('flx:' + name, {detail: {node: node}})); },
        id: function () { return 'row-' + (window.crypto && crypto.randomUUID ? crypto.randomUUID() : Date.now().toString(36) + '-' + Math.random().toString(36).slice(2)); }
    };
    document.addEventListener('submit', function (event) {
        if (!event.target.matches('.flx-settings-form') && !event.target.querySelector('.flx-template-content')) return;
        if (window.tinymce) tinymce.triggerSave();
        if (!event.target.matches('.flx-settings-form')) return;
        var button = event.target.querySelector('[type="submit"]');
        button.disabled = true; button.textContent = 'Đang lưu…';
    });
}());
