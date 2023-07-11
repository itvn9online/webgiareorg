<?php
/*
* Tạo số version dựa theo phiên bản của flatsome -> update cũng dựa theo phiên bản này luôn
*/

//
header('Access-Control-Allow-Origin: *');

// hiển thị lịch sử thay đổi
if (isset($_GET['changes'])) {
    header('Content-Type: text/plain; charset=UTF-8');

    //
    //die(file_get_contents('https://raw.githubusercontent.com/itvn9online/webgiareorg/main/changes.txt'));
    die(file_get_contents('https://webgiare.org/wp-content/themes/flatsome/changes.txt'));
}

//
$flatsome_version = file_get_contents('https://flatsome.echbay.com/wp-content/themes/VERSION', 1);
//die($flatsome_version);

// kiểm tra xem có file để download chưa
$dir_download = dirname(dirname(__DIR__)) . '/download/flatsome-' . $flatsome_version . '.zip';
//die($dir_download);
if (!file_exists($dir_download)) {
    die('ERROR! File for download not exist!');
}

//
header('Content-Type: application/json');

// thông tin update
die(json_encode([
    // phiên bản flatsome
    "version" => $flatsome_version,
    // phiên bản tối thiểu của wordpress
    "requires" => "5.0.0",
    // phiên bản tối thiểu của php
    "requires_php" => "5.6.20",
    // log thay đổi
    //"details_url" => "https://flatsome.echbay.com/wp-content/webgiareorg/info.php?changes=log",
    //"details_url" => "https://raw.githubusercontent.com/itvn9online/webgiareorg/main/changes.txt",
    "details_url" => "https://webgiare.org/wp-content/themes/flatsome/changes.txt",
    // link download
    "download_url" => "https://flatsome.echbay.com/download/flatsome-" . $flatsome_version . ".zip"
]));
