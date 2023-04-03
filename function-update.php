<?php

/**
 * Flatsome Update Functions
 *
 * @author  UX Themes
 * @package Flatsome/Functions
 * File này chủ yếu dùng để ghi đè file function-update.php của flatsome thôi -> sử dụng link update do mình cung cấp
 */

/*
 * Tham khảo từ nguồn:
 * https://rudrastyh.com/wordpress/theme-updates-from-custom-server.html
 */
function webgiare_v2_update_themes($transient)
{
    // lấy thông tin theme flatsome
    $theme = wp_get_theme(get_template());
    $template = $theme->get_template();
    $version = $theme->get('Version');
    //echo $version . PHP_EOL;

    //
    //var_dump(get_transient('webgiare-theme-update' . $version));
    //echo HOUR_IN_SECONDS . PHP_EOL;
    //die(__FILE__ . ':' . __LINE__);
    // tránh việc request liên tục gây chậm web thì dùng hàm này
    if (false == $remote = get_transient('webgiare-theme-update' . $version)) {
        // connect to a remote server where the update information is stored
        $remote = wp_remote_get(
            'https://flatsome.echbay.com/wp-content/webgiareorg/info.php',
            array(
                'timeout' => 30,
                'headers' => array(
                    'Accept' => 'application/json'
                )
            )
        );
        //print_r($remote);

        // do nothing if errors
        if (
            is_wp_error($remote)
            || 200 !== wp_remote_retrieve_response_code($remote)
            || empty(wp_remote_retrieve_body($remote))
        ) {
            return $transient;
        }

        // encode the response body
        $remote = json_decode(wp_remote_retrieve_body($remote));

        if (!$remote) {
            return $transient; // who knows, maybe JSON is not valid
        }
        //print_r($remote);

        //
        set_transient('webgiare-theme-update' . $version, $remote, HOUR_IN_SECONDS);
    }
    //echo $remote->version . PHP_EOL;
    //print_r($remote);

    //
    $data = array(
        'theme' => $template,
        //'url' => $remote->details_url,
        //'url' => esc_url(admin_url('admin.php?page=wgr-version-flatsome')),
        'url' => esc_url(get_site_url() . '/wp-content/webgiareorg/info.php?changes=log'),
        'requires' => $remote->requires,
        'requires_php' => $remote->requires_php,
        'version' => $version,
        'new_version' => $remote->version,
        'package' => $remote->download_url,
    );
    //print_r($data);

    // check all the versions now
    if (
        $remote
        && version_compare($version, $remote->version, '<')
        && version_compare($remote->requires, get_bloginfo('version'), '<')
        && version_compare($remote->requires_php, PHP_VERSION, '<')
    ) {
        $transient->response[$template] = $data;
    } else {
        $transient->no_update[$template] = $data;
    }

    //print_r($transient);
    //die(__FILE__ . ':' . __LINE__);

    return $transient;
}

// để đỡ nặng web, chỉ chạy tính năng update theme khi truy cập các url này
if (!defined('WGR_CHECKED_UPDATE_THEME')) {
    define('WGR_CHECKED_UPDATE_THEME', 1);

    //
    if (
        strpos($_SERVER['REQUEST_URI'], '/update-core.php') !== false
        || strpos($_SERVER['REQUEST_URI'], '/update.php') !== false
        || strpos($_SERVER['REQUEST_URI'], '/themes.php') !== false
    ) {
        add_filter('site_transient_update_themes', 'webgiare_v2_update_themes');
    }
}
//die(__FILE__ . ':' . __LINE__);
