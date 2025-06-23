<?php

/**
 * 
 */
function add_echbay_item_contact()
{
    //
    add_ux_builder_shortcode(
        'echbay_item_contact',
        array(
            'name' => 'Echbay item Contact',
            'category' => 'Echbay',
            // 'info'      => '{{ label }}',
            'require'   => array('echbay_menu_contact'),
            // 'type' => 'container',
            'wrap' => false,
            'presets'   => array(
                array(
                    'name' => 'Default',
                    'content' => '[echbay_item_contact menu_text="Menu title"]',
                ),
            ),
            'options' => array(
                'menu_text' => array(
                    'type' => 'textfield',
                    'heading' => 'Text',
                    'default' => '',
                    // 'auto_focus' => true,
                ),
                'menu_icon' => array(
                    'type' => 'select',
                    'heading' => 'Icon',
                    'default' => '',
                    'options' => [
                        '' => 'None',
                        'icon-500px' => '500px',
                        'icon-angle-down' => 'Arrow Down',
                        'icon-angle-left' => 'Arrow Left',
                        'icon-angle-right' => 'Arrow Right',
                        'icon-angle-up' => 'Arrow Up',
                        'icon-certificate' => 'Certificate',
                        'icon-chat' => 'Chat',
                        'icon-checkmark' => 'Checkmark',
                        'icon-clock' => 'Clock',
                        'icon-cross' => 'Cross',
                        'icon-discord' => 'Discord',
                        'icon-dribbble' => 'Dribbble',
                        'icon-envelop' => 'Envelope',
                        'icon-expand' => 'Expand',
                        'icon-facebook' => 'Facebook',
                        'icon-feed' => 'Feed',
                        'icon-flickr' => 'Flickr',
                        'icon-gift' => 'Gift',
                        'icon-google-plus' => 'Google Plus',
                        'icon-heart' => 'Heart',
                        'icon-heart-o' => 'Heart Outline',
                        'icon-instagram' => 'Instagram',
                        'icon-linkedin' => 'linkedIn',
                        'icon-lock' => 'Lock',
                        'icon-map-pin-fill' => 'Map Pin',
                        'icon-menu' => 'Menu',
                        'icon-pen-alt-fill' => 'Pen',
                        'icon-phone' => 'Phone',
                        'icon-pinterest' => 'Pinterest',
                        'icon-play' => 'Play',
                        'icon-plus' => 'Plus',
                        'icon-search' => 'Search',
                        'icon-shopping-bag' => 'Shopping Bag',
                        'icon-shopping-basket' => 'Shopping Basket',
                        'icon-shopping-cart' => 'Shopping Cart',
                        'icon-skype' => 'Skype',
                        'icon-snapchat' => 'SnapChat',
                        'icon-star' => 'Star',
                        'icon-star-o' => 'Star Outline',
                        'icon-tag' => 'Tag',
                        'icon-telegram' => 'Telegram',
                        'icon-threads' => 'Threads',
                        'icon-tiktok' => 'TikTok',
                        'icon-tumblr' => 'Tumblr',
                        'icon-twitch' => 'Twitch',
                        'icon-twitter' => 'Twitter',
                        'icon-user' => 'User',
                        'icon-user-o' => 'User Outline',
                        'icon-vk' => 'VKontakte',
                        'icon-whatsapp' => 'WhatsApp',
                        'icon-x' => 'X (social media)',
                        'icon-youtube' => 'Youtube',
                        'icon-vnzalo' => 'Zalo',
                    ],
                ),
                'menu_link' => array(
                    'type' => 'textfield',
                    'heading' => 'URL',
                    'default' => '',
                ),
                'menu_tag' => array(
                    'type' => 'select',
                    'heading' => 'Tag',
                    'default' => 'div',
                    'options' => [
                        'div' => 'Div',
                        'h1' => 'H1',
                        'h2' => 'H2',
                        'h3' => 'H3',
                        'h4' => 'H4',
                        'h5' => 'H5',
                        'h6' => 'H6',
                        'p' => 'P',
                        'span' => 'Span',
                    ],
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
add_action('ux_builder_setup', 'add_echbay_item_contact');

// gọi short code từ UX Builder
function action_echbay_item_contact($atts)
{
    extract(
        shortcode_atts(
            array(
                'menu_text' => '',
                'menu_icon' => '',
                'menu_link' => '',
                'menu_tag' => '',
                'custom_class' => '',
            ),
            $atts
        )
    );

    //
    $the_link = $menu_text;
    $the_target = '_self';
    if ($menu_link != '') {
        if (strpos($menu_link, $_SERVER['HTTP_HOST']) === false) {
            $the_target = '_blank';
        }
    }
    if ($menu_text == '') {
        $menu_text = 'Lorem ipsum dolor';
    }
    $menu_text = '<span class="echbay-items-contact-text">' . $menu_text . '</span>';

    // 
    if ($menu_icon != '') {
        // nếu menu link ko được thiết lập thì tạo link tự động luôn
        if ($menu_link == '') {
            if ($menu_icon == 'icon-phone') {
                $menu_link = 'tel:' . $the_link;
            } else if ($menu_icon == 'icon-envelop') {
                $menu_link = 'mailto:' . $the_link;
            }
        }

        // 
        $menu_icon = '<i class="ux-menu-link__icon text-center ' . $menu_icon . '"></i> ';
    }

    // 
    $menu_text = $menu_icon . $menu_text;

    // 
    if ($menu_link != '') {
        $menu_text = '<a href="' . $menu_link . '" rel="nofollow" target="' . $the_target . '" class="echbay-items-contact-link" aria-label="External">' . $menu_text . '</a>';
    }

    // 
    if ($menu_tag == '') {
        $menu_tag = 'div';
    }
    $html = '<' . $menu_tag . ' class="' . trim($custom_class . ' echbay-items-contact-item') . '">' . $menu_text . '</' . $menu_tag . '>';

    //
    return $html;
}
add_shortcode('echbay_item_contact', 'action_echbay_item_contact');
