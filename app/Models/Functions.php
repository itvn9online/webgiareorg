<?php

// https://woocommerce.wp-a2z.org/oik_api/wc_get_cart_url/
function WGR_get_cart_url() {
    $str = wc_get_cart_url();

    //
    $arr = [
        'link' => $str,
        'name' => 'Giỏ hàng',
        'class' => 'woocommerce-shopping_cart',
    ];

    //
    return WGR_json_to_menu( $arr );
}

// https://developer.wordpress.org/reference/functions/get_search_form/
function WGR_get_search_form( $args = array() ) {
    $args[ 'echo' ] = false;
    $args[ 'aria_label' ] = 'echbay-search-form';
    return get_search_form( $args );
}

// https://wordpress.stackexchange.com/questions/213612/get-woocommerce-my-account-page-link
// trả về menu các chức năng của khách hàng
function WGR_get_myaccount_page() {
    $str = get_permalink( get_option( 'woocommerce_myaccount_page_id' ) );
    $str = rtrim( $str, '/' ) . '/';

    //
    $arr = [
        'link' => $str,
        'name' => 'Tài khoản',
        'class' => 'woocommerce-MyAccount',
        'arr' => [
            '' => 'Trang tài khoản',
            'orders' => 'Đơn hàng',
            'downloads' => 'Tải xuống',
            'edit-address' => 'Địa chỉ',
            'edit-account' => 'Tài khoản',
            'customer-logout' => 'Thoát',
        ]
    ];

    //
    return WGR_json_to_menu( $arr );
}

// tạo mã HTML theo định dạng chung để javascript build ra menu HTML cần thiết
function WGR_json_to_menu( $arr ) {
    // trả về dữ liệu dạng json, sau đó javascript sẽ lo phần tiếp theo
    return '<ul class="json-to-menu">' . json_encode( $arr ) . '</ul>';
}