<?php

/*
 * Đưa ra các cảnh báo cho website
 */
if (defined('WP_CACHE') && WP_CACHE !== false) {
    function WGR_WP_CACHE_admin_notice__warning()
    {
        $class = 'notice notice-warning';
        $message = 'Chúng tôi khuyên dùng cache mặc định theo code (bạn không cần cài đặt thêm bất kỳ plugin cache nào khác). Chỉ sử dụng cache bên thứ 3 khi thực sự cần thiết.';

        printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($message));
    }
    add_action('admin_notices', 'WGR_WP_CACHE_admin_notice__warning');
}