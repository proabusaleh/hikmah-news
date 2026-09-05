<?php
/**
 * Photo Gallery Section
 * @package HikmahNews
 */
if (!defined('ABSPATH')) exit;

$gallery_query = new WP_Query([
    'posts_per_page' => 6,
    'category_name'  => 'gallery',
    'no_found_rows'  => true,
]);

if (!$gallery_query->have_posts()) return;
?>

<section class="gallery-section">
    <div class="container">

        <div class="section-title" style="border-bottom-color: #8B5CF6;">
            <h2 class="section-title__text">
                <span class="section-title__icon">📸</span>
                Photo Gallery
            </h2>
            <div class="section-title__line"></div>
        </div>

        <div class="gallery-grid">
            <?php while ($gallery_query->have_posts()) : $gallery_query->the_post(); ?>
                <article class="gallery-card">
                    <a href="<?php the_permalink(); ?>" class="gallery-card__link">
                        <div class="gallery-card__image">
                            <?php if (has_post_thumbnail()) : ?>
                                <?php the_post_thumbnail('hikmahnews-grid'); ?>
                            <?php endif; ?>
                            <div class="gallery-card__overlay">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                     stroke="white" stroke-width="2">
                                    <circle cx="11" cy="11" r="8"/>
                                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                                    <line x1="11" y1="8" x2="11" y2="14"/>
                                    <line x1="8" y1="11" x2="14" y2="11"/>
                                </svg>
                            </div>
                        </div>
                        <div class="gallery-card__caption">
                            <h4 class="gallery-card__title"><?php the_title(); ?></h4>
                            <span class="gallery-card__count">
                                <?php
                                $img_count = get_post_meta(get_the_ID(), 'gallery_count', true);
                                echo $img_count ? esc_html($img_count) . ' Photos' : '12 Photos';
                                ?>
                            </span>
                        </div>
                    </a>
                </article>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>

    </div>
</section>