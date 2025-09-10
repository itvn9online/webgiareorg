<?php

/**
 * Cập nhật lại nội dung cho file robots txt nếu chưa đúng chuẩn
 **/

//
$root_robots_txt = ABSPATH . 'robots.txt';
//echo $root_robots_txt . '<br>' . PHP_EOL;

// nếu chưa có file robotstxt -> khi nào cần reset lại file robotstxt thì xóa file robotstxt đi là được
if (!is_file($root_robots_txt)) {
    echo $root_robots_txt . '<br>' . PHP_EOL;

    //
    // $content_robotstxt = WGR_get_contents('https://raw.echbay.com/itvn9online/webgiareorg/main/tmp/robots.txt');
    if (is_file(WGR_CHILD_PATH . 'tmp/robots.txt')) {
        $content_robotstxt = file_get_contents(WGR_CHILD_PATH . 'tmp/robots.txt');
        if (!is_file(WGR_CHILD_PATH . 'tmp/.htaccess')) {
            WGR_htaccess_deny_all(WGR_CHILD_PATH . 'tmp/.htaccess');
        }
    } else {
        $content_robotstxt = file_get_contents(WGR_BASE_PATH . 'tmp/robots.txt');
    }

    // nội dung file mới phải đảm bảo được lấy thành công
    if (strpos($content_robotstxt, '{my_domain.com}') !== false) {
        $home_url = get_home_url();
        echo $home_url . '<br>' . PHP_EOL;

        // 
        $my_domain = explode('//', $home_url)[1];

        // 
        $content_robotstxt = str_replace('{my_domain.com}', $my_domain, $content_robotstxt);

        // 
        file_put_contents($root_robots_txt, $content_robotstxt, LOCK_EX);
    }
    /*
} else {
    echo $root_robots_txt . '<br>' . PHP_EOL;
    echo file_get_contents($root_robots_txt, 1) . '<br>' . PHP_EOL;
    */
}
