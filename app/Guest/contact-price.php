<?php

// Code chuyển 0đ thành chữ “Liên hệ”
// https://levantoan.com/chuyen-0d-thanh-chu-lien-trong-woocommerce/
function devvn_wc_custom_get_price_html($price, $product)
{
    if ($product->get_price() == 0) {
        if ($product->is_on_sale() && $product->get_regular_price()) {
            $regular_price = wc_get_price_to_display($product, array('qty' => 1, 'price' => $product->get_regular_price()));
            $price = wc_format_price_range($regular_price, __('Free!', 'woocommerce'));
        } else {
            $price = '<span class="contact-price amount">' . __('Liên hệ', 'woocommerce') . '</span>';
        }
    }
    return $price;
}
add_filter('woocommerce_get_price_html', 'devvn_wc_custom_get_price_html', 10, 2);