<?php
/**
 * Helper Functions
 * @package WPNews
 */
if (!defined('ABSPATH')) exit;

/**
 * Calculate reading time in minutes
 */
function wpnews_reading_time($post_id = null) {
    if (!$post_id) $post_id = get_the_ID();
    $content = get_post_field('post_content', $post_id);
    $word_count = str_word_count(strip_tags($content));
    $reading_time = ceil($word_count / 200);
    return max(1, $reading_time);
}

/**
 * Get post view count (simple implementation)
 */
function wpnews_get_post_views($post_id) {
    $count = get_post_meta($post_id, 'wpnews_views', true);
    return $count ? (int)$count : 0;
}

/**
 * Set post view count
 */
function wpnews_set_post_views($post_id) {
    $count = wpnews_get_post_views($post_id);
    update_post_meta($post_id, 'wpnews_views', $count + 1);
}

/**
 * Fallback menu
 */
function wpnews_fallback_menu() {
    echo '<ul class="main-nav__list">';
    echo '<li><a href="' . esc_url(home_url('/')) . '">Home</a></li>';
    $pages = get_pages(['number' => 5, 'sort_column' => 'menu_order']);
    foreach ($pages as $page) {
        echo '<li><a href="' . esc_url(get_page_link($page->ID)) . '">'
             . esc_html($page->post_title) . '</a></li>';
    }
    echo '</ul>';
}

/**
 * Custom comment callback
 */
function wpnews_comment_callback($comment, $args, $depth) {
    $tag = ($args['style'] === 'div') ? 'div' : 'li';
?>
    <<?php echo $tag; ?> id="comment-<?php comment_ID(); ?>" <?php comment_class('comment-item'); ?>>
        <div class="comment-item__inner">
            <div class="comment-item__avatar">
                <?php echo get_avatar($comment, 48); ?>
            </div>
            <div class="comment-item__body">
                <div class="comment-item__header">
                    <span class="comment-item__author">
                        <?php comment_author_link(); ?>
                    </span>
                    <time class="comment-item__date" datetime="<?php comment_time('c'); ?>">
                        <?php printf('%s ago', human_time_diff(get_comment_time('U'), current_time('timestamp'))); ?>
                    </time>
                </div>
                <div class="comment-item__text">
                    <?php if ($comment->comment_approved == '0') : ?>
                        <p class="comment-moderation">Your comment is awaiting moderation.</p>
                    <?php endif; ?>
                    <?php comment_text(); ?>
                </div>
                <div class="comment-item__actions">
                    <?php
                    comment_reply_link(array_merge($args, [
                        'depth'     => $depth,
                        'max_depth' => $args['max_depth'],
                        'before'    => '<span class="comment-reply-link">',
                        'after'     => '</span>',
                    ]));
                    ?>
                </div>
            </div>
        </div>
<?php
}