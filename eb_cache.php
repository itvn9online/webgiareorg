<?php

// TEST
//echo __FILE__ . ':' . __LINE__ . '<br>' . "\n";
//echo session_id();

// thời gian lưu cache
defined('EB_TIME_CACHE') || define('EB_TIME_CACHE', 300);

//
$sub_dir_cache = ['ebcache'];
$cache_prefix = str_replace('www.', '', str_replace('.', '', str_replace('-', '_', explode(':', $_SERVER['HTTP_HOST'])[0])));

//
if (!function_exists('wp_is_mobile')) {
    // fake function wp_is_mobile of wordpress
    function WGR_is_mobile()
    {
        $is_mobile = false;
        if (!empty($_SERVER['HTTP_USER_AGENT'])) {
            $a = $_SERVER['HTTP_USER_AGENT'];
            if (
                // Many mobile devices (all iPhone, iPad, etc.)
                strpos($a, 'Mobile') !== false ||
                strpos($a, 'Android') !== false ||
                strpos($a, 'Silk/') !== false ||
                strpos($a, 'Kindle') !== false ||
                strpos($a, 'BlackBerry') !== false ||
                strpos($a, 'Opera Mini') !== false ||
                strpos($a, 'Opera Mobi') !== false
            ) {
                $is_mobile = true;
            }
        }

        //
        return $is_mobile;
    }

    //
    if (WGR_is_mobile()) {
        $cache_prefix .= '_m';
    }
} else if (wp_is_mobile()) {
    $cache_prefix .= '_m';
}
$sub_dir_cache[] = $cache_prefix;
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
