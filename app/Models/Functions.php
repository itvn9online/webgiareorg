<?php

// https://woocommerce.wp-a2z.org/oik_api/wc_get_cart_url/
function WGR_get_cart_url()
{
    $str = wc_get_cart_url();

    //
    $arr = [
        'link' => $str,
        'name' => 'Giỏ hàng',
        'class' => 'woocommerce-shopping_cart',
    ];

    //
    return WGR_json_to_menu($arr);
}

// https://developer.wordpress.org/reference/functions/get_search_form/
function WGR_get_search_form($args = array())
{
    $args['echo'] = false;
    $args['aria_label'] = 'echbay-search-form';
    return get_search_form($args);
}

// https://wordpress.stackexchange.com/questions/213612/get-woocommerce-my-account-page-link
// trả về menu các chức năng của khách hàng
function WGR_get_myaccount_page()
{
    $str = get_permalink(get_option('woocommerce_myaccount_page_id'));
    $str = rtrim($str, '/') . '/';

    //
    $arr = [];
    if (WGR_USER_ID > 0) {
        $arr = [
            '' => 'Trang tài khoản',
            get_option('woocommerce_myaccount_orders_endpoint') => 'Đơn hàng',
            get_option('woocommerce_myaccount_view_order_endpoint') => 'Xem đơn hàng',
            get_option('woocommerce_myaccount_downloads_endpoint') => 'Tải xuống',
            get_option('woocommerce_myaccount_edit_account_endpoint') => 'Tài khoản',
            get_option('woocommerce_myaccount_edit_address_endpoint') => 'Địa chỉ',
            get_option('woocommerce_myaccount_payment_methods_endpoint') => 'Phương thức thanh toán',
            get_option('woocommerce_myaccount_lost_password_endpoint') => 'Quên mật khẩu',
            get_option('woocommerce_logout_endpoint') => 'Thoát',
        ];
    }
    $arr = [
        'link' => $str,
        'name' => 'Tài khoản',
        'class' => 'woocommerce-MyAccount',
        'arr' => $arr
    ];

    //
    return WGR_json_to_menu($arr);
}

// tạo mã HTML theo định dạng chung để javascript build ra menu HTML cần thiết
function WGR_json_to_menu($arr)
{
    // trả về dữ liệu dạng json, sau đó javascript sẽ lo phần tiếp theo
    return '<ul class="json-to-menu d-none">' . json_encode($arr) . '</ul>';
}

// xóa các dòng trống cho HTML trước khi in ra
function WGR_remove_html_empty_line(string $str): string
{
    /**
     * v2 -> viết bởi AI
     */
    return preg_replace('/^\s*[\r\n]+/m', '', $str);

    /**
     * v1
     */
    $str = explode("\n", $str);

    //
    $result = '';
    foreach ($str as $v) {
        $v = trim($v);

        // dòng trống thì bỏ qua
        if ($v == '') {
            continue;
        } else if (substr($v, 0, 5) == '<!-- ' && substr($v, -4) == ' -->') {
            // dòng comment html cũng bỏ qua luôn -> không bỏ qua mấy dòng hỗ trợ IE kiểu <!--[if IE 9 ]>
            continue;
        }

        //
        $result .= $v . "\n";
        /*
        if (strpos($v, '//') !== false) {
            $result .= "\n";
        } else {
            $result .= ' ';
        }
        */
    }

    //
    return $result;
}

// dọn dẹp HTML trước khi in ra
// https://stackoverflow.com/questions/6225351/how-to-minify-php-page-html-output
function WGR_sanitize_output($buffer)
{
    $search = array(
        '/\>[^\S ]+/s',
        // strip whitespaces after tags, except space
        '/[^\S ]+\</s',
        // strip whitespaces before tags, except space
        '/(\s)+/s',
        // shorten multiple whitespace sequences
        '/<!--(.|\s)*?-->/' // Remove HTML comments

    );

    $replace = array(
        '>',
        '<',
        '\\1',
        ''
    );

    $buffer = preg_replace($search, $replace, $buffer);

    return $buffer;
}

function WGR_create_dir($path, $ftp = true, $mod = 0755)
{
    if (is_dir($path)) {
        return true;
    }

    //
    if (mkdir($path, $mod)) {
        // server window ko cần chmod
        chmod($path, $mod) or die('ERROR chmod dir: ' . $path);
        return true;
    }

    // Không thì tạo thông qua FTP
    if ($ftp === true) {
        return WGR_ftp_create_dir($path, $mod);
    }
    return false;
}

function WGR_create_file($path, $content)
{
    if (file_put_contents($path, $content, LOCK_EX)) {
        touch($path);
        return true;
    }
    return false;
}

// kiểm tra và so khớp URL
function checkRequestToken($require = false)
{
    //
}

// dọn dẹp thử mục thuộc dạng bảo mật nếu chẳng may up nhầm lên host
function WGR_cleanup_vscode($dir)
{
    if (!is_dir($dir)) {
        return false;
    }
    $objects = scandir($dir);
    foreach ($objects as $object) {
        if ($object != "." && $object != "..") {
            if (filetype($dir . "/" . $object) == "dir") {
                WGR_action_cleanup_vscode($dir . "/" . $object);
            } else {
                unlink($dir . "/" . $object);
            }
        }
    }
    reset($objects);
    rmdir($dir);
}

/**
 * https://www.tutorialspoint.com/how-to-recursively-delete-a-directory-and-its-entire-contents-files-plus-sub-dirs-in-php
 * Xóa toàn bộ file trong 1 thư mục (bao gồm cả file trong thư mục con)
 */
function WGR_deleteDirectory($dirPath)
{
    if (!is_dir($dirPath)) {
        return false;
    }

    // 
    $files = scandir($dirPath);
    // print_r($files);
    foreach ($files as $file) {
        // echo $file . '<br>' . "\n";
        if ($file == '.' || $file == '..') {
            continue;
        }

        // 
        $filePath = $dirPath . '/' . $file;
        if (is_dir($filePath)) {
            WGR_deleteDirectory($filePath);
        } else if (is_file($filePath)) {
            echo $filePath . '<br>' . "\n";
            unlink($filePath);
        }
    }
    // rmdir($dirPath);

    // 
    return true;
}

// tạo file htaccess chặn truy cập vào thư mục
function WGR_htaccess_deny_all($f)
{
    $c = file_get_contents(WGR_BASE_PATH . 'app/Helpers/templates/htaccess_deny_all.txt');
    $arr_deny_all = [
        'base_url' => rtrim(get_home_url(), '/'),
    ];
    foreach ($arr_deny_all as $k => $v) {
        $c = str_replace('{{' . $k . '}}', $v, $c);
    }
    return WGR_create_file($f, $c);
}

// tạo file advanced-cache.php để dùng redis cache
function WGR_create_advanced_cache_file()
{
    // các tham số này được khai báo trong file wp-config.php
    if (
        defined('WGR_REDIS_CACHE') &&
        WGR_REDIS_CACHE === true &&
        defined('WGR_CACHE_PREFIX') &&
        !empty(WGR_CACHE_PREFIX)
    ) {
        // Tạo file advanced-cache.php drop-in
        file_put_contents(ABSPATH . 'wp-content/advanced-cache.php', implode("\n", [
            '<?php',
            '',
            '/**',
            ' * Advanced Page Cache with Redis',
            ' *',
            ' * @package WebGiaRe',
            ' * @link https://webgiare.org',
            ' * @license GNU General Public License v2 or later (GPLv2+)',
            ' * Generated on ' . date_i18n('Y-m-d H:i:s'),
            ' * From ' . $_SERVER['REQUEST_URI'],
            ' *',
            ' */',
            '',
            '// Prevent direct access',
            'if (!defined(\'ABSPATH\')) {',
            '    exit;',
            '}',
            '',
            'include_once __DIR__ . \'/webgiareorg/app/Cache/advanced-cache.php\';',
            '',
        ]), LOCK_EX);

        return true;
    }

    return false;
}


// tạo file object-cache.php để dùng redis cache
function WGR_create_object_cache_file()
{
    // các tham số này được khai báo trong file wp-config.php
    if (
        defined('WGR_REDIS_CACHE') &&
        WGR_REDIS_CACHE === true &&
        defined('WGR_CACHE_PREFIX') &&
        !empty(WGR_CACHE_PREFIX)
    ) {
        // tạo file để include file object-cache thay vì copy -> tận dụng được code khi update
        file_put_contents(ABSPATH . 'wp-content/object-cache.php', implode("\n", [
            '<?php',
            '',
            '/**',
            ' * Simple Redis Object Cache',
            ' *',
            ' * @package WebGiaRe',
            ' * @link https://webgiare.org',
            ' * @license GNU General Public License v2 or later (GPLv2+)',
            ' * Generated on ' . date_i18n('Y-m-d H:i:s'),
            ' * From ' . $_SERVER['REQUEST_URI'],
            ' *',
            ' */',
            '',
            '// Prevent direct access',
            'if (!defined(\'ABSPATH\')) {',
            '    exit;',
            '}',
            '',
            'include_once __DIR__ . \'/webgiareorg/app/Cache/object-cache.php\';',
            '',
        ]), LOCK_EX);

        // 
        return true;
    }

    // 
    return false;
}

// tìm dòng define('CONSTANT_NAME', ...) trong 1 chuỗi
function WGR_find_define_constant($str, $constant_name)
{
    if (
        // chuỗi bắt đầu bằng define
        strpos(trim($str), 'define') === 0 &&
        // có chứa tên constant
        strpos($str, $constant_name) !== false &&
        // và các ký tự bắt buộc khác
        strpos($str, '(') !== false &&
        strpos($str, ',') !== false &&
        strpos($str, ')') !== false &&
        strpos($str, ';') !== false
    ) {
        return true;
    }
    return false;
}
