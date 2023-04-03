<?php
/*
* Kiểm tra và cập nhật lại nội dung cho file update của flatsome -> sử dụng chức năng update do webgiare cung cấp
*/

//
if ($_SERVER['HTTP_HOST'] != 'webgiare.org' && $_SERVER['HTTP_HOST'] != 'www.webgiare.org') {
    $flatsome_function_update = dirname(WGR_CHILD_PATH) . '/flatsome/inc/functions/function-update.php';
    //echo $flatsome_function_update . PHP_EOL;
    // nếu còn tồn tại chuỗi _site_transient_update_themes -> vẫn còn đang dùng code của flatsome
    if (
        file_exists($flatsome_function_update)
        && strpos(file_get_contents($flatsome_function_update), 'webgiare_v3_update_themes') === false
    ) {
        //die(__FILE__ . ':' . __LINE__);
        echo $flatsome_function_update . PHP_EOL;

        // copy file mẫu ghi đè vào file của flatsome
        copy(WGR_BASE_PATH . 'function-update.php', $flatsome_function_update);
    }
}
