<?php

/**
 * Ô tìm kiếm nhanh Bài viết/ Trang/ Sản phẩm/ Portfolio trong màn hình Giao diện > Menu (nav-menus.php).
 * Tìm theo post_title hoặc post_name (slug đã bỏ dấu) — chính xác hơn ô tìm kiếm mặc định của WP,
 * bấm vào kết quả là thêm luôn vào menu đang sửa (dùng wpNavMenu.addItemToMenu của WP).
 */

/**
 * Danh sách post type được phép tìm kiếm để thêm vào menu.
 * product (WooCommerce) và featured_item (Flatsome) chỉ có khi web cài plugin/theme tương ứng.
 *
 * @return array<string, string> post_type => label
 */
function WGR_nav_menu_search_post_types()
{
    $result = array();

    foreach (array('post', 'page', 'product', 'featured_item') as $post_type) {
        $obj = get_post_type_object($post_type);
        if (!$obj) {
            continue;
        }

        $result[$post_type] = !empty($obj->labels->name) ? $obj->labels->name : $post_type;
    }

    return $result;
}

/**
 * Tìm bài viết đã publish của 1 post type theo tiêu đề/slug (hoặc ID nếu từ khóa là số).
 * Không có từ khóa thì trả về các bài mới nhất.
 *
 * @param string $keyword   Từ khóa.
 * @param string $post_type Post type cần tìm.
 * @param int    $limit     Số kết quả tối đa.
 * @return array<int, object>
 */
function WGR_nav_menu_search_query($keyword, $post_type, $limit = 30)
{
    global $wpdb;

    if ('' === $keyword) {
        $sql = $wpdb->prepare(
            "SELECT ID, post_title
             FROM {$wpdb->posts}
             WHERE post_type = %s
               AND post_status = 'publish'
             ORDER BY post_date DESC
             LIMIT %d",
            $post_type,
            $limit
        );
    } else {
        $slug = sanitize_title($keyword);
        if ('' === $slug) {
            $slug = $keyword;
        }
        $by_id = ctype_digit($keyword) ? (int) $keyword : 0;

        // ưu tiên: trùng ID > trùng tiêu đề > tiêu đề bắt đầu bằng từ khóa > còn lại
        $sql = $wpdb->prepare(
            "SELECT ID, post_title
             FROM {$wpdb->posts}
             WHERE post_type = %s
               AND post_status = 'publish'
               AND (
                    ID = %d
                    OR post_title LIKE %s
                    OR post_name LIKE %s
               )
             ORDER BY
                (ID = %d) DESC,
                (post_title = %s) DESC,
                (post_title LIKE %s) DESC,
                post_title ASC
             LIMIT %d",
            $post_type,
            $by_id,
            '%' . $wpdb->esc_like($keyword) . '%',
            '%' . $wpdb->esc_like($slug) . '%',
            $by_id,
            $keyword,
            $wpdb->esc_like($keyword) . '%',
            $limit
        );
    }

    $rows = $wpdb->get_results($sql);

    return is_array($rows) ? $rows : array();
}

add_action('wp_ajax_wgr_nav_menu_search_posts', 'WGR_ajax_nav_menu_search_posts');
/**
 * AJAX: trả về kết quả tìm kiếm, nhóm theo từng post type.
 */
function WGR_ajax_nav_menu_search_posts()
{
    if (!current_user_can('edit_theme_options')) {
        wp_send_json_error(array('message' => 'Forbidden'), 403);
    }

    check_ajax_referer('wgr_nav_menu_search_posts', 'nonce');

    $keyword = isset($_POST['keyword']) ? sanitize_text_field(wp_unslash($_POST['keyword'])) : '';
    $keyword = trim(preg_replace('/\s+/u', ' ', $keyword));

    // giống JS: có từ khóa thì phải đủ 3 ký tự (không tính khoảng trắng)
    if ('' !== $keyword && mb_strlen(preg_replace('/\s+/u', '', $keyword)) < 3) {
        wp_send_json_success(array('groups' => array()));
    }

    $groups = array();
    foreach (WGR_nav_menu_search_post_types() as $post_type => $label) {
        $items = array();
        foreach (WGR_nav_menu_search_query($keyword, $post_type) as $row) {
            $items[] = array(
                'id'    => (int) $row->ID,
                'title' => '' !== $row->post_title ? $row->post_title : '#' . $row->ID,
            );
        }

        if (!empty($items)) {
            $groups[] = array(
                'type'  => $post_type,
                'label' => $label,
                'items' => $items,
            );
        }
    }

    wp_send_json_success(array('groups' => $groups));
}

add_action('admin_footer-nav-menus.php', 'WGR_nav_menu_search_script');
/**
 * Nạp JS cho màn hình Giao diện > Menu.
 */
function WGR_nav_menu_search_script()
{
    if (!current_user_can('edit_theme_options')) {
        return;
    }

    $config = array(
        'ajaxUrl'   => admin_url('admin-ajax.php'),
        'nonce'     => wp_create_nonce('wgr_nav_menu_search_posts'),
        'postTypes' => WGR_nav_menu_search_post_types(),
    );
?>
    <script>
        window.wgrNavMenuSearch = <?php echo wp_json_encode($config); ?>;
    </script>
<?php
    WGR_adds_js(
        array(
            WGR_BASE_PATH . 'public/admin/js/nav_menu_search.js',
        ),
        array(
            'cdn' => CDN_BASE_URL,
        )
    );
}
