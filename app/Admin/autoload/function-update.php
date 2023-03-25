<?php

/**
 * Flatsome Update Functions
 *
 * @author  UX Themes
 * @package Flatsome/Functions
 */

/**
 * Inject update data for Flatsome to `_site_transient_update_themes`.
 * The `package` property is a temporary URL which will be replaced with
 * an actual URL to a zip file in the `upgrader_package_options` hook when
 * WordPress runs the upgrader.
 *
 * @param array $transient The pre-saved value of the `update_themes` site transient.
 * @return array
 */

/*
 * Tham khảo từ nguồn:
 * https://rudrastyh.com/wordpress/theme-updates-from-custom-server.html
 */
function webgiare_update_themes($transient)
{

    // let's get the theme directory name
    // it will be "webgiare-theme"
    $stylesheet = get_template();

    // now let's get the theme version
    // but maybe it is better to hardcode it in a constant
    $theme = wp_get_theme();
    $version = $theme->get('Version');

    //
    //var_dump(get_transient('webgiare-theme-update' . $version));
    //echo HOUR_IN_SECONDS . PHP_EOL;
    //die(__FILE__ . ':' . __LINE__);
    // tránh việc request liên tục gây chậm web thì dùng hàm này
    if (false == $remote = get_transient('webgiare-theme-update' . $version)) {
        // connect to a remote server where the update information is stored
        $remote = wp_remote_get(
            'https://flatsome.webgiare.org/wp-content/webgiareorg/info.json',
            array(
                'timeout' => 10,
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

    $data = array(
        'theme' => $stylesheet,
        'url' => $remote->details_url,
        //'requires' => $remote->requires,
        //'requires_php' => $remote->requires_php,
        'new_version' => $remote->version,
        'package' => $remote->download_url,
    );
    //print_r($data);

    // check all the versions now
    if (
        $remote
        && version_compare($version, $remote->version, '<')
        //&& version_compare($remote->requires, get_bloginfo('version'), '<')
        //&& version_compare($remote->requires_php, PHP_VERSION, '<')
    ) {

        $transient->response[$stylesheet] = $data;
    } else {

        $transient->no_update[$stylesheet] = $data;
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
        add_filter('site_transient_update_themes', 'webgiare_update_themes');
    }
}
//die(__FILE__ . ':' . __LINE__);

// kiểm tra và cập nhật lại nội dung cho file update của flatsome
$flatsome_function_update = dirname(WGR_CHILD_PATH) . '/flatsome/inc/functions/function-update.php';
//echo $flatsome_function_update . PHP_EOL;
// nếu còn tồn tại chuỗi _site_transient_update_themes -> vẫn còn đang dùng code của flatsome
if (
    file_exists($flatsome_function_update)
    && strpos(file_get_contents($flatsome_function_update), '_site_transient_update_themes') !== false
) {
    //die(__FILE__ . ':' . __LINE__);
    //echo WGR_BASE_PATH . 'function-update.php';
    // copy file mẫu ghi đè vào file của flatsome
    copy(WGR_BASE_PATH . 'function-update.php', $flatsome_function_update);
}
