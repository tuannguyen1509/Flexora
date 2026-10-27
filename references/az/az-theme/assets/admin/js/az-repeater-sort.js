(function () {
  'use strict';
  var draggingRow = null;
  function renumber(box) {
    box.querySelectorAll('[data-repeater-row]').forEach(function (row, index) {
      row.querySelectorAll('input, select, textarea').forEach(function (field) {
        field.name = field.name.replace(/\[\d+\](?=\[[^\]]+\]$)/, '[' + index + ']');
      });
    });
  }
  function bind(box) {
    box.querySelectorAll('[data-repeater-row]:not([data-sort-bound])').forEach(function (row) {
      var dragHandle = row.querySelector('[data-repeater-drag]');
      row.setAttribute('data-sort-bound', '1');
      if (dragHandle) {
        dragHandle.setAttribute('draggable', 'true');
        dragHandle.addEventListener('dragstart', function (event) {
          draggingRow = row;
          row.classList.add('is-dragging');
          event.dataTransfer.effectAllowed = 'move';
          event.dataTransfer.setData('text/plain', '');
        });
        dragHandle.addEventListener('dragend', function () {
          if (draggingRow) {
            draggingRow.classList.remove('is-dragging');
            draggingRow = null;
            renumber(box);
          }
        });
      }
      row.addEventListener('dragover', function (event) {
        event.preventDefault();
        if (!draggingRow || draggingRow === row) return;
        var rect = row.getBoundingClientRect();
        row.parentNode.insertBefore(draggingRow, event.clientY < rect.top + rect.height / 2 ? row : row.nextSibling);
      });
    });
    renumber(box);
  }
  document.querySelectorAll('[data-repeater]').forEach(function (box) {
    bind(box);
    new MutationObserver(function () { bind(box); }).observe(box, { childList: true });
  });
})();
