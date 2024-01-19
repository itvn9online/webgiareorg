<?php

/**
 * Nạp config phục vụ cho cache nếu có
 */

//
if (defined('EB_MY_CACHE_CONFIG') && is_file(EB_MY_CACHE_CONFIG)) {
    // echo date('r', filemtime(EB_MY_CACHE_CONFIG)) . '<br>' . PHP_EOL;

    // quá 1 ngày sẽ xóa file này 1 lần
    if (time() - filemtime(EB_MY_CACHE_CONFIG) > 8 * 3600) {
        // echo 'Remove file ' . basename(EB_MY_CACHE_CONFIG) . ' because REDIS_MY_HOST not found!' . '<br>' . PHP_EOL;
        unlink(EB_MY_CACHE_CONFIG);

        //
        defined('EB_REDIS_CACHE') || define('EB_REDIS_CACHE', false);
    } else {
        // còn lại sẽ include vào để dùng
        include EB_MY_CACHE_CONFIG;
    }

    // nếu không kết nối được tới db
    function WGR_rm_my_cache_config()
    {
        // xóa file config động
        unlink(EB_MY_CACHE_CONFIG);
        // in ra lỗi linh tinh
        die(basename(__FILE__) . ':' . __LINE__);
    }

    //
    //echo DB_MY_NAME . '<br>' . PHP_EOL;
    //echo DB_MY_USER . '<br>' . PHP_EOL;
    //echo DB_MY_HOST . '<br>' . PHP_EOL;

    // bảng memory không hỗ trợ kiểu text nên loại cache này đang đứt
    function WGR_connect_memory_cache()
    {
        // thực hiện 1 kết nối bằng mysqli vào db
        $connect_mcache = mysqli_connect(DB_MY_HOST, DB_MY_USER, DB_MY_PASSWORD, DB_MY_NAME) or WGR_rm_my_cache_config();
        mysqli_query($connect_mcache, "SET NAMES 'UTF8'");

        // kiểm tra có bảng memory không
        //$sql = mysqli_query($connect_mcache, "SHOW TABLES LIKE 'wp_posts'") or die(mysqli_error($connect_mcache));
        $sql = mysqli_query($connect_mcache, "SHOW TABLES LIKE 'wgr_memory_cache'") or die(mysqli_error($connect_mcache));
        // nếu chưa có
        if (mysqli_num_rows($sql) < 1) {
            //echo __FILE__ . ':' . __LINE__ . '<br>' . PHP_EOL;

            // tạo lệnh insert bảng để lưu cache
            $strsql = "CREATE TABLE IF NOT EXISTS `wgr_memory_cache` (
            `id` BIGINT(20) NOT NULL AUTO_INCREMENT ,
            `ip` VARCHAR(255) NOT NULL ,
            `key` VARCHAR(255) NOT NULL ,
            `data` TEXT NOT NULL ,
            `session_id` VARCHAR(255) NOT NULL ,
            `agent` VARCHAR(255) NOT NULL ,
            `device` TINYINT(2) NOT NULL DEFAULT '0' ,
            `created_at` BIGINT(20) NOT NULL ,
            `expired_at` BIGINT(20) NOT NULL ,
            PRIMARY KEY (`id`) ,
            INDEX (`key`) ,
            INDEX (`session_id`) ,
            INDEX (`device`)
            ) ENGINE = MEMORY CHARSET=utf8mb4 COLLATE utf8mb4_general_ci";
            echo 'CREATE TABLE IF NOT EXISTS `wgr_memory_cache` <br>' . PHP_EOL;
            mysqli_query($connect_mcache, $strsql) or die(mysqli_error($connect_mcache));
            /*
        } else {
            $row = mysqli_fetch_assoc($sql);
            print_r($row);
            */
        }

        //
        return $connect_mcache;
    }
    //$connect_mcache = WGR_connect_memory_cache();

    // thiết lập cache từ memory
    define('EB_MEMORY_CACHE', true);
} else {
    // ko cache từ memory
    define('EB_MEMORY_CACHE', false);
}
