<?php

/**
 * Chuẩn bị cho cache thông qua bảng memory trong db
 */
// echo EB_MY_CACHE_CONFIG . '<br>' . PHP_EOL;

// nếu chưa có file này
if (defined('EB_MY_CACHE_CONFIG') && !is_file(EB_MY_CACHE_CONFIG)) {
    echo 'copy my-config to my-config <br>' . PHP_EOL;
    // copy từ file temp
    copy(WGR_BASE_PATH . 'my-config.php', EB_MY_CACHE_CONFIG);

    // lấy nội dung file config này
    $my_content_config = file_get_contents(EB_MY_CACHE_CONFIG);

    // thay thế nội dung từ file wp-config
    foreach ([
        'date' => date('r'),
        'name' => DB_NAME,
        'user' => DB_USER,
        'pass' => DB_PASSWORD,
        'host' => DB_HOST,
        'redis_host' => WGR_REDIS_HOST,
        'redis_port' => WGR_REDIS_PORT,
    ] as $k => $v) {
        $my_content_config = str_replace('%' . $k . '%', $v, $my_content_config);
    }
    // lưu mới
    file_put_contents(EB_MY_CACHE_CONFIG, $my_content_config, LOCK_EX);
}
