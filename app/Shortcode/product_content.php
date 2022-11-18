<?php

/*
 * In ra nội dung của sản phẩm
 *
 [WGR_product_content]
 *
 */

function action_WGR_product_content($ops = [])
{
    //global $product;

    //
    //$content = get_the_content();
    //$content = apply_filters('the_content', get_post_field('post_content', get_the_ID()));
    $content = apply_filters('the_content', get_the_content());

    //
    return $content;
}
add_shortcode('WGR_product_content', 'action_WGR_product_content');