<?php

/**
 * trả về phiên bản hiện tại của webgiareorg
 * dành cho các website kiểm tra phiên bản qua ajax
 * tránh lỗi Access-Control-Allow-Origin
 */

//
ob_end_clean();
header('Access-Control-Allow-Origin: *');
header('Content-Type: text/plain; charset=UTF-8');

// 
die(json_encode([
    // phiên bản webgiareorg
    "version" => file_get_contents(__DIR__ . '/VERSION'),
]));
