<?php

/**
 * File này dùng để gọi tới file wp-cron.php mặc định của WordPress.
 * Dùng khi thiết lập DISABLE_WP_CRON = true để tắt wp-cron mặc định,
 * sau đó dùng cronjob của hosting để gọi file này thay thế,
 * hoặc dùng chính code webgiareorg để gọi tới file này và kiểm soát tối thiểu 1 phút 1 lần.
 * Trả về định dạng JavaScript để có thể nhúng vào footer dưới dạng <script src="...">
 */

// tạo file lock để tránh bị gọi trùng lặp
$lock_file = __DIR__ . '/wp-cron.lock';
$lock_time = 0;
if (file_exists($lock_file)) {
    $lock_time = file_get_contents($lock_file);
}

// không cho browser và Cloudflare cache file này
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
header('Content-Type: application/javascript');

// 
$time_left = time() - $lock_time;
// nếu đã có file lock và thời gian lock chưa hết 60 giây thì dừng lại
if ($lock_time && $time_left < 60) {
    echo 'console.log("HIT ' . $time_left . '");';
    exit;
}

// MISS: cập nhật lock, gửi JS về browser TRƯỚC, rồi chạy wp-cron ngầm phía sau
// để tránh WordPress bootstrap ghi đè Content-Type thành text/html
file_put_contents($lock_file, time(), LOCK_EX);

$js_body = 'console.log("MISS");';
header('Content-Length: ' . strlen($js_body));
header('Connection: close');
echo $js_body;

// Đóng kết nối với browser, PHP tiếp tục chạy ngầm
@ob_end_flush();
flush();
if (function_exists('fastcgi_finish_request')) {
    fastcgi_finish_request();
}
ignore_user_abort(true);

// chạy vòng lặp back lại mỗi lần 1 thư mục để tìm file wp-cron.php của WordPress, tối đa 5 lần, nếu không tìm thấy thì dừng lại
$dir = dirname(__DIR__);
for ($i = 0; $i < 5; $i++) {
    $wp_cron_file = $dir . '/wp-cron.php';
    // echo $wp_cron_file . '<br>' . "\n";
    if (file_exists($wp_cron_file)) {
        // gọi tới file wp-cron.php mặc định của WordPress
        include_once $wp_cron_file;
        break;
    }
    $dir = dirname($dir);
}

exit;
