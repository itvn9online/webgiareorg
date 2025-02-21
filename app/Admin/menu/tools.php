<?php

/**
 * Công cụ hỗ trợ
 */

?>
<h1>Công cụ hỗ trợ</h1>
<ol>
    <?php

    // 
    $admin_tool_url = admin_url() . 'admin.php?page=eb-tools&tool_action=';
    foreach (
        [
            'echbaydotcom-products-to-webgiareorg' => 'Chuyển dữ liệu Sản phẩm từ echbaydotcom sang webgiareorg',
            'echbaydotcom-blogs-to-webgiareorg' => 'Chuyển dữ liệu Bài viết từ echbaydotcom sang webgiareorg',
        ] as $k => $v
    ) {
    ?>
        <li><a href="<?php echo $admin_tool_url . $k; ?>"><?php echo $v; ?></a></li>
    <?php
    }

    ?>
</ol>
<?php

// 
if (isset($_GET['tool_action']) && ($tool_action = $_GET['tool_action']) != '') {
    $file_action = __DIR__ . '/tool-includes/' . $tool_action . '.php';
    if (is_file($file_action)) {
        include $file_action;
    } else {
?>
        <p class="redcolor bold text-center medium18">Module <?php echo $tool_action; ?> not found!</p>
<?php
    }
}
