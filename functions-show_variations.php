<?php

/**
 * Hiển thị các sản phẩm biến thể trong danh sách sản phẩm trong admin.
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

add_action('manage_product_variation_posts_custom_column', 'WGR_admin_variation_column_content', 10, 2);
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
            $edit_link = get_edit_post_link($post_id);
            echo '<a href="' . esc_url($edit_link) . '">' . $product->get_image('thumbnail') . '</a>';
            break;

        case 'name':
            $edit_link  = get_edit_post_link($post_id);
            $parent_id  = $product->get_parent_id();
            $parent_link = $parent_id ? get_edit_post_link($parent_id) : '';
            echo '<strong><a class="row-title" href="' . esc_url($edit_link) . '">' . esc_html($product->get_formatted_name()) . '</a></strong>';
            if ($parent_id && $parent_link) {
                echo '<div class="wgr-variation-parent"><a href="' . esc_url($parent_link) . '">' . esc_html(get_the_title($parent_id)) . '</a></div>';
            }
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

        case 'product_cat':
        case 'product_tag':
            $taxonomy = ('product_cat' === $column) ? 'product_cat' : 'product_tag';
            $terms    = get_the_terms($product->get_parent_id(), $taxonomy);
            if (empty($terms) || is_wp_error($terms)) {
                echo '<span class="na">&ndash;</span>';
                break;
            }

            $termlist = array();
            foreach ($terms as $term) {
                $termlist[] = '<a href="' . esc_url(admin_url('edit.php?product_' . ('product_cat' === $taxonomy ? 'cat' : 'tag') . '=' . $term->slug . '&post_type=product')) . '">' . esc_html($term->name) . '</a>';
            }
            echo implode(', ', $termlist);
            break;

        case 'featured':
            echo '<span class="na">&ndash;</span>';
            break;

        case 'date':
            $timestamp = get_post_timestamp($post_id);
            if ($timestamp) {
                echo esc_html(date_i18n(get_option('date_format'), $timestamp));
            } else {
                echo '<span class="na">&ndash;</span>';
            }
            break;
    }
}

add_filter('post_row_actions', 'WGR_admin_variation_row_actions', 20, 2);
function WGR_admin_variation_row_actions($actions, $post)
{
    if (!WGR_should_show_variations_in_admin() || 'product_variation' !== $post->post_type) {
        return $actions;
    }

    $parent_id = wp_get_post_parent_id($post->ID);
    if ($parent_id) {
        $actions['parent'] = '<a href="' . esc_url(get_edit_post_link($parent_id)) . '">' . esc_html__('Sản phẩm cha', 'woocommerce') . '</a>';
    }

    $actions['id'] = sprintf(esc_html__('ID: %d', 'woocommerce'), $post->ID);

    return $actions;
}

add_filter('post_class', 'WGR_admin_variation_row_class', 10, 3);
function WGR_admin_variation_row_class($classes, $class, $post_id)
{
    if (!WGR_should_show_variations_in_admin() || 'product_variation' !== get_post_type($post_id)) {
        return $classes;
    }

    $classes[] = 'wgr-admin-variation-row';
    return $classes;
}

add_action('admin_head', 'WGR_admin_variation_list_styles');
function WGR_admin_variation_list_styles()
{
    if (!WGR_is_admin_products_list() || !WGR_should_show_variations_in_admin()) {
        return;
    }
?>
    <style>
        .post-type-product tr.type-product_variation.wgr-admin-variation-row .column-name {
            padding-left: 2em;
        }

        .post-type-product tr.type-product_variation.wgr-admin-variation-row .column-name .row-title::before {
            content: "↳ ";
            color: #787c82;
        }

        .post-type-product tr.type-product_variation .wgr-variation-parent {
            color: #787c82;
            font-size: 12px;
            margin-top: 4px;
        }
    </style>
<?php
}
