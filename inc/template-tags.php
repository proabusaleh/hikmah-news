<?php
function hikmah_news_post_meta(){ echo '<span class="post-date">'.esc_html(get_the_date()).'</span>'; }
function hikmah_news_reading_time(){ $text=wp_strip_all_tags(get_the_content()); $words=str_word_count($text); return max(1,(int)ceil($words/200)); }
