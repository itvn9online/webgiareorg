<?php
/**
 * Chức năng export sản phẩm để up sang facebook ads
 * Cấu trúc dữ liệu sản phẩm theo tiêu chuẩn của google
 * https://support.google.com/merchants/topic/6324338?hl=vi&ref_topic=7294998
 * https://developers.facebook.com/docs/marketing-api/dynamic-product-ads/product-catalog?__mref=message_bubble#feed-format
 */

function action_woo_for_fb($export_type = 'facebook')
{
    checkRequestToken();

    //
    global $wpdb;
    //global $product;

    //
    $header_name = 'products';
    $trang = isset($_GET['trang']) ? (int) $_GET['trang'] : 1;

    //
    header("Content-Type: text/xml");
    header('Content-Disposition: inline; filename="' . $header_name . '-page' . $trang . '.xml"');

    //
    $temp_item = file_get_contents(__DIR__ . '/woo-for-fb/item.xml');

    //
    $tbl_posts = $wpdb->prefix . 'posts';

    //
    $where = [
        "post_type = 'product'",
        "post_status = 'publish'",
    ];

    // tính tổng số sp
    $query = "SELECT COUNT(ID) AS c
    FROM
        $tbl_posts
    WHERE
        " . implode(" AND ", $where);
    //$data = $wpdb->get_results($query, OBJECT);
    //print_r($data);

    // lấy danh sách sp
    $query = "SELECT ID, post_title, post_excerpt
    FROM
        $tbl_posts
    WHERE
        " . implode(" AND ", $where) . "
    ORDER BY
        ID ASC
    LIMIT 0, 100";
    $data = $wpdb->get_results($query, OBJECT);
    //print_r($data);

    //
    $rss_item = '';
    foreach ($data as $v) {
        //print_r($v);

        //
        $_product = wc_get_product($v->ID);

        //
        $str = $temp_item;

        $v->image_link = get_the_post_thumbnail_url($v->ID, 'shop_catalog');
        //$v->stock = get_post_meta($v->ID, '_stock', true);
        $v->permalink = get_permalink($v->ID);
        $v->post_excerpt = strip_tags($v->post_excerpt);

        //
        $v->price = $_product->get_price() . ' VND';
        $v->availability = str_replace('-', ' ', $_product->get_availability()['class']);
        //print_r($v->availability);
        //$v->regular_price = $_product->get_regular_price();
        $v->sale_price = $_product->get_sale_price();

        //
        $add_on_data = '';
        if ($v->sale_price > 0) {
            $add_on_data .= '<g:sale_price>' . $v->sale_price . ' VND</g:sale_price>';
        }

        // cho bản của google
        if ($export_type == 'google') {
            $item_groups_id = $_product->get_category_ids();
            //print_r($item_groups_id);

            //
            if (!empty($item_groups_id)) {
                $add_on_data .= '<g:item_group_id>' . $item_groups_id[0] . '</g:item_group_id>';
            }
        }

        //
        $v->add_on_data = $add_on_data;

        //
        foreach ($v as $k2 => $v2) {
            $str = str_replace('%' . $k2 . '%', $v2, $str);
        }
        $rss_item .= $str;
    }

    //
    //echo get_woocommerce_currency_symbol();

    //
    $rss_brand = explode('.', $_SERVER['HTTP_HOST']);
    $rss_brand = $rss_brand[0];
    foreach (['product_rss_brand' => $rss_brand,] as $k => $v) {
        $rss_item = str_replace('%' . $k . '%', $v, $rss_item);
    }

    //
    wp_reset_query();

    //
    $rss_content = file_get_contents(__DIR__ . '/woo-for-fb/rss.xml');
    foreach (['time' => date('r', time()), 'rss_item' => $rss_item, 'base_url' => get_site_url(), 'blogname' => get_bloginfo('blogname'), 'blogdescription' => get_bloginfo('blogdescription'),] as $k => $v) {
        $rss_content = str_replace('%' . $k . '%', $v, $rss_content);
    }

    //
    die($rss_content);
}

function woo_for_fb()
{
    // route url: domain.com/wp-json/$namespace/$route
    $namespace = 'products';
    $route = 'for-facebook';

    register_rest_route(
        $namespace,
        $route,
        array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => 'action_woo_for_fb'
        )
    );
}

//
add_action('rest_api_init', 'woo_for_fb');

// nạp chức năng export cho google -> nạp kiểu này để còn tái sử dụng code của file dành cho facebook
include WGR_BASE_PATH . 'app/Guest/woo-for-gg.php';