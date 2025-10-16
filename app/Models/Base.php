<?php

// nạp CSS, JS để tránh phải bấm Ctrl + F5
function WGR_get_add_css($f, $ops = [], $attr = [])
{
    //print_r( $ops );
    //echo $f . '<br>' . "\n";
    $f = str_replace(ABSPATH, '', $f);
    $f = ltrim($f, '/');
    //echo $f . '<br>' . "\n";

    if (!is_file(ABSPATH . $f)) {
        return '<!-- ' . $f . ' not exist! -->';
    }
    //echo filesize( $f ) . '<br>' . "\n";
    /*
    if ( filesize( $f ) == 0 ) {
        unlink( $f );
        return '<!-- ' . $f . ' filesize zero! -->';
    }
    */

    //
    if (isset($ops['get_content'])) {
        return '<style>' . file_get_contents($f, 1) . '</style>' . "\n";
    }

    // xem có chạy qua CDN không -> có thì nó sẽ giảm tải cho server
    if (!isset($ops['cdn']) || $ops['cdn'] == '') {
        $ops['cdn'] = DYNAMIC_BASE_URL;
    }
    //echo $ops['cdn'] . '<br>' . "\n";

    //
    if (isset($ops['preload'])) {
        $rel = 'rel="preload" as="style" onload="this.onload=null;this.rel=\'stylesheet\'"';
    } else {
        $rel = 'rel="stylesheet" type="text/css" media="all"';
    }
    return '<link ' . $rel . ' href="' . $ops['cdn'] . $f . '?v=' . filemtime(ABSPATH . $f) . '"' . implode(' ', $attr) . ' />';
}

// chế độ nạp css thông thường
function WGR_add_css($f, $ops = [], $attr = [])
{
    echo WGR_get_add_css($f, $ops, $attr) . "\n";
}

// thêm nhiều file cùng 1 thuộc tính
function WGR_adds_css($fs, $ops = [], $attr = [])
{
    foreach ($fs as $f) {
        echo WGR_get_add_css($f, $ops, $attr) . "\n";
    }
}

// chế độ nạp trước css
function WGR_preload_css($f, $ops = [])
{
    $ops['preload'] = 1;

    //
    echo WGR_get_add_css($f, $ops) . "\n";
}

function WGR_preloads_css($fs, $ops = [])
{
    $ops['preload'] = 1;

    //
    foreach ($fs as $f) {
        echo WGR_get_add_css($f, $ops) . "\n";
    }
}

/**
 * add javascript
 */
function WGR_get_add_js($f, $ops = [], $attr = [])
{
    //print_r( $ops );
    $f = str_replace(ABSPATH, '', $f);
    $f = ltrim($f, '/');
    //echo $f . '<br>' . "\n";
    if (!is_file(ABSPATH . $f)) {
        return '<!-- ' . $f . ' not exist! -->';
    }
    //echo filesize( $f ) . '<br>' . "\n";
    /*
    if ( filesize( $f ) == 0 ) {
        unlink( $f );
        return '<!-- ' . $f . ' filesize zero! -->';
    }
    */

    //
    if (isset($ops['get_content'])) {
        return '<script>' . file_get_contents($f, 1) . '</script>';
    }

    // xem có chạy qua CDN không -> có thì nó sẽ giảm tải cho server
    if (!isset($ops['cdn']) || $ops['cdn'] == '') {
        $ops['cdn'] = DYNAMIC_BASE_URL;
    }

    //
    if (isset($ops['preload'])) {
        return '<link rel="preload" as="script" href="' . $ops['cdn'] . $f . '?v=' . filemtime(ABSPATH . $f) . '">';
    }
    //print_r( $attr );
    return '<script src="' . $ops['cdn'] . $f . '?v=' . filemtime(ABSPATH . $f) . '" ' . implode(' ', $attr) . '></script>';
}

// thêm 1 file
function WGR_add_js($f, $ops = [], $attr = [])
{
    echo WGR_get_add_js($f, $ops, $attr) . "\n";
}
// thêm nhiều file cùng 1 thuộc tính
function WGR_adds_js($fs, $ops = [], $attr = [])
{
    foreach ($fs as $f) {
        echo WGR_get_add_js($f, $ops, $attr) . "\n";
    }
}
// chế độ nạp trước css
function WGR_preload_js($f, $ops = [])
{
    $ops['preload'] = 1;

    //
    echo WGR_get_add_js($f, $ops) . "\n";
}

function WGR_preloads_js($fs, $ops = [])
{
    $ops['preload'] = 1;

    //
    foreach ($fs as $f) {
        echo WGR_get_add_js($f, $ops) . "\n";
    }
}

//
function WGR_select($sql)
{
    global $wpdb;
    return $wpdb->get_results(trim($sql), OBJECT);
}

// hàm file_get_contents khó chạy trên localhost -> dùng cURL để thay thế
function WGR_get_contents($url, $flag = 0)
{
    if ($flag > 0) {
        return file_get_contents($url, 1);
    }

    // 
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    // echo $response;
    return $response;
}
