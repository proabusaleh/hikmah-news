<?php
function hikmah_news_article_schema(){ if(!is_single()) return; $data=['@context'=>'https://schema.org','@type'=>'NewsArticle','headline'=>wp_strip_all_tags(get_the_title()),'datePublished'=>get_the_date('c'),'dateModified'=>get_the_modified_date('c'),'mainEntityOfPage'=>get_permalink()]; echo '<script type="application/ld+json">'.wp_json_encode($data).'</script>'; }
add_action('wp_head','hikmah_news_article_schema');
