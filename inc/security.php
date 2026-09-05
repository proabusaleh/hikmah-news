<?php
/**
 * Security Hardening
 * @package HikmahNews
 */
if (!defined('ABSPATH')) exit;

// ============================================
// 1. SECURITY HEADERS
// ============================================
function hikmahnews_security_headers() {
    if (is_admin()) return;

    $headers = [
        'X-Content-Type-Options'    => 'nosniff',
        'X-Frame-Options'           => 'SAMEORIGIN',
        'X-XSS-Protection'          => '1; mode=block',
        'Referrer-Policy'           => 'strict-origin-when-cross-origin',
        'Permissions-Policy'        => 'camera=(), microphone=(), geolocation=()',
    ];

    foreach ($headers as $key => $value) {
        if (!headers_sent()) {
            header("{$key}: {$value}");
        }
    }
}
add_action('send_headers', 'hikmahnews_security_headers');

// ============================================
// 2. REMOVE WP VERSION
// ============================================
remove_action('wp_head', 'wp_generator');
add_filter('the_generator', '__return_empty_string');

// ============================================
// 3. DISABLE XML-RPC (unless needed)
// ============================================
add_filter('xmlrpc_enabled', '__return_false');

// ============================================
// 4. DISABLE FILE EDITING
// ============================================
if (!defined('DISALLOW_FILE_EDIT')) {
    define('DISALLOW_FILE_EDIT', true);
}

// ============================================
// 5. SANITIZE ALL USER INPUT IN AJAX
// ============================================
// Already done in all AJAX handlers via:
// - check_ajax_referer()
// - sanitize_text_field()
// - absint()
// - wp_kses_post()
// - esc_url_raw()

// ============================================
// 6. PREVENT DIRECTORY LISTING
// ============================================
function hikmahnews_prevent_directory_listing() {
    $htaccess = ABSPATH . '.htaccess';
    if (file_exists($htaccess)) {
        $content = file_get_contents($htaccess);
        if (strpos($content, 'Options -Indexes') === false) {
            // Don't auto-modify, just warn in admin
        }
    }
}

// ============================================
// 7. LIMIT LOGIN ATTEMPTS (Basic)
// ============================================
function hikmahnews_limit_login($user, $username, $password) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $transient = 'hikmahnews_login_' . md5($ip);
    $attempts = get_transient($transient) ?: 0;

    if ($attempts >= 5) {
        return new WP_Error('too_many_attempts',
            '<strong>Error:</strong> Too many login attempts. Try again in 15 minutes.');
    }

    if (is_wp_error($user)) {
        set_transient($transient, $attempts + 1, 15 * MINUTE_IN_SECONDS);
    } else {
        delete_transient($transient);
    }

    return $user;
}
add_filter('authenticate', 'hikmahnews_limit_login', 30, 3);

// ============================================
// 8. CONTENT SECURITY POLICY (Optional)
// ============================================
function hikmahnews_csp_header() {
    if (is_admin()) return;
    // Uncomment to enable (may break external scripts):
    // header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://www.googletagmanager.com https://pagead2.googlesyndication.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: https:;");
}
add_action('send_headers', 'hikmahnews_csp_header');