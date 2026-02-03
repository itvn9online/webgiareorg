<?php

/**
 * Công cụ hỗ trợ
 */

// nếu có tham số goto_menu thì chuyển đến menu tương ứng
if (isset($_GET['goto_menu'])) {
    $goto_menu = trim($_GET['goto_menu']);
    if ($goto_menu != '') {
        // Lấy menu theo slug
        $data_menu = get_term_by('slug', $goto_menu, 'nav_menu');
        // print_r($data_menu);


        // Nếu không tìm thấy và có "menu-" ở đầu, thử cắt bỏ
        if (!$data_menu && substr($goto_menu, 0, 5) == 'menu-') {
            $goto_menu_clean = substr($goto_menu, 5);
            $data_menu = get_term_by('slug', $goto_menu_clean, 'nav_menu');
        }

        // Nếu tìm thấy menu
        if ($data_menu && !is_wp_error($data_menu)) {
            // print_r($data_menu);
            // Chuyển đến link edit menu
            wp_redirect(admin_url() . 'nav-menus.php?action=edit&menu=' . $data_menu->term_id);
            exit();
        } else {
            echo '<p class="redcolor">Không tìm thấy menu với slug: ' . esc_html($goto_menu) . '</p>';
        }
    }
}

?>
<h1>Công cụ hỗ trợ</h1>
<ol>
    <?php

    // 
    $admin_tool_url = admin_url() . 'admin.php?page=eb-tools&tool_action=';
    foreach (
        [
            'generate-demo-post' => 'Tạo hoặc XÓA dữ liệu Bài viết/ Sản phẩm demo ngẫu nhiên',
            // 'generate-demo-product' => 'Tạo Products demo ngẫu nhiên',
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
