<?php

//
if (!empty(phpversion('redis'))) {
    // xác định cache qua redis
    define('EB_REDIS_CACHE', true);

    // Connecting to Redis server on localhost
    /*
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
    echo "Connection to server sucessfully" . '<br>' . PHP_EOL;
    // check whether server is running or not 
    echo "Server is running: " . $redis->ping() . '<br>' . PHP_EOL;
    echo "Redis: " . phpversion('redis') . '<br>' . PHP_EOL;
    */
} else {
    define('EB_REDIS_CACHE', false);
}
