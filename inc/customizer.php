<?php
function hikmah_news_customize($wp_customize) {
 $wp_customize->add_section('hikmah_news_colors',['title'=>__('News Theme Colors','hikmah-news')]);
 $wp_customize->add_setting('primary_color',['default'=>'#0f766e','sanitize_callback'=>'sanitize_hex_color']);
 $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize,'primary_color',['label'=>__('Primary Color','hikmah-news'),'section'=>'hikmah_news_colors']));
}
add_action('customize_register','hikmah_news_customize');
