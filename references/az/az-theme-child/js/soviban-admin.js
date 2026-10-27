(function () {
    'use strict';
    document.querySelectorAll('[data-az-child-list]').forEach(function (list) {
        var rows = list.querySelector('[data-az-child-rows]');
        var add = list.querySelector('[data-az-child-add]');
        list.addEventListener('click', function (event) {
            if (event.target.closest('[data-az-child-add]') && rows.children.length < 12) {
                rows.appendChild(list.querySelector('template').content.cloneNode(true));
                rows.lastElementChild.querySelector('input').focus();
            }
            var remove = event.target.closest('[data-az-child-remove]');
            if (remove) { remove.closest('[data-az-child-row]').remove(); add.focus(); }
        });
    });
})();
