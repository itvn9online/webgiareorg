<h2>Chuyển dữ liệu Bài viết từ echbaydotcom sang webgiareorg</h2>
<?php

// 
global $wpdb;

// xác định id Bài viết từ pha trước
$min_id = isset($_GET['min_id']) ? $_GET['min_id'] : 0;

// lấy Bài viết có ID thấp nhất
$query = "SELECT ID, post_type, post_status
    FROM
        " . $wpdb->prefix . "posts
    WHERE
        post_type = 'post'
        AND ID > $min_id
    ORDER BY
        ID ASC
    LIMIT 0, 50";
// AND post_status = 'publish'
echo $query . '<br>' . "\n";

$data = $wpdb->get_results($query, OBJECT);
// print_r($data);

// 
$has_post = false;
foreach ($data as $v) {
    print_r($v);
    echo '<br>' . "\n";

    // 
    $has_post = true;
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
    print_r($metas);

    // 
    if (!empty($metas)) {
        // nếu có dữ liệu để update thì mới thực thi
        $run_update = false;

        // chưa có thumbnail thì mới update thumbnail
        $has_thumbnail = false;
        $product_avatar = '';

        // 
        foreach ($metas as $meta) {
            if ($meta->meta_key == '_thumbnail_id') {
                if (!empty($meta->meta_value)) {
                    $has_thumbnail = true;
                }
            }
        }

        // 
        if ($has_thumbnail === false && !empty($product_avatar)) {
            echo 'product_avatar: ' . $product_avatar . '<br>' . "\n";
            die(__FILE__ . ':' . __LINE__);
        }

        // 
        var_dump($run_update);
        echo '<br>' . "\n";
        if ($run_update === true) {
            // TEST
            // break;
        }
    }
}

// nếu xác định vẫn còn Bài viết để tiếp tục
if ($has_post === true) {
    var_dump($has_post);
    // echo $admin_tool_url . $tool_action . '&min_id=' . $min_id;
?>
    <script>
        setTimeout(() => {
            window.location = '<?php echo $admin_tool_url . $tool_action . '&min_id=' . $min_id; ?>';
        }, 9000);
    </script>
<?php
}
