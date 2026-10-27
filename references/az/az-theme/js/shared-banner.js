(function () {
  'use strict';
  var banner = document.querySelector('.az-shared-banner');
  if (!banner) return;
  var header = document.querySelector('.az-site-header');
  if (header && banner.dataset.overlayHeader === '1') {
    var measure = function () { document.documentElement.style.setProperty('--az-banner-header-height', header.getBoundingClientRect().height + 'px'); };
    measure(); document.body.classList.add('az-banner-overlay-ready');
    if ('ResizeObserver' in window) new ResizeObserver(measure).observe(header);
    else window.addEventListener('resize', measure);
  }
  var slides = Array.from(banner.querySelectorAll('[data-slide]'));
  if (slides.length < 2) return;
  var controls = banner.querySelector('.az-shared-banner__controls'); controls.hidden = false;
  var index = 0, timer, paused = false, hovered = false, focused = false;
  var reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
  var pause = banner.querySelector('[data-pause]');
  function show(next) {
    index = banner.dataset.loop === '1'
      ? (next + slides.length) % slides.length
      : Math.max(0, Math.min(slides.length - 1, next));
    slides.forEach(function (slide, i) { slide.style.display = i === index ? '' : 'none'; });
    controls.querySelector('[data-slide-status]').textContent = (index + 1) + ' / ' + slides.length;
  }
  function schedule() {
    clearInterval(timer);
    if (banner.dataset.autoplay === '1' && !paused && !hovered && !focused && !document.hidden && !reduced.matches) timer = setInterval(function () { show(index + 1); }, 3000);
  }
  controls.querySelectorAll('[data-direction]').forEach(function (button) {
    button.addEventListener('click', function () { show(index + Number(button.dataset.direction)); schedule(); });
  });
  if (pause) pause.addEventListener('click', function () { paused = !paused; pause.textContent = paused ? 'Tiếp tục' : 'Tạm dừng'; pause.setAttribute('aria-pressed', String(paused)); schedule(); });
  banner.addEventListener('mouseenter', function () { hovered = true; schedule(); });
  banner.addEventListener('mouseleave', function () { hovered = false; schedule(); });
  banner.addEventListener('focusin', function () { focused = true; schedule(); });
  banner.addEventListener('focusout', function (event) { focused = banner.contains(event.relatedTarget); schedule(); });
  document.addEventListener('visibilitychange', schedule); reduced.addEventListener('change', schedule);
  var startX = 0, startY = 0;
  banner.addEventListener('touchstart', function (event) { startX = event.changedTouches[0].clientX; startY = event.changedTouches[0].clientY; }, { passive: true });
  banner.addEventListener('touchend', function (event) {
    var dx = event.changedTouches[0].clientX - startX, dy = event.changedTouches[0].clientY - startY;
    if (Math.abs(dx) > 60 && Math.abs(dx) > Math.abs(dy)) { show(index + (dx < 0 ? 1 : -1)); schedule(); }
  }, { passive: true });
  schedule();
})();
