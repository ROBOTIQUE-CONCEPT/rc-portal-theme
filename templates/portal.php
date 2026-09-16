<?php
/**
 * Main RC Portal application template.
 *
 * @var \RC\Portal\Http\RouteContext|null $context
 */

declare(strict_types=1);

use RC\Portal\Http\PortalRouter;
use RC\Portal\Module\EmbeddedModuleInterface;

if (! defined('ABSPATH')) {
    exit;
}

$context = rc_portal()->router()->context();
if (! $context instanceof \RC\Portal\Http\RouteContext) {
    status_header(404);
    exit;
}
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<?php if ($context->isLogin()) : ?>
    <?php require RC_PORTAL_THEME_DIR . 'templates/portal/parts/login.php'; ?>
<?php else : ?>
    <?php
    $module = $context->module;
    $title = $context->pageLabel ?? ($module instanceof EmbeddedModuleInterface
        ? $module->descriptor()->label
        : __('Tableau de bord', 'rc-portal-theme'));
    $user = wp_get_current_user();
    $navigation = rc_portal()->router()->visibleModuleNavigation(rc_portal()->modules()->all());
    // A page resolved through RC Core's UI Registry carries no `$context->module`
    // (Portal never loads an `EmbeddedModuleInterface` for it), so the active
    // module in the sidebar is derived from the route's first segment, which
    // is always the owning module id by construction (see `UiRegistry::register()`).
    $activeModuleId = $module instanceof EmbeddedModuleInterface
        ? $module->descriptor()->id
        : (string) (explode('/', (string) $context->route)[0] ?? '');
    $displayName = trim((string) $user->display_name) !== '' ? (string) $user->display_name : (string) $user->user_login;
    $nameParts = preg_split('/\s+/u', trim($displayName)) ?: [];
    $initials = '';
    foreach (array_slice($nameParts, 0, 2) as $namePart) {
        $initials .= function_exists('mb_substr') ? mb_substr($namePart, 0, 1) : substr($namePart, 0, 1);
    }
    $initials = strtoupper($initials !== '' ? $initials : 'RC');

    // The interface never reasons about capabilities or role names directly:
    // it only labels itself from the persona RC Core resolves for the current
    // user (see `CoreBridge::personaKey()`), which is why an 'admin' account
    // reads as an internal user here — the Portal has no distinct "admin"
    // space of its own.
    $persona = rc_portal()->core()->personaKey($user);
    $spaceLabel = match ($persona) {
        'internal', 'admin' => __('Espace interne', 'rc-portal-theme'),
        'partner' => __('Espace partenaire', 'rc-portal-theme'),
        'customer' => __('Espace client', 'rc-portal-theme'),
        default => __('Espace Robotique Concept', 'rc-portal-theme'),
    };
    $identityLabel = match ($persona) {
        'internal', 'admin' => __('Utilisateur interne', 'rc-portal-theme'),
        'partner' => __('Utilisateur partenaire', 'rc-portal-theme'),
        'customer' => __('Utilisateur client', 'rc-portal-theme'),
        default => __('Utilisateur connecté', 'rc-portal-theme'),
    };

    // Breadcrumb trail: every ancestor is a real link, only the current page
    // is plain text. "Accueil" is itself the current page on the dashboard,
    // and the module level is skipped when the module's own root page is the
    // current page (it would otherwise duplicate the final crumb).
    $breadcrumb = [
        ['label' => __('Accueil', 'rc-portal-theme'), 'url' => $context->isDashboard() ? null : PortalRouter::homeUrl()],
    ];
    if (! $context->isDashboard()) {
        $activeEntry = $navigation[$activeModuleId] ?? null;
        $isModuleRoot = $activeEntry !== null && $context->route === $activeModuleId;
        if ($activeEntry !== null && ! $isModuleRoot) {
            $breadcrumb[] = ['label' => $activeEntry['label'], 'url' => $activeEntry['url']];
        }
        $breadcrumb[] = ['label' => $title, 'url' => null];
    }
    ?>
    <div class="rc-app-shell">
        <aside class="rc-sidebar" id="rc-portal-sidebar" aria-label="<?php esc_attr_e('Navigation principale', 'rc-portal-theme'); ?>">
            <a class="rc-brand" href="<?php echo esc_url(PortalRouter::homeUrl()); ?>">
                <span class="rc-brand__mark" aria-hidden="true">RC</span>
                <span class="rc-brand__copy">
                    <strong><?php esc_html_e('Robotique Concept', 'rc-portal-theme'); ?></strong>
                    <small><?php echo esc_html($spaceLabel); ?></small>
                </span>
            </a>

            <div class="rc-sidebar-section-label"><?php esc_html_e('Navigation', 'rc-portal-theme'); ?></div>
            <nav class="rc-nav">
                <a class="rc-nav-item<?php echo $context->isDashboard() ? ' is-active' : ''; ?>" href="<?php echo esc_url(PortalRouter::homeUrl()); ?>">
                    <span class="rc-nav-item__icon" aria-hidden="true">TB</span>
                    <span><?php esc_html_e('Tableau de bord', 'rc-portal-theme'); ?></span>
                </a>

                <?php foreach ($navigation as $moduleId => $entry) : ?>
                    <?php
                    $active = $activeModuleId === $moduleId;
                    $icon = trim((string) $entry['icon']) !== '' ? (string) $entry['icon'] : strtoupper(substr($entry['label'], 0, 2));
                    $children = $entry['children'];
                    ?>
                    <div class="rc-nav-group<?php echo $active ? ' is-active' : ''; ?>">
                        <a class="rc-nav-item<?php echo $active ? ' is-active' : ''; ?>" href="<?php echo esc_url($entry['url']); ?>">
                            <span class="rc-nav-item__icon" aria-hidden="true"><?php echo esc_html($icon); ?></span>
                            <span><?php echo esc_html($entry['label']); ?></span>
                        </a>
                        <?php if ($children !== []) : ?>
                            <div class="rc-nav-children">
                                <?php foreach ($children as $child) : ?>
                                    <?php $childActive = $context->route === $child['route']; ?>
                                    <a class="rc-nav-item rc-nav-item--child<?php echo $childActive ? ' is-active' : ''; ?>" href="<?php echo esc_url(PortalRouter::routeUrl($child['route'])); ?>">
                                        <span><?php echo esc_html($child['label']); ?></span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </nav>
        </aside>

        <button class="rc-sidebar-backdrop" type="button" data-rc-nav-close aria-label="<?php esc_attr_e('Fermer la navigation', 'rc-portal-theme'); ?>"></button>

        <div class="rc-workspace">
            <header class="rc-topbar">
                <div class="rc-topbar__left">
                    <button class="rc-nav-toggle-mobile" type="button" data-rc-nav-toggle aria-controls="rc-portal-sidebar" aria-expanded="false" aria-label="<?php esc_attr_e('Ouvrir la navigation', 'rc-portal-theme'); ?>">
                        <span></span><span></span><span></span>
                    </button>
                    <div class="rc-topbar__breadcrumb">
                        <?php foreach ($breadcrumb as $index => $crumb) : ?>
                            <?php if ($index > 0) : ?><span aria-hidden="true">/</span><?php endif; ?>
                            <?php if ($crumb['url'] !== null) : ?>
                                <a href="<?php echo esc_url($crumb['url']); ?>"><?php echo esc_html($crumb['label']); ?></a>
                            <?php else : ?>
                                <strong><?php echo esc_html($crumb['label']); ?></strong>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="rc-topbar__account">
                    <span class="rc-topbar__avatar" aria-hidden="true"><?php echo esc_html($initials); ?></span>
                    <span class="rc-topbar__identity">
                        <strong><?php echo esc_html($displayName); ?></strong>
                        <small><?php echo esc_html($identityLabel); ?></small>
                    </span>
                    <div class="rc-topbar__actions">
                        <?php if (is_super_admin()) : ?>
                            <a class="rc-topbar__action" href="<?php echo esc_url(admin_url()); ?>"><?php esc_html_e('Administration', 'rc-portal-theme'); ?></a>
                        <?php endif; ?>
                        <a class="rc-topbar__action" href="<?php echo esc_url(wp_logout_url(PortalRouter::loginUrl())); ?>"><?php esc_html_e('Se déconnecter', 'rc-portal-theme'); ?></a>
                    </div>
                </div>
            </header>

            <main class="rc-main">
                <?php
                if ($context->status === 404) {
                    require RC_PORTAL_THEME_DIR . 'templates/portal/parts/not-found.php';
                } elseif ($context->status === 403) {
                    require RC_PORTAL_THEME_DIR . 'templates/portal/parts/forbidden.php';
                } elseif ($context->isDashboard()) {
                    require RC_PORTAL_THEME_DIR . 'templates/portal/parts/dashboard.php';
                } elseif ($context->hasUiPage()) {
                    require RC_PORTAL_THEME_DIR . 'templates/portal/parts/page.php';
                } elseif ($module instanceof EmbeddedModuleInterface) {
                    $moduleTemplate = RC_PORTAL_THEME_DIR . 'templates/portal/modules/' . sanitize_key($module->descriptor()->id) . '.php';
                    if (is_file($moduleTemplate)) {
                        require $moduleTemplate;
                    } else {
                        require RC_PORTAL_THEME_DIR . 'templates/portal/parts/module.php';
                    }
                }
                ?>
            </main>
        </div>
    </div>
<?php endif; ?>
<?php wp_footer(); ?>
</body>
</html>
