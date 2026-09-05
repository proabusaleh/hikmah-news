<?php
/**
 * Category Blocks — Politics, Sports, Technology, Business
 * @package HikmahNews
 */
if (!defined('ABSPATH')) exit;

// Define category slugs to display
$block_categories = ['politics', 'sports', 'technology', 'business'];

foreach ($block_categories as $cat_slug) :
    $category = get_category_by_slug($cat_slug);
    if (!$category) continue;

    $cat_query = new WP_Query([
        'category_name'  => $cat_slug,
        'posts_per_page' => 5,
        'no_found_rows'  => true,
    ]);

    if (!$cat_query->have_posts()) continue;

    // Category color map
    $cat_colors = [
        'politics'   => '#DC2626',
        'sports'     => '#059669',
        'technology' => '#7C3AED',
        'business'   => '#D97706',
    ];
    $accent = $cat_colors[$cat_slug] ?? 'var(--color-primary)';
?>

<section class="category-block" style="--cat-accent: <?php echo esc_attr($accent); ?>">
    <div class="container">

        <!-- Section Title -->
        <div class="section-title" style="border-bottom-color: <?php echo esc_attr($accent); ?>">
            <h2 class="section-title__text">
                <?php echo esc_html($category->name); ?>
            </h2>
            <span class="badge" style="background: <?php echo esc_attr($accent); ?>; color: #fff;">
                <?php echo esc_html($category->count); ?> Articles
            </span>
            <div class="section-title__line"></div>
            <a href="<?php echo esc_url(get_category_link($category->term_id)); ?>"
               class="btn btn--outline btn--sm"
               style="border-color: <?php echo esc_attr($accent); ?>; color: <?php echo esc_attr($accent); ?>;">
                More <?php echo esc_html($category->name); ?> →
            </a>
        </div>

        <!-- Category Content: Featured + List -->
        <div class="cat-block-grid">

            <!-- Featured Post (First) -->
            <?php $cat_query->the_post(); ?>
            <article class="cat-block-featured">
                <a href="<?php the_permalink(); ?>" class="cat-block-featured__link">
                    <div class="cat-block-featured__image">
                        <?php if (has_post_thumbnail()) : ?>
                            <?php the_post_thumbnail('hikmahnews-grid'); ?>
                        <?php endif; ?>
                        <div class="cat-block-featured__overlay"></div>
                    </div>
                    <div class="cat-block-featured__content">
                        <h3 class="cat-block-featured__title">
                            <?php the_title(); ?>
                        </h3>
                        <p class="cat-block-featured__excerpt">
                            <?php echo wp_trim_words(get_the_excerpt(), 20, '...'); ?>
                        </p>
                        <div class="cat-block-featured__meta">
                            <span><?php the_author(); ?></span>
                            <span class="dot"></span>
                            <time><?php echo human_time_diff(get_the_time('U'), current_time('timestamp')) . ' ago'; ?></time>
                        </div>
                    </div>
                </a>
            </article>

            <!-- List Posts (Remaining 4) -->
            <div class="cat-block-list">
                <?php while ($cat_query->have_posts()) : $cat_query->the_post(); ?>
                    <article class="news-list-card">
                        <div class="news-list-card__image">
                            <a href="<?php the_permalink(); ?>">
                                <?php if (has_post_thumbnail()) : ?>
                                    <?php the_post_thumbnail('hikmahnews-thumb'); ?>
                                <?php endif; ?>
                            </a>
                        </div>
                        <div class="news-list-card__body">
                            <h4 class="news-list-card__title">
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_title(); ?>
                                </a>
                            </h4>
                            <div class="news-card__meta">
                                <time><?php echo human_time_diff(get_the_time('U'), current_time('timestamp')) . ' ago'; ?></time>
                            </div>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>

        </div>
    </div>
</section>

<?php
    wp_reset_postdata();
endforeach;
?>