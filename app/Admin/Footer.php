<?php

//
function WGR_front_footer() {
    include WGR_BASE_PATH . 'app/Views/Admin/global_admin_html.php';

    //
    global $pagenow;
    //die( $pagenow );

    // nạp css, js theo từng admin page
    $arr_js_admin = [];

    // edit post
    if ( $pagenow == 'post.php' ) {
        // file js chung cho mọi loại post
        $arr_js_admin[] = WGR_BASE_PATH . 'public/admin/js/posts_edit.js';
        // file js cho từng post type riêng
        $arr_js_admin[] = WGR_BASE_PATH . 'public/admin/js/' . get_post_type() . '_edit.js';
    }

    // các file js bắt buộc phải nạp trước
    WGR_adds_js( $arr_js_admin, [
        'cdn' => '//' . $_SERVER[ 'HTTP_HOST' ] . '/',
    ] );

    // các file js bắt buộc phải nạp trước
    WGR_adds_js( [
        // code của mình nạp sau
        WGR_BASE_PATH . 'public/admin/js/footer.js',
    ], [
        'cdn' => '//' . $_SERVER[ 'HTTP_HOST' ] . '/',
    ] );
}
add_action( 'admin_footer', 'WGR_front_footer' );