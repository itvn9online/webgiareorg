<?php

// kiểm tra nạp footer và kích hoạt cache cho web
include __DIR__ . '/header_cache.php';

/*
 * @daidq - 0984533228 - itvn9online@gmail.com
 * Chỉnh sửa và phát triển theo hướng chuyên cho thị trường Việt Nam
 * Bên dưới là header của flatsome
 */

?>
<!DOCTYPE html>
<!--[if IE 9 ]> <html <?php language_attributes(); ?> class="ie9 <?php flatsome_html_classes(); ?>"> <![endif]-->
<!--[if IE 8 ]> <html <?php language_attributes(); ?> class="ie8 <?php flatsome_html_classes(); ?>"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!-->
<html <?php language_attributes(); ?> class="
<?php flatsome_html_classes(); ?>">
<!--<![endif]-->

<head>
    <meta charset="<?php bloginfo('charset'); ?>" />
    <link rel="profile" href="http://gmpg.org/xfn/11" />
    <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
    <base href="<?php echo get_site_url(); ?>/" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php

    // đặt tham số này để không nạp lại base nữa
    //define( 'HAS_BASE_HREF', true );

    // nạp phần css inline để phục vụ cho bản mobile
    ?>
    <style>
        <?php echo file_get_contents(WGR_BASE_PATH . 'public/css/mobile-usability.css', 1);
        ?>
    </style>
    <?php

    //
    wp_head();

    // nếu có lệnh này trong child-theme -> sẽ add fontawesome 4
    if (defined('ADD_FONT_AWESOME4')) {
        // nạp file font theo kiểu inline
        $font_awesome_before = WGR_get_add_css(WGR_BASE_PATH . 'public/thirdparty/awesome47/css/font-awesome.before.css', [
            'get_content' => 1
        ]);
        $font_awesome_before = str_replace('../fonts/', WGR_BASE_URI . 'public/thirdparty/awesome47/fonts/', $font_awesome_before);
        echo $font_awesome_before;
    } else {
    ?>
        <!-- Font Awesome4 disable by webgiareorg default -->
        <?php
    }


    // nạp một số css ở dạng preload
    $arr_preload_bootstrap = [
        defined('ADD_FONT_AWESOME4') ? CDN_BASE_URL . WGR_BASE_URI . 'public/thirdparty/awesome47/css/font-awesome.min.css?v=4.7' : '',

        // bản full
        //CDN_BASE_URL . 'thirdparty/bootstrap-5.1.3/css/bootstrap.min.css',
        //'thirdparty/bootstrap-5.1.3/css/bootstrap.rtl.min.css',

        // các module đơn lẻ
        //'thirdparty/bootstrap-5.1.3/css/bootstrap-grid.min.css',
        //'thirdparty/bootstrap-5.1.3/css/bootstrap-grid.rtl.min.css',
        //'thirdparty/bootstrap-5.1.3/css/bootstrap-reboot.min.css',
        //'thirdparty/bootstrap-5.1.3/css/bootstrap-reboot.rtl.min.css',
        //'thirdparty/bootstrap-5.1.3/css/bootstrap-utilities.min.css',
        //'thirdparty/bootstrap-5.1.3/css/bootstrap-utilities.rtl.min.css',

        //
        CDN_BASE_URL . WGR_BASE_URI . 'public/css/d.css?v=' . filemtime(WGR_BASE_PATH . 'public/css/d.css'),
    ];

    foreach ($arr_preload_bootstrap as $v) {
        if ($v != '') {
        ?>
            <link rel="preload" as="style" onload="this.onload=null;this.rel='stylesheet'" href="<?php echo $v; ?>" />
    <?php
        }
    }


    // nạp các file css dùng chung cho toàn website
    WGR_adds_css([
        //WGR_BASE_PATH . 'public/thirdparty/awesome47/css/font-awesome.min.css',
        //WGR_BASE_PATH . 'public/css/d.css',
        WGR_CHILD_PATH . 'css/d.css',
    ], [
        'cdn' => CDN_BASE_URL,
    ]);

    // nạp css dùng cho từng loại trang
    if (is_home() || is_front_page()) {
        WGR_adds_css([
            WGR_CHILD_PATH . 'css/home.css',
        ], [
            'cdn' => CDN_BASE_URL,
        ]);
    }

    // các file js bắt buộc phải nạp trước
    WGR_adds_js([
        WGR_BASE_PATH . 'public/javascript/functions.js',
    ], [
        'cdn' => CDN_BASE_URL,
    ]);

    ?>
</head>

<body <?php body_class(); ?>>
    <?php do_action('flatsome_after_body_open'); ?>
    <?php wp_body_open(); ?>
    <a class="skip-link screen-reader-text" href="#main">
        <?php esc_html_e('Skip to content', 'flatsome'); ?>
    </a>
    <div id="wrapper">
        <?php

        /*
         * nạp top của webgiareorg (nếu có)
         */
        if (file_exists(WGR_CHILD_PATH . 'Views/top.php')) {
        ?>
            <header id="header" class="header-wrapper wgr-primary-header">
                <?php
                include WGR_CHILD_PATH . 'Views/top.php';
                ?>
            </header>
        <?php
        }
        /*
         * không thì dùng top của flatsome
         */ else {
            do_action('flatsome_before_header');
        ?>
            <header id="header" class="header flatsome-primary-header <?php flatsome_header_classes(); ?>">
                <div class="header-wrapper">
                    <?php get_template_part('template-parts/header/header', 'wrapper'); ?>
                </div>
            </header>
        <?php
            do_action('flatsome_after_header');
        }

        ?>
        <main id="main" class="<?php flatsome_main_classes(); ?>">