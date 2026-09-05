<?php
/**
 * Single Post Template
 * @package HikmahNews
 */
if (!defined('ABSPATH')) exit;

get_header();

// Track post views
if (is_singular()) {
    hikmahnews_set_post_views(get_the_ID());
}
?>

<main class="site-main single-post" id="main">

    <?php while (have_posts()) : ?>
        <?php the_post(); ?>

        <article id="post-<?php the_ID(); ?>" <?php post_class('single-article'); ?>>

            <!-- ===== BREADCRUMB ===== -->
            <div class="single-breadcrumb">
                <div class="container container--narrow">
                    <nav aria-label="Breadcrumb">
                        <ol class="breadcrumb-list">
                            <li><a href="<?php echo esc_url(home_url('/')); ?>">Home</a></li>
                            <?php
                            $categories = get_the_category();
                            if ($categories) :
                                $cat = $categories[0];
                                // Check for parent category
                                if ($cat->parent) {
                                    $parent = get_category($cat->parent);
                                    echo '<li><a href="' . esc_url(get_category_link($parent->term_id)) . '">'
                                         . esc_html($parent->name) . '</a></li>';
                                }
                            ?>
                                <li>
                                    <a href="<?php echo esc_url(get_category_link($cat->term_id)); ?>">
                                        <?php echo esc_html($cat->name); ?>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <li class="breadcrumb-current" aria-current="page">
                                <?php echo wp_trim_words(get_the_title(), 8, '...'); ?>
                            </li>
                        </ol>
                    </nav>
                </div>
            </div>

            <!-- ===== ARTICLE HEADER ===== -->
            <header class="single-header">
                <div class="container container--narrow">

                    <!-- Category Badge -->
                    <?php if ($categories) : ?>
                        <a href="<?php echo esc_url(get_category_link($categories[0]->term_id)); ?>"
                           class="badge badge--primary single-header__category">
                            <?php echo esc_html($categories[0]->name); ?>
                        </a>
                    <?php endif; ?>

                    <!-- Title -->
                    <h1 class="single-header__title">
                        <?php the_title(); ?>
                    </h1>

                    <!-- Subtitle / Excerpt -->
                    <?php if (has_excerpt()) : ?>
                        <p class="single-header__subtitle">
                            <?php echo wp_strip_all_tags(get_the_excerpt()); ?>
                        </p>
                    <?php endif; ?>

                    <!-- Meta Row -->
                    <div class="single-header__meta">
                        <!-- Author -->
                        <div class="single-meta__author">
                            <div class="single-meta__avatar">
                                <?php echo get_avatar(get_the_author_meta('ID'), 40); ?>
                            </div>
                            <div class="single-meta__info">
                                <a href="<?php echo esc_url(get_author_posts_url(get_the_author_meta('ID'))); ?>"
                                   class="single-meta__name">
                                    <?php the_author(); ?>
                                </a>
                                <span class="single-meta__role">
                                    <?php echo esc_html(get_the_author_meta('description') ? 'Staff Writer' : 'Contributor'); ?>
                                </span>
                            </div>
                        </div>

                        <div class="single-meta__divider"></div>

                        <!-- Date & Reading Time -->
                        <div class="single-meta__details">
                            <time datetime="<?php echo get_the_date('c'); ?>">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"/>
                                    <polyline points="12 6 12 12 16 14"/>
                                </svg>
                                <?php echo get_the_date('F j, Y'); ?>
                            </time>
                            <span class="dot"></span>
                            <span>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="2">
                                    <path d="M2 3h6a4 4 0 014 4v14a3 3 0 00-3-3H2z"/>
                                    <path d="M22 3h-6a4 4 0 00-4 4v14a3 3 0 013-3h7z"/>
                                </svg>
                                <?php echo hikmahnews_reading_time(); ?> min read
                            </span>
                            <span class="dot"></span>
                            <span>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                                <?php echo number_format(hikmahnews_get_post_views(get_the_ID())); ?> views
                            </span>
                        </div>
                    </div>

                    <!-- Social Share Bar -->
                    <div class="single-share">
                        <span class="single-share__label">Share:</span>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(get_permalink()); ?>"
                           target="_blank" rel="noopener" class="single-share__btn single-share__btn--fb"
                           aria-label="Share on Facebook">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/>
                            </svg>
                        </a>
                        <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(get_permalink()); ?>&text=<?php echo urlencode(get_the_title()); ?>"
                           target="_blank" rel="noopener" class="single-share__btn single-share__btn--tw"
                           aria-label="Share on Twitter">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2c9 5 20 0 20-11.5a4.5 4.5 0 00-.08-.83A7.72 7.72 0 0023 3z"/>
                            </svg>
                        </a>
                        <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo urlencode(get_permalink()); ?>"
                           target="_blank" rel="noopener" class="single-share__btn single-share__btn--li"
                           aria-label="Share on LinkedIn">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6z"/>
                                <rect x="2" y="9" width="4" height="12"/>
                                <circle cx="4" cy="4" r="2"/>
                            </svg>
                        </a>
                        <a href="https://wa.me/?text=<?php echo urlencode(get_the_title() . ' ' . get_permalink()); ?>"
                           target="_blank" rel="noopener" class="single-share__btn single-share__btn--wa"
                           aria-label="Share on WhatsApp">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                                <path d="M12 0C5.373 0 0 5.373 0 12c0 2.625.846 5.059 2.284 7.034L.789 23.492a.5.5 0 00.612.638l4.71-1.393A11.94 11.94 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22c-2.352 0-4.55-.707-6.39-1.912l-.448-.296-2.795.827.836-2.72-.322-.468A9.935 9.935 0 012 12C2 6.486 6.486 2 12 2s10 4.486 10 10-4.486 10-10 10z"/>
                            </svg>
                        </a>
                        <button class="single-share__btn single-share__btn--copy"
                                onclick="navigator.clipboard.writeText('<?php the_permalink(); ?>')"
                                aria-label="Copy link">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2">
                                <path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/>
                                <path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/>
                            </svg>
                        </button>

                        <!-- Bookmark: Save article -->
                        <?php hikmahnews_bookmark_button(); ?>
                    </div>

                </div>
            </header>

            <!-- ===== FEATURED IMAGE ===== -->
            <?php if (has_post_thumbnail()) : ?>
                <figure class="single-featured-image">
                    <div class="container container--narrow">
                        <?php the_post_thumbnail('hikmahnews-hero'); ?>
                        <?php if (get_post(get_post_thumbnail_id())->post_excerpt) : ?>
                            <figcaption class="single-featured-image__caption">
                                <?php echo esc_html(get_post(get_post_thumbnail_id())->post_excerpt); ?>
                            </figcaption>
                        <?php endif; ?>
                    </div>
                </figure>
            <?php endif; ?>

            <!-- ===== ARTICLE CONTENT ===== -->
            <div class="single-content">
                <div class="container container--narrow">
                    <div class="single-content__body">
                        <?php
                        the_content();

                        wp_link_pages([
                            'before' => '<div class="single-page-links"><span>Pages:</span>',
                            'after'  => '</div>',
                        ]);
                        ?>
                    </div>

                    <!-- Tags -->
                    <?php
                    $tags = get_the_tags();
                    if ($tags) :
                    ?>
                        <div class="single-tags">
                            <span class="single-tags__label">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="2">
                                    <path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/>
                                    <line x1="7" y1="7" x2="7.01" y2="7"/>
                                </svg>
                                Tags:
                            </span>
                            <?php foreach ($tags as $tag) : ?>
                                <a href="<?php echo esc_url(get_tag_link($tag->term_id)); ?>"
                                   class="single-tags__tag">
                                    #<?php echo esc_html($tag->name); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                </div>
            </div>

        </article>

    <?php endwhile; ?>

    <!-- ===== AUTHOR BOX ===== -->
    <?php get_template_part('template-parts/content/author-box'); ?>

    <!-- ===== PREVIOUS / NEXT ===== -->
    <?php get_template_part('template-parts/content/post-nav'); ?>

    <!-- ===== RELATED POSTS ===== -->
    <?php get_template_part('template-parts/content/related-posts'); ?>

    <!-- ===== COMMENTS ===== -->
    <?php
    if (comments_open() || get_comments_number()) :
        comments_template();
    endif;
    ?>

</main>

<?php
get_footer();