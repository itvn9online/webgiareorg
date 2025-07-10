<?php

/**
 * shortcode lấy bài viết cùng danh mục
 */
// trả về danh sách bài viết dạng row>col cách sử dụng -> vào phần nội dung bài viết rồi nhập: [wgr_same_cat]
// trả về post cùng nhóm với 3 col trên row
add_shortcode('wgr_same_cat', 'wgr_action_default_same_cat');
// trả về post cùng nhóm với 4 col trên row
add_shortcode('wgr_same_col4_cat', 'wgr_action_col4_same_cat');
// trả về post cùng nhóm với 2 col trên row
add_shortcode('wgr_same_col6_cat', 'wgr_action_col6_same_cat');

// trả về post cùng nhóm với 1 col trên row và hiển thị hình ảnh dọc (dạng vertical)
add_shortcode('wgr_same_vertical_cat', 'wgr_action_vertical_same_cat');

//
function wgr_action_same_cat($custom_attrs = [])
{
    $pid = get_the_ID();
    // var_dump($pid);
    if ($pid < 1) {
        return null;
    }
    // var_dump($pid);
    // echo $pid . '<br>' . PHP_EOL;

    // Lấy danh sách category của bài viết hiện tại
    $cat_ids = [];
    $arr_list_cat = get_the_category($pid);
    if (empty($arr_list_cat)) {
        return null;
    }
    foreach ($arr_list_cat as $v) {
        $cat_ids[] = $v->term_id;
    }
    if (empty($cat_ids)) {
        return null;
    }
    // print_r($cat_ids);

    // 
    $placeholders = implode(',', $cat_ids);
    // echo $placeholders . '<br>' . PHP_EOL;

    // 
    global $wpdb;

    // 
    $limit = 6;
    if (isset($custom_attrs['posts']) && is_numeric($custom_attrs['posts']) && $custom_attrs['posts'] > 0) {
        $limit = $custom_attrs['posts'] * 1;
        unset($custom_attrs['posts']);
    }
    // echo $limit . '<br>' . PHP_EOL;

    // tạo query lấy ID bài viết có ID lớn hơn $pid
    $sql = "SELECT p.ID FROM {$wpdb->posts} p
        INNER JOIN {$wpdb->term_relationships} tr ON (p.ID = tr.object_id)
        INNER JOIN {$wpdb->term_taxonomy} tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id)
        WHERE p.post_type = 'post' AND p.post_status = 'publish'
        AND tt.taxonomy = 'category' AND tt.term_id IN ($placeholders)
        AND p.ID > %d
        GROUP BY p.ID ASC
        LIMIT 1";
    // echo $sql . '<br>' . PHP_EOL;
    $query = $wpdb->prepare($sql, $pid);
    // echo $query . '<br>' . PHP_EOL;
    $first_id = $wpdb->get_var($query);
    // echo $first_id . '<br>' . PHP_EOL;
    if ($first_id) {
        $limit--;
    }
    // echo $limit . '<br>' . PHP_EOL;

    // tạo query lấy ID bài viết có ID nhỏ hơn $pid
    $sql = "SELECT p.ID FROM {$wpdb->posts} p
        INNER JOIN {$wpdb->term_relationships} tr ON (p.ID = tr.object_id)
        INNER JOIN {$wpdb->term_taxonomy} tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id)
        WHERE p.post_type = 'post' AND p.post_status = 'publish'
        AND tt.taxonomy = 'category' AND tt.term_id IN ($placeholders)
        AND p.ID < %d
        GROUP BY p.ID DESC
        LIMIT $limit";
    $query = $wpdb->prepare($sql, $pid);
    // echo $query . '<br>' . PHP_EOL;
    $ids = $wpdb->get_col($query);
    if (empty($ids)) {
        return null;
    }
    if ($first_id) {
        // nếu có bài viết đầu tiên thì thêm vào đầu mảng
        array_unshift($ids, $first_id);
    }
    // print_r($ids);

    //
    $attrs = [
        'type' => 'row',
        // 'image_width' => '20',
        'image_height' => '56.25%',
        // 'posts' => '6',
        'depth' => get_theme_mod('blog_posts_depth', 0),
        'excerpt' => 'false',
        'depth_hover' => get_theme_mod('blog_posts_depth_hover', 0),
        'text_align' => get_theme_mod('blog_posts_title_align', 'center'),
        'style' => 'default',
        'columns' => '3',
        'columns__md' => '1',
        // 'show_date' => get_theme_mod('blog_badge', 1) ? 'true' : 'false',
        'show_date' => 'text',
        // 'cat' => implode(',', $cat_ids),
        'ids' => implode(',', $ids),
        'class' => 'align-equal',
    ];
    foreach ($custom_attrs as $k => $v) {
        $attrs[$k] = $v;
    }
    // return null;

    // -> trả về shortcode của flatsome
    return flatsome_apply_shortcode('blog_posts', $attrs);
}

function wgr_action_default_same_cat()
{
    return wgr_action_same_cat();
}

function wgr_action_col6_same_cat()
{
    return wgr_action_same_cat([
        'posts' => '4',
        'columns' => '2',
    ]);
}

function wgr_action_col4_same_cat()
{
    return wgr_action_same_cat([
        'posts' => '8',
        'columns' => '4',
    ]);
}

function wgr_action_vertical_same_cat()
{
    return wgr_action_same_cat([
        'image_width' => '20',
        'posts' => '5',
        'columns' => '1',
        'style' => 'vertical',
        'class' => '',
    ]);
}
