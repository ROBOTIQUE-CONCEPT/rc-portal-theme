<?php

declare(strict_types=1);

namespace RC\PortalTheme;

use RC\Portal\Http\RouteContext;

/**
 * Presentation adapter for the RC Portal runtime.
 *
 * The theme consumes Portal's public request/module API but owns all HTML,
 * CSS, installable PWA assets and future client-side presentation code.
 */
final class PortalTheme
{
    private static ?self $instance = null;

    private function __construct()
    {
    }

    public static function instance(): self
    {
        return self::$instance ??= new self();
    }

    public function register(): void
    {
        add_action('after_setup_theme', [$this, 'setup']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueAssets']);
        add_action('wp_head', [$this, 'renderPwaHead'], 1);
        add_action('template_redirect', [$this, 'servePwaEndpoint'], -50);
        add_filter('template_include', [$this, 'filterTemplate'], 99);
        add_filter('body_class', [$this, 'filterBodyClasses']);
        add_filter('show_admin_bar', [$this, 'hideAdminBar']);
        add_action('admin_notices', [$this, 'renderCompatibilityNotice']);
    }

    /**
     * The Portal is its own application shell — the WordPress admin bar is
     * never shown there, whatever the connected user's role/persona.
     */
    public function hideAdminBar(bool $show): bool
    {
        if ($this->isPortalRequest()) {
            return false;
        }

        return $show;
    }

    public function setup(): void
    {
        add_theme_support('title-tag');
        add_theme_support('responsive-embeds');
        add_theme_support('html5', ['style', 'script', 'navigation-widgets']);
    }

    public function enqueueAssets(): void
    {
        if (! $this->isPortalRequest()) {
            return;
        }

        wp_enqueue_style(
            'rc-portal-theme-app',
            RC_PORTAL_THEME_URL . 'assets/css/portal.css',
            [],
            RC_PORTAL_THEME_VERSION
        );

        wp_add_inline_style(
            'rc-portal-theme-app',
            $this->globalStyleBridgeCss()
        );

        wp_enqueue_script(
            'rc-portal-theme-app',
            RC_PORTAL_THEME_URL . 'assets/js/portal.js',
            [],
            RC_PORTAL_THEME_VERSION,
            true
        );

        wp_enqueue_script(
            'rc-portal-theme-pwa',
            RC_PORTAL_THEME_URL . 'assets/js/pwa.js',
            [],
            RC_PORTAL_THEME_VERSION,
            true
        );
    }

    public function renderPwaHead(): void
    {
        if (! $this->isPortalRequest()) {
            return;
        }

        printf('<link rel="manifest" href="%s">' . "\n", esc_url(home_url('/portal.webmanifest')));
        echo '<meta name="theme-color" content="#101820">' . "\n";
        echo '<meta name="mobile-web-app-capable" content="yes">' . "\n";
        echo '<meta name="apple-mobile-web-app-capable" content="yes">' . "\n";
        echo '<meta name="apple-mobile-web-app-status-bar-style" content="default">' . "\n";
        echo '<meta name="apple-mobile-web-app-title" content="RC Portal">' . "\n";
        printf('<link rel="apple-touch-icon" href="%s">' . "\n", esc_url(RC_PORTAL_THEME_URL . 'assets/icons/icon-192.png'));
    }

    public function servePwaEndpoint(): void
    {
        $path = $this->requestPath();

        if ($path === '/portal.webmanifest') {
            $this->serveManifest();
        }

        if ($path === '/portal-sw.js') {
            $this->serveServiceWorker();
        }
    }

    public function filterTemplate(string $template): string
    {
        if (! $this->isPortalRequest()) {
            return $template;
        }

        $portalTemplate = RC_PORTAL_THEME_DIR . 'templates/portal.php';
        return is_file($portalTemplate) ? $portalTemplate : $template;
    }

    /** @param list<string> $classes */
    public function filterBodyClasses(array $classes): array
    {
        if ($this->isPortalRequest()) {
            $classes[] = 'rc-portal-app';

            $context = $this->context();
            if ($context?->isLogin()) {
                $classes[] = 'rc-portal-login';
            }
        }

        return $classes;
    }

    public function renderCompatibilityNotice(): void
    {
        if (! is_super_admin()) {
            return;
        }

        if (! function_exists('rc_portal') || ! defined('RC_PORTAL_UI_API_VERSION')) {
            printf(
                '<div class="notice notice-warning"><p>%s</p></div>',
                esc_html__('RC Portal Theme nécessite le plugin RC Portal pour afficher l’interface applicative.', 'rc-portal-theme')
            );
            return;
        }

        if ((int) RC_PORTAL_UI_API_VERSION !== RC_PORTAL_THEME_UI_API_VERSION) {
            printf(
                '<div class="notice notice-error"><p>%s</p></div>',
                esc_html(
                    sprintf(
                        __('Versions incompatibles de l’API UI RC Portal. Plugin : %1$d ; thème : %2$d.', 'rc-portal-theme'),
                        (int) RC_PORTAL_UI_API_VERSION,
                        RC_PORTAL_THEME_UI_API_VERSION
                    )
                )
            );
        }
    }

    public function isPortalRequest(): bool
    {
        return function_exists('rc_portal')
            && defined('RC_PORTAL_UI_API_VERSION')
            && (int) RC_PORTAL_UI_API_VERSION === RC_PORTAL_THEME_UI_API_VERSION
            && rc_portal()->router()->isPortalRequest();
    }

    public function context(): ?RouteContext
    {
        if (! $this->isPortalRequest()) {
            return null;
        }

        return rc_portal()->router()->context();
    }

    private function serveManifest(): never
    {
        $manifest = [
            'id' => home_url('/'),
            'name' => 'Portail Robotique Concept',
            'short_name' => 'RC Portal',
            'description' => 'Plateforme métier privée Robotique Concept',
            'start_url' => home_url('/'),
            'scope' => home_url('/'),
            'display' => 'standalone',
            'background_color' => '#f5f6f7',
            'theme_color' => '#101820',
            'icons' => [
                [
                    'src' => RC_PORTAL_THEME_URL . 'assets/icons/icon-192.png',
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'any maskable',
                ],
                [
                    'src' => RC_PORTAL_THEME_URL . 'assets/icons/icon-512.png',
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any maskable',
                ],
            ],
        ];

        status_header(200);
        header('Content-Type: application/manifest+json; charset=utf-8');
        header('Cache-Control: public, max-age=3600');
        echo wp_json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function serveServiceWorker(): never
    {
        $assetPath = (string) wp_parse_url(RC_PORTAL_THEME_URL . 'assets/', PHP_URL_PATH);
        $cacheName = 'rc-portal-static-' . preg_replace('/[^a-zA-Z0-9._-]/', '-', RC_PORTAL_THEME_VERSION);
        $offlineTitle = wp_json_encode(__('RC Portal — Hors connexion', 'rc-portal-theme'));
        $offlineMessage = wp_json_encode(__('Une connexion réseau est nécessaire pour accéder aux données métier.', 'rc-portal-theme'));

        status_header(200);
        header('Content-Type: application/javascript; charset=utf-8');
        header('Service-Worker-Allowed: /');
        header('Cache-Control: no-cache, must-revalidate');

        echo "const CACHE_NAME = " . wp_json_encode($cacheName) . ";\n";
        echo "const ASSET_PREFIX = " . wp_json_encode($assetPath) . ";\n";
        echo <<<'JS'
self.addEventListener('install', (event) => {
  event.waitUntil(self.skipWaiting());
});

self.addEventListener('activate', (event) => {
  event.waitUntil((async () => {
    const names = await caches.keys();
    await Promise.all(names.filter((name) => name.startsWith('rc-portal-static-') && name !== CACHE_NAME).map((name) => caches.delete(name)));
    await self.clients.claim();
  })());
});

self.addEventListener('fetch', (event) => {
  const request = event.request;
  if (request.method !== 'GET') return;

  const url = new URL(request.url);
  if (url.origin !== self.location.origin) return;

  // Only immutable presentation assets may be persisted on the device.
  if (url.pathname.startsWith(ASSET_PREFIX)) {
    event.respondWith((async () => {
      const cached = await caches.match(request);
      if (cached) return cached;
      const response = await fetch(request);
      if (response.ok) {
        const cache = await caches.open(CACHE_NAME);
        await cache.put(request, response.clone());
      }
      return response;
    })());
    return;
  }

  // Authenticated HTML, REST, documents and business data are network-only.
  if (request.mode === 'navigate') {
    event.respondWith(fetch(request).catch(() => new Response(
      '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>' + 
JS;
        echo $offlineTitle;
        echo <<<'JS'
 + '</title></head><body><main style="font-family:system-ui;padding:2rem;max-width:40rem;margin:auto"><h1>' + 
JS;
        echo $offlineTitle;
        echo <<<'JS'
 + '</h1><p>' + 
JS;
        echo $offlineMessage;
        echo <<<'JS'
 + '</p></main></body></html>',
      { headers: { 'Content-Type': 'text/html; charset=utf-8', 'Cache-Control': 'no-store' } }
    )));
  }
});
JS;
        exit;
    }

    /**
     * Bridges WordPress Global Styles user choices into Portal semantic tokens.
     *
     * This keeps the app shell coupled to WordPress' native Styles UI rather
     * than introducing a second theme-settings screen.
     */
    private function globalStyleBridgeCss(): string
    {
        if (! function_exists('wp_get_global_styles')) {
            return '';
        }

        $context = ['transforms' => ['resolve-variables']];
        $background = $this->safeCssValue(wp_get_global_styles(['color', 'background'], $context), '#f5f6f8');
        $text = $this->safeCssValue(wp_get_global_styles(['color', 'text'], $context), '#111827');
        $accent = $this->safeCssValue(wp_get_global_styles(['elements', 'link', 'color', 'text'], $context), '#e85d2a');
        $fontFamily = $this->safeCssValue(
            wp_get_global_styles(['typography', 'fontFamily'], $context),
            'Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif'
        );

        return sprintf(
            ':root{--rc-bg:%1$s;--rc-text:%2$s;--rc-accent:%3$s;font-family:%4$s}',
            $background,
            $text,
            $accent,
            $fontFamily
        );
    }

    private function safeCssValue(mixed $value, string $fallback): string
    {
        if (! is_string($value) || trim($value) === '') {
            return $fallback;
        }

        $value = wp_strip_all_tags(trim($value));
        if (preg_match('/[{};]/', $value)) {
            return $fallback;
        }

        return $value;
    }

    private function requestPath(): string
    {
        $uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '/';
        $path = (string) wp_parse_url($uri, PHP_URL_PATH);
        return '/' . ltrim($path, '/');
    }
}
