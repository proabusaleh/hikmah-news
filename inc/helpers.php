<?php
/**
 * Helper Functions
 * @package HikmahNews
 */
if (!defined('ABSPATH')) exit;

/**
 * Calculate reading time in minutes
 */
function hikmahnews_reading_time($post_id = null) {
    if (!$post_id) $post_id = get_the_ID();
    $content = get_post_field('post_content', $post_id);
    $word_count = str_word_count(strip_tags($content));
    $reading_time = ceil($word_count / 200);
    return max(1, $reading_time);
}

/**
 * Get post view count (simple implementation)
 */
function hikmahnews_get_post_views($post_id) {
    $count = get_post_meta($post_id, 'hikmahnews_views', true);
    return $count ? (int)$count : 0;
}

/**
 * Set post view count
 */
function hikmahnews_set_post_views($post_id) {
    $count = hikmahnews_get_post_views($post_id);
    update_post_meta($post_id, 'hikmahnews_views', $count + 1);
}

/**
 * Fallback menu
 */
function hikmahnews_fallback_menu() {
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
/**
 * Dynamic logo text — first word in primary color, the rest in normal color.
 * Falls back to the site title from Settings > General.
 */
function hikmahnews_logo_words() {
    $name = trim(get_bloginfo('name'));
    if (!$name) {
        $name = 'Hikmah News';
    }
    $parts  = preg_split('/\s+/', $name, 2);
    $first  = $parts[0] ?? $name;
    $rest   = isset($parts[1]) ? trim($parts[1]) : '';
    ?>
    <span class="site-logo-text__wp"><?php echo esc_html($first); ?></span>
    <?php if ($rest !== '') : ?>
        <span class="site-logo-text__news"><?php echo esc_html($rest); ?></span>
    <?php endif; ?>
    <?php
}

/**
 * Get extra navbar links configured in Theme Options > Header.
 * Format per line: Label | URL
 */
function hikmahnews_extra_nav_links() {
    $raw = hikmahnews_option('header', 'extra_nav_links', '');
    $raw = trim((string) $raw);
    if ($raw === '') return [];

    $links = [];
    foreach (explode("\n", $raw) as $line) {
        $line = trim($line);
        if ($line === '') continue;
        $parts = array_map('trim', explode('|', $line, 2));
        $label = $parts[0] ?? '';
        $url   = $parts[1] ?? '';
        if ($label === '' || $url === '') continue;
        $links[] = [
            'label' => $label,
            'url'   => $url,
        ];
    }
    return $links;
}

/**
 * Append extra navbar links to the primary menu.
 */
function hikmahnews_extra_nav_items($items, $args) {
    if (!isset($args->theme_location) || $args->theme_location !== 'primary') {
        return $items;
    }
    $links = hikmahnews_extra_nav_links();
    if (!$links) return $items;

    $extra = '';
    foreach ($links as $link) {
        $extra .= '<li class="menu-item"><a href="' . esc_url($link['url']) . '">'
                . esc_html($link['label']) . '</a></li>';
    }
    return $items . $extra;
}
add_filter('wp_nav_menu_items', 'hikmahnews_extra_nav_items', 10, 2);

/**
 * Mobile drawer navigation.
 * Uses the assigned primary menu, or falls back to categories + extra links
 * so the navbar always shows on mobile.
 */
function hikmahnews_mobile_nav() {
    if (has_nav_menu('primary')) {
        wp_nav_menu([
            'theme_location' => 'primary',
            'container'      => false,
            'menu_class'     => 'mobile-nav__list',
            'fallback_cb'    => false,
        ]);
        return;
    }

    echo '<ul class="mobile-nav__list">';
    echo '<li><a href="' . esc_url(home_url('/')) . '">Home</a></li>';

    $parents = get_categories([
        'parent'     => 0,
        'hide_empty' => false,
        'orderby'    => 'term_order',
        'number'     => 8,
    ]);

    foreach ($parents as $cat) {
        $children = get_categories([
            'parent'     => $cat->term_id,
            'hide_empty' => false,
        ]);

        echo '<li>';
        echo '<a href="' . esc_url(get_category_link($cat)) . '">'
             . esc_html($cat->name) . '</a>';

        if ($children) {
            echo '<ul class="sub-menu">';
            foreach ($children as $child) {
                echo '<li><a href="' . esc_url(get_category_link($child)) . '">'
                     . esc_html($child->name) . '</a></li>';
            }
            echo '</ul>';
        }
        echo '</li>';
    }

    foreach (hikmahnews_extra_nav_links() as $link) {
        echo '<li><a href="' . esc_url($link['url']) . '">'
             . esc_html($link['label']) . '</a></li>';
    }

    echo '</ul>';
}

/**
 * Custom comment callback
 */
function hikmahnews_comment_callback($comment, $args, $depth) {
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