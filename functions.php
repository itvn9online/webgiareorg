<?php

/*
 * code viết chủ yếu cho website sử dụng woocomerce
 */

//
define( 'WGR_BASE_PATH', __DIR__ . '/' );

// nạp config
include __DIR__ . '/Config/Constants.php';

// thuộc tính này để xác định code áp dụng cho plugin wocomerce -> sẽ có 1 số tính năng bổ sung cho nó
define( 'WGR_FOR_WOOCOMERCE', class_exists( 'WooCommerce' ) ? true : false );

// nạp model
foreach ( glob( WGR_BASE_PATH . 'Models/*.php' ) as $filename ) {
    //echo $filename . '<br>' . "\n";
    include $filename;
}

// nạp ux kết hợp với flatsome
foreach ( glob( WGR_BASE_PATH . 'ux-builder-setup/*.php' ) as $filename ) {
    //echo $filename . '<br>' . "\n";
    include $filename;
}