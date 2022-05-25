<?php

/*
 * nhúng css, js cho header
 */
function WGR_front_header() {
    // nạp các file css dùng chung cho toàn website
    WGR_adds_css( [
        // nạp thư viện bên thứ 3 trước
        //WGR_BASE_PATH . 'public/thirdparty/bootstrap-5.1.3/css/bootstrap.min.css',
        // code của mình nạp sau
        WGR_BASE_PATH . 'public/admin/css/admin.css',
        WGR_BASE_PATH . 'public/css/d.css',
        //WGR_CHILD_PATH . 'css/d.css',
    ], [
        'cdn' => DYNAMIC_BASE_URL,
    ] );

    // các file js bắt buộc phải nạp trước
    WGR_adds_js( [
        // nạp thư viện bên thứ 3 trước
        WGR_BASE_PATH . 'public/thirdparty/bootstrap-5.1.3/js/bootstrap.bundle.min.js',
        //WGR_BASE_PATH . 'public/thirdparty/bootstrap-5.1.3/js/bootstrap.min.js',
        WGR_BASE_PATH . 'public/thirdparty/vuejs-2.6.10/vue' . ( WP_DEBUG === true ? '.min' : '' ) . '.js',
        // code của mình nạp sau
        WGR_BASE_PATH . 'public/javascript/functions.js',
    ], [
        'cdn' => DYNAMIC_BASE_URL,
    ] );
}
add_action( 'admin_head', 'WGR_front_header' );