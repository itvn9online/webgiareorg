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
if (defined('EB_MY_CACHE_CONFIG')) {
    $my_config_php = EB_MY_CACHE_CONFIG;
} else {
    $my_config_php = $root_dir_cache . '/my-config.php';
}
// echo $my_config_php . '<br>' . PHP_EOL;
$my_config_content = '';
if (is_file($my_config_php)) {
    $my_config_content = 'Nội dung này chỉ coder mới có thể xem!';
    // $my_config_content = file_get_contents($my_config_php);
}


?>
<h1>Cấu hình website</h1>
<h2><?php echo str_replace(ABSPATH, '', $my_config_php); ?></h2>
<div class="text-center w99">
    <textarea rows="<?php echo count(explode("\n", $my_config_content)); ?>" class="large-text code" placeholder="My config content" readonly disabled><?php echo esc_html($my_config_content); ?></textarea>
</div>
<br>
<!-- Cleanup site cache -->
<?php

// xóa cache khi chạy qua phương thức POST (chỉ khi không phải save robots)
if (isset($_POST['cleanup_cache']) && wp_verify_nonce($_POST['_wpnonce_cleanup_cache'], 'cleanup_cache_action')) {
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

?>
<h2>Cleanup site cache</h2>
<form action="" method="post" onsubmit="return confirm('Cleanup all site cache?');">
    <?php wp_nonce_field('cleanup_cache_action', '_wpnonce_cleanup_cache'); ?>
    <button type="submit" name="cleanup_cache" class="button button-primary button-large">Dọn dẹp WGR Cache</button>
</form>
<br>
<!-- END Cleanup site cache -->
<!-- Edit robots.txt -->
<?php
// Logic lấy nội dung robots.txt
$robots_content = '';
$robots_base_content = '';
$robots_main_path = ABSPATH . 'robots.txt';
$robots_child_path = WGR_CHILD_PATH . 'tmp/robots.txt';
$robots_base_path = WGR_BASE_PATH . 'tmp/robots.txt';

// Ưu tiên lấy từ ABSPATH trước
if (is_file($robots_main_path)) {
    $robots_content = file_get_contents($robots_main_path);
} elseif (is_file($robots_child_path)) {
    $robots_content = file_get_contents($robots_child_path);
} else {
    $robots_base_content = trim(file_get_contents($robots_base_path));
    $robots_content = $robots_base_content;
}

// Xử lý form submit
if (isset($_POST['save_robots']) && wp_verify_nonce($_POST['_wpnonce_robots'], 'save_robots_action')) {
    $new_robots_content = stripslashes($_POST['robots_content']);
    if (empty($new_robots_content)) {
        if ($robots_base_content == '') {
            $robots_base_content = trim(file_get_contents($robots_base_path));
            $new_robots_content = $robots_base_content;
        } else {
            $new_robots_content = $robots_base_content;
        }
    }

    // Lưu vào ABSPATH/robots.txt
    if (file_put_contents($robots_main_path, $new_robots_content) !== false) {
        echo '<div class="notice notice-success"><p>Đã lưu robots.txt thành công!</p></div>';

        // 
        if ($robots_base_content == '') {
            $robots_base_content = trim(file_get_contents($robots_base_path));
        }

        // Kiểm tra nếu nội dung khác với base template thì lưu thêm vào child
        if ($new_robots_content !== $robots_base_content) {
            // Tạo thư mục tmp nếu chưa có
            $child_tmp_dir = dirname($robots_child_path);
            if (!is_dir($child_tmp_dir)) {
                wp_mkdir_p($child_tmp_dir);
            }
            file_put_contents($robots_child_path, $new_robots_content);
        } else if (is_file($robots_child_path)) {
            unlink($robots_child_path);
        }

        $robots_content = $new_robots_content;
    } else {
        echo '<div class="notice notice-error"><p>Lỗi: Không thể lưu robots.txt!</p></div>';
    }
}
?>
<h2>Edit <a href="<?php echo get_home_url(); ?>/robots.txt" target="_blank">robots.txt</a></h2>
<form action="" method="post">
    <?php wp_nonce_field('save_robots_action', '_wpnonce_robots'); ?>
    <div class="text-center w99">
        <textarea name="robots_content" rows="<?php echo count(explode("\n", $robots_content)) + 1; ?>" class="large-text code" placeholder="Nhập nội dung robots.txt..."><?php echo esc_textarea($robots_content); ?></textarea>
    </div>
    <br>
    <div>
        <button type="submit" name="save_robots" class="button button-primary button-large">Lưu robots.txt</button>
        <p><strong>Thứ tự ưu tiên đọc file:</strong></p>
        <ol>
            <li><?php echo str_replace(ABSPATH, '', $robots_main_path); ?></li>
            <li><?php echo str_replace(ABSPATH, '', $robots_child_path); ?></li>
            <li><?php echo str_replace(ABSPATH, '', $robots_base_path); ?></li>
        </ol>
    </div>
</form>
<!-- END Edit robots.txt -->