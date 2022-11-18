<?php

/*
 * Trả về danh sách bình luận của sản phẩm
 *
 [WGR_product_comment]
 *
 */

function action_WGR_product_comment($ops = [])
{
    //global $product;

    //
    ob_start();

    //
    if (comments_open() || get_comments_number()):
        comments_template();
    endif;

    //
    $result = ob_get_contents();

    //
    ob_end_clean();

    //
    return $result;
}
add_shortcode('WGR_product_comment', 'action_WGR_product_comment');