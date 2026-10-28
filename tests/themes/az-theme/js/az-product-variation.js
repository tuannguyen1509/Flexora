(function () {
  'use strict';

  function hasContent(node) {
    if (!node) return false;
    if (node.textContent && node.textContent.trim() !== '') return true;
    return Boolean(node.querySelector('img, video, iframe, button, input, select, textarea'));
  }

  function syncPanel(panel) {
    if (!panel) return;

    var sections = panel.querySelectorAll(
      '.woocommerce-variation-description, .woocommerce-variation-price, .woocommerce-variation-availability'
    );
    var panelHasContent = false;

    sections.forEach(function (section) {
      var sectionHasContent = hasContent(section);
      section.hidden = !sectionHasContent;
      panelHasContent = panelHasContent || sectionHasContent;
    });

    panel.hidden = !panelHasContent;
  }

  function init() {
    document.querySelectorAll('.single_variation').forEach(function (panel) {
      syncPanel(panel);

      new MutationObserver(function () {
        syncPanel(panel);
      }).observe(panel, {
        childList: true,
        subtree: true,
        characterData: true
      });
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
