<?php
/**
 * Progressive Web App (PWA) Support
 * - Web App Manifest
 * - Offline page
 * - Service worker (offline caching)
 * - Install prompt
 * - App icons
 * @package HikmahNews
 */
if (!defined('ABSPATH')) exit;

define('HIKMAHNEWS_MANIFEST_SLUG', 'hikmahnews-manifest.json');
define('HIKMAHNEWS_SW_SLUG', 'hikmahnews-sw.js');

// 1. Web App Manifest <head> tags
function hikmahnews_pwa_manifest() {
    if (is_admin()) return;
    $icon = get_template_directory_uri() . '/assets/images/';
    $name = get_bloginfo('name');
    ?>
    <link rel="manifest" href="<?php echo esc_url(home_url('/' . HIKMAHNEWS_MANIFEST_SLUG)); ?>">
    <meta name="theme-color" content="<?php echo esc_attr(hikmahnews_option('colors', 'primary', '#DC2626')); ?>">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="<?php echo esc_attr(wp_trim_words($name, 2, '')); ?>">
    <link rel="apple-touch-icon" href="<?php echo esc_url($icon . 'icon-192.png'); ?>">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="application-name" content="<?php echo esc_attr($name); ?>">
    <?php
}
add_action('wp_head', 'hikmahnews_pwa_manifest', 2);

// 2. Serve manifest.json
function hikmahnews_serve_manifest() {
    if (hikmahnews_is_virtual_file(HIKMAHNEWS_MANIFEST_SLUG)) {
        header('Content-Type: application/manifest+json');
        $icon = get_template_directory_uri() . '/assets/images/';
        $primary = hikmahnews_option('colors', 'primary', '#DC2626');
        $manifest = [
            'name'             => get_bloginfo('name'),
            'short_name'       => wp_trim_words(get_bloginfo('name'), 2, ''),
            'description'      => get_bloginfo('description'),
            'start_url'        => home_url('/'),
            'scope'            => home_url('/'),
            'display'          => 'standalone',
            'background_color' => '#ffffff',
            'theme_color'      => $primary,
            'orientation'      => 'portrait-primary',
            'icons'            => [
                ['src' => $icon . 'icon-192.png', 'sizes' => '192x192', 'type' => 'image/png'],
                ['src' => $icon . 'icon-512.png', 'sizes' => '512x512', 'type' => 'image/png'],
                ['src' => $icon . 'icon-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
            ],
        ];
        echo wp_json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
add_action('init', 'hikmahnews_serve_manifest');

// 3. Offline Page
function hikmahnews_offline_page() {
    if (hikmahnews_is_virtual_file('offline')) {
        header('Content-Type: text/html; charset=' . get_option('blog_charset'));
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width,initial-scale=1">
            <title>Offline — <?php bloginfo('name'); ?></title>
            <style>
                body { font-family: Inter, -apple-system, sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; background: #fafafa; text-align: center; }
                .offline { max-width: 400px; padding: 40px; }
                .offline h1 { font-size: 48px; margin-bottom: 8px; }
                .offline p { color: #666; margin-bottom: 24px; }
                .offline button { padding: 12px 24px; background: #dc2626; color: white; border: none; border-radius: 8px; font-size: 16px; cursor: pointer; }
            </style>
        </head>
        <body>
        <div class="offline">
            <h1>📡</h1>
            <h2>You're Offline</h2>
            <p>Check your internet connection and try again.</p>
            <button onclick="location.reload()">Try Again</button>
        </div>
        </body>
        </html>
        <?php
        exit;
    }
}
add_action('init', 'hikmahnews_offline_page');

// 4. Service Worker (offline caching)
function hikmahnews_serve_sw() {
    if (hikmahnews_is_virtual_file(HIKMAHNEWS_SW_SLUG)) {
        header('Content-Type: application/javascript; charset=UTF-8');
        header('Cache-Control: no-cache');
        $home = home_url('/');
        $offline = home_url('/offline');
        ?>
        (function() {
            var CACHE = 'hikmahnews-cache-v1';
            var CORE = ['<?php echo $home; ?>', '<?php echo $offline; ?>'];

            self.addEventListener('install', function(event) {
                event.waitUntil(
                    caches.open(CACHE).then(function(cache) {
                        return cache.addAll(CORE);
                    }).then(function() { return self.skipWaiting(); })
                );
            });

            self.addEventListener('activate', function(event) {
                event.waitUntil(
                    caches.keys().then(function(keys) {
                        return Promise.all(keys.filter(function(k) { return k !== CACHE; })
                            .map(function(k) { return caches.delete(k); }));
                    }).then(function() { return self.clients.claim(); })
                );
            });

            self.addEventListener('fetch', function(event) {
                var req = event.request;
                if (req.method !== 'GET') return;
                var url = new URL(req.url);
                if (url.origin !== location.origin) return;          // skip cross-origin
                if (url.pathname.indexOf('/wp-admin') === 0) return; // never cache admin

                // Navigation requests: network-first, offline fallback
                if (req.mode === 'navigate') {
                    event.respondWith(
                        fetch(req).then(function(res) {
                            var copy = res.clone();
                            caches.open(CACHE).then(function(c) { c.put(req, copy); });
                            return res;
                        }).catch(function() {
                            return caches.match(req).then(function(hit) {
                                return hit || caches.match('<?php echo $offline; ?>');
                            });
                        })
                    );
                    return;
                }

                // Static assets: cache-first with network refresh
                event.respondWith(
                    caches.match(req).then(function(hit) {
                        return hit || fetch(req).then(function(res) {
                            if (res.ok) {
                                var copy = res.clone();
                                caches.open(CACHE).then(function(c) { c.put(req, copy); });
                            }
                            return res;
                        });
                    })
                );
            });
        })();
        <?php
        exit;
    }
}
add_action('init', 'hikmahnews_serve_sw');

// 5. Register Service Worker
function hikmahnews_pwa_register_sw() {
    if (is_admin()) return;
    ?>
    <script>
    if ('serviceWorker' in navigator && !/AMP/.test('<?php echo esc_js(get_user_locale()); ?>')) {
        window.addEventListener('load', function() {
            navigator.serviceWorker.register('<?php echo esc_js(home_url('/' . HIKMAHNEWS_SW_SLUG)); ?>')
                .catch(function() { /* SW disabled (e.g. HTTP) is acceptable */ });
        });
    }
    </script>
    <?php
}
add_action('wp_footer', 'hikmahnews_pwa_register_sw', 1);

// 6. Install Prompt
function hikmahnews_pwa_install_prompt() {
    if (is_admin()) return;
    ?>
    <div class="pwa-install" id="pwaInstall" style="display:none;">
        <div class="pwa-install__inner">
            <span style="font-size:28px;">📲</span>
            <div>
                <h4>Install <?php bloginfo('name'); ?></h4>
                <p>Read news offline. Fast. No app store needed.</p>
            </div>
            <button class="modern-btn modern-btn--primary" id="pwaInstallBtn">Install</button>
            <button class="modern-btn modern-btn--ghost" id="pwaDismissBtn">Later</button>
        </div>
    </div>
    <style>
        .pwa-install { position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%); z-index: 9997;
                       background: var(--modern-surface, #fff); border-radius: 16px; box-shadow: 0 8px 30px rgba(0,0,0,0.15);
                       padding: 16px 20px; border: 1px solid var(--modern-border, #e5e5e5); max-width: 420px; width: 90%; }
        .pwa-install__inner { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
        .pwa-install h4 { margin: 0; font-size: 14px; } .pwa-install p { margin: 0; font-size: 12px; color: var(--modern-text-2, #525252); }
    </style>
    <script>
    (function() {
        var deferredPrompt;
        var box = document.getElementById('pwaInstall');
        if (!box) return;
        window.addEventListener('beforeinstallprompt', function(e) {
            e.preventDefault();
            deferredPrompt = e;
            if (!localStorage.getItem('hikmahnews_pwa_dismissed')) {
                box.style.display = 'block';
            }
        });
        document.getElementById('pwaInstallBtn')?.addEventListener('click', function() {
            if (deferredPrompt) { deferredPrompt.prompt(); deferredPrompt = null; }
            box.style.display = 'none';
            localStorage.setItem('hikmahnews_pwa_dismissed', '1');
        });
        document.getElementById('pwaDismissBtn')?.addEventListener('click', function() {
            localStorage.setItem('hikmahnews_pwa_dismissed', '1');
            box.style.display = 'none';
        });
        window.addEventListener('appinstalled', function() {
            box.style.display = 'none';
        });
    })();
    </script>
    <?php
}
add_action('wp_footer', 'hikmahnews_pwa_install_prompt', 3);

// Helper: match virtual file routes (works for subdirectory installs)
function hikmahnews_is_virtual_file($target) {
    if (is_admin()) return false;
    if (empty($_SERVER['REQUEST_URI'])) return false;
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $base = trailingslashit(parse_url(home_url('/'), PHP_URL_PATH));
    if (strpos($path, $base) === 0) {
        $path = substr($path, strlen($base));
    }
    return $path === $target;
}