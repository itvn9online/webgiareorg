<?php

/**
 * Chức năng gọi tới các menu dựng sẵn của webgiareorg
 */
function add_echbay_call_menu()
{
    $ops_list = [
        0 => '- Chọn menu -',
    ];
    $a = wp_get_nav_menus();
    // print_r($a);
    foreach ($a as $k => $v) {
        $ops_list[$v->term_id] = $v->name;
    }

    //
    add_ux_builder_shortcode('echbay_call_menu', array(
        'name' => 'Echbay Call Menu',
        'category' => 'Echbay',
        'options' => array(
            'call_menu' => array(
                'type' => 'select',
                'heading' => 'Menu',
                'default' => 0,
                'options' => $ops_list,
            ),
            'menu_title' => array(
                'type' => 'textfield',
                'heading' => 'Custom Title',
                'default' => '',
                'placeholder' => 'Custom title',
            ),
            'auto_title' => array(
                'type' => 'checkbox',
                'heading' => 'Menu Title',
                'default' => 'false',
                'placeholder' => 'Menu title',
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
add_action('ux_builder_setup', 'add_echbay_call_menu');

// gọi short code từ UX Builder
function action_echbay_call_menu($atts)
{
    extract(shortcode_atts(array(
        'call_menu' => '',
        'menu_title' => '',
        'auto_title' => '',
        'custom_class' => '',
    ), $atts));

    // call_menu -> ID của menu cần gọi
    if (empty($call_menu)) {
        return __FUNCTION__ . ' call_menu is empty!';
    }

    // sử dụng cache cho menu -> tránh duplicate query
    $filename = '';
    if (WHY_EBCACHE_DISABLE == '' && function_exists('my_builder_path_cache')) {
        $filename = my_builder_path_cache(__FUNCTION__ .  $call_menu);

        //
        $html = WGR_my_cache($filename);
        if ($html !== false) {
            return $html;
        }
    }
    // return $filename;

    //
    $html = wp_nav_menu([
        // không echo -> lấy kết quả trả về để return
        'echo' => false,
        'menu' => $call_menu,
        'menu_class' => 'eb-set-menu-selected eb-menu cf',
    ]);

    // lấy tên menu từ term (bỏ chức năng lấy tự động vì 1 số trường hợp không muốn hiển thị tên menu)
    if ($menu_title == '' && $auto_title) {
        $menu_title = get_term($call_menu, 'nav_menu');
        if (is_object($menu_title) && isset($menu_title->name)) {
            $menu_title = $menu_title->name;
        } else {
            $menu_title = 'Menu ' . $call_menu;
        }
    }
    if ($menu_title != '') {
        $html = '<h4 class="echbayflatsome-title-menu">' . $menu_title . '</h4>' . $html;
    }

    if ($custom_class != '') {
        $html = '<div class="' . $custom_class . '">' . $html . '</div>';
    }

    // lưu cache nếu có path cache file
    if ($filename != '') {
        WGR_my_cache($filename, $html, mt_rand(120, 180));
    }

    //
    return $html;
}
add_shortcode('echbay_call_menu', 'action_echbay_call_menu');
