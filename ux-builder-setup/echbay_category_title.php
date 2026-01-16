<?php

/**
 * Chức năng tạo tiêu đề danh mục và hiển thị các danh mục con
 */
function add_echbay_taxonomy_title($taxonomy = 'category')
{
    // Lấy danh sách danh mục cha (parent = 0)
    $ops_list = [
        0 => '- Chọn danh mục -',
    ];

    $categories = get_terms([
        'taxonomy' => $taxonomy,
        'parent' => 0,
        'hide_empty' => false,
        'orderby' => 'name',
        'order' => 'ASC',
    ]);

    if (!is_wp_error($categories) && !empty($categories)) {
        foreach ($categories as $category) {
            $ops_list[$category->term_id] = $category->name;
        }
    }

    //
    add_ux_builder_shortcode('echbay_' . $taxonomy . '_title', array(
        'name' => 'Echbay ' . $taxonomy . ' Title',
        'category' => 'Echbay',
        'options' => array(
            'category_id' => array(
                'type' => 'select',
                'heading' => 'Danh mục',
                'default' => 0,
                'options' => $ops_list,
            ),
            'custom_title' => array(
                'type' => 'textfield',
                'heading' => 'Tiêu đề tùy chỉnh',
                'default' => '',
                'placeholder' => 'Để trống sẽ lấy tên danh mục',
            ),
            'view_all' => array(
                'type' => 'textfield',
                'heading' => 'Xem tất cả',
                'default' => '',
                'placeholder' => 'VD: Xem tất cả',
            ),
            'custom_class' => array(
                'type' => 'textfield',
                'heading' => 'Class CSS',
                'default' => '',
                'placeholder' => 'Tùy chỉnh CSS',
            ),
        ),
    ));
}
function add_echbay_category_title()
{
    return add_echbay_taxonomy_title();
}
function add_echbay_product_cat_title()
{
    return add_echbay_taxonomy_title('product_cat');
}
add_action('ux_builder_setup', 'add_echbay_category_title');
add_action('ux_builder_setup', 'add_echbay_product_cat_title');

// gọi short code từ UX Builder
function action_echbay_taxonomy_title($atts, $taxonomy = 'category')
{
    extract(shortcode_atts(array(
        'category_id' => '',
        'custom_title' => '',
        'view_all' => '',
        'custom_class' => '',
    ), $atts));

    //
    if (empty($category_id)) {
        return __FUNCTION__ . ' category_id is empty!';
    }

    // Lấy thông tin danh mục
    $category = get_term($category_id, $taxonomy);

    if (is_wp_error($category) || !$category) {
        return __FUNCTION__ . ' category not found!';
    }

    // Lấy tiêu đề: ưu tiên custom_title, không thì lấy tên danh mục
    $category_title = !empty($custom_title) ? $custom_title : $category->name;

    // Lấy URL danh mục
    $category_link = get_term_link($category, $taxonomy);
    if (is_wp_error($category_link)) {
        $category_link = '#';
    }

    // Bắt đầu tạo HTML
    $html = '<div class="echbay-category-title-wrapper">';
    $html .= '<div><h2 class="echbay-category-title">';
    $html .= '<a href="' . esc_url($category_link) . '">' . esc_html($category_title) . '</a>';
    $html .= '</h2></div>';

    // Lấy danh mục con
    $child_categories = get_terms([
        'taxonomy' => $taxonomy,
        'parent' => $category_id,
        'hide_empty' => false,
    ]);

    // Hiển thị danh mục con nếu có
    if (!empty($child_categories) && !is_wp_error($child_categories)) {
        $html .= '<div class="echbay-subcategories">';
        $html .= '<ul class="echbay-subcategories-list">';

        foreach ($child_categories as $child) {
            // Bỏ qua danh mục con không có sản phẩm
            if ($child->count < 1) {
                continue;
            }

            // 
            $child_link = get_term_link($child->term_id, $taxonomy);
            $html .= '<li class="echbay-subcategory-item">';
            $html .= '<a href="' . esc_url($child_link) . '">' . esc_html($child->name) . '</a>';
            $html .= ' <span>(' . $child->count . ')</span>';
            $html .= '</li>';
        }

        // Thêm link "Xem tất cả" nếu có
        if (!empty($view_all)) {
            $html .= '<li class="echbay-subcategory-item echbay-view-all">';
            $html .= '<a href="' . esc_url($category_link) . '">' . esc_html($view_all) . '</a>';
            $html .= '</li>';
        }

        $html .= '</ul>';
        $html .= '</div>';
    }

    $html .= '</div>';

    // Thêm class tùy chỉnh nếu có
    return '<div class="' . esc_attr(trim($custom_class . ' container')) . '">' . $html . '</div>';
}
function action_echbay_category_title($atts)
{
    return action_echbay_taxonomy_title($atts);
}
function action_echbay_product_cat_title($atts)
{
    return action_echbay_taxonomy_title($atts, 'product_cat');
}
add_shortcode('echbay_category_title', 'action_echbay_category_title');
add_shortcode('echbay_product_cat_title', 'action_echbay_product_cat_title');
