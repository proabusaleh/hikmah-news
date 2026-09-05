<?php
/**
 * Push Notification System
 * - Service Worker registration
 * - Web Push API ready
 * - Admin settings for VAPID keys
 * - Auto-notify on new breaking/featured posts
 * @package WPNews
 */
if (!defined('ABSPATH')) exit;

// ============================================
// 1. SERVICE WORKER REGISTRATION
// ============================================
function wpnews_service_worker_script() {
    ?>
    <script>
        if ('serviceWorker' in navigator && 'PushManager' in window) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('<?php echo esc_url(home_url('/wpnews-sw.js')); ?>')
                    .then(function(reg) {
                        console.log('SW registered:', reg.scope);
                    })
                    .catch(function(err) {
                        console.log('SW registration failed:', err);
                    });
            });
        }
    </script>
    <?php
}
add_action('wp_footer', 'wpnews_service_worker_script');

// ============================================
// 2. SERVE SERVICE WORKER FILE
// ============================================
function wpnews_serve_service_worker() {
    if ($_SERVER['REQUEST_URI'] !== '/wpnews-sw.js') return;

    header('Content-Type: application/javascript');
    header('Service-Worker-Allowed: /');

    $assets_uri = WPNEWS_URI . '/assets';
    ?>
    // WP News Service Worker v1.0
    const CACHE_NAME = 'wpnews-v1';
    const OFFLINE_URL = '/offline/';

    // Install
    self.addEventListener('install', (event) => {
        event.waitUntil(
            caches.open(CACHE_NAME).then((cache) => {
                return cache.addAll([
                    OFFLINE_URL,
                    <?php echo wp_json_encode($assets_uri . '/css/main.css'); ?>,
                ]);
            })
        );
        self.skipWaiting();
    });

    // Activate
    self.addEventListener('activate', (event) => {
        event.waitUntil(
            caches.keys().then((keys) => {
                return Promise.all(
                    keys.filter((key) => key !== CACHE_NAME)
                        .map((key) => caches.delete(key))
                );
            })
        );
        self.clients.claim();
    });

    // Fetch (Network-first for HTML, Cache-first for assets)
    self.addEventListener('fetch', (event) => {
        if (event.request.mode === 'navigate') {
            event.respondWith(
                fetch(event.request).catch(() => {
                    return caches.match(OFFLINE_URL);
                })
            );
        }
    });

    // Push Notification
    self.addEventListener('push', (event) => {
        if (!event.data) return;

        const data = event.data.json();
        const options = {
            body: data.body || 'New article published',
            icon: data.icon || <?php echo wp_json_encode($assets_uri . '/images/icon-192.png'); ?>,
            badge: data.badge || <?php echo wp_json_encode($assets_uri . '/images/badge-72.png'); ?>,
            image: data.image || '',
            tag: data.tag || 'wpnews-notification',
            requireInteraction: data.urgent || false,
            vibrate: [200, 100, 200],
            data: {
                url: data.url || '/',
            },
            actions: [
                { action: 'open', title: 'Read Now' },
                { action: 'dismiss', title: 'Dismiss' },
            ],
        };

        event.waitUntil(
            self.registration.showNotification(data.title || 'WP News', options)
        );
    });

    // Notification Click
    self.addEventListener('notificationclick', (event) => {
        event.notification.close();

        if (event.action === 'dismiss') return;

        const url = event.notification.data?.url || '/';
        event.waitUntil(
            clients.matchAll({ type: 'window' }).then((list) => {
                for (const client of list) {
                    if (client.url.includes(url) && 'focus' in client) {
                        return client.focus();
                    }
                }
                if (clients.openWindow) {
                    return clients.openWindow(url);
                }
            })
        );
    });
    <?php
    exit;
}
add_action('init', 'wpnews_serve_service_worker');

// ============================================
// 3. NOTIFICATION PERMISSION PROMPT (Frontend)
// ============================================
function wpnews_notification_prompt() {
    ?>
    <div class="notification-prompt" id="notificationPrompt" style="display:none;">
        <div class="notification-prompt__inner">
            <div class="notification-prompt__icon">🔔</div>
            <div class="notification-prompt__content">
                <h4>Stay Updated!</h4>
                <p>Get instant notifications for breaking news and top stories.</p>
            </div>
            <div class="notification-prompt__actions">
                <button class="btn btn--primary btn--sm" id="notifAllow">Allow</button>
                <button class="btn btn--ghost btn--sm" id="notifDismiss">Not Now</button>
            </div>
        </div>
    </div>
    <?php
}
add_action('wp_footer', 'wpnews_notification_prompt');

function wpnews_notification_prompt_script() {
    wp_add_inline_script('wpnews-main', '
    (function() {
        var prompt = document.getElementById("notificationPrompt");
        var allowBtn = document.getElementById("notifAllow");
        var dismissBtn = document.getElementById("notifDismiss");
        if (!prompt) return;

        // Show after 30 seconds if not already decided
        if (!localStorage.getItem("wpnews_notif_decision") &&
            "Notification" in window &&
            Notification.permission === "default") {
            setTimeout(function() {
                prompt.style.display = "block";
            }, 30000);
        }

        if (allowBtn) {
            allowBtn.addEventListener("click", function() {
                Notification.requestPermission().then(function(perm) {
                    localStorage.setItem("wpnews_notif_decision", perm);
                    prompt.style.display = "none";

                    if (perm === "granted") {
                        // Subscribe to push
                        if ("serviceWorker" in navigator) {
                            navigator.serviceWorker.ready.then(function(reg) {
                                reg.pushManager.subscribe({
                                    userVisibleOnly: true,
                                    applicationServerKey: "' . esc_js(get_option('wpnews_vapid_public', '')) . '"
                                });
                            });
                        }
                    }
                });
            });
        }

        if (dismissBtn) {
            dismissBtn.addEventListener("click", function() {
                localStorage.setItem("wpnews_notif_decision", "dismissed");
                prompt.style.display = "none";
            });
        }
    })();
    ');
}
add_action('wp_enqueue_scripts', 'wpnews_notification_prompt_script');

// ============================================
// 4. AUTO-NOTIFY ON BREAKING NEWS PUBLISH
// ============================================
function wpnews_send_push_on_breaking($post_id) {
    if (get_post_type($post_id) !== 'post') return;
    if (wp_is_post_revision($post_id)) return;

    $is_breaking = get_post_meta($post_id, '_wpnews_breaking', true);
    if ($is_breaking !== '1') return;

    $post = get_post($post_id);
    $payload = wp_json_encode([
        'title'  => '🔴 BREAKING: ' . $post->post_title,
        'body'   => wp_trim_words(strip_tags($post->post_content), 20, '...'),
        'url'    => get_permalink($post_id),
        'icon'   => has_post_thumbnail($post_id)
                    ? get_the_post_thumbnail_url($post_id, 'thumbnail')
                    : '',
        'urgent' => true,
        'tag'    => 'breaking-' . $post_id,
    ]);

    // Store for push service (OneSignal, Firebase, etc.)
    do_action('wpnews_send_push', $payload, $post_id);

    // Log notification
    $log = get_option('wpnews_push_log', []);
    $log[] = [
        'time'    => current_time('mysql'),
        'post_id' => $post_id,
        'title'   => $post->post_title,
    ];
    // Keep last 50
    $log = array_slice($log, -50);
    update_option('wpnews_push_log', $log);
}
add_action('save_post', 'wpnews_send_push_on_breaking', 30);

// ============================================
// 5. ADMIN SETTINGS (VAPID Keys)
// ============================================
function wpnews_push_admin_settings() {
    add_options_page(
        'Push Notifications',
        '🔔 Push Notifications',
        'manage_options',
        'wpnews-push',
        'wpnews_push_settings_page'
    );
}
add_action('admin_menu', 'wpnews_push_admin_settings');

function wpnews_push_settings_page() {
    if (isset($_POST['wpnews_push_nonce']) && wp_verify_nonce($_POST['wpnews_push_nonce'], 'wpnews_push')) {
        update_option('wpnews_vapid_public', sanitize_text_field($_POST['vapid_public']));
        update_option('wpnews_vapid_private', sanitize_text_field($_POST['vapid_private']));
        echo '<div class="notice notice-success"><p>Settings saved!</p></div>';
    }
    ?>
    <div class="wrap">
        <h1>🔔 Push Notification Settings</h1>
        <p>Configure Web Push (VAPID) keys for browser notifications.</p>
        <p>Generate keys at <a href="https://web-push-codelab.glitch.me/" target="_blank">Web Push Codelab</a>.</p>

        <form method="POST">
            <?php wp_nonce_field('wpnews_push', 'wpnews_push_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th>VAPID Public Key</th>
                    <td>
                        <input type="text" name="vapid_public" class="regular-text"
                               value="<?php echo esc_attr(get_option('wpnews_vapid_public')); ?>">
                    </td>
                </tr>
                <tr>
                    <th>VAPID Private Key</th>
                    <td>
                        <input type="password" name="vapid_private" class="regular-text"
                               value="<?php echo esc_attr(get_option('wpnews_vapid_private')); ?>">
                    </td>
                </tr>
            </table>
            <?php submit_button('Save Settings'); ?>
        </form>

        <h2>Recent Push Log</h2>
        <?php
        $log = get_option('wpnews_push_log', []);
        if (empty($log)) :
            echo '<p>No notifications sent yet.</p>';
        else :
        ?>
            <table class="widefat">
                <thead>
                    <tr><th>Time</th><th>Post</th><th>Title</th></tr>
                </thead>
                <tbody>
                    <?php foreach (array_reverse($log) as $entry) : ?>
                        <tr>
                            <td><?php echo esc_html($entry['time']); ?></td>
                            <td>#<?php echo esc_html($entry['post_id']); ?></td>
                            <td><?php echo esc_html($entry['title']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    <?php
}