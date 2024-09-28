<?php

/**
 * Chuẩn bị cho cache thông qua bảng memory trong db
 */

// echo EB_MY_CACHE_CONFIG . '<br>' . PHP_EOL;

// nếu chưa có file này
if (defined('EB_MY_CACHE_CONFIG') && !is_file(EB_MY_CACHE_CONFIG)) {
    // echo 'copy my-config to my-config <br>' . PHP_EOL;
    // copy từ file temp
    copy(WGR_BASE_PATH . 'my-config.php', EB_MY_CACHE_CONFIG);

    // 
    $enable_redis = 'false';

    // Nếu có tham số này -> fixed cứng redis cache theo nó
    if (defined('WGR_REDIS_CACHE')) {
        if (WGR_REDIS_CACHE == true) {
            $enable_redis = 'true';
        }
    } else if (1 > 2) {
        // tạm bỏ chế độ cache qua redis -> lỗi trùng key trong server
        if (!empty(phpversion('redis'))) {
            // thử kết nối tới redis
            try {
                // connect thử vào redis
                $rd = new Redis();
                $rd->connect(WGR_REDIS_HOST, WGR_REDIS_PORT);

                // nếu không lỗi lầm gì thì set true
                $enable_redis = 'true';
            } catch (Exception $e) {
                // lỗi thì set false
                $enable_redis = 'false';
            }
        }
    }

    // lấy nội dung file config này
    $my_content_config = file_get_contents(EB_MY_CACHE_CONFIG);

    // Thay riêng cho tham số true|false
    $my_content_config = str_replace('enable_redis', $enable_redis, $my_content_config);

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
}
