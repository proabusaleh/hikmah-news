<?php
/**
 * Sidebar Template
 * @package HikmahNews
 */
if (!defined('ABSPATH')) exit;

if (!is_active_sidebar('sidebar-main')) return;
?>

<aside class="sidebar" id="sidebar">
    <?php dynamic_sidebar('sidebar-main'); ?>
</aside>