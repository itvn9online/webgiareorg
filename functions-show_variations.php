<?php

/**
 * Hiển thị các sản phẩm biến thể trong danh sách sản phẩm trong admin.
 */

/**
 * Kiểm tra có đang ở trang danh sách sản phẩm trong admin hay không
 * (wp-admin/edit.php?post_type=product).
 *
 * @param WP_Query|null $query Query đang xử lý (nếu có) để kiểm tra thêm khi
 *                             $_GET['post_type'] không tồn tại.
 * @return bool True nếu đang ở trang danh sách sản phẩm.
 */
function WGR_is_admin_products_list($query = null)
{
    global $pagenow;

    if (!is_admin() || 'edit.php' !== $pagenow) {
        return false;
    }

    if (isset($_GET['post_type']) && 'product' === $_GET['post_type']) {
        return true;
    }

    if ($query instanceof WP_Query && $query->is_main_query()) {
        $post_type = $query->get('post_type');
        if ('product' === $post_type || (is_array($post_type) && in_array('product', $post_type, true))) {
            return true;
        }
    }

    return false;
}

/**
 * Quyết định có nên hiển thị biến thể trong danh sách sản phẩm hay không.
 * Trả về false khi: không phải trang danh sách sản phẩm, WooCommerce chưa nạp,
 * người dùng chọn "Ẩn biến thể" (show_variations=0), hoặc đang lọc theo
 * loại sản phẩm khác "variable".
 *
 * @param WP_Query|null $query Query đang xử lý (nếu có).
 * @return bool True nếu được phép hiển thị biến thể.
 */
function WGR_should_show_variations_in_admin($query = null)
{
    if (!WGR_is_admin_products_list($query)) {
        return false;
    }

    if (!function_exists('wc_get_product')) {
        return false;
    }

    if (isset($_GET['show_variations']) && '0' === wp_unslash($_GET['show_variations'])) {
        return false;
    }

    $product_type = isset($_GET['product_type']) ? wc_clean(wp_unslash($_GET['product_type'])) : '';
    if ($product_type && 'variable' !== $product_type) {
        return false;
    }

    return true;
}

add_action('restrict_manage_posts', 'WGR_admin_products_variation_filter');
/**
 * Thêm dropdown "Hiển thị biến thể / Ẩn biến thể" vào thanh bộ lọc
 * phía trên danh sách sản phẩm để người dùng bật/tắt nhanh.
 *
 * @param string $post_type Post type của màn hình danh sách hiện tại.
 */
function WGR_admin_products_variation_filter($post_type)
{
    if ('product' !== $post_type || !current_user_can('edit_products')) {
        return;
    }

    $current = isset($_GET['show_variations']) ? wc_clean(wp_unslash($_GET['show_variations'])) : '1';
?>
    <select name="show_variations" id="dropdown_show_variations">
        <option value="1" <?php selected($current, '1'); ?>><?php esc_html_e('Hiển thị biến thể', 'woocommerce'); ?></option>
        <option value="0" <?php selected($current, '0'); ?>><?php esc_html_e('Ẩn biến thể', 'woocommerce'); ?></option>
    </select>
<?php
}

add_filter('posts_clauses', 'WGR_admin_products_list_variation_clauses', 20, 2);
/**
 * Chỉnh sửa SQL của query danh sách sản phẩm:
 * - Mở rộng WHERE để lấy thêm post_type 'product_variation'
 *   (thay vì set query var thành array, tránh warning ở wp-admin/edit.php).
 * - Sắp xếp để biến thể luôn nằm ngay dưới sản phẩm cha.
 * - Khi lọc theo danh mục: thêm biến thể của các sản phẩm cha thuộc danh mục đó
 *   (biến thể không được gán term trực tiếp nên phải join qua sản phẩm cha).
 * - Khi lọc theo loại "variable": thêm biến thể của các sản phẩm variable.
 *
 * @param array    $clauses Các mảnh SQL (where, orderby, ...) của query.
 * @param WP_Query $query   Query đang xử lý.
 * @return array Clauses sau khi chỉnh sửa.
 */
function WGR_admin_products_list_variation_clauses($clauses, $query)
{
    if (!WGR_should_show_variations_in_admin($query) || !$query->is_main_query()) {
        return $clauses;
    }

    global $wpdb;

    $product_post_type_where = "{$wpdb->posts}.post_type = 'product'";
    if (strpos($clauses['where'], $product_post_type_where) !== false) {
        $clauses['where'] = str_replace(
            $product_post_type_where,
            "{$wpdb->posts}.post_type IN ('product', 'product_variation')",
            $clauses['where']
        );
    }

    $clauses['orderby'] = "COALESCE(NULLIF({$wpdb->posts}.post_parent, 0), {$wpdb->posts}.ID) ASC, {$wpdb->posts}.post_parent ASC, {$wpdb->posts}.menu_order ASC, {$wpdb->posts}.post_title ASC";

    if (!empty($_GET['product_cat'])) {
        $term = get_term_by('slug', wc_clean(wp_unslash($_GET['product_cat'])), 'product_cat');
        if ($term && !is_wp_error($term)) {
            $parent_ids = get_posts(
                array(
                    'post_type'      => 'product',
                    'post_status'    => 'any',
                    'fields'         => 'ids',
                    'posts_per_page' => -1,
                    'tax_query'      => array(
                        array(
                            'taxonomy' => 'product_cat',
                            'field'    => 'term_id',
                            'terms'    => array((int) $term->term_id),
                        ),
                    ),
                )
            );

            if (!empty($parent_ids)) {
                $parent_ids_sql = implode(',', array_map('absint', $parent_ids));
                $clauses['where'] .= " OR ({$wpdb->posts}.post_type = 'product_variation' AND {$wpdb->posts}.post_parent IN ({$parent_ids_sql}))";
            }
        }
    }

    $product_type = isset($_GET['product_type']) ? wc_clean(wp_unslash($_GET['product_type'])) : '';
    if ('variable' === $product_type) {
        $clauses['where'] .= " OR ({$wpdb->posts}.post_type = 'product_variation' AND {$wpdb->posts}.post_parent IN (
      SELECT tr.object_id FROM {$wpdb->term_relationships} tr
      INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
      INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
      WHERE tt.taxonomy = 'product_type' AND t.slug = 'variable'
    ))";
    }

    return $clauses;
}

/**
 * Lấy tên hiển thị ngắn gọn của biến thể (chỉ phần thuộc tính, ví dụ "2kg").
 * Không dùng get_formatted_name() vì hàm đó trả về cả tên sản phẩm cha,
 * SKU/ID và HTML <span class="description">.
 *
 * @param WC_Product_Variation $product Biến thể cần lấy tên.
 * @return string Tên biến thể để hiển thị trong admin.
 */
function WGR_get_admin_variation_name($product)
{
    $attrs = wc_get_formatted_variation($product, true, false);
    if ('' !== $attrs) {
        return $attrs;
    }

    // Fallback: get_name() thường có dạng "Tên sản phẩm cha - 2kg"
    $name = $product->get_name();
    if (false !== strpos($name, ' - ')) {
        $parts = explode(' - ', $name);
        return (string) end($parts);
    }

    return $name;
}

add_action('manage_product_variation_posts_custom_column', 'WGR_admin_variation_column_content', 10, 2);
/**
 * Đổ nội dung cho các cột của dòng biến thể trong bảng danh sách sản phẩm
 * (ảnh, tên biến thể, SKU, giá, tồn kho...). WordPress không tự render
 * các cột này cho post_type 'product_variation' nên phải tự xử lý.
 *
 * @param string $column  Tên cột đang render.
 * @param int    $post_id ID của biến thể.
 */
function WGR_admin_variation_column_content($column, $post_id)
{
    if (!WGR_should_show_variations_in_admin()) {
        return;
    }

    $product = wc_get_product($post_id);
    if (!$product || !$product->is_type('variation')) {
        return;
    }

    switch ($column) {
        case 'thumb':
            echo $product->get_image('thumbnail');
            break;

        case 'name':
            echo '<strong class="row-title">' . esc_html(WGR_get_admin_variation_name($product)) . '</strong>';
            break;

        case 'sku':
            echo $product->get_sku() ? esc_html($product->get_sku()) : '<span class="na">&ndash;</span>';
            break;

        case 'price':
            echo $product->get_price_html() ? wp_kses_post($product->get_price_html()) : '<span class="na">&ndash;</span>';
            break;

        case 'is_in_stock':
            if ($product->is_on_backorder()) {
                $stock_html = '<mark class="onbackorder">' . esc_html__('On backorder', 'woocommerce') . '</mark>';
            } elseif ($product->is_in_stock()) {
                $stock_html = '<mark class="instock">' . esc_html__('In stock', 'woocommerce') . '</mark>';
            } else {
                $stock_html = '<mark class="outofstock">' . esc_html__('Out of stock', 'woocommerce') . '</mark>';
            }

            if ($product->managing_stock()) {
                $stock_html .= ' (' . wc_stock_amount($product->get_stock_quantity()) . ')';
            }

            echo wp_kses_post($stock_html);
            break;

        default:
            echo '<span class="na wgr-na" aria-hidden="true">–</span>';
            break;
    }
}

add_filter('post_row_actions', 'WGR_admin_variation_row_actions', 999, 2);
/**
 * Ẩn toàn bộ row-actions (Chỉnh sửa, Sửa nhanh, Xóa tạm...) trên dòng biến thể.
 *
 * @param array   $actions Danh sách action link hiện có.
 * @param WP_Post $post    Post của dòng đang render.
 * @return array Actions sau khi lọc.
 */
function WGR_admin_variation_row_actions($actions, $post)
{
    if (!WGR_should_show_variations_in_admin() || 'product_variation' !== $post->post_type) {
        return $actions;
    }

    return array();
}

add_filter('post_class', 'WGR_admin_variation_row_class', 10, 3);
/**
 * Thêm class 'wgr-admin-variation-row' vào dòng biến thể
 * để CSS nhận diện và style riêng (thụt lề, mũi tên...).
 *
 * @param array $classes Danh sách class hiện có của dòng.
 * @param array $class   Class truyền thêm (không dùng).
 * @param int   $post_id ID của post đang render.
 * @return array Classes sau khi bổ sung.
 */
function WGR_admin_variation_row_class($classes, $class, $post_id)
{
    if (!WGR_should_show_variations_in_admin() || 'product_variation' !== get_post_type($post_id)) {
        return $classes;
    }

    $classes[] = 'wgr-admin-variation-row';
    return $classes;
}

add_action('admin_head', 'WGR_admin_variation_list_styles');
/**
 * In CSS vào <head> của trang danh sách sản phẩm: thụt lề tên biến thể,
 * thêm ký hiệu "↳" trước tên, ẩn row-actions không cần thiết.
 */
function WGR_admin_variation_list_styles()
{
    if (!WGR_is_admin_products_list() || !WGR_should_show_variations_in_admin()) {
        return;
    }
?>
    <style>
        .post-type-product tr.type-product_variation.wgr-admin-variation-row {
            .check-column {
                /* opacity: 0; */
                /* visibility: hidden; */

                * {
                    display: none;
                }
            }

            .column-name {
                padding-left: 2em;
            }

            .column-name .row-title::before {
                content: "↳ ";
                color: #787c82;
            }

            .row-actions {
                display: none;
            }

            .column-date {
                color: transparent;
            }
        }
    </style>
<?php
}
