<?php
/**
 * Category-Based Homepage Builder
 * - Customizer: select which categories appear on homepage
 * - Drag-and-drop section ordering
 * - Per-section layout selection
 * - Dynamic rendering
 * @package HikmahNews
 */
if (!defined('ABSPATH')) exit;

// ============================================
// 1. CUSTOMIZER: Homepage Sections
// ============================================
function hikmahnews_homepage_builder_customize($wp_customize) {

    $wp_customize->add_section('hikmahnews_homepage_builder', [
        'title'    => '🏠 Homepage Builder',
        'panel'    => 'hikmahnews_options',
        'priority' => 25,
    ]);

    // --- Section Order ---
    $wp_customize->add_setting('hikmahnews_home_sections', [
        'default'           => 'hero,latest,politics,business,sports,technology,trending,video,newsletter',
        'sanitize_callback' => 'sanitize_text_field',
    ]);

    $wp_customize->add_control('hikmahnews_home_sections', [
        'label'       => 'Section Order (comma-separated)',
        'description' => 'Available: hero, latest, politics, business, sports, technology, entertainment, health, opinion, trending, video, gallery, newsletter, spotlight',
        'section'     => 'hikmahnews_homepage_builder',
        'type'        => 'text',
    ]);

    // --- Posts Per Section ---
    $wp_customize->add_setting('hikmahnews_home_posts_per_section', [
        'default'           => 5,
        'sanitize_callback' => 'absint',
    ]);

    $wp_customize->add_control('hikmahnews_home_posts_per_section', [
        'label'   => 'Posts Per Category Section',
        'section' => 'hikmahnews_homepage_builder',
        'type'    => 'number',
        'input_attrs' => ['min' => 3, 'max' => 10],
    ]);

    // --- Enable/Disable Sections ---
    $toggle_sections = [
        'hero'       => '🏠 Hero Section',
        'trending'   => '🔥 Trending Section',
        'video'      => '🎬 Video Section',
        'gallery'    => '📸 Gallery Section',
        'spotlight'  => '💡 Spotlight Section',
        'newsletter' => '📬 Newsletter Section',
    ];

    foreach ($toggle_sections as $key => $label) {
        $wp_customize->add_setting("hikmahnews_show_{$key}", [
            'default'           => true,
            'sanitize_callback' => 'hikmahnews_sanitize_checkbox',
        ]);

        $wp_customize->add_control("hikmahnews_show_{$key}", [
            'label'   => "Show {$label}",
            'section' => 'hikmahnews_homepage_builder',
            'type'    => 'checkbox',
        ]);
    }

    // --- Category Layout Per Section ---
    $parent_cats = get_categories(['parent' => 0, 'hide_empty' => false, 'number' => 10]);
    foreach ($parent_cats as $cat) {
        $wp_customize->add_setting("hikmahnews_cat_layout_{$cat->slug}", [
            'default'           => 'grid',
            'sanitize_callback' => 'sanitize_text_field',
        ]);

        $wp_customize->add_control("hikmahnews_cat_layout_{$cat->slug}", [
            'label'   => "{$cat->name} Layout",
            'section' => 'hikmahnews_homepage_builder',
            'type'    => 'select',
            'choices' => [
                'grid'      => '📰 Grid (3 columns)',
                'list'      => '📋 List (Featured + List)',
                'compact'   => '📎 Compact (Numbered)',
                'carousel'  => '🎠 Horizontal Scroll',
            ],
        ]);
    }
}
add_action('customize_register', 'hikmahnews_homepage_builder_customize');

// ============================================
// 2. DYNAMIC HOMEPAGE RENDERER
// ============================================
function hikmahnews_render_homepage() {
    $sections_str = get_theme_mod('hikmahnews_home_sections',
        'hero,latest,politics,business,sports,technology,trending,video,newsletter');
    $sections = array_map('trim', explode(',', $sections_str));
    $posts_per = get_theme_mod('hikmahnews_home_posts_per_section', 5);

    foreach ($sections as $section) {
        switch ($section) {
            case 'hero':
                if (get_theme_mod('hikmahnews_show_hero', true)) {
                    get_template_part('template-parts/content/hero');
                    do_action('hikmahnews_after_hero');
                }
                break;

            case 'latest':
                get_template_part('template-parts/content/latest-news');
                if (function_exists('hikmahnews_homepage_mid_ad')) {
                    hikmahnews_homepage_mid_ad();
                }
                break;

            case 'spotlight':
                if (get_theme_mod('hikmahnews_show_spotlight', true)) {
                    hikmahnews_spotlight_section();
                }
                break;

            case 'trending':
                if (get_theme_mod('hikmahnews_show_trending', true)) {
                    hikmahnews_trending_section();
                }
                break;

            case 'video':
                if (get_theme_mod('hikmahnews_show_video', true)) {
                    get_template_part('template-parts/content/video-news');
                }
                break;

            case 'gallery':
                if (get_theme_mod('hikmahnews_show_gallery', true)) {
                    get_template_part('template-parts/content/photo-gallery');
                }
                break;

            case 'newsletter':
                if (get_theme_mod('hikmahnews_show_newsletter', true)) {
                    get_template_part('template-parts/content/newsletter');
                }
                break;

            default:
                // It's a category slug
                hikmahnews_render_category_section($section, $posts_per);
                break;
        }
    }
}

// ============================================
// 3. CATEGORY SECTION RENDERER (Multiple Layouts)
// ============================================
function hikmahnews_render_category_section($slug, $count = 5) {
    $category = get_category_by_slug($slug);
    if (!$category || $category->count === 0) return;

    $color = hikmahnews_get_category_color($category->term_id);
    $icon = hikmahnews_get_category_icon($category->term_id);
    $layout = get_theme_mod("hikmahnews_cat_layout_{$slug}", 'grid');

    $query = new WP_Query([
        'category_name'  => $slug,
        'posts_per_page' => $count,
        'no_found_rows'  => true,
    ]);

    if (!$query->have_posts()) return;
    ?>
    <section class="home-cat-section home-cat-section--<?php echo esc_attr($layout); ?>"
             style="--cat-color: <?php echo esc_attr($color); ?>;">
        <div class="container">

            <!-- Section Header -->
            <div class="section-title" style="border-bottom-color: <?php echo esc_attr($color); ?>;">
                <h2 class="section-title__text">
                    <span class="section-title__icon"><?php echo $icon; ?></span>
                    <?php echo esc_html($category->name); ?>
                </h2>
                <span class="badge" style="background: <?php echo esc_attr($color); ?>; color: white;">
                    <?php echo $category->count; ?>
                </span>
                <div class="section-title__line"></div>
                <a href="<?php echo esc_url(get_category_link($category)); ?>"
                   class="btn btn--outline btn--sm"
                   style="border-color: <?php echo esc_attr($color); ?>; color: <?php echo esc_attr($color); ?>;">
                    All <?php echo esc_html($category->name); ?> →
                </a>
            </div>

            <!-- Layout: Grid -->
            <?php if ($layout === 'grid') : ?>
                <div class="grid grid--3">
                    <?php while ($query->have_posts()) : $query->the_post(); ?>
                        <article class="news-card">
                            <div class="news-card__image">
                                <a href="<?php the_permalink(); ?>">
                                    <?php if (has_post_thumbnail()) the_post_thumbnail('hikmahnews-grid'); ?>
                                </a>
                                <span class="badge news-card__badge"
                                      style="background: <?php echo esc_attr($color); ?>;">
                                    <?php echo $icon; ?>
                                </span>
                            </div>
                            <div class="news-card__body">
                                <h3 class="news-card__title">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h3>
                                <p class="news-card__excerpt">
                                    <?php echo wp_trim_words(get_the_excerpt(), 15, '...'); ?>
                                </p>
                                <div class="news-card__meta">
                                    <span class="author"><?php the_author(); ?></span>
                                    <span class="dot"></span>
                                    <time><?php echo human_time_diff(get_the_time('U'), current_time('timestamp')) . ' ago'; ?></time>
                                    <span class="dot"></span>
                                    <span>👁 <?php echo hikmahnews_get_formatted_views(); ?></span>
                                </div>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>

            <!-- Layout: List (Featured + Side List) -->
            <?php elseif ($layout === 'list') : ?>
                <div class="cat-block-grid">
                    <?php $query->the_post(); ?>
                    <article class="cat-block-featured">
                        <a href="<?php the_permalink(); ?>" class="cat-block-featured__link">
                            <div class="cat-block-featured__image">
                                <?php if (has_post_thumbnail()) the_post_thumbnail('hikmahnews-grid'); ?>
                                <div class="cat-block-featured__overlay"></div>
                            </div>
                            <div class="cat-block-featured__content">
                                <h3 class="cat-block-featured__title"><?php the_title(); ?></h3>
                                <p class="cat-block-featured__excerpt">
                                    <?php echo wp_trim_words(get_the_excerpt(), 20, '...'); ?>
                                </p>
                            </div>
                        </a>
                    </article>
                    <div class="cat-block-list">
                        <?php while ($query->have_posts()) : $query->the_post(); ?>
                            <article class="news-list-card">
                                <div class="news-list-card__image">
                                    <a href="<?php the_permalink(); ?>">
                                        <?php if (has_post_thumbnail()) the_post_thumbnail('hikmahnews-thumb'); ?>
                                    </a>
                                </div>
                                <div class="news-list-card__body">
                                    <h4 class="news-list-card__title">
                                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                    </h4>
                                    <div class="news-card__meta">
                                        <time><?php echo human_time_diff(get_the_time('U'), current_time('timestamp')) . ' ago'; ?></time>
                                    </div>
                                </div>
                            </article>
                        <?php endwhile; ?>
                    </div>
                </div>

            <!-- Layout: Compact (Numbered) -->
            <?php elseif ($layout === 'compact') : ?>
                <div class="grid grid--2">
                    <div class="numbered-list">
                        <?php
                        $num = 1;
                        while ($query->have_posts()) : $query->the_post();
                        ?>
                            <article class="numbered-item">
                                <span class="numbered-item__num <?php echo $num <= 3 ? 'numbered-item__num--highlight' : ''; ?>"
                                      style="<?php echo $num <= 3 ? 'color:' . esc_attr($color) : ''; ?>">
                                    <?php echo str_pad($num, 2, '0', STR_PAD_LEFT); ?>
                                </span>
                                <div class="numbered-item__content">
                                    <h4 class="numbered-item__title">
                                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                    </h4>
                                    <div class="numbered-item__meta">
                                        <time><?php echo human_time_diff(get_the_time('U'), current_time('timestamp')) . ' ago'; ?></time>
                                        <span class="dot"></span>
                                        <span>👁 <?php echo hikmahnews_get_formatted_views(); ?></span>
                                    </div>
                                </div>
                            </article>
                        <?php $num++; endwhile; ?>
                    </div>
                </div>

            <!-- Layout: Carousel (Horizontal Scroll) -->
            <?php elseif ($layout === 'carousel') : ?>
                <div class="home-carousel" id="carousel-<?php echo esc_attr($slug); ?>">
                    <?php while ($query->have_posts()) : $query->the_post(); ?>
                        <article class="news-card home-carousel__card">
                            <div class="news-card__image">
                                <a href="<?php the_permalink(); ?>">
                                    <?php if (has_post_thumbnail()) the_post_thumbnail('hikmahnews-grid'); ?>
                                </a>
                            </div>
                            <div class="news-card__body">
                                <h3 class="news-card__title">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h3>
                                <div class="news-card__meta">
                                    <time><?php echo human_time_diff(get_the_time('U'), current_time('timestamp')) . ' ago'; ?></time>
                                </div>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>
            <?php endif; ?>

        </div>
    </section>
    <?php
    wp_reset_postdata();
}