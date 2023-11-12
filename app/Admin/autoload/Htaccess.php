<?php

/**
 * Cập nhật lại nội dung cho file htacess nếu chưa đúng chuẩn
 * Thiếu redirect HTTPS
 * Thiếu redirect non-www
 **/

//
$root_htaccess = ABSPATH . '.htaccess';
//echo $root_htaccess . '<br>' . PHP_EOL;
if (!is_file($root_htaccess) || strpos(file_get_contents($root_htaccess), 'RewriteCond %{HTTPS} off') === false) {
    echo $root_htaccess . '<br>' . PHP_EOL;

    //
    $content_htaccess = file_get_contents('https://raw.githubusercontent.com/itvn9online/webgiareorg/main/htaccess.txt');
    if (strpos($content_htaccess, 'RewriteCond %{HTTPS} off') !== false) {
        file_put_contents($root_htaccess, $content_htaccess);
    }
    /*
} else {
    echo $root_htaccess . '<br>' . PHP_EOL;
    */
}
