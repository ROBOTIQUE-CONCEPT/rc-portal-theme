# Changelog

## 0.1.0-alpha12
- `.rc-toolbar`'s styled-input selector now also covers `input[type="date"]` and `input[type="datetime-local"]` (previously only `select`, `input[type="text"]`, `input[type="search"]`) — needed by the KUKA archive analyzer's new merged message-log table (RC Portal 0.3.0-alpha17), whose date/time range filter uses `datetime-local` inputs inside a `.rc-toolbar`.

## 0.1.0-alpha11
- Fix `.rc-module-grid--square` (added in 0.1.0-alpha10): the grid's column track was still `1fr`, so a single card — the common case, e.g. the `tools` dashboard with one tool so far — stretched to fill the whole row instead of a square the size of cards elsewhere. The modifier now also fixes the column track to `minmax(240px, 240px)`, matching the visual size of a regular `.rc-module-card`; `auto-fill` still wraps further cards onto new rows as more tools are added.

## 0.1.0-alpha10
- `templates/portal/parts/page.php` no longer renders its own generic `<header class="rc-page-header"><h1>{label}</h1></header>` — every module page routed through it already renders its own, real `.rc-page-header` (now itself a `<header>` element, not a `div`), so the two no longer stack as a duplicated title. Affects every module using this template (Products, Tools).
- Add `.rc-module-grid--square`, an additive modifier a module's own dashboard can opt into for square sub-tool cards (`aspect-ratio: 1`) — first consumed by the `tools` module's dashboard (RC Portal 0.3.0-alpha13); the default `.rc-module-grid` (Portal home, Products dashboard) is unchanged.

## 0.1.0-alpha9
- Add `.rc-erp-columns`/`.rc-erp-column`, a 3-column layout (stacked below tablet width) for a read-only data card presenting grouped fields — first consumed by the `products` module's Axonaut card (RC Portal 0.3.0-alpha11).
- Add `.rc-dashboard-actions`, a right-aligned single-action row for a page whose primary heading was removed.
- Lazy WYSIWYG initialization (`assets/js/portal.js`) no longer enables quicktags, matching the eager-tab editor's own config — both stay pure visual editors, no Texte/Visuel toggle.

## 0.1.0-alpha8
- Make `.rc-topbar` sticky (`position: sticky; top: 0`) so it stays visible on vertical scroll instead of scrolling away with the page content.
- Add generic tabs primitives (`.rc-tabs__nav`/`.rc-tab`/`.rc-tabpanel`, driven by `data-rc-tabs`/`data-rc-tab`/`data-rc-tabpanel` in `assets/js/portal.js`) and a generic typology/variant field-toggle primitive (`.rc-family-fields[hidden]`, driven by `data-rc-family-toggle`/`data-rc-family-fields`) — both module-agnostic, first consumed by the `products` module's fiche page (RC Portal 0.3.0-alpha10) but usable by any module presenting per-variant panels.
- Add `.rc-list-toolbar-actions` (a compact count + action row, for a list page that no longer needs a full page-header block) and `.rc-pagination`.
- Add `.rc-erp-description`/`.rc-erp-description__body` for a read-only rich-text block rendered below a field grid, and `.rc-erp-group`/`.rc-card__header-actions` for grouping a card's fields and header badges.
- `assets/js/portal.js` is now three independent IIFEs instead of one: the existing mobile-nav toggle no longer gates the new tabs/family-toggle behavior from initializing when no mobile-nav toggle element is present on the page.

## 0.1.0-alpha7
- Add generic module-content primitives, additive only, for modules presenting lists as tables and fiches as cards/sections: `.rc-toolbar` (filter/search bar), `.rc-table-wrap`/`.rc-table`, `.rc-badge` (+ `--accent`/`--success`/`--muted`), `.rc-card-grid`/`.rc-card`/`.rc-card__header` (+ `.rc-card--stat` for a dashboard stat tile), `.rc-field-grid`/`.rc-field` (+ `.rc-field--help`).
- Add `.rc-portal-alert--success`, alongside the existing `--error`, for a positive inline confirmation banner.
- No template or PHP change: existing `.rc-module-grid`/`.rc-module-card` dashboard-card styles were already generic and reusable as-is by a module's own dashboard.

## 0.1.0-alpha6
- The topbar breadcrumb now links every ancestor of the current page ("Accueil" and, when applicable, the current module) instead of showing them as plain text; only the current page stays unlinked. The module level is omitted when the module's own root page is the current page, so it doesn't repeat the final crumb.
- Remove the now-unused `.rc-breadcrumb` styles (the per-page breadcrumb they served was removed in 0.1.0-alpha5).

## 0.1.0-alpha5
- Never show the WordPress admin bar on Portal routes, regardless of the connected user's role, via a `show_admin_bar` filter scoped to Portal requests.
- Remove the per-page breadcrumb from the module/UI-page headers; the single breadcrumb in the topbar is now the only one (it was effectively duplicated between the topbar and each page header).
- Remove the user card and action links from the bottom of the sidebar (duplicated the topbar identity); "Se déconnecter" (and the super-admin "Administration" link) move to the topbar, next to the current user.
- Sidebar subtitle and topbar identity label now reflect the connected user's persona ("Espace interne" / "Espace client" / "Espace partenaire" and "Utilisateur interne" / "Utilisateur client" / "Utilisateur partenaire") via RC Portal's `CoreBridge::personaKey()`, instead of a static "Plateforme métier" / "Utilisateur connecté".
- Sidebar navigation and the dashboard module grid now both consume `PortalRouter::visibleModuleNavigation()`: a module is only listed when the current user can actually reach it (its own page or at least one of its child pages), instead of every logged-in user seeing every module regardless of the configured permissions.

## 0.1.0-alpha4
- Add `templates/portal/parts/page.php`, rendering a page resolved through RC Core's UI Registry (a module dashboard or one of its declared child pages) with the same header/layout as the existing module placeholder.
- Sidebar navigation now renders each module's declared child pages (via `PortalRouter::navigationChildren()`) under its own entry, grouped in a new `.rc-nav-group`/`.rc-nav-children` structure, with `.rc-nav-item--child` styling for the nested links.
- No new business logic in the theme: navigation grouping and page rendering both consume data RC Portal already resolved; the theme only lays it out.

## 0.1.0-alpha3
- Restore the richer RC login wall and welcome dashboard visual language in the theme.
- Make all user-facing Portal copy French-first.
- Consume WordPress Global Styles variables for Portal colors and typography.
- Add direct module navigation support for `/maintenance/`, `/products/`, `/inventory/` and `/leads/`.
- Add responsive application-shell navigation without moving business logic into the theme.

## 0.1.0-alpha2

- Add the dedicated private Portal login UI for the `/login/` runtime route.
- Add installable PWA metadata, manifest, service worker registration and 192/512 icons.
- Cache only static theme assets in the service worker.
- Keep authenticated HTML, REST APIs, documents and business data network-only.
- Add a neutral offline navigation fallback containing no business data.
- Keep all authentication/authorization decisions inside RC Portal; the theme remains presentation-only.

## 0.1.0-alpha1

- Initial dedicated Portal presentation theme.
- Child theme of Twenty Twenty-Five.
