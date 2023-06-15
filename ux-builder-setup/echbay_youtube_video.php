<?php
/*
 * Chức năng tạo video youtube
 * https://stackoverflow.com/questions/2068344/how-do-i-get-a-youtube-video-thumbnail-from-the-youtube-api
 */
function add_echbay_youtube_video()
{
    //
    $arr_ebe_function = [
        'medium',
        'medium_large',
        'large',
        'full',
    ];
    $ops_list = [];
    foreach ($arr_ebe_function as $v) {
        $ops_list[$v] = $v;
    }

    //
    add_ux_builder_shortcode(
        'echbay_youtube_video',
        array(
            'name' => 'Echbay Youtube Video',
            'category' => 'Echbay',
            //'priority' => 1,
            //'type' => 'container',
            //'thumbnail' => flatsome_ux_builder_thumbnail('image_box'),
            //'wrap' => false,
            'options' => array(
                'video_id' => array(
                    'type' => 'textfield',
                    'heading' => 'Video ID',
                    'default' => 'AoPiLg8DZ3A',
                    'placeholder' => 'Video URL',
                    'description' => 'Enter a Youtube video ID here. Video will open in a lightbox. Example: AoPiLg8DZ3A',
                ),
                'video_title' => array(
                    'type' => 'textfield',
                    'heading' => 'Title',
                    'default' => '',
                    'placeholder' => 'Video title',
                ),
                'video_img' => array(
                    'type' => 'image',
                    'heading' => 'Image',
                    'group' => 'background',
                    'param_name' => 'img',
                ),
                'video_size_img' => array(
                    'type' => 'select',
                    'heading' => 'Size',
                    'default' => 'medium',
                    'options' => $ops_list,
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
add_action('ux_builder_setup', 'add_echbay_youtube_video');

// gọi short code từ UX Builder
function action_echbay_youtube_video($atts)
{
    extract(
        shortcode_atts(
            array(
                'video_id' => '',
                'video_title' => '',
                'video_img' => '',
                'video_size_img' => '',
                'custom_class' => '',
            ),
            $atts
        )
    );

    //
    if (empty($video_id)) {
        $video_id = 'AoPiLg8DZ3A';
    }

    //
    $video_src_img = '';
    if (!empty($video_img)) {
        //echo $video_img;

        //
        $video_src_img = wp_get_attachment_image($video_img, $video_size_img);
    } else {
        $video_src_img = '<img width="800" height="450" src="data:image/svg+xml,%3Csvg%20viewBox%3D%220%200%20800%20450%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3C%2Fsvg%3E" data-src="https://img.youtube.com/vi/' . $video_id . '/hqdefault.jpg" class="lazy-load attachment-large size-large" alt="' . $video_title . '" loading="lazy" srcset="" data-srcset="https://img.youtube.com/vi/' . $video_id . '/hqdefault.jpg 800w, https://img.youtube.com/vi/' . $video_id . '/0.jpg 300w, https://img.youtube.com/vi/' . $video_id . '/mqdefault.jpg 768w" sizes="(max-width: 800px) 100vw, 800px" />';
    }
    //echo $video_src_img;

    //
    $html = '<div class="img has-hover x md-x lg-x y md-y lg-y"> <a class="open-video" href="https://www.youtube.com/watch?v=' . $video_id . '">
    <div class="img-inner image-cover dark" style="padding-top:56.25%;"> ' . $video_src_img . '
        <div class="overlay" style="background-color: rgba(0,0,0,.2)"></div>
        <div class="absolute no-click x50 y50 md-x50 md-y50 lg-x50 lg-y50 text-shadow-2">
            <div class="overlay-icon"> <i class="icon-play"></i> </div>
        </div>
    </div>
    </a></div>';

    if ($video_title != '') {
        $html .= '<h4 class="echbayflatsome-title-video">' . $video_title . '</h4>';
    }

    if ($custom_class != '') {
        $html = '<div class="' . $custom_class . '">' . $html . '</div>';
    }

    //
    return $html;
}
add_shortcode('echbay_youtube_video', 'action_echbay_youtube_video');
