<?php
/**
 * Previous / Next Post Navigation
 * @package HikmahNews
 */
if (!defined('ABSPATH')) exit;

$prev_post = get_previous_post();
$next_post = get_next_post();

if (!$prev_post && !$next_post) return;
?>

<div class="post-nav">
    <div class="container container--narrow">
        <div class="post-nav__inner">

            <!-- Previous -->
            <?php if ($prev_post) : ?>
                <a href="<?php echo esc_url(get_permalink($prev_post)); ?>" class="post-nav__link post-nav__link--prev">
                    <span class="post-nav__direction">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2">
                            <polyline points="15 18 9 12 15 6"/>
                        </svg>
                        Previous Article
                    </span>
                    <span class="post-nav__title">
                        <?php echo esc_html($prev_post->post_title); ?>
                    </span>
                </a>
            <?php else : ?>
                <div class="post-nav__link post-nav__link--empty"></div>
            <?php endif; ?>

            <div class="post-nav__divider"></div>

            <!-- Next -->
            <?php if ($next_post) : ?>
                <a href="<?php echo esc_url(get_permalink($next_post)); ?>" class="post-nav__link post-nav__link--next">
                    <span class="post-nav__direction">
                        Next Article
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2">
                            <polyline points="9 18 15 12 9 6"/>
                        </svg>
                    </span>
                    <span class="post-nav__title">
                        <?php echo esc_html($next_post->post_title); ?>
                    </span>
                </a>
            <?php else : ?>
                <div class="post-nav__link post-nav__link--empty"></div>
            <?php endif; ?>

        </div>
    </div>
</div>