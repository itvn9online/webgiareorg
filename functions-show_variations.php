<?php

/**
 * Hiển thị biến thể sản phẩm trong danh sách admin bằng AJAX.
 * Không sửa query chính — giữ nguyên phân trang/lọc/tìm kiếm của WooCommerce,
 * rồi sau khi bảng load xong thì nạp biến thể và append ngay dưới sản phẩm cha.
 */

/**
 * Kiểm tra có đang ở trang danh sách sản phẩm trong admin hay không
 * (wp-admin/edit.php?post_type=product).
 *
 * @return bool
 */
function WGR_is_admin_products_list()
{
    global $pagenow;

    if (!is_admin() || 'edit.php' !== $pagenow) {
        return false;
    }

    return isset($_GET['post_type']) && 'product' === $_GET['post_type'];
}

/**
 * Quyết định có nên hiển thị biến thể trong danh sách sản phẩm hay không.
 *
 * @return bool
 */
function WGR_should_show_variations_in_admin()
{
    if (!WGR_is_admin_products_list()) {
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
 * Thêm dropdown "Hiển thị biến thể / Ẩn biến thể" vào thanh bộ lọc.
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

/**
 * Lấy tên hiển thị ngắn gọn của biến thể (chỉ phần thuộc tính).
 *
 * @param WC_Product_Variation $product Biến thể cần lấy tên.
 * @return string
 */
function WGR_get_admin_variation_name($product)
{
    $attrs = wc_get_formatted_variation($product, true, false);
    if ('' !== $attrs) {
        return $attrs;
    }

    $name = $product->get_name();
    if (false !== strpos($name, ' - ')) {
        $parts = explode(' - ', $name);
        return (string) end($parts);
    }

    return $name;
}

/**
 * Danh sách cột của bảng sản phẩm (để build HTML dòng biến thể khớp cột).
 *
 * @return array<string, string>
 */
function WGR_admin_product_list_columns()
{
    $columns = get_column_headers('edit-product');

    if (!is_array($columns) || empty($columns)) {
        $columns = array(
            'cb'          => '<input type="checkbox" />',
            'thumb'       => '<span class="wc-image tips">' . esc_html__('Image', 'woocommerce') . '</span>',
            'name'        => esc_html__('Name', 'woocommerce'),
            'sku'         => esc_html__('SKU', 'woocommerce'),
            'is_in_stock' => esc_html__('Stock', 'woocommerce'),
            'price'       => esc_html__('Price', 'woocommerce'),
            'product_cat' => esc_html__('Categories', 'woocommerce'),
            'product_tag' => esc_html__('Tags', 'woocommerce'),
            'featured'    => '<span class="wc-featured tips">' . esc_html__('Featured', 'woocommerce') . '</span>',
            'date'        => esc_html__('Date'),
        );
    }

    return $columns;
}

/**
 * Nội dung 1 ô cột cho dòng biến thể.
 *
 * @param string               $column  Tên cột.
 * @param WC_Product_Variation $product Biến thể.
 * @return string
 */
function WGR_admin_variation_column_html($column, $product)
{
    switch ($column) {
        // case 'cb':
            // return '<input type="checkbox" disabled="disabled" />';

        case 'thumb':
            return $product->get_image('thumbnail');

        case 'name':
            return '<strong class="row-title">' . esc_html(WGR_get_admin_variation_name($product)) . '</strong>';

        case 'sku':
            $sku = $product->get_sku();
            return $sku ? esc_html($sku) : '<span class="na">&ndash;</span>';

        case 'price':
            $price_html = $product->get_price_html();
            return $price_html ? wp_kses_post($price_html) : '<span class="na">&ndash;</span>';

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

            return wp_kses_post($stock_html);

        default:
            return '<span class="na wgr-na" aria-hidden="true">&ndash;</span>';
    }
}

/**
 * Build HTML <tr> cho một biến thể.
 *
 * @param WC_Product_Variation $product  Biến thể.
 * @param array                $columns  Cột bảng.
 * @return string
 */
function WGR_admin_variation_row_html($product, $columns)
{
    $post_id = $product->get_id();
    $classes = array(
        'iedit',
        'author-self',
        'level-0',
        'post-' . $post_id,
        'type-product_variation',
        'status-' . esc_attr($product->get_status()),
        'hentry',
        'wgr-admin-variation-row',
    );

    $html = '<tr id="post-' . absint($post_id) . '" class="' . esc_attr(implode(' ', $classes)) . '" data-parent="' . absint($product->get_parent_id()) . '">';

    foreach ($columns as $column_name => $column_label) {
        $primary = ('name' === $column_name) ? ' column-primary' : '';

        if ('cb' === $column_name) {
            $html .= '<th scope="row" class="check-column">' . WGR_admin_variation_column_html($column_name, $product) . '</th>';
            continue;
        }

        $html .= '<td class="' . esc_attr($column_name) . ' column-' . esc_attr($column_name) . $primary . '" data-colname="' . esc_attr(wp_strip_all_tags((string) $column_label)) . '">';
        $html .= WGR_admin_variation_column_html($column_name, $product);
        $html .= '</td>';
    }

    $html .= '</tr>';

    return $html;
}

add_action('wp_ajax_wgr_load_product_variations', 'WGR_ajax_load_product_variations');
/**
 * AJAX: nhận danh sách product ID đang hiện trên page, trả về HTML biến thể
 * theo từng parent để JS append ngay dưới dòng cha.
 */
function WGR_ajax_load_product_variations()
{
    if (!current_user_can('edit_products')) {
        wp_send_json_error(array('message' => 'Forbidden'), 403);
    }

    check_ajax_referer('wgr_load_product_variations', 'nonce');

    if (!function_exists('wc_get_product')) {
        wp_send_json_error(array('message' => 'WooCommerce missing'), 400);
    }

    $product_ids = isset($_POST['product_ids']) ? (array) wp_unslash($_POST['product_ids']) : array();
    $product_ids = array_values(array_unique(array_filter(array_map('absint', $product_ids))));

    if (empty($product_ids)) {
        wp_send_json_success(array('rows' => array()));
    }

    // Giới hạn để tránh request quá lớn từ 1 page admin.
    $product_ids = array_slice($product_ids, 0, 100);

    // Ưu tiên thứ tự cột từ thead phía client để khớp đúng bảng đang hiển thị.
    $columns = WGR_admin_product_list_columns();
    if (!empty($_POST['columns']) && is_array($_POST['columns'])) {
        $requested = array_map('sanitize_key', wp_unslash($_POST['columns']));
        $ordered   = array();
        foreach ($requested as $col) {
            if ('' === $col) {
                continue;
            }
            $ordered[$col] = isset($columns[$col]) ? $columns[$col] : $col;
        }
        if (!empty($ordered)) {
            $columns = $ordered;
        }
    }

    $rows = array();

    foreach ($product_ids as $parent_id) {
        $parent = wc_get_product($parent_id);
        if (!$parent || !$parent->is_type('variable')) {
            continue;
        }

        $variation_ids = $parent->get_children();
        if (empty($variation_ids)) {
            continue;
        }

        $html = '';
        foreach ($variation_ids as $variation_id) {
            $variation = wc_get_product($variation_id);
            if (!$variation || !$variation->is_type('variation')) {
                continue;
            }

            $html .= WGR_admin_variation_row_html($variation, $columns);
        }

        if ('' !== $html) {
            $rows[(string) $parent_id] = $html;
        }
    }

    wp_send_json_success(array('rows' => $rows));
}

add_action('admin_footer-edit.php', 'WGR_admin_variation_list_script');
/**
 * Nạp JS: lấy product ID từ #the-list, gọi AJAX, append biến thể dưới từng sản phẩm cha.
 */
function WGR_admin_variation_list_script()
{
    if (!WGR_should_show_variations_in_admin()) {
        return;
    }

    $config = array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('wgr_load_product_variations'),
    );
?>
    <script>
        window.wgrShowVariations = <?php echo wp_json_encode($config); ?>;
    </script>
<?php
    WGR_adds_js(
        array(
            WGR_BASE_PATH . 'public/admin/js/show_variations.js',
        ),
        array(
            'cdn' => CDN_BASE_URL,
        )
    );
}
