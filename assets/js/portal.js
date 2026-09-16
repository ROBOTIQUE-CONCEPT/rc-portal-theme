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

/**
 * Generic tabs component (`data-rc-tabs` / `data-rc-tab` / `data-rc-tabpanel`).
 * Module-agnostic on purpose — any module can use this markup shape, not just
 * Products. A tab's panel may contain a lazily-initialized WYSIWYG editor
 * (`textarea.rc-wysiwyg-lazy`): TinyMCE renders as a 0×0 box if initialized
 * while its container is `hidden`, so those editors are only handed to
 * `wp.editor.initialize()` the first time their tab is actually shown; the
 * tab that starts visible is rendered eagerly server-side (see
 * ProductsPages::renderI18nFields) and is skipped here.
 */
(() => {
  'use strict';

  const groups = document.querySelectorAll('[data-rc-tabs]');
  if (!groups.length) {
    return;
  }

  const initializedEditors = new Set();

  const initLazyEditor = (panel) => {
    const textarea = panel.querySelector('textarea.rc-wysiwyg-lazy');
    if (!textarea || initializedEditors.has(textarea.id)) {
      return;
    }

    initializedEditors.add(textarea.id);

    if (window.wp && window.wp.editor && typeof window.wp.editor.initialize === 'function') {
      window.wp.editor.initialize(textarea.id, {
        tinymce: {
          wpautop: true,
          toolbar1: 'bold,italic,bullist,numlist,link,unlink,undo,redo',
        },
        quicktags: true,
        mediaButtons: false,
      });
    }
  };

  groups.forEach((group) => {
    const tabs = group.querySelectorAll('[data-rc-tab]');
    const panels = group.querySelectorAll('[data-rc-tabpanel]');

    tabs.forEach((tab) => {
      tab.addEventListener('click', () => {
        const key = tab.getAttribute('data-rc-tab');

        tabs.forEach((otherTab) => {
          otherTab.setAttribute('aria-selected', otherTab === tab ? 'true' : 'false');
        });

        panels.forEach((panel) => {
          const isMatch = panel.getAttribute('data-rc-tabpanel') === key;
          panel.hidden = !isMatch;
          if (isMatch) {
            initLazyEditor(panel);
          }
        });
      });
    });
  });
})();

/**
 * Family-fields dynamic panel (`data-rc-family-toggle` on the Typologie
 * select, `data-rc-family-fields="slug,slug"` on each candidate panel).
 * Lets a fiche's typology-specific fields "permute" live when the select
 * changes, without a page reload — every variant is already in the DOM
 * (see ProductsPages::renderFamilyFields), this just flips `hidden`.
 */
(() => {
  'use strict';

  const selects = document.querySelectorAll('[data-rc-family-toggle]');
  if (!selects.length) {
    return;
  }

  selects.forEach((select) => {
    const panels = document.querySelectorAll('[data-rc-family-fields]');

    select.addEventListener('change', () => {
      const family = select.value;

      panels.forEach((panel) => {
        const families = (panel.getAttribute('data-rc-family-fields') || '').split(',');
        panel.hidden = !families.includes(family);
      });
    });
  });
})();
