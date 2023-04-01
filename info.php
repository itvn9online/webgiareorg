<?php
/*
* Tạo số version dựa theo phiên bản của flatsome -> update cũng dựa theo phiên bản này luôn
*/

//
header('Access-Control-Allow-Origin: *');

//
$flatsome_version = '3.16.7';

// hiển thị lịch sử thay đổi
if (isset($_GET['changes'])) {
    $changes_txt = dirname(__DIR__) . '/themes/flatsome/changes.txt';
    //echo $changes_txt . PHP_EOL;
    if (file_exists($changes_txt)) {
        header('Content-Type: text/plain; charset=UTF-8');
        header_remove('X-Frame-Options');
        header('X-Frame-Options: ALLOWALL', true);

        //
        die(file_get_contents($changes_txt));
    }

    //
    //die(__FILE__ . ':' . __LINE__);
}

// thông tin update
$update = array(
    // phiên bản flatsome
    "version" => $flatsome_version,
    // phiên bản tối thiểu của wordpress
    "requires" => "5.0.0",
    // phiên bản tối thiểu của php
    "requires_php" => "5.6.20",
    // log thay đổi
    "details_url" => "https://flatsome.echbay.com/wp-content/webgiareorg/info.php?changes=log",
    // link download
    "download_url" => "https://flatsome.echbay.com/download/flatsome-" . $flatsome_version . ".zip"
);

//
header('Content-Type: application/json');
die(json_encode($update));
