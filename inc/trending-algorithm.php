<?php
/**
 * Trending News Algorithm
 * Score = (Views × 0.4) + (Comments × 0.3) + (Recency × 0.2) + (Shares × 0.1)
 * - Recalculates every hour via WP-Cron
 * - Stores trending score as post meta
 * - Provides "Trending Now" query
 * @package HikmahNews
 */
if (!defined('ABSPATH')) exit;

// ============================================
// 1. TRENDING SCORE CALCULATOR
// ============================================
function hikmahnews_calculate_trending_score($post_id) {
    $post = get_post($post_id);
    if (!$post || $post->post_status !== 'publish') return 0;

    // --- VIEWS (normalized) ---
    $views = (int) get_post_meta($post_id, '_hikmahnews_views', true);
    $views_score = min($views / 100, 100); // 10K views = max score

    // --- COMMENTS ---
    $comments = (int) $post->comment_count;
    $comments_score = min($comments * 5, 100); // 20 comments = max

    // --- RECENCY (hours old, exponential decay) ---
    $hours_old = (current_time('timestamp') - strtotime($post->post_date)) / 3600;
    $recency_score = max(0, 100 - ($hours_old * 1.5)); // Decays over ~67 hours

    // --- SOCIAL SHARES (simulated from meta) ---
    $shares = (int) get_post_meta($post_id, '_hikmahnews_shares', true);
    $shares_score = min($shares * 2, 100);

    // --- WEIGHTED TOTAL ---
    $score = (
        ($views_score * 0.40) +
        ($comments_score * 0.30) +
        ($recency_score * 0.20) +
        ($shares_score * 0.10)
    );

    return round($score, 2);
}

// ============================================
// 2. RECALCULATE ALL TRENDING SCORES (Cron)
// ============================================
function hikmahnews_recalculate_trending() {
    $posts = get_posts([
        'post_type'      => 'post',
        'posts_per_page' => 200,
        'post_status'    => 'publish',
        'date_query'     => [
            ['after' => '7 days ago'], // Only recent posts
        ],
        'fields'         => 'ids',
        'no_found_rows'  => true,
    ]);

    foreach ($posts as $post_id) {
        $score = hikmahnews_calculate_trending_score($post_id);
        update_post_meta($post_id, '_hikmahnews_trending_score', $score);
    }

    // Log last calculation
    update_option('hikmahnews_trending_last_run', current_time('mysql'));
}

// Schedule hourly
if (!wp_next_scheduled('hikmahnews_hourly_trending')) {
    wp_schedule_event(time(), 'hourly', 'hikmahnews_hourly_trending');
}
add_action('hikmahnews_hourly_trending', 'hikmahnews_recalculate_trending');

// Also recalculate on save (for the specific post)
function hikmahnews_update_single_trending($post_id) {
    if (get_post_type($post_id) !== 'post') return;
    if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) return;

    $score = hikmahnews_calculate_trending_score($post_id);
    update_post_meta($post_id, '_hikmahnews_trending_score', $score);
}
add_action('save_post', 'hikmahnews_update_single_trending', 20);
add_action('comment_post', function($comment_id) {
    $comment = get_comment($comment_id);
    if ($comment && $comment->comment_post_ID) {
        hikmahnews_update_single_trending($comment->comment_post_ID);
    }
});

// ============================================
// 3. GET TRENDING POSTS (Query Helper)
// ============================================
function hikmahnews_get_trending_posts($count = 10, $hours = 72) {
    return get_posts([
        'post_type'      => 'post',
        'posts_per_page' => $count,
        'meta_key'       => '_hikmahnews_trending_score',
        'orderby'        => 'meta_value_num',
        'order'          => 'DESC',
        'date_query'     => [
            ['after' => "{$hours} hours ago"],
        ],
        'no_found_rows'  => true,
    ]);
}

// ============================================
// 4. TRENDING BADGE (Auto for top 5)
// ============================================
function hikmahnews_trending_badge($post_id = null) {
    if (!$post_id) $post_id = get_the_ID();

    $score = (float) get_post_meta($post_id, '_hikmahnews_trending_score', true);
    if ($score < 20) return ''; // Threshold

    // Check if in top 5
    $trending = hikmahnews_get_trending_posts(5);
    $trending_ids = wp_list_pluck($trending, 'ID');

    if (!in_array($post_id, $trending_ids)) return '';

    $rank = array_search($post_id, $trending_ids) + 1;
    $labels = ['🔥 #1 Trending', '🔥 #2 Trending', '🔥 #3 Trending', '📈 Trending', '📈 Trending'];
    $label = $labels[$rank - 1] ?? '📈 Trending';

    return '<span class="badge badge--trending">' . $label . '</span>';
}

// ============================================
// 5. TRENDING SECTION (Enhanced Frontend)
// ============================================
function hikmahnews_trending_section() {
    $trending = hikmahnews_get_trending_posts(6, 48);
    if (empty($trending)) return;
    ?>
    <section class="trending-section">
        <div class="container">
            <div class="section-title" style="border-bottom-color: #F59E0B;">
                <h2 class="section-title__text">
                    <span class="section-title__icon">🔥</span>
                    Trending Now
                </h2>
                <span class="trending-updated">
                    Updated <?php
                    $last = get_option('hikmahnews_trending_last_run');
                    echo $last ? human_time_diff(strtotime($last), current_time('timestamp')) . ' ago' : 'just now';
                    ?>
                </span>
                <div class="section-title__line"></div>
            </div>

            <div class="trending-grid">
                <?php
                $rank = 1;
                foreach ($trending as $post) : setup_postdata($post);
                    $score = get_post_meta($post->ID, '_hikmahnews_trending_score', true);
                ?>
                    <article class="trending-card <?php echo $rank <= 3 ? 'trending-card--top' : ''; ?>">
                        <a href="<?php the_permalink(); ?>" class="trending-card__link">
                            <span class="trending-card__rank">
                                <?php echo str_pad($rank, 2, '0', STR_PAD_LEFT); ?>
                            </span>
                            <div class="trending-card__image">
                                <?php if (has_post_thumbnail()) the_post_thumbnail('hikmahnews-thumb'); ?>
                            </div>
                            <div class="trending-card__content">
                                <?php
                                $cats = get_the_category();
                                if ($cats) :
                                ?>
                                    <span class="trending-card__cat">
                                        <?php echo esc_html($cats[0]->name); ?>
                                    </span>
                                <?php endif; ?>
                                <h3 class="trending-card__title"><?php the_title(); ?></h3>
                                <div class="trending-card__meta">
                                    <span>👁 <?php echo hikmahnews_get_formatted_views(); ?></span>
                                    <span class="dot"></span>
                                    <span>💬 <?php echo get_comments_number(); ?></span>
                                    <span class="dot"></span>
                                    <span class="trending-card__score">
                                        Score: <?php echo round($score, 1); ?>
                                    </span>
                                </div>
                            </div>
                        </a>
                    </article>
                <?php
                    $rank++;
                endforeach;
                wp_reset_postdata();
                ?>
            </div>
        </div>
    </section>
    <?php
}