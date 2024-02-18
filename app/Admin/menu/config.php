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
        <button type="submit" class="button button-primary button-large">Dọn dẹp WGR Cache</button>
    </form>
</div>
<?php

// xóa cache khi chạy qua phương thức POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    WGR_deleteDirectory($root_dir_cache);
}
