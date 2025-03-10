<?php

/**
 * Chuẩn bị cho cache thông qua bảng memory trong db
 */

// echo EB_MY_CACHE_CONFIG . '<br>' . PHP_EOL;

// nếu chưa có file này
if (defined('EB_MY_CACHE_CONFIG') && !is_file(EB_MY_CACHE_CONFIG)) {
    $enable_redis = 'false';
    // Nếu có tham số này -> fixed cứng redis cache theo nó
    if (defined('WGR_REDIS_CACHE') && WGR_REDIS_CACHE == true) {
        $enable_redis = 'true';
    }

    // lấy nội dung file config này
    $my_content_config = file_get_contents(WGR_BASE_PATH . 'my-config.php');

    // Thay riêng cho tham số true|false
    $my_content_config = str_replace('enable_redis', $enable_redis, $my_content_config);
    // gán prefix cho cache luôn
    defined('WGR_CACHE_PREFIX') || define('WGR_CACHE_PREFIX', str_replace([
        'www.',
        '.'
    ], '', str_replace('-', '_', explode(':', $_SERVER['HTTP_HOST'])[0])));
    $my_content_config = str_replace('str_cache_prefix', WGR_CACHE_PREFIX, $my_content_config);

    // thay thế nội dung từ file wp-config
    foreach (
        [
            'date' => date('r'),
            'name' => DB_NAME,
            'user' => DB_USER,
            'pass' => DB_PASSWORD,
            'host' => DB_HOST,
            'redis_host' => WGR_REDIS_HOST,
            'redis_port' => WGR_REDIS_PORT,
        ] as $k => $v
    ) {
        $my_content_config = str_replace('%' . $k . '%', $v, $my_content_config);
    }
    // lưu mới
    file_put_contents(EB_MY_CACHE_CONFIG, $my_content_config, LOCK_EX);

    // tìm và xóa các file cache cũ hơn
    $dir_config = dirname(EB_MY_CACHE_CONFIG);
    for ($i = 2; $i < 10; $i++) {
        $file_config = $dir_config . '/my-config-' . date('Ymd', time() - 86400 * $i) . '.php';
        // echo $file_config . '<br>' . PHP_EOL;
        if (is_file($file_config)) {
            unlink($file_config);
        }
    }
}
