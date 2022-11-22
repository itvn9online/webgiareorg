<?php

/*
 * code viết chủ yếu cho website sử dụng woocomerce
 */

//
define('WGR_BASE_PATH', __DIR__ . '/');
define('WGR_BASE_URI', str_replace(ABSPATH, '', __DIR__) . '/');

// nạp function tạo cache
//echo WGR_BASE_PATH . 'app/Cache/Global.php' . '<br>' . "\n";
include_once WGR_BASE_PATH . 'app/Cache/Global.php';

//
include WGR_BASE_PATH . 'app/Helpers/Viewport.php';

// nạp config
foreach (glob(WGR_BASE_PATH . 'app/Config/*.php') as $filename) {
    //echo $filename . '<br>' . "\n";
    include $filename;
}

// nạp model
foreach (glob(WGR_BASE_PATH . 'app/Models/*.php') as $filename) {
    //echo $filename . '<br>' . "\n";
    include $filename;
}

// nạp Shortcode
foreach (glob(WGR_BASE_PATH . 'app/Shortcode/*.php') as $filename) {
    //echo $filename . '<br>' . "\n";
    include $filename;
}

// nạp ux kết hợp với flatsome
foreach (glob(WGR_BASE_PATH . 'ux-builder-setup/*.php') as $filename) {
    //echo $filename . '<br>' . "\n";
    include $filename;
}

// nạp thêm code cho admin
if (is_admin()) {
    include WGR_BASE_PATH . 'app/Admin/Autoload.php';
    include WGR_BASE_PATH . 'app/Admin/Menu.php';
}
// các chức năng chỉ chạy ngoài trang khách
else {
    // thay đổi header trang login
    if (USER_ID === 0) {
        include WGR_BASE_PATH . 'app/Helpers/LoginMod.php';
    }

    //
    include WGR_BASE_PATH . 'app/Guest/woo-for-fb.php';
}