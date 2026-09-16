(() => {
  'use strict';

  const root = document.documentElement;
  const toggle = document.querySelector('[data-rc-nav-toggle]');
  const backdrop = document.querySelector('[data-rc-nav-close]');

  if (!toggle) {
    return;
  }

  const close = () => {
    root.classList.remove('rc-nav-open');
    toggle.setAttribute('aria-expanded', 'false');
  };

  toggle.addEventListener('click', () => {
    const opened = root.classList.toggle('rc-nav-open');
    toggle.setAttribute('aria-expanded', opened ? 'true' : 'false');
  });

  if (backdrop) {
    backdrop.addEventListener('click', close);
  }

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      close();
    }
  });
})();
