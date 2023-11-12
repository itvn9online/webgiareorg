<?php

//
if (!empty(phpversion('redis'))) {
    // xác định cache qua redis
    if (defined('REDIS_MY_HOST') && defined('REDIS_MY_PORT')) {
        define('EB_REDIS_CACHE', true);
    } else {
        // xóa file my-config nếu có -> vì có mà không có 2 tham số kia thì coi như lỗi
        if (defined('EB_MY_CACHE_CONFIG') && is_file(EB_MY_CACHE_CONFIG)) {
            echo 'Remove file ' . basename(EB_MY_CACHE_CONFIG) . ' because REDIS_MY_HOST not found!' . '<br>' . PHP_EOL;
            unlink(EB_MY_CACHE_CONFIG);
        }
        define('EB_REDIS_CACHE', false);
    }

    // Connecting to Redis server on localhost
    /*
    $redis = new Redis();
    $redis->connect(REDIS_MY_HOST, REDIS_MY_PORT);
    echo "Connection to server sucessfully" . '<br>' . PHP_EOL;
    // check whether server is running or not 
    echo "Server is running: " . $redis->ping() . '<br>' . PHP_EOL;
    echo "Redis: " . phpversion('redis') . '<br>' . PHP_EOL;
    */
} else {
    define('EB_REDIS_CACHE', false);
}
