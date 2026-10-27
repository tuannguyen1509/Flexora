(() => {
  const menu = document.querySelector('.az-mobile-menu');
  if (!menu) return;
  const summary = menu.querySelector('summary');
  const panel = menu.querySelector('.az-mobile-menu__panel');
  const screen = matchMedia('(max-width:782px)');
  const drawer = !menu.classList.contains('az-mobile-menu--dropdown');
  const coffee = menu.classList.contains('az-mobile-menu--brand_dropdown');
  const branded = coffee || menu.classList.contains('az-mobile-menu--brand_right');
  if (branded) {
    panel.querySelectorAll('li').forEach((item, index) => {
      const submenu = [...item.children].find(child => child.matches('ul.sub-menu'));
      const link = [...item.children].find(child => child.matches('a'));
      if (!submenu || !link) return;
      const button = document.createElement('button');
      const label = link.textContent.trim();
      const openLabel = panel.dataset.submenuOpenLabel || 'Mở menu con';
      const closeLabel = panel.dataset.submenuCloseLabel || 'Đóng menu con';
      submenu.id ||= `az-mobile-submenu-${index}`;
      button.type = 'button';
      button.className = 'az-mobile-menu__submenu-toggle';
      button.setAttribute('aria-controls', submenu.id);
      button.setAttribute('aria-expanded', 'false');
      button.setAttribute('aria-label', `${openLabel}: ${label}`);
      button.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="m9 5 7 7-7 7"/></svg>';
      submenu.hidden = true;
      item.classList.add('az-mobile-menu__has-submenu');
      item.insertBefore(button, submenu);
      button.addEventListener('click', () => {
        submenu.hidden = !submenu.hidden;
        button.setAttribute('aria-expanded', String(!submenu.hidden));
        button.setAttribute('aria-label', `${submenu.hidden ? openLabel : closeLabel}: ${label}`);
      });
    });
  }
  const close = () => { menu.open = false; summary.focus(); };
  menu.querySelectorAll('.az-mobile-menu__close,.az-mobile-menu__shade').forEach(button => button.addEventListener('click', close));
  if (branded) panel.querySelectorAll('nav a[href]').forEach(link => link.addEventListener('click', close));
  menu.addEventListener('toggle', () => {
    summary.setAttribute('aria-expanded', String(menu.open));
    document.documentElement.classList.toggle('az-mobile-menu-open', menu.open && drawer && screen.matches);
    if (menu.open && drawer) (branded ? panel.querySelector('.az-mobile-menu__close') : panel.querySelector('button,a'))?.focus();
  });
  summary.setAttribute('aria-expanded', String(menu.open));
  menu.addEventListener('keydown', event => {
    if (!menu.open) return;
    if (event.key === 'Escape') { event.preventDefault(); close(); }
    if (event.key !== 'Tab' || !drawer) return;
    const items = [...panel.querySelectorAll('a[href],button:not([disabled])')].filter(item => !item.closest('[hidden]'));
    if (event.shiftKey && document.activeElement === items[0]) { event.preventDefault(); items.at(-1)?.focus(); }
    else if (!event.shiftKey && document.activeElement === items.at(-1)) { event.preventDefault(); items[0]?.focus(); }
  });
  screen.addEventListener('change', () => { menu.open = false; document.documentElement.classList.remove('az-mobile-menu-open'); });
})();
