<?php
/**
 * Template Name: Modern Homepage
 * Description: Ultra-modern bento grid homepage with glassmorphism effects
 * @package HikmahNews
 */
if (!defined('ABSPATH')) exit;

get_header();

// Pre-fetch all data
$breaking = hikmahnews_get_breaking_posts(5);
$featured = hikmahnews_get_featured_posts('hero', 1);
$latest = get_posts(['posts_per_page' => 6, 'ignore_sticky_posts' => 1, 'no_found_rows' => true]);
$trending = function_exists('hikmahnews_get_trending_posts') ? hikmahnews_get_trending_posts(4, 48) : [];
$categories = get_categories(['parent' => 0, 'hide_empty' => true, 'number' => 4, 'orderby' => 'count', 'order' => 'DESC']);
?>

<main class="modern-home" id="main">

    <!-- ═══════════════════════════════════════════
         SECTION 1: HERO BENTO GRID
         ═══════════════════════════════════════════ -->
    <section class="hero-bento">
        <div class="container">
            <div class="bento-grid">

                <!-- MAIN HERO (Large Card) -->
                <?php
                $hero_post = !empty($featured) ? $featured[0] : ($latest[0] ?? null);
                if ($hero_post) :
                    $hero_cats = get_the_category($hero_post->ID);
                    $hero_color = $hero_cats ? hikmahnews_get_category_color($hero_cats[0]->term_id) : '#DC2626';
                ?>
                <article class="bento-card bento-card--hero" style="--accent: <?php echo esc_attr($hero_color); ?>">
                    <a href="<?php echo get_permalink($hero_post); ?>" class="bento-card__link">
                        <div class="bento-card__bg">
                            <?php if (has_post_thumbnail($hero_post)) : ?>
                                <?php echo get_the_post_thumbnail($hero_post, 'hikmahnews-hero'); ?>
                            <?php endif; ?>
                        </div>
                        <div class="bento-card__glass"></div>
                        <div class="bento-card__content">
                            <?php if ($hero_cats) : ?>
                                <span class="modern-badge" style="background: <?php echo esc_attr($hero_color); ?>">
                                    <?php echo hikmahnews_get_category_icon($hero_cats[0]->term_id); ?>
                                    <?php echo esc_html($hero_cats[0]->name); ?>
                                </span>
                            <?php endif; ?>
                            <h1 class="bento-card__title bento-card__title--xl">
                                <?php echo esc_html($hero_post->post_title); ?>
                            </h1>
                            <p class="bento-card__excerpt">
                                <?php echo wp_trim_words(wp_strip_all_tags($hero_post->post_excerpt ?: $hero_post->post_content), 25, '...'); ?>
                            </p>
                            <div class="bento-card__meta">
                                <div class="modern-author">
                                    <?php echo get_avatar($hero_post->post_author, 32); ?>
                                    <span><?php echo get_the_author_meta('display_name', $hero_post->post_author); ?></span>
                                </div>
                                <span class="meta-dot"></span>
                                <time><?php echo human_time_diff(strtotime($hero_post->post_date), current_time('timestamp')); ?> ago</time>
                                <span class="meta-dot"></span>
                                <span><?php echo hikmahnews_reading_time_detailed($hero_post->ID)['label']; ?></span>
                            </div>
                        </div>
                    </a>
                </article>
                <?php endif; ?>

                <!-- SIDE CARDS (2 stacked) -->
                <div class="bento-stack">
                    <?php
                    $side_posts = array_slice($latest, 1, 2);
                    foreach ($side_posts as $sp) :
                        $sp_cats = get_the_category($sp->ID);
                        $sp_color = $sp_cats ? hikmahnews_get_category_color($sp_cats[0]->term_id) : '#6366F1';
                    ?>
                    <article class="bento-card bento-card--side" style="--accent: <?php echo esc_attr($sp_color); ?>">
                        <a href="<?php echo get_permalink($sp); ?>" class="bento-card__link">
                            <div class="bento-card__bg">
                                <?php if (has_post_thumbnail($sp)) echo get_the_post_thumbnail($sp, 'hikmahnews-grid'); ?>
                            </div>
                            <div class="bento-card__glass"></div>
                            <div class="bento-card__content">
                                <?php if ($sp_cats) : ?>
                                    <span class="modern-badge modern-badge--sm" style="background: <?php echo esc_attr($sp_color); ?>">
                                        <?php echo esc_html($sp_cats[0]->name); ?>
                                    </span>
                                <?php endif; ?>
                                <h3 class="bento-card__title"><?php echo esc_html($sp->post_title); ?></h3>
                                <div class="bento-card__meta bento-card__meta--sm">
                                    <time><?php echo human_time_diff(strtotime($sp->post_date), current_time('timestamp')); ?> ago</time>
                                </div>
                            </div>
                        </a>
                    </article>
                    <?php endforeach; ?>
                </div>

                <!-- TRENDING CARD (Tall) -->
                <article class="bento-card bento-card--trending">
                    <div class="bento-card__header">
                        <span class="modern-badge modern-badge--gradient">🔥 Trending</span>
                    </div>
                    <div class="bento-trending-list">
                        <?php
                        $rank = 1;
                        $trend_posts = !empty($trending) ? $trending : array_slice($latest, 3, 4);
                        foreach (array_slice($trend_posts, 0, 4) as $tp) :
                            $tp_cats = get_the_category($tp->ID);
                        ?>
                        <a href="<?php echo get_permalink($tp); ?>" class="bento-trending-item">
                            <span class="bento-trending-item__rank <?php echo $rank <= 2 ? 'bento-trending-item__rank--hot' : ''; ?>">
                                <?php echo str_pad($rank, 2, '0', STR_PAD_LEFT); ?>
                            </span>
                            <div class="bento-trending-item__info">
                                <?php if ($tp_cats) : ?>
                                    <span class="bento-trending-item__cat"><?php echo esc_html($tp_cats[0]->name); ?></span>
                                <?php endif; ?>
                                <h4 class="bento-trending-item__title"><?php echo esc_html($tp->post_title); ?></h4>
                            </div>
                            <span class="bento-trending-item__views">
                                👁 <?php echo hikmahnews_get_formatted_views($tp->ID); ?>
                            </span>
                        </a>
                        <?php $rank++; endforeach; ?>
                    </div>
                </article>

            </div>
        </div>
    </section>

    <!-- ═══════════════════════════════════════════
         SECTION 2: BREAKING NEWS TICKER (Modern)
         ═══════════════════════════════════════════ -->
    <?php if (!empty($breaking)) : ?>
    <section class="modern-ticker">
        <div class="container modern-ticker__inner">
            <div class="modern-ticker__label">
                <span class="modern-ticker__pulse"></span>
                LIVE
            </div>
            <div class="modern-ticker__track-wrap">
                <div class="modern-ticker__track">
                    <?php foreach ($breaking as $bp) : ?>
                        <a href="<?php echo get_permalink($bp); ?>" class="modern-ticker__item">
                            <?php echo esc_html($bp->post_title); ?>
                        </a>
                    <?php endforeach; ?>
                    <?php foreach ($breaking as $bp) : ?>
                        <a href="<?php echo get_permalink($bp); ?>" class="modern-ticker__item">
                            <?php echo esc_html($bp->post_title); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- ═══════════════════════════════════════════
         SECTION 3: CATEGORY SECTIONS (Modern Cards)
         ═══════════════════════════════════════════ -->
    <?php foreach ($categories as $cat) :
        $cat_color = hikmahnews_get_category_color($cat->term_id);
        $cat_icon = hikmahnews_get_category_icon($cat->term_id);
        $cat_posts = get_posts([
            'category'       => $cat->term_id,
            'posts_per_page' => 4,
            'no_found_rows'  => true,
        ]);
        if (empty($cat_posts)) continue;
    ?>
    <section class="modern-section" style="--section-color: <?php echo esc_attr($cat_color); ?>">
        <div class="container">
            <div class="modern-section__header">
                <div class="modern-section__title-group">
                    <span class="modern-section__icon"><?php echo $cat_icon; ?></span>
                    <h2 class="modern-section__title"><?php echo esc_html($cat->name); ?></h2>
                    <span class="modern-section__count"><?php echo $cat->count; ?> articles</span>
                </div>
                <a href="<?php echo get_category_link($cat); ?>" class="modern-btn modern-btn--ghost">
                    View All
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
                    </svg>
                </a>
            </div>

            <div class="modern-grid">
                <?php foreach ($cat_posts as $idx => $cp) :
                    $cp_cats = get_the_category($cp->ID);
                ?>
                <article class="modern-card <?php echo $idx === 0 ? 'modern-card--featured' : ''; ?>">
                    <a href="<?php echo get_permalink($cp); ?>" class="modern-card__link">
                        <div class="modern-card__image">
                            <?php if (has_post_thumbnail($cp)) : ?>
                                <?php echo get_the_post_thumbnail($cp, $idx === 0 ? 'hikmahnews-hero' : 'hikmahnews-grid'); ?>
                            <?php endif; ?>
                            <div class="modern-card__overlay"></div>
                        </div>
                        <div class="modern-card__body">
                            <?php if ($cp_cats) : ?>
                                <span class="modern-card__cat" style="color: <?php echo esc_attr($cat_color); ?>">
                                    <?php echo esc_html($cp_cats[0]->name); ?>
                                </span>
                            <?php endif; ?>
                            <h3 class="modern-card__title"><?php echo esc_html($cp->post_title); ?></h3>
                            <?php if ($idx === 0) : ?>
                                <p class="modern-card__excerpt">
                                    <?php echo wp_trim_words(wp_strip_all_tags($cp->post_excerpt ?: $cp->post_content), 18, '...'); ?>
                                </p>
                            <?php endif; ?>
                            <div class="modern-card__meta">
                                <span><?php echo get_the_author_meta('display_name', $cp->post_author); ?></span>
                                <span class="meta-dot"></span>
                                <time><?php echo human_time_diff(strtotime($cp->post_date), current_time('timestamp')); ?> ago</time>
                            </div>
                        </div>
                    </a>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endforeach; ?>

    <!-- ═══════════════════════════════════════════
         SECTION 4: NEWSLETTER (Glassmorphism)
         ═══════════════════════════════════════════ -->
    <section class="modern-newsletter">
        <div class="container">
            <div class="modern-newsletter__box">
                <div class="modern-newsletter__glow"></div>
                <div class="modern-newsletter__content">
                    <span class="modern-badge modern-badge--gradient">📬 Newsletter</span>
                    <h2 class="modern-newsletter__title">
                        Stories that matter,<br>delivered daily.
                    </h2>
                    <p class="modern-newsletter__text">
                        Join 50,000+ readers. No spam. Unsubscribe anytime.
                    </p>
                    <form class="modern-newsletter__form">
                        <input type="email" placeholder="Enter your email" class="modern-newsletter__input">
                        <button type="submit" class="modern-btn modern-btn--primary">
                            Subscribe Free →
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

</main>

<?php get_footer(); ?>