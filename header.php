<?php
/**
 * Theme Header
 * @package WPNews
 */
if (!defined('ABSPATH')) exit;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site">

    <!-- ========== TOP BAR ========== -->
    <?php get_template_part('template-parts/header/top-bar'); ?>

    <!-- ========== SITE BRANDING ========== -->
    <?php get_template_part('template-parts/header/site-branding'); ?>

    <!-- ========== MAIN NAVIGATION ========== -->
    <?php get_template_part('template-parts/header/main-nav'); ?>