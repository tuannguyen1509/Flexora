(function () {
    'use strict';
    function sync(node) {
        node.querySelectorAll('.flx-rich-editor').forEach(function (textarea) {
            var editor = window.tinymce && tinymce.get(textarea.id);
            if (editor) editor.save();
        });
    }
    function destroy(node) {
        sync(node);
        node.querySelectorAll('[data-flx-editor]').forEach(function (container) {
            var textarea = container.querySelector('textarea');
            if (!textarea) return;
            var clean = textarea.cloneNode(true);
            clean.value = textarea.value;
            clean.removeAttribute('style');
            clean.removeAttribute('aria-hidden');
            var current = window.tinymce && tinymce.get(textarea.id);
            clean.dataset.flxEditorMode = current && current.isHidden() ? 'html' : 'tmce';
            if (window.wp && wp.editor) wp.editor.remove(textarea.id);
            container.replaceChildren(clean);
        });
    }
    function init(node) {
        if (!window.wp || !wp.editor) return;
        node.querySelectorAll('.flx-rich-editor').forEach(function (textarea) {
            if (window.tinymce && tinymce.get(textarea.id)) return;
            wp.editor.initialize(textarea.id, {
                tinymce: {wpautop: true, height: 190, menubar: false, plugins: 'charmap,hr,lists,paste,tabfocus,textcolor,wordpress,wpautoresize,wpeditimage,wpemoji,wpgallery,wplink,wptextpattern,wpview', toolbar1: 'formatselect,bold,italic,bullist,numlist,link,unlink,undo,redo', setup: function (editor) {
                    editor.on('init', function () {
                        if (textarea.dataset.flxEditorMode === 'html') { window.switchEditors.go(textarea.id, 'html'); }
                        else { editor.show(); }
                    });
                }},
                quicktags: true, mediaButtons: false
            });
        });
    }
    window.FLXEditors = {sync: sync, destroy: destroy, init: init};
    document.addEventListener('flx:row-added', function (event) { init(event.detail.node); });
    document.addEventListener('flx:row-moved', function (event) { init(event.detail.node); });
}());
