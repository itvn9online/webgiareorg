<?php

// 
// print_r(WGR_BASE_PATH);

// 
// $eb_cache_php = ABSPATH . 'wp-content/webgiareorg/eb_cache.php';
// if (is_file($eb_cache_php)) {
if (defined('EB_THEME_CACHE')) {
    $root_dir_cache = dirname(EB_THEME_CACHE);
} else {
    $root_dir_cache = ABSPATH . 'wp-content/uploads/ebcache';

    // 
    // include $eb_cache_php;
}
// $root_dir_cache = dirname(EB_THEME_CACHE);
// print_r($root_dir_cache);
// }

// 
$my_config_php = $root_dir_cache . '/my-config.php';
// echo $my_config_php . '<br>' . PHP_EOL;
$my_config_content = '';
if (is_file($my_config_php)) {
    $my_config_content = 'Nội dung này chỉ coder mới có thể xem!';
    // $my_config_content = file_get_contents($my_config_php);
}


?>
<h1>Cấu hình website</h1>
<div class="text-center w99">
    <textarea rows="<?php echo count(explode("\n", $my_config_content)); ?>" class="large-text code" placeholder="My config content" readonly disabled><?php echo esc_html($my_config_content); ?></textarea>
</div>
<br>
<div>
    <form action="" method="post" onsubmit="return confirm('Cleanup all site cache?');">
        <?php wp_nonce_field(); ?>
        <button type="submit" class="button button-primary button-large">Dọn dẹp WGR Cache</button>
    </form>
</div>
<?php

// xóa cache khi chạy qua phương thức POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // xóa cache theo redis nếu có
    if (is_file($my_config_php)) {
        include $my_config_php;

        // 
        if (defined('EB_REDIS_CACHE') && EB_REDIS_CACHE == true && !empty(phpversion('redis'))) {
            // print_r(EB_REDIS_CACHE);

            // 
            try {
                $rd = new Redis();
                $rd->connect(WGR_REDIS_HOST, WGR_REDIS_PORT);

                // chỉ lấy cache theo prefix của từng tên miền
                if (defined('WGR_CACHE_PREFIX')) {
                    $cache_prefix = WGR_CACHE_PREFIX;
                } else {
                    $cache_prefix = strtolower(str_replace([
                        'www.',
                        '.'
                    ], '', str_replace('-', '_', explode(':', $_SERVER['HTTP_HOST'])[0])));
                }
                $cache_prefix .= '*';
                // print_r($cache_prefix);

                // 
                $keys = $rd->keys($cache_prefix);
                // print_r($keys);
                foreach ($keys as $key) {
                    echo $key . '<br>' . PHP_EOL;

                    // 
                    $rd->del($key);
                }
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        }
    }

    // xong mới xóa cache trong file
    WGR_deleteDirectory($root_dir_cache);
}
