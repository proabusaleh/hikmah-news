<?php
function hikmah_news_sidebars() {
 register_sidebar(['name'=>__('Main Sidebar','hikmah-news'),'id'=>'sidebar-1','before_widget'=>'<section class="widget">','after_widget'=>'</section>','before_title'=>'<h3>','after_title'=>'</h3>']);
 register_sidebar(['name'=>__('Footer','hikmah-news'),'id'=>'footer-1','before_widget'=>'<section class="widget">','after_widget'=>'</section>','before_title'=>'<h3>','after_title'=>'</h3>']);
}
add_action('widgets_init','hikmah_news_sidebars');
