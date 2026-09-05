<?php
/**
 * Category Landing Page — Dynamic Layouts
 * @package WPNews
 */
if (!defined('ABSPATH')) exit;

get_header();

$category = get_queried_object();
$cat_color = wpnews_get_category_color($category->term_id);
$cat_icon = wpnews_get_category_icon($category->term_id);
$cat_layout = wpnews_get_category_layout($category->term_id);
$cat_image = get_term_meta($category->term_id, 'wpnews_image', true);
$children = get_categories(['parent' => $category->term_id, 'hide_empty' => true]);
?>

<main class="site-main category-page" id="main"
      style="--cat-color: <?php echo esc_attr($cat_color); ?>">

    <!-- ===== CATEGORY HERO HEADER ===== -->
    <header class="cat-hero" <?php if ($cat_image) : ?>
            style="background-image: url('<?php echo esc_url($cat_image); ?>');"
            <?php endif; ?>>
        <div class="cat-hero__overlay"></div>
        <div class="container cat-hero__inner">
            <span class="cat-hero__icon"><?php echo $cat_icon; ?></span>
            <span class="badge" style="background: <?php echo esc_attr($cat_color); ?>;">
                Category
            </span>
            <h1 class="cat-hero__title"><?php single_cat_title(); ?></h1>
            <?php if (category_description()) : ?>
                <p class="cat-hero__desc"><?php echo category_description(); ?></p>
            <?php endif; ?>
            <div class="cat-hero__stats">
                <span>📝 <?php echo esc_html($category->count); ?> Articles</span>
                <?php if ($children) : ?>
                    <span>📂 <?php echo count($children); ?> Subcategories</span>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- ===== SUBCATEGORY NAVIGATION ===== -->
    <?php if ($children) : ?>
        <nav class="cat-nav">
            <div class="container">
                <div class="cat-nav__inner">
                    <a href="<?php echo esc_url(get_category_link($category)); ?>"
                       class="cat-nav__tab cat-nav__tab--active">
                        All <?php echo esc_html($category->name); ?>
                    </a>
                    <?php foreach ($children as $child) :
                        $child_color = wpnews_get_category_color($child->term_id);
                        $child_icon = wpnews_get_category_icon($child->term_id);
                    ?>
                        <a href="<?php echo esc_url(get_category_link($child)); ?>"
                           class="cat-nav__tab">
                            <?php echo $child_icon; ?> <?php echo esc_html($child->name); ?>
                            <span class="cat-nav__count">(<?php echo $child->count; ?>)</span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </nav>
    <?php endif; ?>

    <!-- ===== FEATURED POST ===== -->
    <?php
    $featured = new WP_Query([
        'cat'            => $category->term_id,
        'posts_per_page' => 1,
        'no_found_rows'  => true,
    ]);

    if ($featured->have_posts()) : $featured->the_post();
    ?>
        <section class="cat-featured-section">
            <div class="container">
                <article class="cat-block-featured" style="min-height: 420px;">
                    <a href="<?php the_permalink(); ?>" class="cat-block-featured__link">
                        <div class="cat-block-featured__image">
                            <?php if (has_post_thumbnail()) the_post_thumbnail('wpnews-hero'); ?>
                            <div class="cat-block-featured__overlay"></div>
                        </div>
                        <div class="cat-block-featured__content">
                            <span class="badge" style="background: <?php echo esc_attr($cat_color); ?>;">
                                ⭐ Featured
                            </span>
                            <h2 class="cat-block-featured__title"><?php the_title(); ?></h2>
                            <p class="cat-block-featured__excerpt">
                                <?php echo wp_trim_words(get_the_excerpt(), 30, '...'); ?>
                            </p>
                            <div class="cat-block-featured__meta">
                                <span>By <?php the_author(); ?></span>
                                <span class="dot"></span>
                                <time><?php echo get_the_date(); ?></time>
                                <span class="dot"></span>
                                <span>👁 <?php echo wpnews_get_formatted_views(); ?></span>
                            </div>
                        </div>
                    </a>
                </article>
            </div>
        </section>
    <?php wp_reset_postdata(); endif; ?>

    <!-- ===== MAIN CONTENT GRID ===== -->
    <section class="cat-content-section">
        <div class="container">
            <div class="grid grid--sidebar">

                <!-- Main Column -->
                <div class="cat-main-col">
                    <div class="section-title" style="border-bottom-color: <?php echo esc_attr($cat_color); ?>;">
                        <h2 class="section-title__text">
                            <?php echo $cat_icon; ?> Latest in <?php single_cat_title(); ?>
                        </h2>
                        <div class="section-title__line"></div>
                    </div>

                    <div class="grid grid--2 load-more-container">
                        <?php
                        $paged = get_query_var('paged') ?: 1;
                        $cat_query = new WP_Query([
                            'cat'            => $category->term_id,
                            'posts_per_page' => 6,
                            'paged'          => $paged,
                            'offset'         => 1, // Skip featured
                        ]);

                        while ($cat_query->have_posts()) : $cat_query->the_post();
                        ?>
                            <article class="news-card">
                                <div class="news-card__image">
                                    <a href="<?php the_permalink(); ?>">
                                        <?php if (has_post_thumbnail()) the_post_thumbnail('wpnews-grid'); ?>
                                    </a>
                                    <span class="badge news-card__badge"
                                          style="background: <?php echo esc_attr($cat_color); ?>;">
                                        <?php
                                        $cats = get_the_category();
                                        echo $cats ? esc_html($cats[0]->name) : '';
                                        ?>
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
                                        <span>👁 <?php echo wpnews_get_formatted_views(); ?></span>
                                    </div>
                                </div>
                            </article>
                        <?php endwhile; wp_reset_postdata(); ?>
                    </div>

                    <!-- Load More -->
                    <?php
                    wpnews_load_more_button([
                        'max_pages' => $cat_query->max_num_pages,
                        'per_page'  => 6,
                        'category'  => $category->slug,
                        'container' => '.load-more-container',
                    ]);
                    ?>
                </div>

                <!-- Sidebar Column -->
                <div class="cat-sidebar-col">
                    <!-- Popular in Category -->
                    <div class="sidebar-widget">
                        <h3 class="sidebar-widget__title"
                            style="border-bottom-color: <?php echo esc_attr($cat_color); ?>;">
                            🔥 Most Popular
                        </h3>
                        <div class="numbered-list">
                            <?php
                            $popular = new WP_Query([
                                'cat'            => $category->term_id,
                                'posts_per_page' => 5,
                                'orderby'        => 'meta_value_num',
                                'meta_key'       => '_wpnews_views',
                                'no_found_rows'  => true,
                            ]);
                            $num = 1;
                            while ($popular->have_posts()) : $popular->the_post();
                            ?>
                                <article class="numbered-item">
                                    <span class="numbered-item__num <?php echo $num <= 3 ? 'numbered-item__num--highlight' : ''; ?>"
                                          style="<?php echo $num <= 3 ? 'color:' . esc_attr($cat_color) : ''; ?>">
                                        <?php echo str_pad($num, 2, '0', STR_PAD_LEFT); ?>
                                    </span>
                                    <div class="numbered-item__content">
                                        <h4 class="numbered-item__title">
                                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                        </h4>
                                        <div class="numbered-item__meta">
                                            <span>👁 <?php echo wpnews_get_formatted_views(); ?></span>
                                        </div>
                                    </div>
                                </article>
                            <?php $num++; endwhile; wp_reset_postdata(); ?>
                        </div>
                    </div>

                    <!-- Category Tabs Widget -->
                    <?php
                    if (class_exists('WPNews_Category_Tabs_Widget')) {
                        the_widget('WPNews_Category_Tabs_Widget', [
                            'title'      => 'More Stories',
                            'categories' => $category->slug,
                        ]);
                    }
                    ?>

                    <!-- Newsletter -->
                    <div class="sidebar-widget" style="background: <?php echo esc_attr($cat_color); ?>10; border-color: <?php echo esc_attr($cat_color); ?>30;">
                        <h3 class="sidebar-widget__title" style="border-bottom-color: <?php echo esc_attr($cat_color); ?>;">
                            📬 <?php echo esc_html($category->name); ?> Newsletter
                        </h3>
                        <p style="font-size:var(--text-sm);color:var(--text-secondary);margin-bottom:var(--space-3);">
                            Get the best <?php echo esc_html($category->name); ?> stories weekly.
                        </p>
                        <input type="email" placeholder="Your email"
                               style="width:100%;padding:var(--space-3);border:1px solid var(--border-color);
                                      border-radius:var(--radius-md);margin-bottom:var(--space-3);">
                        <button class="btn btn--sm" style="width:100%;background:<?php echo esc_attr($cat_color); ?>;color:white;">
                            Subscribe
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </section>

</main>

<?php get_footer(); ?>