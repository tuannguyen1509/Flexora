(function () {
    'use strict';
    var dragged;
    function destroy(row) { if (window.FLXEditors) FLXEditors.destroy(row); }
    function init(row) { FLXFields.emit('row-added', row); }
    function rename(row, before, after) {
        row.dataset.flxRow = after;
        row.querySelectorAll('*').forEach(function (element) {
            ['id', 'name', 'for', 'aria-describedby', 'aria-labelledby', 'data-flx-name'].forEach(function (attribute) {
                if (element.hasAttribute(attribute)) element.setAttribute(attribute, element.getAttribute(attribute).split(before).join(after));
            });
        });
        row.querySelector('input[name$="[_id]"]').value = after;
    }
    function move(row, target, after) {
        if (!target || row === target) return;
        var focused = row.contains(document.activeElement) ? document.activeElement : null;
        destroy(row);
        if (after) target.after(row); else target.before(row);
        FLXFields.emit('row-moved', row);
        if (focused && focused.isConnected) focused.focus();
        row.closest('[data-flx-repeater]').querySelector('[data-flx-row-status]').textContent = 'Đã thay đổi thứ tự dòng.';
    }
    document.addEventListener('click', function (event) {
        var button = event.target.closest('button'); if (!button) return;
        var repeater = button.closest('[data-flx-repeater]'); if (!repeater) return;
        var list = repeater.querySelector('.flx-repeater-rows'), row = button.closest('[data-flx-row]');
        if (button.hasAttribute('data-flx-row-add')) {
            var fragment = repeater.querySelector('template').content.cloneNode(true); var added = fragment.firstElementChild;
            rename(added, '__FLX_ROW__', FLXFields.id()); list.append(added); init(added);
            var first = added.querySelector('.flx-row-fields input:not([type="hidden"]), .flx-row-fields select, .flx-row-fields textarea') || added.querySelector('button');
            if (first) first.focus();
        }
        if (!row) return;
        if (button.hasAttribute('data-flx-row-collapse')) { var fields = row.querySelector('.flx-row-fields'); fields.hidden = !fields.hidden; button.setAttribute('aria-expanded', String(!fields.hidden)); button.textContent = fields.hidden ? 'Mở rộng' : 'Thu gọn'; }
        if (button.hasAttribute('data-flx-row-remove')) { destroy(row); row.remove(); repeater.querySelector('[data-flx-row-add]').focus(); }
        if (button.hasAttribute('data-flx-row-duplicate')) {
            destroy(row);
            // Remove the WP color-picker wrapper before cloning; reinitialize both copies.
            row.querySelectorAll('.flx-color').forEach(function (input) { if (window.jQuery && jQuery(input).closest('.wp-picker-container').length) jQuery(input).wpColorPicker('destroy'); });
            var copy = row.cloneNode(true);
            row.querySelectorAll('select').forEach(function (select, i) { copy.querySelectorAll('select')[i].value = select.value; });
            rename(copy, row.dataset.flxRow, FLXFields.id()); row.after(copy); init(row); init(copy);
        }
        if (button.hasAttribute('data-flx-row-up')) move(row, row.previousElementSibling, false);
        if (button.hasAttribute('data-flx-row-down')) move(row, row.nextElementSibling, true);
    });
    document.addEventListener('dragstart', function (event) { if (!event.target.matches('.flx-drag-handle')) return; dragged = event.target.closest('[data-flx-row]'); event.dataTransfer.setData('text/plain', dragged.dataset.flxRow); });
    document.addEventListener('dragover', function (event) { if (dragged && event.target.closest('[data-flx-row]')) event.preventDefault(); });
    document.addEventListener('drop', function (event) { var target = event.target.closest('[data-flx-row]'); if (dragged && target && target.parentNode === dragged.parentNode) { event.preventDefault(); move(dragged, target, false); } dragged = null; });
    document.addEventListener('dragend', function () { dragged = null; });
}());
