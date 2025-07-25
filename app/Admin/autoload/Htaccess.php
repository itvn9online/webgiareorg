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
    // $WGR_get_contents = WGR_get_contents('https://raw.echbay.com/itvn9online/webgiareorg/main/tmp/htaccess.txt');
    if (is_file(WGR_CHILD_PATH . 'tmp/htaccess.txt')) {
        $content_htaccess = file_get_contents(WGR_CHILD_PATH . 'tmp/htaccess.txt');
        if (!is_file(WGR_CHILD_PATH . 'tmp/.htaccess')) {
            WGR_htaccess_deny_all(WGR_CHILD_PATH . 'tmp/.htaccess');
        }
    } else {
        $content_htaccess = file_get_contents(WGR_BASE_PATH . 'tmp/htaccess.txt');
    }

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
    echo file_get_contents($root_htaccess, 1) . '<br>' . PHP_EOL;
    */
}
