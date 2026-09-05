<?php
/**
 * Admin Enhancements
 * - Dashboard widgets
 * - Dark mode preference sync
 * - Admin bar menu
 * - Theme activation/deactivation hooks
 * @package HikmahNews
 */
if (!defined('ABSPATH')) exit;

// ============================================
// 1. DASHBOARD WIDGETS
// ============================================
function hikmahnews_dashboard_widget() {
    wp_add_dashboard_widget(
        'hikmahnews_dashboard_posts',
        '⚡ Hikmah News Quick Stats',
        'hikmahnews_dashboard_widget_render'
    );
}
add_action('wp_dashboard_setup', 'hikmahnews_dashboard_widget');

function hikmahnews_dashboard_widget_render() {
    $total = wp_count_posts();
    $published = $total->publish ?? 0;
    $drafts = $total->draft ?? 0;

    $categories = wp_count_terms('category');
    $comments = wp_count_comments();

    // Breaking news count
    $breaking_count = count(hikmahnews_get_breaking_posts(10));

    // Views this week
    $this_week = get_transient('hikmahnews_views_this_week');
    if ($this_week === false) {
        global $wpdb;
        $this_week = $wpdb->get_var(
            "SELECT SUM(meta_value) FROM {$wpdb->postmeta} WHERE meta_key = '_hikmahnews_views'"
        );
        set_transient('hikmahnews_views_this_week', $this_week, HOUR_IN_SECONDS);
    }
    ?>
    <div class="hikmahnews-dash-stats" style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;">
        <div style="text-align:center;padding:15px;background:#f8f9fa;border-radius:6px;">
            <div style="font-size:24px;font-weight:700;color:#111827;"><?php echo $published; ?></div>
            <div style="font-size:12px;color:#6b7280;">Published Posts</div>
        </div>
        <div style="text-align:center;padding:15px;background:#f8f9fa;border-radius:6px;">
            <div style="font-size:24px;font-weight:700;color:#dc2626;"><?php echo $breaking_count; ?></div>
            <div style="font-size:12px;color:#6b7280;">Breaking Posts</div>
        </div>
        <div style="text-align:center;padding:15px;background:#f8f9fa;border-radius:6px;">
            <div style="font-size:24px;font-weight:700;color:#1e3a5f;"><?php echo number_format_i18n($this_week); ?></div>
            <div style="font-size:12px;color:#6b7280;">Total Views</div>
        </div>
    </div>

    <div style="margin-top:15px;display:flex;gap:20px;font-size:13px;color:#555;">
    <div>📂 <?php echo $categories; ?> categories</div>
    <div>💬 <?php echo $comments->total_comments; ?> comments</div>
    <div>✏️ <?php echo $drafts; ?> drafts</div>
    </div>

    <p style="margin:15px 0 0;">
        <a href="<?php echo admin_url('themes.php?page=hikmahnews-options'); ?>" class="button button-primary">
            ⚙️ Theme Options
        </a>
        <a href="<?php echo admin_url('post-new.php'); ?>" class="button">✍️ New Post</a>
    </p>
    <?php
}

// ============================================
// 2. DARK MODE PREFERENCE SYNC
// ============================================
function hikmahnews_admin_dark_sync() {
    ?>
    <script>
    (function() {
        // Sync admin theme with front-end preference
        if (localStorage.getItem('hikmahnews-dark') === 'dark') {
            document.body.classList.add('hikmahnews-admin-dark');
        }
    })();
    </script>
    <style>
        body.hikmahnews-admin-dark #adminmenumain {
            background: #111827;
        }
        body.hikmahnews-admin-dark #wpcontent {
            background: #1f2937;
        }
        body.hikmahnews-admin-dark #screen-meta-links,
        body.hikmahnews-admin-dark #screen-meta {
            background: #1f2937 !important;
        }
        body.hikmahnews-admin-dark .wrap {
            color: #e5e7eb;
        }
    </style>
    <?php
}
add_action('admin_footer', 'hikmahnews_admin_dark_sync');

// ============================================
// 3. BACK TO TOP (ADMIN)
// ============================================
function hikmahnews_admin_back_to_top() {
    $screen = get_current_screen();
    if ($screen->id !== 'toplevel_page_hikmahnews-options' &&
        $screen->id !== 'appearance_page_hikmahnews-options') return;
    ?>
    <button id="hikmahnews-admin-top" style="
        position:fixed;bottom:20px;right:20px;z-index:9999;
        background:#dc2626;color:#fff;border:none;border-radius:50%;
        width:44px;height:44px;font-size:18px;cursor:pointer;display:none;
        box-shadow:0 2px 8px rgba(0,0,0,.3);
    ">↑</button>
    <script>
    (function() {
        var btn = document.getElementById('hikmahnews-admin-top');
        window.addEventListener('scroll', function() {
            btn.style.display = window.scrollY > 400 ? 'block' : 'none';
        });
        btn.addEventListener('click', function() {
            window.scrollTo({top: 0, behavior: 'smooth'});
        });
    })();
    </script>
    <?php
}
add_action('admin_footer', 'hikmahnews_admin_back_to_top');

// ============================================
// 4. ADMIN BAR ADDITIONS
// ============================================
function hikmahnews_admin_bar_menu($wp_admin_bar) {
    if (!current_user_can('manage_options')) return;

    $wp_admin_bar->add_node([
        'id'    => 'hikmahnews-options',
        'title' => '⚙️ Theme Options',
        'href'  => admin_url('themes.php?page=hikmahnews-options'),
    ]);

    $wp_admin_bar->add_node([
        'parent' => 'hikmahnews-options',
        'id'     => 'hikmahnews-clear-cache',
        'title'  => '🧹 Clear All Cache',
        'href'   => wp_nonce_url(
            admin_url('admin-post.php?action=hikmahnews_clear_cache'),
            'hikmahnews_clear_cache'
        ),
    ]);
}
add_action('admin_bar_menu', 'hikmahnews_admin_bar_menu', 80);

function hikmahnews_handle_clear_cache() {
    if (!current_user_can('manage_options')) return;

    // Clear our transients
    if (class_exists('HikmahNews_Cache')) {
        delete_transient('hikmahnews_trending_10');
        delete_transient('hikmahnews_breaking_6');
        delete_transient('hikmahnews_popular_5');
        delete_transient('hikmahnews_views_this_week');
    }

    // Trigger cache plugin purges
    if (class_exists('HikmahNews_Cache_Compat')) {
        $compat = new HikmahNews_Cache_Compat();
        $compat->purge_cache();
    }

    wp_safe_redirect(add_query_arg('cache_cleared', '1', admin_url('themes.php?page=hikmahnews-options')));
    exit;
}
add_action('admin_post_hikmahnews_clear_cache', 'hikmahnews_handle_clear_cache');

// ============================================
// 5. THEME ACTIVATION / DEACTIVATION
// ============================================
function hikmahnews_theme_activation() {
    // Set up default theme options
    hikmahnews_default_options();
    add_option('hikmahnews_theme_options', hikmahnews_default_options());

    // Create default categories
    if (function_exists('hikmahnews_create_categories')) {
        hikmahnews_create_categories();
    }

    // Clear any stale options
    delete_transient('hikmahnews_trending_10');
    delete_transient('hikmahnews_breaking_6');

    // Refresh rewrite rules
    flush_rewrite_rules();
}

function hikmahnews_theme_deactivation() {
    // Clean up scheduled hooks
    wp_clear_scheduled_hook('hikmahnews_hourly_trending');
    wp_clear_scheduled_hook('hikmahnews_daily_cleanup');

    // Clean up transients
    delete_transient('hikmahnews_trending_10');
    delete_transient('hikmahnews_trending_20');
    delete_transient('hikmahnews_breaking_6');
    delete_transient('hikmahnews_breaking_10');
    delete_transient('hikmahnews_views_this_week');

    // Clear popular caches
    global $wpdb;
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '%hikmahnews_popular_%'");
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_hikmahnews_%'");

    // Refresh rewrite rules
    flush_rewrite_rules();
}
add_action('after_switch_theme', 'hikmahnews_theme_activation');
add_action('switch_theme', 'hikmahnews_theme_deactivation');