(function (window, document) {
  'use strict';
  if (window.FLXSkipLink) { window.FLXSkipLink.init(document); return; }

  function init(root) {
    root = root || document;
    var links = root.querySelectorAll('.flx-skip-link');
    if (!links.length) return;
    // Explicit preference, rather than the first match in document order.
    var main = document.querySelector('main') || document.querySelector('[role="main"]') ||
      document.querySelector('#content') || document.querySelector('#primary');
    if (main && !main.id) {
      var id = 'flx-main-content';
      var suffix = 2;
      while (document.getElementById(id)) { id = 'flx-main-content-' + suffix++; }
      main.id = id;
    }
    Array.prototype.forEach.call(links, function (link) {
      if (!link._flxSkipLink) {
        link._flxSkipLink = { target: null };
        link.addEventListener('click', function (event) {
          var target = link._flxSkipLink.target;
          if (!target) { event.preventDefault(); return; }
          if (!target.hasAttribute('tabindex')) target.setAttribute('tabindex', '-1');
          try { target.focus({ preventScroll: true }); } catch (error) { target.focus(); }
          // Preserve native fragment navigation; focus does not add a second scroll.
        });
      }
      link._flxSkipLink.target = main;
      link.hidden = !main;
      if (main) link.setAttribute('href', '#' + encodeURIComponent(main.id));
      else link.removeAttribute('href');
    });
  }
  window.FLXSkipLink = { init: init };
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', function () { init(document); }, { once: true });
  init(document);
})(window, document);
