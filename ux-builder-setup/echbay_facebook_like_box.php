<?php

/**
 * Chức năng tạo like box facebook
 */
function add_echbay_facebook_like_box()
{
    add_ux_builder_shortcode(
        'echbay_facebook_like_box',
        array(
            'name' => 'Echbay Facebook Like Box',
            'category' => 'Echbay',
            //'priority' => 1,
            //'type' => 'container',
            //'thumbnail' => flatsome_ux_builder_thumbnail('image_box'),
            //'wrap' => false,
            'options' => array(
                'fb_href' => array(
                    'type' => 'textfield',
                    'heading' => 'URL page',
                    'default' => '',
                    //'placeholder' => '',
                ),
                'fb_app_id' => array(
                    'type' => 'textfield',
                    'heading' => 'App Id',
                    'default' => '',
                    //'placeholder' => '',
                ),
                'fb_width' => array(
                    'type' => 'textfield',
                    'heading' => 'Width',
                    'default' => '',
                    'placeholder' => '340',
                ),
                'fb_height' => array(
                    'type' => 'textfield',
                    'heading' => 'Height',
                    'default' => '',
                    'placeholder' => '130',
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
add_action('ux_builder_setup', 'add_echbay_facebook_like_box');

// gọi short code từ UX Builder
function action_echbay_facebook_like_box($atts)
{
    extract(
        shortcode_atts(
            array(
                'fb_href' => '',
                'fb_app_id' => '',
                'fb_width' => '',
                'fb_height' => '',
                'custom_class' => '',
            ),
            $atts
        )
    );

    //
    if (empty($fb_href)) {
        return 'Please Enter your fan page URL!';
    }

    // giá trị mặc định
    if (empty($fb_width)) {
        $fb_width = '340';
    }
    if (empty($fb_height)) {
        $fb_height = '130';
    }

    //
    $html = file_get_contents(__DIR__ . '/echbay_facebook_like_box.html', 1);
    foreach (
        [
            'fb_href' => urlencode($fb_href),
            'fb_app_id' => $fb_app_id,
            'fb_width' => $fb_width,
            'fb_height' => $fb_height,
            'custom_class' => $custom_class,
        ] as $k => $v
    ) {
        $html = str_replace('{{' . $k . '}}', $v, $html);
    }

    //
    return $html;
}
add_shortcode('echbay_facebook_like_box', 'action_echbay_facebook_like_box');
