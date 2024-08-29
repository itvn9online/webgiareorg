<?php

/**
 * Optimize css, js file
 */

function WGR_update_core_remove_html_comment($a)
{
    $a = explode(PHP_EOL, $a);

    $str = '';
    foreach ($a as $v) {
        $v = trim($v);

        if ($v == '') {
            continue;
        }
        // loại bỏ các comment html đơn giản
        //echo substr( $v, 0, 4 ) . '<br>' . PHP_EOL;
        //echo substr( $v, -3 ) . '<br>' . PHP_EOL;
        if (substr($v, 0, 4) == '<!--' && substr($v, -3) == '-->') {
            continue;
        }

        $str .= $v;
        if (strpos($v, '//') !== false) {
            $str .= PHP_EOL;
        } else {
            $str .= ' ';
        }
    }

    //
    return trim($str);
    //return trim( $str );
}

function WGR_update_core_remove_php_comment($a)
{
    $a = explode(PHP_EOL, $a);

    $str = '';
    foreach ($a as $v) {
        $v = trim($v);

        // loại bỏ các dòng comment đơn
        if ($v == '' || substr($v, 0, 2) == '//' || substr($v, 0, 2) == '# ') {
            continue;
        }

        // loại bỏ comment php nếu nó nằm trên 1 dòng
        //			if ( substr( $v, 0, 2 ) == '/*' && substr( $v, -2 ) == '*/' ) {
        //			}
        // trong code php có sẽ code html -> loại bỏ như html luôn
        if (substr($v, 0, 4) == '<!--' && substr($v, -3) == '-->') {
            continue;
        }

        //
        $str .= $v . PHP_EOL;
    }

    //	return trim( WGR_remove_js_multi_comment( $str ) );
    return trim($str);
}

function WGR_update_core_remove_php_multi_comment($fileStr)
{
    // https://stackoverflow.com/questions/503871/best-way-to-automatically-remove-comments-from-php-code
    $str = '';

    //
    $commentTokens = array(T_COMMENT);
    if (defined('T_DOC_COMMENT')) {
        $commentTokens[] = T_DOC_COMMENT; // PHP 5
    }
    if (defined('T_ML_COMMENT')) {
        $commentTokens[] = T_ML_COMMENT; // PHP 4
    }

    //
    $tokens = token_get_all($fileStr);

    //
    foreach ($tokens as $token) {
        if (is_array($token)) {
            if (in_array($token[0], $commentTokens)) {
                continue;
            }

            $token = $token[1];
        }

        $str .= $token;
    }

    return trim($str);
}

function WGR_optimize_action_views($path, $dir = 'Views', $check_active = true)
{
    $path = $path . rtrim($dir, '/');
    //echo $path . ':<em>' . __CLASS__ . '</em>:' . __LINE__ . '<br>' . PHP_EOL;
    if (!is_dir($path)) {
        return false;
    }
    if (WGR_check_active_optimize($path . '/') !== true) {
        if ($check_active === true) {
            return false;
        }
    }
    echo '<strong>' . $path . '</strong>:<em>' . __CLASS__ . '</em>:' . __LINE__ . '<br>' . PHP_EOL;

    // optimize file php
    foreach (glob($path . '/*.php') as $filename) {
        echo $filename . ':<em>' . __CLASS__ . '</em>:' . __LINE__ . '<br>' . PHP_EOL;

        //
        $c = WGR_update_core_remove_php_multi_comment(WGR_update_core_remove_php_comment(file_get_contents($filename, 1)));
        if ($c != '') {
            $c .= PHP_EOL;
            //$c .= ' ';
        }
        WGR_create_file($filename, $c);
    }

    // optimize file html
    foreach (glob($path . '/*.html') as $filename) {
        echo $filename . ':<em>' . __CLASS__ . '</em>:' . __LINE__ . '<br>' . PHP_EOL;

        //
        $c = WGR_update_core_remove_html_comment(file_get_contents($filename, 1));
        WGR_create_file($filename, $c);
    }

    // optimize các thư mục con
    foreach (glob($path . '/*') as $filename) {
        if (is_dir($filename)) {
            WGR_optimize_action_views($filename, '', false);
        }
    }

    //
    return true;
}

function WGR_arr_block_fix_content()
{
    // https://www.google.com/search?q=site:charbase.com+%E1%BB%9D#q=site:charbase.com+%E1%BA%A3
    return array(
        'á' => '\u00e1',
        'à' => '\u00e0',
        'ả' => '\u1ea3',
        'ã' => '\u00e3',
        'ạ' => '\u1ea1',
        'ă' => '\u0103',
        'ắ' => '\u1eaf',
        'ặ' => '\u1eb7',
        'ằ' => '\u1eb1',
        'ẳ' => '\u1eb3',
        'ẵ' => '\u1eb5',
        'â' => '\u00e2',
        'ấ' => '\u1ea5',
        'ầ' => '\u1ea7',
        'ẩ' => '\u1ea9',
        'ẫ' => '\u1eab',
        'ậ' => '\u1ead',
        'Á' => '\u00c1',
        'À' => '\u00c0',
        'Ả' => '\u1ea2',
        'Ã' => '\u00c3',
        'Ạ' => '\u1ea0',
        'Ă' => '\u0102',
        'Ắ' => '\u1eae',
        'Ặ' => '\u1eb6',
        'Ằ' => '\u1eb0',
        'Ẳ' => '\u1eb2',
        'Ẵ' => '\u1eb4',
        'Â' => '\u00c2',
        'Ấ' => '\u1ea4',
        'Ầ' => '\u1ea6',
        'Ẩ' => '\u1ea8',
        'Ẫ' => '\u1eaa',
        'Ậ' => '\u1eac',
        'đ' => '\u0111',
        'Đ' => '\u0110',
        'é' => '\u00e9',
        'è' => '\u00e8',
        'ẻ' => '\u1ebb',
        'ẽ' => '\u1ebd',
        'ẹ' => '\u1eb9',
        'ê' => '\u00ea',
        'ế' => '\u1ebf',
        'ề' => '\u1ec1',
        'ể' => '\u1ec3',
        'ễ' => '\u1ec5',
        'ệ' => '\u1ec7',
        'É' => '\u00c9',
        'È' => '\u00c8',
        'Ẻ' => '\u1eba',
        'Ẽ' => '\u1ebc',
        'Ẹ' => '\u1eb8',
        'Ê' => '\u00ca',
        'Ế' => '\u1ebe',
        'Ề' => '\u1ec0',
        'Ể' => '\u1ec2',
        'Ễ' => '\u1ec4',
        'Ệ' => '\u1ec6',
        'í' => '\u00ed',
        'ì' => '\u00ec',
        'ỉ' => '\u1ec9',
        'ĩ' => '\u0129',
        'ị' => '\u1ecb',
        'Í' => '\u00cd',
        'Ì' => '\u00cc',
        'Ỉ' => '\u1ec8',
        'Ĩ' => '\u0128',
        'Ị' => '\u1eca',
        'ó' => '\u00f3',
        'ò' => '\u00f2',
        'ỏ' => '\u1ecf',
        'õ' => '\u00f5',
        'ọ' => '\u1ecd',
        'ô' => '\u00f4',
        'ố' => '\u1ed1',
        'ồ' => '\u1ed3',
        'ổ' => '\u1ed5',
        'ỗ' => '\u1ed7',
        'ộ' => '\u1ed9',
        'ơ' => '\u01a1',
        'ớ' => '\u1edb',
        'ờ' => '\u1edd',
        'ở' => '\u1edf',
        'ỡ' => '\u1ee1',
        'ợ' => '\u1ee3',
        'Ó' => '\u00d3',
        'Ò' => '\u00d2',
        'Ỏ' => '\u1ece',
        'Õ' => '\u00d5',
        'Ọ' => '\u1ecc',
        'Ô' => '\u00d4',
        'Ố' => '\u1ed0',
        'Ồ' => '\u1ed2',
        'Ổ' => '\u1ed4',
        'Ỗ' => '\u1ed6',
        'Ộ' => '\u1ed8',
        'Ơ' => '\u01a0',
        'Ớ' => '\u1eda',
        'Ờ' => '\u1edc',
        'Ở' => '\u1ede',
        'Ỡ' => '\u1ee0',
        'Ợ' => '\u1ee2',
        'ú' => '\u00fa',
        'ù' => '\u00f9',
        'ủ' => '\u1ee7',
        'ũ' => '\u0169',
        'ụ' => '\u1ee5',
        'ư' => '\u01b0',
        'ứ' => '\u1ee9',
        'ừ' => '\u1eeb',
        'ử' => '\u1eed',
        'ữ' => '\u1eef',
        'ự' => '\u1ef1',
        'Ú' => '\u00da',
        'Ù' => '\u00d9',
        'Ủ' => '\u1ee6',
        'Ũ' => '\u0168',
        'Ụ' => '\u1ee4',
        'Ư' => '\u01af',
        'Ứ' => '\u1ee8',
        'Ừ' => '\u1eea',
        'Ử' => '\u1eec',
        'Ữ' => '\u1eee',
        'Ự' => '\u1ef0',
        'ý' => '\u00fd',
        'ỳ' => '\u1ef3',
        'ỷ' => '\u1ef7',
        'ỹ' => '\u1ef9',
        'ỵ' => '\u1ef5',
        'Ý' => '\u00dd',
        'Ỳ' => '\u1ef2',
        'Ỷ' => '\u1ef6',
        'Ỹ' => '\u1ef8',
        'Ỵ' => '\u1ef4'
    );
}

function WGR_str_text_fix_js_content($str)
{
    if ($str == '') {
        return '';
    }

    //	$str = iconv('UTF-16', 'UTF-8', $str);
    //	$str = mb_convert_encoding($str, 'UTF-8', 'UTF-16');
    //	$str = mysqli_escape_string($str);
    //	$str = htmlentities($str, ENT_COMPAT, 'UTF-16');
    $arr = WGR_arr_block_fix_content();

    //
    foreach ($arr as $k => $v) {
        if ($v != '') {
            $str = str_replace($k, $v, $str);
        }
    }
    return $str;
}

function WGR_remove_js_comment($a, $chim = false)
{
    $a = explode(PHP_EOL, $a);
    if (count($a) < 10) {
        return false;
    }

    $str = '';
    foreach ($a as $v) {
        $v = trim($v);

        if ($v == '' || substr($v, 0, 2) == '//') {
        } else {
            // thêm dấu xuống dòng với 1 số trường hợp
            if ($chim == true || strpos($v, '//') !== false || substr($v, -1) == '\\') {
                $v .= PHP_EOL;
            }
            $str .= $v;
        }
    }

    // loại bỏ khoảng trắng
    $arr = array(
        ' ( ' => '(',
        ' ) ' => ')',
        '( \'' => '(\'',
        '\' )' => '\')',

        '\' + ' => '\'+',
        ' + \'' => '+\'',

        ' == ' => '==',
        ' != ' => '!=',
        ' || ' => '||',
        ' === ' => '===',

        ' () ' => '()',
        ' && ' => '&&',
        '\' +\'' => '\'+\'',
        ' += ' => '+=',
        '+ \'' => '+\'',
        '; i < ' => ';i<',
        'var i = 0;' => 'var i=0;',
        '; i' => ';i',
        ' = \'' => '=\''
    );

    foreach ($arr as $k => $v) {
        $str = str_replace($k, $v, $str);
    }

    //
    return $str;
}

function WGR_update_core_remove_js_comment($a)
{
    $a = WGR_remove_js_comment($a);
    if ($a === false) {
        return false;
    }
    $a = WGR_str_text_fix_js_content($a);

    return trim($a);
}

function WGR_optimize_action_js($path, $dir = 'javascript', $type = 'js')
{
    $path = rtrim($path, '/') . '/' . rtrim($dir, '/');
    //echo $path . ':<em>' . __CLASS__ . '</em>:' . __LINE__ . '<br>' . PHP_EOL;
    if (!is_dir($path) || WGR_check_active_optimize($path . '/') !== true) {
        return false;
    }
    echo '<strong>' . $path . '</strong>:<em>' . __CLASS__ . '</em>:' . __LINE__ . '<br>' . PHP_EOL;

    //
    foreach (glob($path . '/*.' . $type) as $filename) {
        $c = file_get_contents($filename, 1);
        // nếu file không có nội dung gì thì xóa luôn file đí -> tối ưu cho frontend đỡ phải nạp
        if (trim($c) == false) {
            unlink($filename);
            continue;
        }
        $c = WGR_update_core_remove_js_comment($c);
        if ($c === false) {
            echo 'continue (' . basename($filename) . ') <br>' . PHP_EOL;
            continue;
        }
        echo $filename . ':<em>' . __CLASS__ . '</em>:' . __LINE__ . '<br>' . PHP_EOL;

        //
        if (!empty($c)) {
            WGR_create_file($filename, $c, ['ftp' => 1]);
        }
    }

    //
    return true;
}

// optimize cho file css
function WGR_remove_css_multi_comment($a)
{
    $a = explode('*/', $a);
    $str = '';
    foreach ($a as $v) {
        $v = explode('/*', $v);
        $str .= $v[0];
    }

    //
    $a = explode(PHP_EOL, $str);
    if (count($a) < 10) {
        return false;
    }
    //echo 'count a: ' . count( $a ) . '<br>' . PHP_EOL;
    $str = '';
    foreach ($a as $v) {
        $v = trim($v);
        if ($v != '') {
            $str .= $v;
        }
    }

    // bỏ các ký tự thừa nhiều nhất có thể
    $str = str_replace('; }', '}', $str);
    $str = str_replace(';}', '}', $str);
    $str = str_replace(' { ', '{', $str);
    $str = str_replace(' {', '{', $str);
    $str = str_replace(', .', ',.', $str);
    $str = str_replace(', #', ',#', $str);
    $str = str_replace(': ', ':', $str);
    $str = str_replace('} .', '}.', $str);
    $str = str_replace('{ }', '{}', $str);
    // $str = str_replace('{ }', '{}', $str);
    $str = str_replace('}.', '}' . PHP_EOL . '.', $str);
    $str = str_replace('}#', '}' . PHP_EOL . '#', $str);
    $str = str_replace('}@', '}' . PHP_EOL . '@', $str);

    // 
    $a = explode(PHP_EOL, $str);
    $str = '';
    foreach ($a as $v) {
        $v = trim($v);
        if ($v != '') {
            if (strpos($v, '{}') !== false) {
                $first_char = substr($v, 0, 1);
                if ($first_char == '.' || $first_char == '#' || $first_char == '@') {
                    continue;
                }
            }
            $str .= $v;
            // $str .= PHP_EOL;
        }
    }

    // chuyển đổi tên màu sang mã màu
    $arr_colorname_to_code = [
        'transparent' => '00000000',
        'red' => 'ff0000',
        'darkred' => '8b0000',
        'black' => '000000',
        'white' => 'ffffff',
        'blue' => '0000ff',
        'darkblue' => '00008b',
        'green' => '008000',
        'darkgreen' => '006400',
        'orange' => 'ffa500',
        'darkorange' => 'ff8c00',
    ];
    foreach ($arr_colorname_to_code as $k => $v) {
        $str = str_replace(':' . $k . '}', ':#' . $v . '}', $str);
        $str = str_replace(':' . $k . ';', ':#' . $v . ';', $str);

        // !important
        $str = str_replace(':' . $k . ' !', ':#' . $v . ' !', $str);
        $str = str_replace(':' . $k . '!', ':#' . $v . '!', $str);
    }

    // loại bỏ các dòng css chưa có code
    // $str = WGR_remove_css_not_using($str);

    //
    return $str;
}

// loại bỏ các dòng css chưa có code
function WGR_remove_css_not_using($str)
{
    $str = str_replace('{  }', '{}', $str);
    $str = str_replace('{ }', '{}', $str);
    $str = explode('{}', $str);
    //print_r($str);
    foreach ($str as $k => $v) {
        // cắt chuỗi
        $v = explode('}', $v);
        //print_r($v);
        //$v = array_pop($v);
        $v[count($v) - 1] = '';
        //print_r($v);
        $v = implode('}', $v);
        //print_r($v);

        //
        $str[$k] = $v;
    }
    $str = implode('', $str);
    //die(__CLASS__ . ':' . __LINE__);

    //
    return $str;
}

// kiểm tra xem có sự tồn tại của file kích hoạt chế độ optimize không
function WGR_check_active_optimize($path)
{
    //echo '<strong>' . $path . '</strong>:<em>' . __CLASS__ . '</em>:' . __LINE__ . '<br>' . PHP_EOL;
    $full_path = $path . 'active-optimize.txt';
    //echo $full_path . ':<em>' . __CLASS__ . '</em>:' . __LINE__ . '<br>' . PHP_EOL;
    if (is_file($full_path)) {
        // thử xóa file optimize -> XÓA được thì mới trả về true -> đảm bảo có quyền chỉnh sửa các file trong này
        if (unlink($full_path)) {
            return true;
        }
    }
    return false;
}

function WGR_optimize_action_css($path, $dir = 'css', $type = 'css')
{
    $path = rtrim($path, '/') . '/' . rtrim($dir, '/');
    //echo $path . ':<em>' . __CLASS__ . '</em>:' . __LINE__ . '<br>' . PHP_EOL;
    if (!is_dir($path) || WGR_check_active_optimize($path . '/') !== true) {
        return false;
    }
    echo '<strong>' . $path . '</strong>:<em>' . __CLASS__ . '</em>:' . __LINE__ . '<br>' . PHP_EOL;

    //
    foreach (glob($path . '/*.' . $type) as $filename) {
        $c = file_get_contents($filename, 1);
        // nếu file không có nội dung gì thì xóa luôn file đí -> tối ưu cho frontend đỡ phải nạp
        if (trim($c) == false) {
            unlink($filename);
            continue;
        }
        $c = WGR_remove_css_multi_comment($c);
        //var_dump( $c );
        if ($c === false) {
            echo 'continue (' . basename($filename) . ') <br>' . PHP_EOL;
            continue;
        }
        echo $filename . ':<em>' . __CLASS__ . '</em>:' . __LINE__ . '<br>' . PHP_EOL;

        //
        $c = trim($c);
        if (!empty($c)) {
            WGR_create_file($filename, $c);
        }
    }

    //
    return true;
}

function WGR_optimize_css_js()
{
    // tính năng này không hoạt động trên localhost
    if (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) {
        return false;
    }
    $last_optimize_code = WGR_BASE_PATH . 'last-optimize-code.txt';
    //echo $last_optimize_code . '<br>' . PHP_EOL;
    // giãn cách optmize -> trong thời gian cho phép thì hủy bỏ việc optmize luôn
    if (WGR_cache_expire($last_optimize_code)) {
        //echo __FILE__ . ':' . __LINE__ . '<br>' . PHP_EOL;
        return false;
    }

    // css, js chung -> nếu optimize thành công ở thư mục dùng chung -> tạo file để lát xử lý ở thư mục riêng luôn
    if (WGR_optimize_action_css(WGR_BASE_PATH . 'public') === true) {
        file_put_contents(WGR_CHILD_PATH . 'css/active-optimize.txt', time(), LOCK_EX);
    }
    if (WGR_optimize_action_js(WGR_BASE_PATH . 'public') === true) {
        file_put_contents(WGR_CHILD_PATH . 'javascript/active-optimize.txt', time(), LOCK_EX);
    }

    // css, js của từng theme
    WGR_optimize_action_css(WGR_CHILD_PATH);
    WGR_optimize_action_js(WGR_CHILD_PATH);

    // optimize phần view -> optimize HTML
    if (WGR_optimize_action_views(WGR_BASE_PATH . 'app') === true) {
        WGR_optimize_action_views(WGR_CHILD_PATH, '', false);
        WGR_optimize_action_views(WGR_CHILD_PATH, 'shortcode', false);
    }

    //
    WGR_create_file($last_optimize_code, time());
}

//
WGR_optimize_css_js();
