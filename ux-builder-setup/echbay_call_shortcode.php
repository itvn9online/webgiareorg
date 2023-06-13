<?php
/*
 * Echo shortcode trong flatsome thay vì dùng text của flatsome, hay bị đính kèm thẻ P
 */
function add_echbay_call_shortcode()
{
    // các shortcode được build sẵn theo framework của webgiareorg
    $arr_ebe_function = [
        'wgr_h1_breadcrumb', // breadcrumb có kèm thẻ h1 từ title
        'wgr_h2_breadcrumb', // breadcrumb có kèm thẻ h2 từ title -> tương tự như thẻ h1 -> khác chỗ nó là thẻ h2
        'wgr_breadcrumb', // breadcrumb không bao gồm h1
        'WGR_product_comment',
        'WGR_product_content',
        'wgr_same_cat',
    ];
    $ops_list = [
        '' => '- Chọn shortcode -',
    ];
    foreach ($arr_ebe_function as $v) {
        $ops_list[$v] = $v;
    }

    //
    add_ux_builder_shortcode(
        'echbay_call_shortcode',
        array(
            'name' => 'Echbay call Shortcode',
            'category' => 'Echbay',
            //'priority' => 1,
            'options' => array(
                'call_shortcode' => array(
                    'type' => 'select',
                    'heading' => 'Shortcode dựng sẵn',
                    'default' => '',
                    'options' => $ops_list,
                ),
                'for_shortcode' => array(
                    'type' => 'textfield',
                    'heading' => 'Shortcode',
                    'default' => '',
                    'placeholder' => 'Shortcode',
                ),
                'custom_class' => array(
                    'type' => 'textfield',
                    'heading' => 'Class CSS',
                    'default' => '',
                    'placeholder' => 'Tùy chỉnh CSS',
                ),
            ),
        )
    );
}
add_action('ux_builder_setup', 'add_echbay_call_shortcode');

// gọi short code từ UX Builder
function action_echbay_call_shortcode($atts)
{
    extract(
        shortcode_atts(
            array(
                'call_shortcode' => '',
                'for_shortcode' => '',
                'custom_class' => '',
            ),
            $atts
        )
    );

    //
    if ($call_shortcode != '') {
        $for_shortcode = $call_shortcode;
    }
    if ($for_shortcode == '') {
        return __FUNCTION__ . ' for_shortcode is empty!';
    }

    // gọi tới function của widget shortcode
    $html = do_shortcode('[' . ltrim(rtrim($for_shortcode, ']'), '[') . ']');
    if ($custom_class != '') {
        $html = '<div class="' . $custom_class . '">' . $html . '</div>';
    }
    return $html;
}
add_shortcode('echbay_call_shortcode', 'action_echbay_call_shortcode');
