<?php
/**
 * Front Page — Dynamic Homepage Builder
 * @package WPNews
 */
if (!defined('ABSPATH')) exit;

get_header(); ?>

<main class="site-main" id="main">
    <?php wpnews_render_homepage(); ?>
</main>

<?php get_footer(); ?>