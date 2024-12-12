<?php

// thuộc tính này để xác định code áp dụng cho plugin wocomerce -> sẽ có 1 số tính năng bổ sung cho nó
define('WGR_FOR_WOOCOMERCE', class_exists('WooCommerce') ? true : false);
//echo WGR_FOR_WOOCOMERCE . '<br>' . PHP_EOL;

// thời gian lưu cache
defined('EB_TIME_CACHE') || define('EB_TIME_CACHE', rand(300, 600));

//
if (is_user_logged_in()) {
    define('USER_ID', get_current_user_id());

    // duy trì cookie báo rằng đang đăng nhập -> duy trì 1 ngày để đảm bảo không bị chênh lệch múi giờ
    setcookie('wgr_ebsuppercache_timeout', time() + EB_TIME_CACHE, time() + 86400, '/');
} else {
    define('USER_ID', 0);
}
//echo USER_ID . '<br>' . PHP_EOL;

/**
 * URL động cho website để có thể chạy trên nhiều tên miền khác nhau mà không cần config lại
 */
// tinh chỉnh protocol theo ý thích -> mặc định là https
defined('BASE_PROTOCOL') || define('BASE_PROTOCOL', 'https');
// -> url động cho website
//define('DYNAMIC_BASE_URL', BASE_PROTOCOL . '://' . $_SERVER['HTTP_HOST'] . '/');
define('DYNAMIC_BASE_URL', get_home_url() . '/');
// print_r(DYNAMIC_BASE_URL);

// khi cần chuyển các file tĩnh sang url khác để giảm tải cho server chính thì dùng chức năng này
defined('CDN_BASE_URL') || define('CDN_BASE_URL', DYNAMIC_BASE_URL);
// print_r(CDN_BASE_URL);

// khi cần chuyển các file ảnh trong thư mục wp-content/uploads/ sang url khác để giảm tải cho server chính thì dùng chức năng này
defined('EB_CDN_UPLOADS_URL') || define('EB_CDN_UPLOADS_URL', '');
// print_r(EB_CDN_UPLOADS_URL);
if (EB_CDN_UPLOADS_URL != '' && strpos(EB_CDN_UPLOADS_URL, '/' . $_SERVER['HTTP_HOST'] . '/') !== false) {
    header('HTTP/1.1 403 Forbidden');
    die('DNS Prefetch');
}
