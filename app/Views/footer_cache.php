<?php

// ở đây sẽ lấy nội dung để in ra và replace
$main_content = ob_get_contents();
ob_end_clean();

/**
 * Thay thế 1 số nội dung theo chuẩn chung
 */
$main_content = str_replace('="./"', '="' . DYNAMIC_BASE_URL . '"', $main_content);

//
echo $main_content;

//
//echo __FILE__ . ':' . __LINE__ . '<br>' . PHP_EOL;

// nếu không có ghi chú về việc tắt cache
if (WHY_EBCACHE_DISABLE == '') {
    if (ENABLE_EBCACHE != '') {
        //echo 'EB_THEME_CACHE: ' . EB_THEME_CACHE . '<br>' . PHP_EOL;
        if (defined('EB_THEME_CACHE')) {
            if (function_exists('WGR_get_cache_file')) {
                $filename = WGR_get_cache_file();
                //echo $filename . '<br>' . PHP_EOL;

                //
                if (defined('EB_FALSE_CACHE') || WGR_my_cache($filename) === false) {
                    //echo __FILE__ . ':' . __LINE__ . '<br>' . PHP_EOL;
                    WGR_my_cache($filename, WGR_remove_html_empty_line($main_content) . WGR_builder_eb_cache_note(ENABLE_EBCACHE), EB_TIME_CACHE);
                }
            } else {
                echo '<!-- WGR_get_cache_file not exists -->';
            }
        } else {
            echo '<!-- EB_THEME_CACHE not define -->';
        }
    } else {
        echo '<!-- ENABLE_EBCACHE not define -->';
    }
}
// có thì in ra
else {
    echo '<!-- ' . WHY_EBCACHE_DISABLE . ' -->';
}
