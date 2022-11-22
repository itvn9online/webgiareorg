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
    if (USER_ID > 0) {
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
function WGR_remove_html_empty_line($str)
{
    $str = explode("\n", $str);

    //
    $result = '';
    foreach ($str as $v) {
        $v = trim($v);

        //
        if ($v == '') {
            continue;
        }

        //
        $result .= $v;
        if (strpos($v, '//') !== false) {
            $result .= "\n";
        } else {
            $result .= ' ';
        }
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