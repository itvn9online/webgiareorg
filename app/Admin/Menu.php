<?php

function func_include_wgr_private_code()
{
    $page = isset($_GET['page']) ? $_GET['page'] : '';
    if ($page == '') {
        echo 'page EMPTY!';
        return false;
    }
    $page = str_replace('eb-', '', $page);
    //echo $page;

    //
    $inc = __DIR__ . '/menu/' . $page . '.php';
    if (!file_exists($inc)) {
        echo 'file not exists! ' . $inc;
        return false;
    }

    //
    include $inc;

    //
    return true;
}

function WGR_create_admin_menu()
{
    // hiển thị menu dựa theo quyền
    // từ quyền tác giả là được vào xem đơn
    if (current_user_can('publish_posts')) {
        $parent_slug = 'eb-order';
    }
    // dưới đó thì chỉ xem thông tin cơ bản
    else if (current_user_can('edit_posts')) {
        $parent_slug = 'eb-dashboard';
    }
    // dưới nữa thì cho xem giới thiệu
    else {
        $parent_slug = 'eb-about';
    }

    /*
     * EchBay menu -> mọi người đều có thể nhìn thấy menu này
     */
    add_menu_page('Danh sách đơn hàng', PARTNER_WEBSITE, 'read', $parent_slug, 'func_include_wgr_private_code', NULL, 6);


    /*
     * submenu -> Super Admin, Administrator, Editor, Author
     */
    add_submenu_page($parent_slug, 'Danh sách Đơn hàng', 'Đơn hàng', 'publish_posts', 'eb-order', 'func_include_wgr_private_code');


    /*
     * submenu -> Super Admin, Administrator, Editor, Author, Contributor
     */
    //add_submenu_page($parent_slug, 'Tổng quan về website', 'Tổng quan', 'edit_posts', 'eb-dashboard', 'func_include_wgr_private_code');


    // Danh sách thành viên trên website -> một dạng dữ liệu khác để tiện quản lý -> Super Admin, Administrator, Editor
    //add_submenu_page($parent_slug, 'Danh sách Thành viên/ Khách hàng', 'Thành viên', 'publish_pages', 'eb-members', 'func_include_wgr_private_code');


    // menu chỉnh sửa sản phẩm nhanh -> Super Admin, Administrator, Editor
    add_submenu_page($parent_slug, 'Danh sách Sản phẩm', 'Sản phẩm', 'publish_pages', 'eb-products', 'func_include_wgr_private_code');


    /*
     * Super Admin, Administrator, Editor
     */
    add_submenu_page($parent_slug, 'Cấu hình website', 'Cấu hình website', 'publish_pages', 'eb-config', 'func_include_wgr_private_code');

    //add_submenu_page($parent_slug, 'Cài đặt và chỉnh sửa giao diện mặc định', 'Cài đặt giao diện', 'manage_options', 'eb-config_theme', 'func_include_wgr_private_code');

    //add_submenu_page($parent_slug, 'Các chức năng danh cho kỹ thuật viên', 'Kỹ thuật', 'manage_options', 'eb-coder', 'func_include_wgr_private_code');

    add_submenu_page($parent_slug, 'Thông tin Server', 'Thông tin Server', 'publish_pages', 'eb-server', 'func_include_wgr_private_code');


    /*
     * Mọi người đều có thể nhìn thấy menu này
     */
    add_submenu_page($parent_slug, 'Giới thiệu về tác giả', 'Giới thiệu', 'read', 'eb-about', 'func_include_wgr_private_code');


    /*
     * Bản nâng cao thì chỉ cần admin nhìn thôi, người khác không quan trọng
     */
    //add_submenu_page($parent_slug, 'Phiên bản cao cấp, hỗ trợ nhiều tính năng hơn', 'Phiên bản ', 'manage_options', 'eb-licenses', 'func_include_wgr_private_code');

    //
    //add_submenu_page(null, '', '', 'manage_options', 'wgr-version-flatsome', '__wgr_v2_version_flatsome');
}

// thêm menu vào admin
add_filter('admin_menu', 'WGR_create_admin_menu');


// Tạo page để xem thông tin phiên bản
function __wgr_v2_version_flatsome()
{
    //$a = file_get_contents('https://raw.githubusercontent.com/itvn9online/webgiareorg/main/changes.txt');
    $a = file_get_contents('https://webgiare.org/wp-content/themes/flatsome/changes.txt');
    echo nl2br($a);
}
