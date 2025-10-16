<h2>Chuyển dữ liệu Sản phẩm từ echbaydotcom sang webgiareorg</h2>
<?php

// 
global $wpdb;

// xác định id sản phẩm từ pha trước
$min_id = isset($_GET['min_id']) ? $_GET['min_id'] : 0;

// 
$query = "UPDATE
        `" . $wpdb->prefix . "posts`
    SET
        `comment_status` = 'open'
    WHERE
        `post_status` = 'publish'
        AND `post_type` = 'product'";
echo $query . '<br>' . "\n";

// lấy sản phẩm có ID thấp nhất
$query = "SELECT ID, post_type, post_status
    FROM
        " . $wpdb->prefix . "posts
    WHERE
        post_type = 'product'
        AND ID > $min_id
    ORDER BY
        ID ASC
    LIMIT 0, 50";
// AND post_status = 'publish'
echo $query . '<br>' . "\n";

$data = $wpdb->get_results($query, OBJECT);
// print_r($data);

// 
$has_product = false;
foreach ($data as $v) {
    print_r($v);
    echo '<br>' . "\n";

    // 
    $has_product = true;
    $min_id = $v->ID;

    // 
    $query = "SELECT *
    FROM
        " . $wpdb->prefix . "postmeta
    WHERE
        post_id = '" . $min_id . "'";
    // AND meta_key LIKE '_eb_%'";
    echo $query . '<br>' . "\n";

    $metas = $wpdb->get_results($query, OBJECT);
    // print_r($metas);

    // 
    if (!empty($metas)) {
        // nếu có dữ liệu để update thì mới thực thi
        $run_update = false;

        // 
        $regular_price = 0;
        $regular_has_price = false;
        $sale_price = 0;
        $sale_has_price = false;
        $has_price = false;
        // chưa có thumbnail thì mới update thumbnail
        $has_thumbnail = false;
        $product_avatar = '';
        // 
        $has_stock = false;
        $product_quantity = 0;
        // 
        $has_sku = false;
        $product_sku = 0;
        // 
        $has_gallery = false;
        $product_gallery = [];

        // 
        foreach ($metas as $meta) {
            if ($meta->meta_key == '_eb_product_price') {
                $sale_price = $meta->meta_value * 1;
            } else if ($meta->meta_key == '_eb_product_oldprice') {
                $regular_price = $meta->meta_value * 1;
            } else if ($meta->meta_key == '_eb_product_gallery') {
                // xử lý phần gallery từ bản cũ sang bản mới
                $gallerys = explode(' wp-image-', $meta->meta_value);
                foreach ($gallerys as $gallery) {
                    $gallery = explode('"', $gallery)[0];
                    if (is_numeric($gallery)) {
                        $product_gallery[] = $gallery;
                    }
                }
                $gallerys = explode(' id="attachment_', $meta->meta_value);
                foreach ($gallerys as $gallery) {
                    $gallery = explode('"', $gallery)[0];
                    if (is_numeric($gallery)) {
                        $product_gallery[] = $gallery;
                    }
                }
                // print_r($product_gallery);
                $product_gallery = array_unique($product_gallery);
                // print_r($product_gallery);
                // die(__FILE__ . ':' . __LINE__);
            } else if ($meta->meta_key == '_eb_product_quantity') {
                $product_quantity = $meta->meta_value * 1;
            } else if ($meta->meta_key == '_eb_product_sku') {
                $product_sku = $meta->meta_value;
            } else if ($meta->meta_key == '_eb_product_avatar') {
                $product_avatar = $meta->meta_value;
            } else if ($meta->meta_key == '_thumbnail_id') {
                if (!empty($meta->meta_value)) {
                    $has_thumbnail = true;
                }
            } else if ($meta->meta_key == '_regular_price') {
                if (!empty($meta->meta_value)) {
                    $regular_has_price = true;
                }
            } else if ($meta->meta_key == '_sale_price') {
                if (!empty($meta->meta_value)) {
                    $sale_has_price = true;
                }
            } else if ($meta->meta_key == '_price') {
                if (!empty($meta->meta_value)) {
                    $has_price = true;
                }
            } else if ($meta->meta_key == '_stock') {
                if (!empty($meta->meta_value)) {
                    $has_stock = true;
                }
            } else if ($meta->meta_key == '_sku') {
                if (!empty($meta->meta_value)) {
                    $has_sku = true;
                }
            } else if ($meta->meta_key == '_product_image_gallery') {
                if (!empty($meta->meta_value)) {
                    $has_gallery = true;
                }
            }
        }

        // Get an instance of the WC_Product object
        $product = wc_get_product($min_id);
        // print_r($product);

        // xử lý phần giá bán nếu tìm thấy
        if ($regular_price > 0) {
            if ($sale_price > $regular_price) {
                $old_price = $regular_price;
                $regular_price = $sale_price;
                $sale_price = $old_price;
            }
        } else if ($sale_price > 0) {
            // nếu có giá sale mà ko có giá cũ
            // thiết lập 1 giá thôi -> bỏ giá sale
            $regular_price = $sale_price;
            $sale_price = 0;
        }

        // 
        if ($has_thumbnail === false && !empty($product_avatar)) {
            echo 'product_avatar: ' . $product_avatar . '<br>' . "\n";
            die(__FILE__ . ':' . __LINE__);
        }

        // 
        if ($has_stock === false && $product_quantity > 0) {
            echo 'product_quantity: ' . $product_quantity . '<br>' . "\n";
            $product->set_manage_stock(true);
            $product->set_stock_quantity($product_quantity);
            // $product->set_stock_status('outofstock');
            $product->set_stock_status('instock');
            $run_update = true;
        }

        // 
        if ($has_sku === false && !empty($product_sku)) {
            echo 'product_sku: ' . $product_sku . '<br>' . "\n";
            // SKU ko được trùng lặp nên hơi khó update
            // $product->set_sku($product_sku);
            // $run_update = true;
        }

        // 
        if ($has_gallery === false && !empty($product_gallery)) {
            echo 'product_gallery: ' . '<br>' . "\n";
            print_r($product_gallery);
            echo '<br>' . "\n";
            $product->set_gallery_image_ids($product_gallery);
            $run_update = true;
        }

        // 
        if ($regular_price > 0) {
            echo 'regular_price: ' . $regular_price . '<br>' . "\n";

            // Set product sale price
            if ($sale_price > 0) {
                echo 'sale_price: ' . $sale_price . '<br>' . "\n";

                // 
                if ($sale_has_price === false) {
                    $product->set_sale_price($sale_price);
                    $run_update = true;
                }

                if ($has_price === false) {
                    $product->set_price($sale_price); // Set active price with sale price
                    $run_update = true;
                }
            } else if ($has_price === false) {
                $product->set_price($regular_price); // Set active price with regular price
                $run_update = true;
            }
            // Set product regular price
            if ($regular_has_price === false) {
                $product->set_regular_price($regular_price);
                $run_update = true;
            }
        }

        // 
        var_dump($run_update);
        echo '<br>' . "\n";
        if ($run_update === true) {
            // Sync data, refresh caches and saved data to the database
            $product->save();

            // TEST
            // break;
        }
    }
}

// nếu xác định vẫn còn sản phẩm để tiếp tục
if ($has_product === true) {
    var_dump($has_product);
    // echo $admin_tool_url . $tool_action . '&min_id=' . $min_id;
?>
    <script>
        setTimeout(() => {
            window.location = '<?php echo $admin_tool_url . $tool_action . '&min_id=' . $min_id; ?>';
        }, 9000);
    </script>
<?php
}
