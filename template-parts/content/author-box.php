<?php
/**
 * Author Box
 * @package HikmahNews
 */
if (!defined('ABSPATH')) exit;

$author_id = get_the_author_meta('ID');
$author_bio = get_the_author_meta('description');
$author_posts_url = get_author_posts_url($author_id);
$author_website = get_the_author_meta('user_url');
?>

<div class="author-box">
    <div class="container container--narrow">
        <div class="author-box__inner">

            <div class="author-box__avatar">
                <?php echo get_avatar($author_id, 100); ?>
            </div>

            <div class="author-box__content">
                <span class="author-box__label">Written by</span>
                <h3 class="author-box__name">
                    <a href="<?php echo esc_url($author_posts_url); ?>">
                        <?php the_author(); ?>
                    </a>
                </h3>
                <?php if ($author_bio) : ?>
                    <p class="author-box__bio">
                        <?php echo esc_html($author_bio); ?>
                    </p>
                <?php endif; ?>
                <div class="author-box__links">
                    <a href="<?php echo esc_url($author_posts_url); ?>" class="btn btn--outline btn--sm">
                        View All Posts
                    </a>
                    <?php if ($author_website) : ?>
                        <a href="<?php echo esc_url($author_website); ?>"
                           target="_blank" rel="noopener" class="btn btn--ghost btn--sm">
                            🌐 Website
                        </a>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>