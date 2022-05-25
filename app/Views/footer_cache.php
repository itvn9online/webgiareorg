<?php

// ở đây sẽ lấy nội dung để in ra và replace
$main_content = ob_get_contents();
ob_end_clean();

//
echo $main_content;

// nếu không có ghi chú về việc tắt cache
if ( WHY_EBCACHE_DISABLE == '' ) {
    if ( ENABLE_EBCACHE != '' ) {
        if ( function_exists( 'WGR_create_cache_file' ) ) {
            $filename = WGR_create_cache_file();
            //echo $filename . '<br>' . "\n";

            //
            if ( !file_exists( $filename ) || filemtime( $filename ) + EB_TIME_CACHE < time() ) {
                //echo __FILE__ . ':' . __LINE__ . '<br>' . "\n";
                touch( $filename );
                file_put_contents( $filename, WGR_remove_html_empty_line( $main_content ) . WGR_builder_eb_cache_note( ENABLE_EBCACHE ), LOCK_EX );
            }
        } else {
            echo '<!-- WGR_create_cache_file not exists -->';
        }
    } else {
        echo '<!-- ENABLE_EBCACHE not define -->';
    }
}
// có thì in ra
else {
    echo '<!-- ' . WHY_EBCACHE_DISABLE . ' -->';
}