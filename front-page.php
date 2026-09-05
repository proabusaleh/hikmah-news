<?php
/**
 * Front Page — Dynamic Homepage Builder
 * @package HikmahNews
 */
if (!defined('ABSPATH')) exit;

get_header(); ?>

<main class="site-main" id="main">
    <?php hikmahnews_render_homepage(); ?>
</main>

<?php get_footer(); ?>