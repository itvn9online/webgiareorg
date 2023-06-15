<?php
/*
 * Chức năng tạo google map
 */
function add_echbay_google_map()
{
    add_ux_builder_shortcode(
        'echbay_google_map',
        array(
            'name' => 'Echbay Google Map',
            'category' => 'Echbay',
            //'priority' => 1,
            //'type' => 'container',
            //'thumbnail' => flatsome_ux_builder_thumbnail('image_box'),
            //'wrap' => false,
            'options' => array(
                'map_height' => array(
                    'type' => 'textfield',
                    'heading' => 'Height',
                    'default' => '',
                    'placeholder' => '400',
                ),
                'map_latitude' => array(
                    'type' => 'textfield',
                    'heading' => 'Latitude',
                    'default' => '',
                    'placeholder' => '40.79028',
                ),
                'map_longitude' => array(
                    'type' => 'textfield',
                    'heading' => 'Longitude',
                    'default' => '',
                    'placeholder' => '-73.95972',
                ),
                'map_zoom' => array(
                    'type' => 'textfield',
                    'heading' => 'Zoom',
                    'default' => '',
                    'placeholder' => '10 - 30',
                ),
                'map_id' => array(
                    'type' => 'textfield',
                    'heading' => 'Map ID',
                    'default' => 'ebe_ggmap_id_' . time(),
                    'placeholder' => 'Auto create if empty',
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
add_action('ux_builder_setup', 'add_echbay_google_map');

// gọi short code từ UX Builder
function action_echbay_google_map($atts)
{
    extract(
        shortcode_atts(
            array(
                'map_height' => '',
                'map_latitude' => '',
                'map_longitude' => '',
                'map_zoom' => '',
                'map_id' => '',
                'custom_class' => '',
            ),
            $atts
        )
    );

    //
    if (empty($map_latitude)) {
        return 'Please Enter your latitude!';
    }
    if (empty($map_longitude)) {
        return 'Please Enter your longitude!';
    }

    // giá trị mặc định
    if (empty($map_height)) {
        $map_height = '400';
    }
    if (empty($map_zoom)) {
        $map_zoom = 17;
    }
    if (empty($map_id)) {
        $map_id = 'ebe_ggmap_id_' . time();
    }

    //
    $html = file_get_contents(__DIR__ . '/echbay_google_map.html', 1);
    foreach ([
        'map_latitude' => $map_latitude,
        'map_longitude' => $map_longitude,
        'map_height' => $map_height,
        'map_zoom' => $map_zoom,
        'map_id' => $map_id,
        'custom_class' => $custom_class,
    ] as $k => $v) {
        $html = str_replace('{{' . $k . '}}', $v, $html);
    }

    //
    return $html;
}
add_shortcode('echbay_google_map', 'action_echbay_google_map');
