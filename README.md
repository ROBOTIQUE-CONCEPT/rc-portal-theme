# RC Portal Theme

Version: `0.1.0-alpha1`
Parent: `Twenty Twenty-Five` (`twentytwentyfive`)

Dedicated presentation layer for RC Portal on `my`.

## Responsibilities

The theme owns all Portal frontend presentation:

- HTML templates;
- navigation shell;
- CSS/design tokens;
- responsive application layout;
- future Vue/browser-side UI components;
- module-specific visual templates.

The theme does **not** own business logic, persistence, migrations or direct ERP access.

## Dependency direction

The theme consumes RC Portal UI API version `1`.
RC Portal itself has no dependency on this theme.

## Template convention

- `templates/portal.php`: application shell;
- `templates/portal/parts/`: generic Portal views;
- `templates/portal/modules/<module-id>.php`: optional module-specific presentation overrides.

When a module-specific template is absent, the generic module foundation view is used.

## Parent theme

Twenty Twenty-Five must be installed. It does not need to be activated independently; WordPress loads it as the parent of RC Portal Theme.

## Private app UI and PWA (0.1.0-alpha3)

The theme owns the `/login/` presentation and installable PWA shell. It does not authenticate users itself; RC Portal owns authentication and authorization.

The PWA service worker caches only static files below the theme `assets/` directory. Authenticated HTML, REST responses, documents and business data are never written to CacheStorage by this release.


## Apparence native WordPress

RC Portal Theme est un thème enfant de Twenty Twenty-Five. Les couleurs et la typographie de base sont déclarées dans `theme.json` et sont donc pilotables depuis **Apparence → Éditeur → Styles** sur le site `my`. Le CSS Portal consomme les variables Global Styles WordPress au lieu de dupliquer une page de réglages propriétaire.

## Langue

Le français est la langue source et la langue de repli de l’interface Portal. Les chaînes restent internationalisables via le text domain `rc-portal-theme` afin de permettre une intégration Polylang ultérieure sans imposer cette dépendance aujourd’hui.
