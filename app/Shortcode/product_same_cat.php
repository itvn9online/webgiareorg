<?php

/*
* shortcode lấy bài viết cùng danh mục
*/
// trả về danh sách bài viết dạng row>col cách sử dụng -> vào phần nội dung bài viết rồi nhập: [wgr_product_same_cat]
// trả về post với 3 col trên row
add_shortcode('wgr_product_same_cat', 'wgr_action_default_product_same_cat');
// trả về post với 4 col trên row
add_shortcode('wgr_product_same_col4_cat', 'wgr_action_col4_product_same_cat');
// trả về post với 2 col trên row
add_shortcode('wgr_product_same_col6_cat', 'wgr_action_col6_product_same_cat');

// trả về danh sách bài viết dạng row>col cách sử dụng -> vào phần nội dung bài viết rồi nhập: [wgr_same_vertical_cat]
add_shortcode('wgr_product_same_vertical_cat', 'wgr_action_vertical_product_same_cat');

//
function wgr_action_product_same_cat($custom_attrs = [])
{
    $pid = get_the_ID();
    //var_dump($pid);
    if ($pid <= 0) {
        return false;
    }
    //var_dump($pid);

    $arr_list_tag = get_the_terms($pid, 'product_cat');
    //print_r($arr_list_tag);
    if (empty($arr_list_tag)) {
        return false;
    }

    //
    $cat_ids = [];
    foreach ($arr_list_tag as $v) {
        $cat_ids[] = $v->term_id;
    }
    //print_r($cat_ids);

    //
    $attrs = [
        'type' => 'row',
        'show_cat' => '0',
        'show_rating' => '0',
        'show_quick_view' => '0',
        'equalize_box' => 'true',
        'cat' => implode(',', $cat_ids),
        'products' => '4',
    ];
    foreach ($custom_attrs as $k => $v) {
        $attrs[$k] = $v;
    }

    // -> trả về shortcode của flatsome
    return flatsome_apply_shortcode('ux_products', $attrs);
}

function wgr_action_default_product_same_cat()
{
    return wgr_action_product_same_cat();
}

function wgr_action_col6_product_same_cat()
{
    return wgr_action_product_same_cat([
        'posts' => '4',
        'columns' => '2',
    ]);
}

function wgr_action_col4_product_same_cat()
{
    return wgr_action_product_same_cat([
        'posts' => '8',
        'columns' => '4',
    ]);
}

function wgr_action_vertical_product_same_cat()
{
    return wgr_action_product_same_cat([
        'image_width' => '20',
        'posts' => '5',
        'columns' => '1',
        'style' => 'vertical',
        'class' => '',
    ]);
}
