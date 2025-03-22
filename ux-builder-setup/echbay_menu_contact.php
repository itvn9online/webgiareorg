<?php

/**
 * Dùng để tạo menu tương tự ux_menu của flatsome nhưng hỗ trợ cả text thuần để tối ưu SEO
 */
function add_echbay_menu_contact()
{
    add_ux_builder_shortcode(
        'echbay_menu_contact',
        array(
            'type' => 'container',
            'name' => 'Echbay Menu Contact',
            'category' => 'Echbay',
            'allow' => array('echbay_item_contact'),
            'wrap' => false,
            'nested'    => false,
            'options' => array(
                'custom_class' => array(
                    'type' => 'textfield',
                    'heading' => 'Class CSS',
                    'default' => '',
                    'placeholder' => 'Tùy chỉnh CSS',
                    'auto_focus' => true,
                ),
            ),
        )
    );
}
add_action('ux_builder_setup', 'add_echbay_menu_contact');

// gọi short code từ UX Builder
function action_echbay_menu_contact($atts, $content)
{
    extract(
        shortcode_atts(
            array(
                'custom_class' => '',
            ),
            $atts
        )
    );

    //
    $html = '<div class="' . trim($custom_class . ' echbay-menu-contact') . '">' . do_shortcode($content) . '</div>';

    //
    return $html;
}
add_shortcode('echbay_menu_contact', 'action_echbay_menu_contact');
