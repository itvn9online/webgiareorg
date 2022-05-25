<?php

/*
 * code viết chủ yếu cho website sử dụng woocomerce
 */

//
define( 'WGR_BASE_PATH', __DIR__ . '/' );

// nạp config
foreach ( glob( WGR_BASE_PATH . 'app/Config/*.php' ) as $filename ) {
    //echo $filename . '<br>' . "\n";
    include $filename;
}

// nạp model
foreach ( glob( WGR_BASE_PATH . 'app/Models/*.php' ) as $filename ) {
    //echo $filename . '<br>' . "\n";
    include $filename;
}

// nạp ux kết hợp với flatsome
foreach ( glob( WGR_BASE_PATH . 'ux-builder-setup/*.php' ) as $filename ) {
    //echo $filename . '<br>' . "\n";
    include $filename;
}

// nạp thêm code cho admin
if ( is_admin() ) {
    include WGR_BASE_PATH . 'app/Admin/Autoload.php';
}
// các chức năng chỉ chạy ngoài trang khách
else {
    //
}