<?php

/**
 * Kiểm tra và cập nhật lại nội dung cho file update của flatsome -> sử dụng chức năng update do webgiare cung cấp
 */

function WGR_flatsome_function_update($f)
{
    // echo $f . PHP_EOL;
    // nếu còn tồn tại chuỗi _site_transient_update_themes -> vẫn còn đang dùng code của flatsome
    if (
        is_file($f)
        && strpos(file_get_contents($f), 'webgiare_v3_update_themes') === false
    ) {
        // die(__FILE__ . ':' . __LINE__);
        echo $f . PHP_EOL;

        // copy 1 bản backup
        copy($f, str_replace('/function-update.php', '/function-update-flatsome-' . date('Ymd-His') . '.php', $f));

        // copy file mẫu ghi đè vào file của flatsome
        copy(WGR_BASE_PATH . 'function-update.php', $f);
    }
}

//
if (
    !in_array(
        $_SERVER['HTTP_HOST'],
        [
            'webgiare.org',
            'www.webgiare.org',
            // 'world.webgiare.org',
        ]
    )
    // && is_admin()
    // && defined('EB_CHILD_THEME_URL')
) {
    WGR_flatsome_function_update(dirname(WGR_CHILD_PATH) . '/flatsome/inc/functions/function-update.php');
    /*
} else {
    echo get_template_directory();
    */
}
