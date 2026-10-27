(function () {
  'use strict';

  function prepareLegacyContent(root) {
    var button = root.querySelector('.more-view-shadown .btn');
    if (!button) return;

    if (root.scrollHeight <= root.clientHeight + 8) {
      button.closest('.more-view-shadown').hidden = true;
      return;
    }

    button.setAttribute('role', 'button');
    button.setAttribute('tabindex', '0');
    button.setAttribute('aria-expanded', 'false');

    function toggle() {
      var expanded = root.classList.toggle('is-expanded');
      button.setAttribute('aria-expanded', expanded ? 'true' : 'false');
      button.childNodes[0].nodeValue = expanded ? 'Thu gọn ' : 'Xem thêm ';
    }

    button.addEventListener('click', toggle);
    button.addEventListener('keydown', function (event) {
      if (event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        toggle();
      }
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.az-legacy-section .scrollHide').forEach(prepareLegacyContent);
  });
}());
