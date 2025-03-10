<?php

// TEST
//echo __FILE__ . ':' . __LINE__ . '<br>' . PHP_EOL;
//echo session_id();

// thời gian lưu cache
defined('EB_TIME_CACHE') || define('EB_TIME_CACHE', rand(300, 600));

// thư mục ebcache luôn cho vào uploads để đảm bảo lệnh tạo thư mục sẽ luôn được thực thi do permission
$sub_dir_cache = ['uploads', 'ebcache'];
$cache_prefix = '_';

/**
 * xác định thiết bị cache trong db memory
 * mặc định là 0 = desktop
 */
$memory_cache_device = 0;

//
if (isset($_GET['amp'])) {
    $cache_prefix .= '_amp';
} else {
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
            $memory_cache_device = 1;
        }
    } else if (wp_is_mobile()) {
        $cache_prefix .= '_m';
        $memory_cache_device = 1;
    }
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
        chmod($root_dir_cache, 0777) or die('ERROR chmod cache dir');
        // echo $root_dir_cache . '<br>' . PHP_EOL;
    }
}

//
//define('EB_DEVICE_CACHE', $memory_cache_device);

// thư mục lưu ebcache
define('EB_THEME_CACHE', $root_dir_cache . '/');
//echo EB_THEME_CACHE . '<br>' . PHP_EOL;
//die( __FILE__ . ':' . __LINE__ );

// file nạp config kết nối database
define('EB_MY_CACHE_CONFIG', dirname(EB_THEME_CACHE) . '/my-config-' . date('Ymd') . '.php');
// echo EB_MY_CACHE_CONFIG . '<br>' . PHP_EOL;

// nạp function tạo cache
include_once __DIR__ . '/app/Cache/Global.php';

/**
 * tạo prefix để tránh xung đột cho cache của các website khác nhau
 */
defined('EB_CACHE_PREFIX') || define('EB_CACHE_PREFIX', str_replace([
    'www.',
    '.'
], '', str_replace('-', '_', explode(':', $_SERVER['HTTP_HOST'])[0])));

// 
define('EB_PREFIX_CACHE', EB_CACHE_PREFIX . $cache_prefix);

// chỉ cache với phương thức GET
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // nếu tồn tại cookie wgr_ebsuppercache_timeout -> xem thời hạn của cookie còn không
    $last_update_logeg_cache = isset($_COOKIE['wgr_ebsuppercache_timeout']) ? $_COOKIE['wgr_ebsuppercache_timeout'] : 0;
    //echo date( 'Y-m-d H:i:s', $last_update_logeg_cache ) . '<br>' . PHP_EOL;
    //echo $last_update_logeg_cache . '<br>' . PHP_EOL;

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
