<?php

// Thay doi duong dan logo login page
function WGR_wpc_url_login()
{
    //global $arr_private_info_setting;
    // duong dan vao website cua ban
    return 'https://' . PARTNER_WEBSITE . '/?utm_source=ebe_wp_theme&utm_campaign=wp_login&utm_term=copyright';
}
add_filter('login_headerurl', 'WGR_wpc_url_login');


// Thay doi logo login page
function WGR_login_css()
{
    // duong dan den file css
    WGR_adds_css([
        WGR_BASE_PATH . 'public/css/login.css',
        // add thêm css riêng của child theme nếu muốn tùy chỉnh logo
        WGR_CHILD_PATH . 'css/login.css',
    ], [
            'cdn' => CDN_BASE_URL,
        ]);

    // duong dan den file js
    WGR_adds_js([
        WGR_BASE_PATH . 'public/javascript/login.js',
    ], [
            'cdn' => CDN_BASE_URL,
        ], [
            'defer'
        ]);
}
add_filter('login_head', 'WGR_login_css');