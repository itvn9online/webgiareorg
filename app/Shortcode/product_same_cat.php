<?php

/*
* shortcode lấy bài viết cùng danh mục
*/
// cách sử dụng -> vào phần nội dung bài viết rồi nhập: [wgr_same_cat]
add_shortcode('wgr_same_cat', 'wgr_action_same_cat');
function wgr_action_same_cat()
{
    $pid = get_the_ID();
    //var_dump($pid);
    if ($pid <= 0) {
        return false;
    }
    //var_dump($pid);

    $arr_list_tag = get_the_category($pid);
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

    // -> trả về shortcode của flatsome
    echo flatsome_apply_shortcode('blog_posts', array(
        'type'        => 'row',
        'image_width' => '20',
        'posts' => '5',
        'depth'       => get_theme_mod('blog_posts_depth', 0),
        'excerpt'       => 'false',
        'depth_hover' => get_theme_mod('blog_posts_depth_hover', 0),
        'text_align'  => get_theme_mod('blog_posts_title_align', 'center'),
        'style'       => 'vertical',
        'columns'     => '1',
        //'show_date'   => get_theme_mod('blog_badge', 1) ? 'true' : 'false',
        'show_date'   => 'text',
        'cat'         => implode(',', $cat_ids),
    ));
}
