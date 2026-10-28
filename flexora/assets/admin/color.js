/* jQuery is confined to WordPress' color picker API. */
(function ($) {
    'use strict';
    function init(node) {
        $(node).find('.flx-color').each(function () {
            if (!$(this).closest('.wp-picker-container').length) $(this).wpColorPicker();
        });
    }
    $(function () { init(document); });
    document.addEventListener('flx:row-added', function (event) { init(event.detail.node); });
}(jQuery));
