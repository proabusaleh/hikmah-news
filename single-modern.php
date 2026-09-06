<?php
/**
 * Template Name: Modern Single Post
 * Modern editorial single post layout
 * @package HikmahNews
 */
if (!defined('ABSPATH')) exit;

get_header();

// Track post views
if (is_singular()) {
    hikmahnews_set_post_views(get_the_ID());
}
?>

<main class="modern-single" id="main">
    <?php while (have_posts()) : the_post();

        $cats = get_the_category();
        $cat_color = $cats ? hikmahnews_get_category_color($cats[0]->term_id) : '#DC2626';
        $reading = hikmahnews_reading_time_detailed(get_the_ID());
    ?>

    <!-- Reading Progress -->
    <div class="modern-progress" id="readingProgress">
        <div class="modern-progress__bar" id="readingProgressBar"
             style="background: <?php echo esc_attr($cat_color); ?>;"></div>
    </div>

    <article class="modern-article">

        <!-- Header -->
        <header class="modern-article__header">
            <div class="container container--narrow">

                <!-- Breadcrumb -->
                <nav class="modern-breadcrumb">
                    <a href="<?php echo home_url('/'); ?>">Home</a>
                    <span>/</span>
                    <?php if ($cats) : ?>
                        <a href="<?php echo get_category_link($cats[0]); ?>"><?php echo esc_html($cats[0]->name); ?></a>
                        <span>/</span>
                    <?php endif; ?>
                    <span class="modern-breadcrumb__current"><?php echo wp_trim_words(get_the_title(), 6, '...'); ?></span>
                </nav>

                <!-- Category -->
                <?php if ($cats) : ?>
                    <a href="<?php echo get_category_link($cats[0]); ?>" class="modern-badge"
                       style="background: <?php echo esc_attr($cat_color); ?>;">
                        <?php echo hikmahnews_get_category_icon($cats[0]->term_id); ?>
                        <?php echo esc_html($cats[0]->name); ?>
                    </a>
                <?php endif; ?>

                <!-- Title -->
                <h1 class="modern-article__title"><?php the_title(); ?></h1>

                <!-- Subtitle -->
                <?php if (has_excerpt()) : ?>
                    <p class="modern-article__subtitle"><?php echo wp_strip_all_tags(get_the_excerpt()); ?></p>
                <?php endif; ?>

                <!-- Meta Bar -->
                <div class="modern-article__meta-bar">
                    <div class="modern-article__author">
                        <?php echo get_avatar(get_the_author_meta('ID'), 48); ?>
                        <div>
                            <a href="<?php echo get_author_posts_url(get_the_author_meta('ID')); ?>"
                               class="modern-article__author-name">
                                <?php the_author(); ?>
                            </a>
                            <span class="modern-article__author-role">Staff Writer</span>
                        </div>
                    </div>

                    <div class="modern-article__meta-details">
                        <time datetime="<?php echo get_the_date('c'); ?>">
                            📅 <?php echo get_the_date('F j, Y'); ?>
                        </time>
                        <span class="meta-dot"></span>
                        <span>⏱ <?php echo $reading['label_full']; ?></span>
                        <span class="meta-dot"></span>
                        <span>👁 <?php echo hikmahnews_get_formatted_views(); ?></span>
                    </div>

                    <!-- Share + Bookmark -->
                    <div class="modern-article__actions">
                        <?php if (function_exists('hikmahnews_bookmark_button')) hikmahnews_bookmark_button(); ?>
                        <button class="modern-share-btn" onclick="navigator.share({title: '<?php the_title_attribute(); ?>', url: '<?php the_permalink(); ?>'})">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/>
                                <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/>
                                <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <?php do_action('hikmahnews_after_author_box'); ?>
            </div>
        </header>

        <?php do_action('hikmahnews_before_content'); ?>

        <!-- Featured Image -->
        <?php if (has_post_thumbnail()) : ?>
            <figure class="modern-article__hero-image">
                <div class="container">
                    <?php the_post_thumbnail('hikmahnews-hero'); ?>
                    <?php if (get_post(get_post_thumbnail_id())->post_excerpt) : ?>
                        <figcaption><?php echo esc_html(get_post(get_post_thumbnail_id())->post_excerpt); ?></figcaption>
                    <?php endif; ?>
                </div>
            </figure>
        <?php endif; ?>

        <!-- Content -->
        <div class="modern-article__content">
            <div class="container container--narrow">
                <div class="modern-prose">
                    <?php the_content(); ?>
                </div>

                <!-- Tags -->
                <?php $tags = get_the_tags(); if ($tags) : ?>
                    <div class="modern-tags">
                        <span class="modern-tags__label">Tags:</span>
                        <?php foreach ($tags as $tag) : ?>
                            <a href="<?php echo get_tag_link($tag); ?>" class="modern-tag">
                                #<?php echo esc_html($tag->name); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </article>

    <!-- Related Posts -->
    <?php
    $related = new WP_Query([
        'category__in'   => $cats ? wp_list_pluck($cats, 'term_id') : [],
        'post__not_in'   => [get_the_ID()],
        'posts_per_page' => 3,
        'orderby'        => 'rand',
        'no_found_rows'  => true,
    ]);

    if ($related->have_posts()) :
    ?>
    <section class="modern-related">
        <div class="container">
            <h2 class="modern-section__title" style="margin-bottom:32px;">Continue Reading</h2>
            <div class="modern-grid" style="grid-template-columns: repeat(3, 1fr);">
                <?php while ($related->have_posts()) : $related->the_post(); ?>
                    <article class="modern-card">
                        <a href="<?php the_permalink(); ?>" class="modern-card__link">
                            <div class="modern-card__image">
                                <?php if (has_post_thumbnail()) the_post_thumbnail('hikmahnews-grid'); ?>
                            </div>
                            <div class="modern-card__body">
                                <h3 class="modern-card__title"><?php the_title(); ?></h3>
                                <div class="modern-card__meta">
                                    <time><?php echo human_time_diff(get_the_time('U'), current_time('timestamp')); ?> ago</time>
                                </div>
                            </div>
                        </a>
                    </article>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <?php endwhile; ?>
</main>

<?php

// Reading progress bar JS for the modern layout
if (wp_script_is('hikmahnews-main', 'enqueued')) {
    wp_add_inline_script('hikmahnews-main', '
        (function() {
            var bar = document.getElementById("readingProgressBar");
            var article = document.querySelector(".modern-article");
            if (!bar || !article) return;

            function onScroll() {
                var total = article.scrollHeight - window.innerHeight;
                var scrolled = Math.max(0, Math.min(window.scrollY - article.offsetTop + window.innerHeight * 0.25, total));
                var progress = total > 0 ? scrolled / total * 100 : 0;
                bar.style.width = Math.min(progress, 100) + "%";
            }

            window.addEventListener("scroll", onScroll, { passive: true });
            window.addEventListener("resize", onScroll);
            onScroll();
        })();
    ');
}

get_footer();