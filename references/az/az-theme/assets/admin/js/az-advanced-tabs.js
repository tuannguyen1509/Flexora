(function () {
  'use strict';
  var form = document.getElementById('az-advanced-form');
  if (!form) return;
  var panels = Array.from(form.querySelectorAll('[data-az-panel]'));
  var links = Array.from(document.querySelectorAll('.az-advanced__nav a'));
  // Keep one shared Save button, but submit only the visible panel. This avoids
  // PHP max_input_vars truncating large repeaters from inactive tabs.
  form.addEventListener('submit', function () {
    var activeId = form.elements.az_section ? form.elements.az_section.value : '';
    panels.forEach(function (panel) {
      panel.querySelectorAll('input, select, textarea, button').forEach(function (field) { field.disabled = false; });
      if (panel.dataset.azPanel === activeId) {
        return;
      }
      panel.querySelectorAll('input, select, textarea, button').forEach(function (field) {
        field.disabled = true;
      });
    });
  });
  function activate(id) {
    var panel = panels.find(function (item) { return item.dataset.azPanel === id; });
    if (!panel) return;
    panels.forEach(function (item) { item.hidden = item !== panel; });
    panel.querySelectorAll('input, select, textarea, button').forEach(function (field) { field.disabled = false; });
    links.forEach(function (link) {
      var active = new URL(link.href).searchParams.get('section') === id;
      link.classList.toggle('is-active', active);
      link.setAttribute('aria-current', active ? 'page' : 'false');
    });
    form.elements.az_section.value = id;
    document.querySelector('#az-reset-section-form [name="az_section"]').value = id;
    document.querySelector('.az-admin-header h1 + p').textContent = panel.dataset.title;
  }
  links.forEach(function (link) {
    link.addEventListener('click', function (event) {
      if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
      event.preventDefault();
      var url = new URL(link.href);
      activate(url.searchParams.get('section'));
      history.pushState(null, '', url);
    });
  });
  window.addEventListener('popstate', function () {
    activate(new URL(location.href).searchParams.get('section') || 'general');
  });
  form.addEventListener('invalid', function (event) {
    var panel = event.target.closest('[data-az-panel]');
    if (panel) activate(panel.dataset.azPanel);
  }, true);
})();
