<?php
/**
 * Search Results Template
 * @package WPNews
 */
if (!defined('ABSPATH')) exit;

get_header();

$search_query = get_search_query();
$total_results = $wp_query->found_posts;
?>

<main class="site-main" id="main">

    <header class="archive-header">
        <div class="container">
            <span class="badge badge--secondary">Search Results</span>
            <h1 class="archive-header__title">
                Results for "<?php echo esc_html($search_query); ?>"
            </h1>
            <p class="archive-header__desc">
                <?php echo esc_html($total_results); ?> article(s) found
            </p>

            <!-- Search Form -->
            <form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>"
                  class="search-inline">
                <input type="search" name="s" value="<?php echo esc_attr($search_query); ?>"
                       class="search-inline__input" placeholder="Try another search...">
                <button type="submit" class="btn btn--primary">Search</button>
            </form>
        </div>
    </header>

    <section class="archive-grid-section">
        <div class="container">

            <?php if (have_posts()) : ?>
                <div class="search-results-list">
                    <?php while (have_posts()) : the_post(); ?>
                        <article class="search-result-item">
                            <div class="search-result-item__image">
                                <a href="<?php the_permalink(); ?>">
                                    <?php if (has_post_thumbnail()) : ?>
                                        <?php the_post_thumbnail('wpnews-list'); ?>
                                    <?php endif; ?>
                                </a>
                            </div>
                            <div class="search-result-item__content">
                                <?php
                                $cats = get_the_category();
                                if ($cats) :
                                ?>
                                    <span class="badge badge--outline search-result-item__cat">
                                        <?php echo esc_html($cats[0]->name); ?>
                                    </span>
                                <?php endif; ?>
                                <h3 class="search-result-item__title">
                                    <a href="<?php the_permalink(); ?>">
                                        <?php the_title(); ?>
                                    </a>
                                </h3>
                                <p class="search-result-item__excerpt">
                                    <?php echo wp_trim_words(get_the_excerpt(), 25, '...'); ?>
                                </p>
                                <div class="news-card__meta">
                                    <span class="author"><?php the_author(); ?></span>
                                    <span class="dot"></span>
                                    <time><?php echo get_the_date(); ?></time>
                                    <span class="dot"></span>
                                    <span><?php echo wpnews_reading_time(); ?> min read</span>
                                </div>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>

                <div class="archive-pagination">
                    <?php
                    the_posts_pagination([
                        'mid_size'  => 2,
                        'prev_text' => '← Previous',
                        'next_text' => 'Next →',
                    ]);
                    ?>
                </div>

            <?php else : ?>
                <div class="search-empty">
                    <div class="search-empty__icon">🔍</div>
                    <h2>No results found</h2>
                    <p>We couldn't find anything matching "<?php echo esc_html($search_query); ?>".</p>
                    <p>Try different keywords or browse our categories.</p>
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="btn btn--primary btn--lg">
                        Go to Homepage
                    </a>
                </div>
            <?php endif; ?>

        </div>
    </section>

</main>

<?php get_footer(); ?>