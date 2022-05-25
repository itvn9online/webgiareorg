<?php

//
function WGR_create_cache_file( $cache_dir = '' ) {
    if ( isset( $_SERVER[ 'REQUEST_URI' ] ) ) {
        $url = $_SERVER[ 'REQUEST_URI' ];
    } else {
        $url = $_SERVER[ 'SCRIPT_NAME' ];
        $url .= ( !empty( $_SERVER[ 'QUERY_STRING' ] ) ) ? '?' . $_SERVER[ 'QUERY_STRING' ] : '';
    }
    if ( $url == '/' || $url == '' ) {
        $url = '-';
    } else {
        $arr_cat_social_parameter = array(
            'fbclid=',
            'gclid=',
            'fb_comment_id=',
            'utm_'
        );
        foreach ( $arr_cat_social_parameter as $v ) {
            $url = explode( '?' . $v, $url );
            $url = explode( '&' . $v, $url[ 0 ] );
            $url = $url[ 0 ];
        }

        //
        if ( strlen( $url ) > 200 ) {
            $url = md5( $url );
        } else {
            $url = preg_replace( "/\/|\?|\&|\,|\=/", '-', $url );
        }
    }

    //
    if ( $cache_dir != '' ) {
        $cache_dir .= '/';
    }
    $url = EB_THEME_CACHE . $cache_dir . $url . '.txt';

    //
    return $url;
}

// thêm câu báo rằng đang lấy nội dung trong cache
function WGR_builder_eb_cache_note( $note = '' ) {
    return '<!-- Plugin by daidq - Theme by itvn9online
Cached page generated by WGR Cache (ebcache), an product of EB
Served from: ' . $_SERVER[ 'HTTP_HOST' ] . $_SERVER[ 'REQUEST_URI' ] . ' on ' . date( 'Y-m-d H:i:s' ) . '
Served to: ebcache all URI (' . $note . ')
Caching using hard disk drive. Recommendations using SSD for your website.
Compression = gzip -->';
}