<?php

//
function WGR_front_footer()
{
    // include WGR_BASE_PATH . 'app/Views/Admin/global_admin_html.php';

    //
    global $pagenow;
    // die($pagenow);


    /**
     * nạp css, js theo từng admin page
     */
    $arr_css_admin = [];
    $arr_js_admin = [];

    // edit post
    if (strpos($_SERVER['REQUEST_URI'], '/post.php') !== false || $pagenow == 'post.php') {
        // file css, js chung cho mọi loại post
        $arr_css_admin[] = WGR_BASE_PATH . 'public/admin/css/posts_edit.css';
        $arr_js_admin[] = WGR_BASE_PATH . 'public/admin/js/posts_edit.js';

        // file css, js cho từng post type riêng
        $arr_css_admin[] = WGR_BASE_PATH . 'public/admin/css/' . get_post_type() . '_edit.css';
        $arr_js_admin[] = WGR_BASE_PATH . 'public/admin/js/' . get_post_type() . '_edit.js';
    }

    // css
    WGR_adds_css($arr_css_admin, [
        'cdn' => CDN_BASE_URL,
    ]);

    // js
    WGR_adds_js($arr_js_admin, [
        'cdn' => CDN_BASE_URL,
    ]);


    /**
     * các file js dùng chung
     */
    WGR_adds_js([
        // code của mình nạp sau
        WGR_BASE_PATH . 'public/admin/js/footer.js',
    ], [
        'cdn' => CDN_BASE_URL,
    ]);
}
add_action('admin_footer', 'WGR_front_footer');
