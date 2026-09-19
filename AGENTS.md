# AGENTS.md — rc-portal-theme

## Repository purpose

RC Portal Theme is the strictly-visual presentation layer for `my`: a
child theme of Twenty Twenty-Five that provides the app shell (sidebar,
topbar, breadcrumb), the design system (via `theme.json` Global Styles),
and PWA plumbing (manifest, service worker, offline fallback). It contains
no business logic, no persistence, and no ERP/database access of any kind.

Current version: `0.1.0-alpha12` (`style.css` header — the authoritative
version; also the one `CHANGELOG.md`'s top entry agrees with).
`Template: twentytwentyfive`. `Requires at least: 6.8`, `Requires PHP: 8.1`.

**Known drift to fix, not to trust as current**: `functions.php`'s
`RC_PORTAL_THEME_VERSION` constant is `0.1.0-alpha11` — one release behind
`style.css`. This constant is what's actually used as the cache-busting
query string on every enqueued CSS/JS file, so a deploy currently ships
with a stale asset-version tag. This is a one-line code fix in
`functions.php`, not a documentation fix — it's called out here rather
than changed in this pass because this pass is documentation-only; raise
it as a small, separate, low-risk fix.

## Architecture boundaries

- This theme has no upward or sideways dependency on RC Core. Its only
  cross-repo coupling is consuming RC Portal's public HTTP/routing/module
  surface: `use RC\Portal\Http\RouteContext;`, `RC\Portal\Http\PortalRouter`,
  `RC\Portal\Module\EmbeddedModuleInterface` — never `RC\Portal\Modules\*`
  (an individual module's internals) and never anything under `RC\Core\*`
  or `WPRC\Core\*`.
- **UI API compatibility is a real runtime gate, not just a version
  label.** `functions.php` defines `RC_PORTAL_THEME_UI_API_VERSION = 1`.
  `inc/PortalTheme.php::isPortalRequest()` only treats a request as a
  Portal request when RC Portal's own `RC_PORTAL_UI_API_VERSION` constant
  is defined **and equals** this theme's value exactly (int equality, not
  `>=`). If RC Portal ever bumps `RC_PORTAL_UI_API_VERSION` to `2` without
  a matching theme change, every Portal page falls through to an
  incompatibility notice — this is a coordinated two-repo version bump,
  never a Portal-only or theme-only one.
- One page template for everything: `templates/portal.php`, selected via
  `template_include` whenever `isPortalRequest()` is true. Content inside
  it is dispatched to `templates/portal/parts/{login,not-found,forbidden,
  dashboard,page,module}.php` based on `RouteContext`'s state — there is
  no `page-{slug}.php`-per-route convention, and no per-module template
  override currently exists on disk (only the generic `parts/module.php`
  fallback), even though `README.md` documents an optional
  `templates/portal/modules/{module-id}.php` override point.
- `templates/portal/parts/page.php` does `echo $context->pageHtml;` —
  raw, unescaped output of HTML a module produced through RC Core's UI
  Registry render contract. This is today's real, working trust boundary
  (the theme "only frames" a module's own already-rendered content) — not
  a bug, and not something to wrap in `esc_html()` right now (that would
  break every module page by literal-encoding its markup). It is **not**,
  however, the permanent model: **decided 2026-09-19, no exception**, the
  target is RC Core's declarative `PageDefinition`/`TableDefinition` data
  contract (`rc-core/docs/PORTAL-UI.md`), with the theme (via Portal) doing
  all rendering and no module ever handing the theme a pre-built HTML
  string. `pageHtml` is migration debt to retire, not a pattern to
  reinforce — don't add a second module-owned trust boundary like it while
  this migration is pending.

## Repository map

- `style.css` — theme header; the authoritative version (see above).
- `theme.json` — Global Styles: a real, populated 11-color palette
  (`defaultPalette: false`), one custom font family (Inter stack), 4 font
  sizes, fluid typography, layout/spacing tokens. `inc/PortalTheme.php`'s
  `globalStyleBridgeCss()` bridges these into CSS custom properties
  consumed by `assets/css/portal.css` — don't hand-duplicate a color/type
  value in CSS that `theme.json` already defines; extend `theme.json` and
  consume the bridged variable instead.
- `functions.php` — theme bootstrap; defines `RC_PORTAL_THEME_VERSION` and
  `RC_PORTAL_THEME_UI_API_VERSION`; enqueues `assets/css/portal.css` and
  `assets/js/{portal,pwa}.js` using `RC_PORTAL_THEME_VERSION` as the cache-
  bust string.
- `inc/PortalTheme.php` — `isPortalRequest()`/compatibility notice,
  `template_include` wiring, the dynamically-served `/portal.webmanifest`
  (`serveManifest()`) and `/portal-sw.js` service worker
  (`serveServiceWorker()`).
- `templates/portal.php` + `templates/portal/parts/*.php` — the app shell
  and its content-dispatch partials (see Architecture boundaries).
- `assets/css/portal.css` (~26 KB, single flat stylesheet, no
  preprocessor), `assets/js/portal.js` (three independent IIFEs: mobile
  nav toggle, a generic tabs component, family-fields toggle),
  `assets/js/pwa.js` (service-worker registration only),
  `assets/icons/icon-{192,512}.png`. No build step, no bundler, no
  `node_modules` — these are hand-authored files enqueued directly (unlike
  `rc-portal`'s `modules/tools`, which has a compiled-bundle split).
- `tools/preflight.php` — see Validation. Its banner string is hardcoded
  to `"RC Portal Theme 0.1.0-alpha3 — Preflight"`, three releases behind
  current — cosmetic only (doesn't affect pass/fail), left as-is in this
  documentation-only pass; worth a one-line fix alongside the
  `functions.php` version-constant fix mentioned above.
- `README.md` — version header (`0.1.0-alpha1`) is eleven releases stale
  and the body references `alpha3`-era functionality; left as-is here for
  the same reason as `rc-portal/README.md` (more than a version-number
  fix) — recommended as separate follow-up content work.

## Mandatory rules

- No `$wpdb`, no `wp_remote_get()`/`wp_remote_post()`, no
  `switch_to_blog()` anywhere in this theme — checked by
  `tools/preflight.php`. If a template needs data, it comes from
  `rc_portal()`'s public router/module surface (already-resolved values),
  never a direct query or remote call.
- No `use WPRC\Core\*` or `use RC\Core\*`, and no `use
  RC\Portal\Modules\*` (an individual module's internal namespace) — only
  `RC\Portal\Http\*`/`RC\Portal\Module\*`, RC Portal's declared public
  surface.
- No migration, no business validation, no ERP call — if a template
  appears to need one, that's a sign the logic belongs in RC Portal or the
  owning module, not here.
- French is the source and fallback language for every user-facing string
  in this theme (`__()`/`_e()`/`esc_html_e()` etc. under text domain
  `rc-portal-theme`) — this is deliberate (documented in `README.md`) to
  allow a future Polylang integration without requiring it today. Write
  new strings in French, in that text domain, the same way.
- Escape every dynamic value for its output context
  (`esc_html()`/`esc_attr()`/`esc_url()`) with the one deliberate exception
  of `$context->pageHtml` (see Architecture boundaries) — don't add a
  second unescaped-echo of anything else without a documented reason as
  strong as that one.
- PWA caching stays scoped to static assets only. The service worker
  (emitted by `PortalTheme::serveServiceWorker()`) caches only requests
  under this theme's `assets/` URL prefix; navigations and any business
  data are `fetch()`-only with an explicit `Cache-Control: no-store`
  offline fallback. **Never** add a cache entry for an authenticated page,
  a REST response, or business data to the service worker's CacheStorage
  logic — this is both a documented invariant (`README.md`) and asserted
  (though not independently verified — see Validation) by
  `tools/preflight.php`.

## Security rules

- Authentication happens entirely in RC Portal (`wp_signon()` behind
  `/login/`); this theme's `templates/portal/parts/login.php` renders only
  the form markup and posts to `PortalRouter::loginUrl()` with RC Portal's
  nonce — never add client-side or theme-side credential handling here.
- The theme sets no `Cache-Control` header anywhere except two narrow,
  correct cases: the manifest (`public, max-age=3600` — a static,
  non-personalized JSON file) and the service worker script itself
  (`no-cache, must-revalidate`). Don't add a broader cache header without
  re-reading the "no caching of authenticated pages/REST/business data"
  rule above first.

## Change workflow

- A change to `theme.json`'s palette/typography should flow through
  `globalStyleBridgeCss()`'s bridged CSS variables in
  `assets/css/portal.css`, not a hand-added duplicate value.
- A change to `RC_PORTAL_THEME_UI_API_VERSION` must be coordinated with
  `rc-portal`'s `RC_PORTAL_UI_API_VERSION` in the same change window (see
  Architecture boundaries) — never bump one without confirming the other.
- A new template part under `templates/portal/parts/` should follow the
  existing ones' pattern: consume already-resolved values from
  `RouteContext`/`rc_portal()`, escape everything except the one
  documented `pageHtml` exception, and add no new business logic.

## Validation

- `php -l` every changed PHP file.
- `php tools/preflight.php` — checks: PHP lint; a substring scan for
  `$wpdb->`, `wp_remote_get(`, `wp_remote_post(`, `switch_to_blog(`; that
  `style.css` declares `Template: twentytwentyfive`; that
  `templates/portal.php`, `assets/js/pwa.js`, `assets/js/portal.js`,
  `assets/icons/icon-192.png`, `assets/icons/icon-512.png`, and
  `templates/portal/parts/login.php` all exist; that `theme.json` parses
  and has a non-empty color palette and font-family list with
  `appearanceTools: true`; and that `assets/css/portal.css` contains
  `--wp--preset--color--accent` (i.e. actually consumes the Global Styles
  bridge).
- **Known limitation**: the script's printed summary lines for "Direct
  HTTP/ERP calls," "Multisite cross-site calls," and "PWA caches business
  data" are asserted as "0 expected" in its output text, but only the
  first two are actually backed by the substring scan above — nothing in
  the script inspects the service worker's JS caching logic itself. Don't
  treat a GREEN preflight result as proof the service worker's caching
  scope is still correct after a change to it; re-read
  `serveServiceWorker()`'s emitted JS by hand.
- No PHPUnit/JS test suite exists in this repo.

## Versioning and documentation

- Bump `style.css`'s `Version:` header for every release; keep
  `functions.php`'s `RC_PORTAL_THEME_VERSION` in sync in the same change
  (see the drift called out above — don't let it recur).
- Add a `CHANGELOG.md` entry for every release — it is the most reliably
  current-state doc in this repo (unlike `README.md`, see Repository map).

## Definition of done

- `php -l` clean; `php tools/preflight.php` GREEN.
- No new `$wpdb`, `wp_remote_*`, or `switch_to_blog()` reference anywhere
  in the diff.
- No new unescaped output besides the one documented `pageHtml` exception.
- No new service-worker cache rule that could match an authenticated page,
  a REST response, or business data.
- `style.css` and `functions.php`'s version constant bumped together;
  `CHANGELOG.md` updated.

## Read before changing

| Touching… | Read first |
|---|---|
| Global Styles / design tokens | `theme.json` + `globalStyleBridgeCss()` in `inc/PortalTheme.php` |
| The app shell / a template part | `templates/portal.php` and the relevant `templates/portal/parts/*.php` — note the one unescaped `pageHtml` exception |
| PWA / service worker | `inc/PortalTheme.php::serveServiceWorker()` directly — re-verify caching scope by hand, preflight doesn't fully cover it |
| UI API version compatibility | This file's Architecture boundaries section, then `rc-portal/AGENTS.md` |
| Presentation ownership / `pageHtml` | Decided (2026-09-19, no exception) — `rc-core/docs/PORTAL-UI.md`'s implementation-status note and this file's Architecture boundaries section above, not the open-questions doc |
