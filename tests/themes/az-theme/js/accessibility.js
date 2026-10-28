(() => {
  'use strict';
  const link = document.querySelector('.az-skip-link');
  const main = document.querySelector('main, [role="main"], #content, #primary');
  if (!link) return;
  if (!main) { link.hidden = true; return; }
  if (!main.id) main.id = 'az-main-content';
  link.href = '#' + main.id;
  link.addEventListener('click', () => {
    if (!main.hasAttribute('tabindex')) main.setAttribute('tabindex', '-1');
    main.focus({ preventScroll: true });
  });
})();
