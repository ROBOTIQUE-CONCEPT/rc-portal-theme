<?php
/**
 * RC Portal Theme bootstrap.
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

define('RC_PORTAL_THEME_VERSION', '0.1.0-alpha7');
define('RC_PORTAL_THEME_UI_API_VERSION', 1);
define('RC_PORTAL_THEME_DIR', __DIR__ . '/');
define('RC_PORTAL_THEME_URL', get_stylesheet_directory_uri() . '/');

require_once RC_PORTAL_THEME_DIR . 'inc/PortalTheme.php';

\RC\PortalTheme\PortalTheme::instance()->register();
