<?php
/**
 * Popular & Trending Section — Two Column Layout
 * @package HikmahNews
 */
if (!defined('ABSPATH')) exit;

// Popular: by comment count
$popular_query = new WP_Query([
    'posts_per_page' => 5,
    'orderby'        => 'comment_count',
    'order'          => 'DESC',
    'no_found_rows'  => true,
]);

// Trending: recent with tag "trending" or just recent
$trending_query = new WP_Query([
    'posts_per_page' => 5,
    'orderby'        => 'date',
    'order'          => 'DESC',
    'no_found_rows'  => true,
    'date_query'     => [
        ['after' => '3 days ago'],
    ],
]);
?>

<section class="popular-trending-section">
    <div class="container">
        <div class="grid grid--2">

            <!-- ===== POPULAR POSTS ===== -->
            <div class="popular-block">
                <div class="section-title">
                    <h2 class="section-title__text">
                        <span class="section-title__icon">🏆</span>
                        Most Popular
                    </h2>
                    <div class="section-title__line"></div>
                </div>

                <div class="numbered-list">
                    <?php
                    $counter = 1;
                    if ($popular_query->have_posts()) :
                        while ($popular_query->have_posts()) : $popular_query->the_post();
                    ?>
                        <article class="numbered-item">
                            <span class="numbered-item__num <?php echo $counter <= 3 ? 'numbered-item__num--highlight' : ''; ?>">
                                <?php echo str_pad($counter, 2, '0', STR_PAD_LEFT); ?>
                            </span>
                            <div class="numbered-item__content">
                                <?php
                                $cats = get_the_category();
                                if ($cats) :
                                ?>
                                    <span class="numbered-item__cat">
                                        <?php echo esc_html($cats[0]->name); ?>
                                    </span>
                                <?php endif; ?>
                                <h4 class="numbered-item__title">
                                    <a href="<?php the_permalink(); ?>">
                                        <?php the_title(); ?>
                                    </a>
                                </h4>
                                <div class="numbered-item__meta">
                                    <span><?php the_author(); ?></span>
                                    <span class="dot"></span>
                                    <time><?php echo human_time_diff(get_the_time('U'), current_time('timestamp')) . ' ago'; ?></time>
                                    <span class="dot"></span>
                                    <span>💬 <?php echo get_comments_number(); ?></span>
                                </div>
                            </div>
                        </article>
                    <?php
                            $counter++;
                        endwhile;
                        wp_reset_postdata();
                    endif;
                    ?>
                </div>
            </div>

            <!-- ===== TRENDING POSTS ===== -->
            <div class="trending-block">
                <div class="section-title" style="border-bottom-color: var(--color-accent);">
                    <h2 class="section-title__text">
                        <span class="section-title__icon">📈</span>
                        Trending Now
                    </h2>
                    <div class="section-title__line"></div>
                </div>

                <div class="trending-list">
                    <?php
                    if ($trending_query->have_posts()) :
                        while ($trending_query->have_posts()) : $trending_query->the_post();
                    ?>
                        <article class="trending-item">
                            <div class="trending-item__image">
                                <a href="<?php the_permalink(); ?>">
                                    <?php if (has_post_thumbnail()) : ?>
                                        <?php the_post_thumbnail('hikmahnews-thumb'); ?>
                                    <?php endif; ?>
                                </a>
                            </div>
                            <div class="trending-item__content">
                                <?php
                                $cats = get_the_category();
                                if ($cats) :
                                ?>
                                    <span class="badge badge--accent trending-item__badge">
                                        <?php echo esc_html($cats[0]->name); ?>
                                    </span>
                                <?php endif; ?>
                                <h4 class="trending-item__title">
                                    <a href="<?php the_permalink(); ?>">
                                        <?php the_title(); ?>
                                    </a>
                                </h4>
                                <div class="trending-item__meta">
                                    <time>
                                        <?php echo human_time_diff(get_the_time('U'), current_time('timestamp')) . ' ago'; ?>
                                    </time>
                                    <span class="dot"></span>
                                    <span><?php echo hikmahnews_reading_time(); ?> min read</span>
                                </div>
                            </div>
                        </article>
                    <?php
                        endwhile;
                        wp_reset_postdata();
                    else :
                    ?>
                        <p class="trending-empty">No trending stories right now.</p>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</section>