<?php

/**
 * Cập nhật lại nội dung cho file htacess nếu chưa đúng chuẩn
 * Thiếu redirect HTTPS
 * Thiếu redirect non-www
 **/

//
$root_htaccess = ABSPATH . '.htaccess';
//echo $root_htaccess . '<br>' . PHP_EOL;

// nếu chưa có file htaccess -> khi nào cần reset lại file htaccess thì xóa file htaccess đi là được
if (!is_file($root_htaccess)) {
    echo $root_htaccess . '<br>' . PHP_EOL;

    //
    // $content_htaccess = file_get_contents('https://raw.githubusercontent.com/itvn9online/webgiareorg/main/htaccess.txt');
    $content_htaccess = file_get_contents(WGR_BASE_PATH . 'htaccess.txt');

    // nội dung file mới phải đảm bảo được lấy thành công
    if (strpos($content_htaccess, 'RewriteCond %{HTTPS} off') !== false) {
        $home_url = get_home_url();
        echo $home_url . '<br>' . PHP_EOL;

        // xem code này có nằm trong thư mục con ko
        $sub_dir = explode('//', $home_url);
        $sub2_dir = '';
        if (count($sub_dir) > 1) {
            $sub_dir = explode('/', $sub_dir[1]);
            if (count($sub_dir) > 1) {
                $sub_dir = $sub_dir[1];
                $sub2_dir = $sub_dir . '/';
            } else {
                $sub_dir = '';
            }
        } else {
            $sub_dir = '';
        }

        // thay thế nội dung mẫu
        foreach (
            [
                'sub_dir' => $sub_dir,
                'sub2_dir' => $sub2_dir,
            ] as $k => $v
        ) {
            $content_htaccess = str_replace('{tmp.' . $k . '}', $v, $content_htaccess);
        }
        // $content_htaccess = str_replace('# Header always set Permissions-Policy ', 'Header always set Permissions-Policy ', $content_htaccess);

        // 
        file_put_contents($root_htaccess, $content_htaccess, LOCK_EX);
    }
    /*
} else {
    echo $root_htaccess . '<br>' . PHP_EOL;
    */
}
