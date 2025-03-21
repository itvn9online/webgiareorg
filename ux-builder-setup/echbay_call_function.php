<?php

/**
 * Chức năng gọi tới các function dựng sẵn của webgiareorg
 */
function add_echbay_call_function()
{
    $arr_ebe_function = [
        'WGR_get_cart_url',
        'WGR_get_search_form',
        'WGR_get_myaccount_page',
        //
        //'EBE_get_html_logo',
        //'EBE_echbay_top_menu',
        //'EBE_echbay_footer_menu',
        //'EBE_get_html_search',
        //'EBE_get_html_cart',
        //'EBE_get_html_profile',
        //'EBE_html_address',
        //'EBE_get_html_address',
        //'WGR_get_quick_register',
        //'WGR_get_footer_social',
        //'WGR_get_fb_like_box',
        //'EBE_echbay_license',
    ];
    $ops_list = [
        '' => '- Chọn function -',
    ];
    foreach ($arr_ebe_function as $v) {
        $ops_list[$v] = $v;
    }

    //
    add_ux_builder_shortcode('echbay_call_function', array(
        'name' => 'Echbay Call Function',
        'category' => 'Echbay',
        'options' => array(
            'call_function' => array(
                'type' => 'select',
                'heading' => 'Function',
                'default' => '',
                'options' => $ops_list,
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
add_action('ux_builder_setup', 'add_echbay_call_function');

// gọi short code từ UX Builder
function action_echbay_call_function($atts)
{
    extract(shortcode_atts(array(
        'call_function' => '',
        'custom_class' => '',
    ), $atts));

    //
    if ($call_function == '') {
        return __FUNCTION__ . ' call_function is empty!';
    }
    //echo $call_function . ' bbbbbbbbbbbbbb <br>' . PHP_EOL;

    //
    if (!function_exists($call_function)) {
        return __FUNCTION__ . ' function ' . $call_function . ' not exist!';
    }

    //
    $html = $call_function();
    //echo '<aaaaaaaaaaaa>' . $html . '</aaaaaaaaaaaa>';
    if ($custom_class != '') {
        $html = '<div class="' . $custom_class . '">' . $html . '</div>';
    }

    return $html;
}
add_shortcode('echbay_call_function', 'action_echbay_call_function');
