<?php
/**
 * Chức năng export sản phẩm để up sang google
 * Cấu trúc dữ liệu sản phẩm theo tiêu chuẩn của google
 * https://support.google.com/merchants/topic/6324338?hl=vi&ref_topic=7294998
 * https://developers.facebook.com/docs/marketing-api/dynamic-product-ads/product-catalog?__mref=message_bubble#feed-format
 */

function action_woo_for_gg()
{
    return action_woo_for_fb('google');
}

function woo_for_gg()
{
    // route url: domain.com/wp-json/$namespace/$route
    $namespace = 'products';
    $route = 'for-google';

    register_rest_route(
        $namespace,
        $route,
        array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => 'action_woo_for_gg'
        )
    );
}

//
add_action('rest_api_init', 'woo_for_gg');