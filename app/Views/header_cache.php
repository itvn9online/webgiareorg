<?php

// đã gọi đến header ở đây thì kiểm tra xem có file footer chưa, chưa có thì tạo luôn
if (!file_exists(WGR_CHILD_PATH . 'footer.php')) {
    copy(__DIR__ . '/footer-tmp.php', WGR_CHILD_PATH . 'footer.php') or die('ERROR! copy footer for child theme');
}

//
//echo __FILE__ . ':' . __LINE__ . '<br>' . PHP_EOL;

//
$why_ebcache_not_active = '';
$active_using_ebcache = '';

// không cache khi user đang đăng nhập
//echo USER_ID . '<br>' . PHP_EOL;
if (USER_ID > 0) {
    $why_ebcache_not_active = 'EchBay Cache (ebcache) is enable, but not caching requests by known users';
}
// chỉ cache với phương thức POST
else if ($_SERVER['REQUEST_METHOD'] != 'GET') {
    $why_ebcache_not_active = 'EchBay Cache (ebcache) running in GET method only';
}
// nếu đang dùng cache của đơn vị khác -> cũng hủy cache luôn
else if (defined('WP_CACHE') && WP_CACHE !== false) {
    $why_ebcache_not_active = 'EchBay Cache (ebcache) is enable, but an another plugin cache is enable too. If you want to using EchBay Cache, please set WP_CACHE = false or comment WP_CACHE in file wp-config.php';
}
// tắt ép cache -> dùng với các custom page mà cần kiểu submit -> tìm kiếm, đặt hàng
/*
else if (defined('WGR_NO_CACHE')) {
    $why_ebcache_not_active = 'EchBay Cache (ebcache) not running because WGR_NO_CACHE enable';
}
*/
// constant defined by w3 total cache
else if (defined('DONOTCACHEPAGE')) {
    $why_ebcache_not_active = 'EchBay Cache (ebcache) not running because DONOTCACHEPAGE enable';
}
//
else if (is_home() || is_front_page()) {
    // xác nhận có sử dụng ebcache
    $active_using_ebcache = 'is home';

    // copy file index để sử dụng cache
    if (!defined('WP_COPY_WGR_SUPPER_CACHE')) {
        echo 'copy index-tmp to index <br>' . PHP_EOL;
        copy(WGR_BASE_PATH . 'index-tmp.php', ABSPATH . 'index.php');
    }
}
//
else if (is_single()) {
    // xác nhận có sử dụng ebcache
    $active_using_ebcache = 'is single';
}
//
else if (is_archive()) {
    // xác nhận có sử dụng ebcache
    $active_using_ebcache = 'is archive';
}
// chưa hỗ trợ với page template
else if (is_page_template() || is_page()) {
    // các page template không sử dụng cache
    if (function_exists('is_cart') && is_cart()) {
        $why_ebcache_not_active = 'EchBay Cache not cache in: cart page';
    } else if (function_exists('is_checkout') && is_checkout()) {
        $why_ebcache_not_active = 'EchBay Cache not cache in: checkout page';
    } else if (function_exists('is_account_page') && is_account_page()) {
        $why_ebcache_not_active = 'EchBay Cache not cache in: account page';
    }
    // còn lại thì có dùng cache
    else {
        //$why_ebcache_not_active = 'EchBay Cache (ebcache) not running because (is page template) enable';

        // xác nhận có sử dụng ebcache
        //$active_using_ebcache = 'is page template';
        $active_using_ebcache = 'is page';
    }
}
//
/*
else if ( is_page() ) {
    // xác nhận có sử dụng ebcache
    $active_using_ebcache = 'is page';
}
*/
// chỉ cache với 1 số trang cụ thể thôi
else {
    $why_ebcache_not_active = 'EchBay Cache cache only home page, category page, post details page';
}
//echo $active_using_ebcache . '<br>' . PHP_EOL;

//
define('WHY_EBCACHE_DISABLE', $why_ebcache_not_active);
// gán giá trị để kích hoạt cache
define('ENABLE_EBCACHE', $active_using_ebcache);

// kích hoạt ob start để còn replace content ở footer
ob_start();
