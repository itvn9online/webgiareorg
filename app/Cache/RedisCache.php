<?php

//
// echo EB_MY_CACHE_CONFIG . '<br>' . PHP_EOL;

// if (!defined('EB_REDIS_CACHE')) {
if (!empty(phpversion('redis'))) {
    // xác định cache qua redis
    if (defined('REDIS_MY_HOST') && defined('REDIS_MY_PORT')) {
        try {
            // connect thử vào redis
            $rd = new Redis();
            $rd->connect(REDIS_MY_HOST, REDIS_MY_PORT);

            // nếu không lỗi lầm gì thì set true
            define('EB_REDIS_CACHE', true);
        } catch (Exception $e) {
            // lỗi thì set false
            define('EB_REDIS_CACHE', false);

            //
            if (defined('EB_MY_CACHE_CONFIG') && is_file(EB_MY_CACHE_CONFIG)) {
                file_put_contents(EB_MY_CACHE_CONFIG, str_replace(' ', '', '< ? php') . PHP_EOL . ' /* Redis disable because test connect ERROR */', LOCK_EX);
                touch(EB_MY_CACHE_CONFIG, time());
            }
        }
    } else {
        // xóa file my-config nếu có -> vì có mà không có 2 tham số kia thì coi như lỗi
        if (defined('EB_MY_CACHE_CONFIG') && is_file(EB_MY_CACHE_CONFIG)) {
            // echo date('r', filemtime(EB_MY_CACHE_CONFIG)) . '<br>' . PHP_EOL;

            // quá 1 ngày sẽ xóa file này 1 lần
            if (time() - filemtime(EB_MY_CACHE_CONFIG) > 24 * 3600) {
                // echo 'Remove file ' . basename(EB_MY_CACHE_CONFIG) . ' because REDIS_MY_HOST not found!' . '<br>' . PHP_EOL;
                unlink(EB_MY_CACHE_CONFIG);
            } else {
                // file_put_contents(EB_MY_CACHE_CONFIG, str_replace(' ', '', '< ? php') . PHP_EOL . ' /* Redis disable because REDIS_MY_HOST not found */', LOCK_EX);
                // touch(EB_MY_CACHE_CONFIG, time());
            }
        }
        define('EB_REDIS_CACHE', false);
    }
    // var_dump(EB_REDIS_CACHE);

    // Connecting to Redis server on localhost
    /*
        $rd = new Redis();
        $rd->connect(REDIS_MY_HOST, REDIS_MY_PORT);
        echo "Connection to server sucessfully" . '<br>' . PHP_EOL;
        // check whether server is running or not 
        echo "Server is running: " . $rd->ping() . '<br>' . PHP_EOL;
        echo "Redis: " . phpversion('redis') . '<br>' . PHP_EOL;
        */
} else {
    define('EB_REDIS_CACHE', false);
}
// }
