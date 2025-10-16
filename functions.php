<?php

/**
 * code viết chủ yếu cho website sử dụng woocomerce
 */

//
define('WGR_BASE_PATH', __DIR__ . '/');
define('WGR_BASE_URI', str_replace(ABSPATH, '', __DIR__) . '/');

// nạp custom config nếu có
if (is_file(WGR_CHILD_PATH . 'custom_config.php')) {
    include WGR_CHILD_PATH . 'custom_config.php';
}
// khai báo các hằng số để phòng custom config không có
defined('WGR_ADVANCED_CACHE') || define('WGR_ADVANCED_CACHE', '0');
defined('WGR_TERM_DESCRIPTION_ORDER') || define('WGR_TERM_DESCRIPTION_ORDER', '0');
defined('WGR_CONTACT_PRICE') || define('WGR_CONTACT_PRICE', '');
defined('WGR_ADD_FONT_AWESOME') || define('WGR_ADD_FONT_AWESOME', '0');

// khai báo qua tham số này để khi cần có thể chuyển url cho partner
defined('WGR_PARTNER_NAME') || define('WGR_PARTNER_NAME', 'WebGiaRe');
defined('WGR_PARTNER_WEBSITE') || define('WGR_PARTNER_WEBSITE', 'webgiare.org');

// bật chế độ auto update nếu chưa có
defined('WP_AUTO_UPDATE_CORE') || define('WP_AUTO_UPDATE_CORE', true);

//
defined('WGR_REDIS_HOST') || define('WGR_REDIS_HOST', '127.0.0.1');
defined('WGR_REDIS_PORT') || define('WGR_REDIS_PORT', 6379);

// Chức năng thêm mới plugin và chỉnh sửa code, lúc nào cần dùng thì comment DISALLOW_FILE_MODS -> bất tiện tí nhưng tăng bảo mật
// defined('DISALLOW_FILE_MODS') || define('DISALLOW_FILE_MODS', true);

// nạp function tạo cache
// echo WGR_BASE_PATH . 'app/Cache/Global.php' . '<br>' . "\n";
include_once WGR_BASE_PATH . 'app/Cache/MyConfig.php';
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
    // echo $filename . '<br>' . "\n";
    include $filename;
}

// nạp Shortcode
foreach (glob(WGR_BASE_PATH . 'app/Shortcode/*.php') as $filename) {
    // echo $filename . '<br>' . "\n";
    include $filename;
}

// nạp ux kết hợp với flatsome
foreach (glob(WGR_BASE_PATH . 'ux-builder-setup/*.php') as $filename) {
    // echo $filename . '<br>' . "\n";
    include $filename;
}

// nạp thêm code cho admin
if (is_admin()) {
    include WGR_BASE_PATH . 'app/Admin/Autoload.php';
    include WGR_BASE_PATH . 'app/Admin/Menu.php';

    //
    WGR_cleanup_vscode(__DIR__ . '/.vscode');
    WGR_cleanup_vscode(WGR_CHILD_PATH . '.vscode');
}
// các chức năng chỉ chạy ngoài trang khách
else {
    // thay đổi header trang login
    if (USER_ID === 0) {
        include WGR_BASE_PATH . 'app/Helpers/LoginMod.php';
    }

    //
    // echo basename(__FILE__ . ':') . __LINE__ . '<br>' . "\n";
    include WGR_BASE_PATH . 'app/Guest/woo-for-fb.php';
    if (WGR_CONTACT_PRICE != '') {
        include WGR_BASE_PATH . 'app/Guest/contact-price.php';
    }
    include WGR_BASE_PATH . 'app/ThirdParty/rank_math_the_breadcrumbs.php';
}
