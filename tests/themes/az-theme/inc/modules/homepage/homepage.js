document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('[data-az-main-banner].is-slider').forEach(function(banner) {
    var slides = Array.prototype.slice.call(banner.querySelectorAll('img'));
    if (slides.length < 2) {
      return;
    }

    var current = Math.max(0, slides.findIndex(function(slide) {
      return slide.classList.contains('is-active');
    }));

    window.setInterval(function() {
      slides[current].classList.remove('is-active');
      current = (current + 1) % slides.length;
      slides[current].classList.add('is-active');
    }, 4200);
  });

  document.querySelectorAll('[data-az-product-tabs]').forEach(function(section) {
    var buttons = Array.prototype.slice.call(section.querySelectorAll('[data-az-tab-target]'));
    buttons.forEach(function(button) {
      button.addEventListener('click', function() {
        var targetId = button.getAttribute('data-az-tab-target');
        var targetPanel = targetId ? document.getElementById(targetId) : null;
        if (targetPanel && !section.contains(targetPanel)) {
          targetPanel = null;
        }
        if (!targetPanel) {
          return;
        }

        buttons.forEach(function(item) {
          item.classList.toggle('is-active', item === button);
        });
        section.querySelectorAll('.az-product-tab-panel').forEach(function(panel) {
          var isActive = panel === targetPanel;
          panel.hidden = !isActive;
          panel.classList.toggle('is-active', isActive);
        });
      });
    });
  });
});
