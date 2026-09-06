<?php
/**
 * Theme Analytics Dashboard + Reading History Display
 * - Admin dashboard with real-time stats
 * - Views chart (last 7 days)
 * - Top articles, categories, authors
 * - Revenue estimation (ad impressions)
 * - Reading history display (frontend shortcode)
 * @package HikmahNews
 */
if (!defined('ABSPATH')) exit;

// ============================================
// 1. ADMIN ANALYTICS PAGE
// ============================================
function hikmahnews_analytics_menu() {
    add_menu_page(
        'Hikmah News Analytics',
        '📊 Analytics',
        'manage_options',
        'hikmahnews-analytics',
        'hikmahnews_analytics_page',
        'dashicons-chart-area',
        29
    );
}
add_action('admin_menu', 'hikmahnews_analytics_menu');

function hikmahnews_analytics_page() {
    global $wpdb;

    // Satisfy static analyzers: $wpdb is used for aggregate sums below
    // =============================================================
    // Aggregate stats (meta-key SUMs via $wpdb — accurate across all posts)
    // =============================================================
    $total_posts = (int) wp_count_posts('post')->publish;
    $today       = date('Y-m-d');

    $total_views = (int) $wpdb->get_var(
        "SELECT SUM(meta_value) FROM {$wpdb->postmeta} WHERE meta_key = '_hikmahnews_views'"
    );
    $today_views = (int) $wpdb->get_var(
        $wpdb->prepare("SELECT SUM(meta_value) FROM {$wpdb->postmeta} WHERE meta_key = %s", '_hikmahnews_views_' . $today)
    );

    // Top 10 posts by views
    $top_posts = get_posts([
        'post_type'      => 'post',
        'posts_per_page' => 10,
        'meta_key'       => '_hikmahnews_views',
        'orderby'        => 'meta_value_num',
        'order'          => 'DESC',
        'no_found_rows'  => true,
    ]);

    // Category stats
    $categories = get_categories(['orderby' => 'count', 'order' => 'DESC', 'number' => 8, 'hide_empty' => true]);

    // Author stats
    $authors = get_users(['role__in' => ['author', 'editor', 'administrator'], 'orderby' => 'post_count', 'order' => 'DESC', 'number' => 5]);

    // 7-day views chart data (daily meta keys: _hikmahnews_views_YYYY-MM-DD)
    $chart_data = [];
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-{$i} days"));
        $label = date('M j', strtotime("-{$i} days"));
        $day_views = (int) $wpdb->get_var(
            $wpdb->prepare("SELECT SUM(meta_value) FROM {$wpdb->postmeta} WHERE meta_key = %s", '_hikmahnews_views_' . $date)
        );
        $chart_data[] = ['label' => $label, 'value' => $day_views];
    }
    $max_chart = max(array_column($chart_data, 'value') ?: [1]);

    // Ad impressions today (transients written by hikmahnews_ad_impression_handler)
    $ad_positions = array_keys(hikmahnews_ad_positions());
    $total_impressions = 0;
    foreach ($ad_positions as $pos) {
        $imp = (int) get_transient('hikmahnews_ad_imp_' . $pos . '_' . $today);
        $total_impressions += $imp;
    }

    // Estimated revenue (CPM $2 average)
    $est_revenue = round(($total_impressions / 1000) * 2, 2);

    // Breaking & Trending counts
    $breaking_count = count(hikmahnews_get_breaking_posts(20));
    $trending_count = count(hikmahnews_get_trending_posts(10, 48));
    ?>
    <div class="wrap">
        <h1>📊 Hikmah News Analytics</h1>
        <p>Real-time statistics for your news portal. Data updates automatically.</p>

        <!-- KPI Cards -->
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;margin:24px 0;">
            <?php
            $kpis = [
                ['📝', 'Total Articles', number_format($total_posts), '#3B82F6'],
                ['👁', 'Total Views', number_format($total_views), '#8B5CF6'],
                ['📈', 'Today\'s Views', number_format($today_views), '#10B981'],
                ['🔴', 'Breaking News', $breaking_count, '#DC2626'],
                ['🔥', 'Trending Now', $trending_count, '#F59E0B'],
                ['💰', 'Ad Impressions', number_format($total_impressions), '#059669'],
                ['💵', 'Est. Revenue', '$' . $est_revenue, '#0EA5E9'],
            ];
            foreach ($kpis as $kpi) :
            ?>
                <div style="background:white;padding:20px;border-radius:8px;border:1px solid #e5e7eb;border-top:3px solid <?php echo $kpi[3]; ?>;">
                    <div style="font-size:24px;"><?php echo $kpi[0]; ?></div>
                    <div style="font-size:28px;font-weight:800;color:#111;margin:4px 0;"><?php echo $kpi[2]; ?></div>
                    <div style="font-size:12px;color:#666;"><?php echo $kpi[1]; ?></div>
                </div>
            <?php endforeach; ?>
        </div>

        <div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;">

            <!-- 7-Day Views Chart -->
            <div style="background:white;padding:24px;border-radius:8px;border:1px solid #e5e7eb;">
                <h2 style="margin:0 0 20px;font-size:18px;">📈 Views — Last 7 Days</h2>
                <div style="display:flex;align-items:flex-end;gap:8px;height:200px;padding-top:20px;">
                    <?php foreach ($chart_data as $d) :
                        $height = $max_chart > 0 ? ($d['value'] / $max_chart * 100) : 0;
                    ?>
                        <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:4px;">
                            <span style="font-size:11px;font-weight:700;color:#333;"><?php echo $d['value']; ?></span>
                            <div style="width:100%;background:linear-gradient(to top,#DC2626,#F59E0B);
                                        border-radius:4px 4px 0 0;height:<?php echo max($height, 2); ?>%;
                                        min-height:4px;transition:height 0.5s;"></div>
                            <span style="font-size:10px;color:#999;"><?php echo $d['label']; ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Top Categories -->
            <div style="background:white;padding:24px;border-radius:8px;border:1px solid #e5e7eb;">
                <h2 style="margin:0 0 16px;font-size:18px;">📂 Top Categories</h2>
                <?php foreach ($categories as $cat) :
                    $color = hikmahnews_get_category_color($cat->term_id);
                    $pct = $total_posts > 0 ? round($cat->count / $total_posts * 100) : 0;
                ?>
                    <div style="margin-bottom:12px;">
                        <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:4px;">
                            <span><?php echo hikmahnews_get_category_icon($cat->term_id); ?> <?php echo esc_html($cat->name); ?></span>
                            <span style="font-weight:700;"><?php echo $cat->count; ?> (<?php echo $pct; ?>%)</span>
                        </div>
                        <div style="height:6px;background:#f0f0f0;border-radius:3px;overflow:hidden;">
                            <div style="height:100%;width:<?php echo $pct; ?>%;background:<?php echo esc_attr($color); ?>;border-radius:3px;"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:20px;">

            <!-- Top Articles -->
            <div style="background:white;padding:24px;border-radius:8px;border:1px solid #e5e7eb;">
                <h2 style="margin:0 0 16px;font-size:18px;">🏆 Top 10 Articles</h2>
                <table class="widefat" style="border:none;">
                    <thead><tr><th>#</th><th>Title</th><th>Views</th><th>Comments</th></tr></thead>
                    <tbody>
                        <?php
                        $rank = 1;
                        foreach ($top_posts as $p) :
                            $views = (int) get_post_meta($p->ID, '_hikmahnews_views', true);
                        ?>
                            <tr>
                                <td><strong><?php echo $rank; ?></strong></td>
                                <td><a href="<?php echo get_permalink($p); ?>"><?php echo esc_html(wp_trim_words($p->post_title, 8, '...')); ?></a></td>
                                <td><strong><?php echo number_format($views); ?></strong></td>
                                <td><?php echo $p->comment_count; ?></td>
                            </tr>
                        <?php $rank++; endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Top Authors -->
            <div style="background:white;padding:24px;border-radius:8px;border:1px solid #e5e7eb;">
                <h2 style="margin:0 0 16px;font-size:18px;">✍️ Top Authors</h2>
                <?php foreach ($authors as $author) :
                    $count = count_user_posts($author->ID);
                ?>
                    <div style="display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid #f0f0f0;">
                        <?php echo get_avatar($author->ID, 40); ?>
                        <div style="flex:1;">
                            <strong style="font-size:14px;"><?php echo esc_html($author->display_name); ?></strong>
                            <div style="font-size:12px;color:#999;"><?php echo $count; ?> articles</div>
                        </div>
                        <a href="<?php echo get_author_posts_url($author->ID); ?>" class="button button-small">View</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Quick Links -->
        <div style="margin-top:24px;padding:20px;background:#f8f9fa;border-radius:8px;">
            <h3 style="margin:0 0 12px;">⚡ Quick Actions</h3>
            <a href="<?php echo admin_url('post-new.php'); ?>" class="button button-primary">✏️ Write New Article</a>
            <a href="<?php echo admin_url('post-new.php?post_type=hikmahnews_live_blog'); ?>" class="button">🔴 Start Live Blog</a>
            <a href="<?php echo admin_url('admin.php?page=hikmahnews-ads'); ?>" class="button">💰 Manage Ads</a>
            <a href="<?php echo admin_url('themes.php?page=hikmahnews-options'); ?>" class="button">⚙️ Theme Options</a>
            <a href="<?php echo admin_url('edit.php?' . build_query(['meta_key' => '_hikmahnews_breaking', 'meta_value' => '1'])); ?>" class="button">🔴 Breaking News</a>
        </div>
    </div>
    <?php
}

// ============================================
// 2. READING HISTORY DISPLAY (Frontend)
// ============================================
function hikmahnews_reading_history_shortcode($atts) {
    if (!is_user_logged_in()) {
        return '<p><a href="' . wp_login_url() . '">Log in</a> to see your reading history.</p>';
    }

    $history = get_user_meta(get_current_user_id(), '_hikmahnews_reading_history', true);
    $history = is_array($history) ? $history : [];
    if (empty($history)) return '<p>You haven\'t read any articles yet.</p>';

    $html = '<div class="reading-history"><h3>📖 Your Reading History</h3><div class="reading-history__list">';
    foreach (array_slice($history, 0, 10) as $item) {
        $post = get_post($item['id'] ?? 0);
        if (!$post) continue;
        $html .= '<a href="' . get_permalink($post) . '" class="reading-history__item">';
        $html .= '<div class="reading-history__image">';
        if (has_post_thumbnail($post)) $html .= get_the_post_thumbnail($post, 'hikmahnews-thumb');
        $html .= '</div>';
        $html .= '<div class="reading-history__info">';
        $html .= '<h4>' . esc_html($post->post_title) . '</h4>';
        $time = $item['time'] ?? current_time('mysql');
        $html .= '<time>' . human_time_diff(strtotime($time), current_time('timestamp')) . ' ago</time>';
        $html .= '</div></a>';
    }
    $html .= '</div></div>';
    return $html;
}
add_shortcode('hikmahnews_reading_history', 'hikmahnews_reading_history_shortcode');

// Reading History CSS
function hikmahnews_reading_history_css() {
    ?>
    <style>
        .reading-history { max-width: 600px; }
        .reading-history h3 { font-size: 20px; margin-bottom: 16px; }
        .reading-history__list { display: flex; flex-direction: column; gap: 8px; }
        .reading-history__item { display: flex; gap: 12px; padding: 12px; background: var(--modern-surface, #fff);
            border-radius: 10px; border: 1px solid var(--modern-border, #e5e5e5); text-decoration: none;
            color: var(--modern-text, #0a0a0a); transition: all 0.2s; }
        .reading-history__item:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.08); transform: translateX(4px); }
        .reading-history__image { flex-shrink: 0; width: 80px; height: 55px; border-radius: 6px; overflow: hidden; }
        .reading-history__image img { width: 100%; height: 100%; object-fit: cover; }
        .reading-history__info h4 { font-size: 14px; font-weight: 700; margin: 0 0 4px; line-height: 1.3;
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .reading-history__info time { font-size: 11px; color: var(--modern-text-3, #a3a3a3); }
    </style>
    <?php
}
add_action('wp_head', 'hikmahnews_reading_history_css');