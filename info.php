<?php
/*
* Tạo số version dựa theo phiên bản của flatsome -> update cũng dựa theo phiên bản này luôn
*/

//
header('Access-Control-Allow-Origin: *');

//
$flatsome_version = '3.17.0';

// hiển thị lịch sử thay đổi
if (isset($_GET['changes'])) {
    header('Content-Type: text/plain; charset=UTF-8');

    //
    //die(file_get_contents('https://raw.githubusercontent.com/itvn9online/webgiareorg/main/changes.txt'));
    die(file_get_contents('https://webgiare.org/wp-content/themes/flatsome/changes.txt'));
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
    //"details_url" => "https://flatsome.echbay.com/wp-content/webgiareorg/info.php?changes=log",
    //"details_url" => "https://raw.githubusercontent.com/itvn9online/webgiareorg/main/changes.txt",
    "details_url" => "https://webgiare.org/wp-content/themes/flatsome/changes.txt",
    // link download
    "download_url" => "https://flatsome.echbay.com/download/flatsome-" . $flatsome_version . ".zip"
);

//
header('Content-Type: application/json');
die(json_encode($update));
