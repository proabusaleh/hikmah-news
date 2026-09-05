<?php
function hikmah_news_load_more(){ check_ajax_referer('hikmah_news_nonce','nonce'); wp_send_json_success(); }
