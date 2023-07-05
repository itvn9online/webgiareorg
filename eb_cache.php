<?php

// TEST
//echo __FILE__ . ':' . __LINE__ . '<br>' . "\n";
//echo session_id();

// thời gian lưu cache
defined('EB_TIME_CACHE') || define('EB_TIME_CACHE', 300);

//
$sub_dir_cache = ['ebcache'];
$sub_dir_cache[] = explode(':', str_replace('www.', '', $_SERVER['HTTP_HOST']))[0];

//
if (!function_exists('wp_is_mobile')) {
    // fake function wp_is_mobile of wordpress
    function WGR_is_mobile()
    {
        if (empty($_SERVER['HTTP_USER_AGENT'])) {
            $is_mobile = false;
        }
        // Many mobile devices (all iPhone, iPad, etc.)
        else if (
            strpos($_SERVER['HTTP_USER_AGENT'], 'Mobile') !== false ||
            strpos($_SERVER['HTTP_USER_AGENT'], 'Android') !== false ||
            strpos($_SERVER['HTTP_USER_AGENT'], 'Silk/') !== false ||
            strpos($_SERVER['HTTP_USER_AGENT'], 'Kindle') !== false ||
            strpos($_SERVER['HTTP_USER_AGENT'], 'BlackBerry') !== false ||
            strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mini') !== false ||
            strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mobi') !== false
        ) {
            $is_mobile = true;
        } else {
            $is_mobile = false;
        }

        //
        return $is_mobile;
    }

    //
    if (WGR_is_mobile()) {
        $sub_dir_cache[] = 'm';
    }
} else if (wp_is_mobile()) {
    $sub_dir_cache[] = 'm';
}
//print_r($sub_dir_cache);

// tự động tạo thư mục cache nếu chưa có
$root_dir_cache = dirname(__DIR__);
foreach ($sub_dir_cache as $v) {
    $root_dir_cache .= '/' . $v;

    //
    if (!is_dir($root_dir_cache)) {
        mkdir($root_dir_cache, 0777);
        echo $root_dir_cache . '<br>' . "\n";
    }
}

// thư mục lưu ebcache
define('EB_THEME_CACHE', $root_dir_cache . '/');
//echo EB_THEME_CACHE . '<br>' . "\n";
//die( __FILE__ . ':' . __LINE__ );

// nạp function tạo cache
include_once __DIR__ . '/app/Cache/Global.php';

// chỉ cache với phương thức GET
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // nếu tồn tại cookie wgr_ebsuppercache_timeout -> xem thời hạn của côkie còn không
    $last_update_logeg_cache = isset($_COOKIE['wgr_ebsuppercache_timeout']) ? $_COOKIE['wgr_ebsuppercache_timeout'] : 0;
    //echo date( 'Y-m-d H:i:s', $last_update_logeg_cache ) . '<br>' . "\n";
    //echo $last_update_logeg_cache . '<br>' . "\n";

    // nếu còn hạn thì bỏ qua
    if ($last_update_logeg_cache > time()) {
        // đăng nhập rồi thì bỏ qua -> không nạp cache
        //echo 'wgr_ebsuppercache_timeout';
    }
    // nếu cache còn hiệu lức -> in ra luôn và thoát
    else {
        WGR_display(WGR_get_cache_file());

        /*
        $cache_content = WGR_my_cache(WGR_get_cache_file());
        if ($cache_content !== false) {
        echo $cache_content;
        exit();
        }
        */
        define('EB_FALSE_CACHE', 1);
    }
}
