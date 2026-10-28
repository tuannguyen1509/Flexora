(function () {
  'use strict';

  document.addEventListener('click', function (event) {
    var descriptionToggle = event.target.closest('[data-az-shop-description-toggle]');
    if (descriptionToggle) {
      var description = document.getElementById(descriptionToggle.getAttribute('aria-controls'));
      if (!description) return;

      var descriptionExpanded = descriptionToggle.getAttribute('aria-expanded') === 'true';
      descriptionToggle.setAttribute('aria-expanded', descriptionExpanded ? 'false' : 'true');
      description.classList.toggle('is-collapsed', descriptionExpanded);
      return;
    }

    var toggle = event.target.closest('.az-shop-cat-toggle');
    if (!toggle) return;

    var item = toggle.closest('.az-shop-cat-item');
    var children = item ? item.querySelector(':scope > .az-shop-cat-children') : null;
    if (!item || !children) return;

    var expanded = toggle.getAttribute('aria-expanded') === 'true';
    var name = item.querySelector(':scope > .az-shop-cat-row .az-shop-cat-name');
    toggle.setAttribute('aria-expanded', expanded ? 'false' : 'true');
    toggle.setAttribute('aria-label', (expanded ? 'Mở danh mục ' : 'Thu gọn danh mục ') + (name ? name.textContent.trim() : ''));
    item.classList.toggle('is-expanded', !expanded);
    children.hidden = expanded;
  });

  document.querySelectorAll('[data-az-shop-description-toggle]').forEach(function (toggle) {
    var description = document.getElementById(toggle.getAttribute('aria-controls'));
    if (!description) return;

    if (description.scrollHeight <= description.clientHeight + 1) {
      toggle.hidden = true;
    }
  });
}());
