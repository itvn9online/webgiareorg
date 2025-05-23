<?php

/**
 * Nạp config phục vụ cho cache nếu có
 */

//
if (defined('EB_MY_CACHE_CONFIG') && is_file(EB_MY_CACHE_CONFIG)) {
    include EB_MY_CACHE_CONFIG;
}
defined('EB_REDIS_CACHE') || define('EB_REDIS_CACHE', false);

//
// include __DIR__ . '/DbCache.php';
// include __DIR__ . '/RedisCache.php';

//
function WGR_cache_expire($path, $t = 3600)
{
    if ($t > 0 && is_file($path) && time() - filemtime($path) < $t) {
        // cache còn hạn
        return true;
    }
    // cache hết hạn
    return false;
}

// tạo key cho redis cache từ file name
function WGR_redis_key($f)
{
    // dùng redis thì cắt bỏ đuôi txt cho nhẹ
    // echo $f . '<br>' . PHP_EOL;
    $f = str_replace('.', '-', explode('.txt', basename($f))[0]);
    // echo $f . '<br>' . PHP_EOL;
    if (defined('EB_PREFIX_CACHE')) {
        return EB_PREFIX_CACHE . $f;
    }
    return $f;
}

function WGR_my_cache($path, $c = '', $t = 120)
{
    // TEST
    // return false;

    // $by_line = '¦';
    $by_line = '|WGR_CACHE|';
    //die($path);

    // có nội dung thì lưu file
    if ($c != '') {
        if (EB_CDN_UPLOADS_URL != '') {
            // thay đường dẫn cho file tĩnh trong thư mục uploads
            $c = str_replace(DYNAMIC_BASE_URL . 'wp-content/uploads/', EB_CDN_UPLOADS_URL . 'wp-content/uploads/', $c);
            // thay đường dẫn cho file tĩnh trong thư mục themes -> lỗi CORS -> bỏ
            // $c = str_replace(DYNAMIC_BASE_URL . 'wp-content/themes/', EB_CDN_UPLOADS_URL . 'wp-content/themes/', $c);
        }

        //
        $t *= 1;
        // cache qua redis (nếu có)
        if (EB_REDIS_CACHE == true) {
            $rd = new Redis();
            $rd->connect(REDIS_MY_HOST, REDIS_MY_PORT);
            // echo "Connection to server sucessfully";
            $rd_key = WGR_redis_key($path);
            // echo $rd_key;

            // try catch
            try {
                // key will be deleted after 10 seconds
                $rd->setex($rd_key, $t, $c);
            } catch (Exception $e) {
                // set the data in redis string
                $rd->set($rd_key, $c);
                // key will be deleted after 10 seconds
                $rd->expire($rd_key, $t);
            }
            return true;
        }
        $c = (time() + $t) . $by_line . $c;
        return WGR_create_file($path, $c);
    }

    // cache qua redis (nếu có)
    if (EB_REDIS_CACHE == true) {
        $rd = new Redis();
        $rd->connect(REDIS_MY_HOST, REDIS_MY_PORT);
        // echo "Connection to server sucessfully";
        // Get the stored data and return it 
        return $rd->get(WGR_redis_key($path));
    }
    // không có nội dung thì kiểm tra hạn cache
    else if (is_file($path)) {
        return WGR_check_cache_content(explode($by_line, file_get_contents($path, 1), 2));
    }

    // mặc định trả về false
    return false;
}

function WGR_check_cache_content($content)
{
    // không chuẩn cache thì báo false
    if (count($content) != 2 || !is_numeric($content[0])) {
        return false;
    }

    // cọn hạn thì trả về nội dung
    if (($content[0] * 1) > time()) {
        return $content[1];
    }
    return false;
}

function WGR_display($f)
{
    // cache qua redis (nếu có)
    if (EB_REDIS_CACHE == true) {
        $rd = new Redis();
        $rd->connect(REDIS_MY_HOST, REDIS_MY_PORT);
        // echo "Connection to server sucessfully";
        // Get the stored data and print it 
        $data = $rd->get(WGR_redis_key($f));
        // var_dump($data);
        // echo "Stored string in redis: " . $data;
        // die(WGR_redis_key($f));
        if ($data === false) {
            return false;
        }
        // echo __FILE__ . ':' . __LINE__ . '<br>' . PHP_EOL;

        // với redis cache thì print ra luôn
        echo $data;
        echo '<!-- generated by ebsuppercache (using redis) -->';
        exit();
    } else if (!is_file($f)) {
        return false;
    } else {
        // còn không thì cache qua file
        $data = file_get_contents($f, 1);
    }
    //die(__FILE__ . ':' . __LINE__);

    //
    // $content = explode('¦', $data, 2);
    $content = explode('|WGR_CACHE|', $data, 2);
    if (count($content) < 2 || !is_numeric($content[0])) {
        return false;
    }
    $reset_time = rand(1, 30);
    $active_reset = ($content[0] * 1) - time() - $reset_time;
    //echo $active_reset . '<br>' . PHP_EOL;
    //echo $reset_time . '<br>' . PHP_EOL;
    //die(__FILE__ . ':' . __LINE__);
    if ($active_reset < 0) {
        return false;
    }

    // -> done
    echo $content[1];
    echo '<!-- generated by ebsuppercache (' . $active_reset . ' | ' . $reset_time . ' | ' . date('Y-m-d H:i:s', $content[0]) . ') -->';
    exit();
}

//
function WGR_get_cache_file($cache_dir = '')
{
    if (isset($_SERVER['REQUEST_URI'])) {
        $url = $_SERVER['REQUEST_URI'];
        // echo $url . '<br>' . PHP_EOL;
    } else {
        $url = $_SERVER['SCRIPT_NAME'];
        $url .= (!empty($_SERVER['QUERY_STRING'])) ? '?' . $_SERVER['QUERY_STRING'] : '';
        // echo $url . '<br>' . PHP_EOL;
    }
    if ($url == '/' || $url == '') {
        $url = '-';
    } else if (1 > 2 && defined('WGR_IS_HOME')) {
        $url = WGR_IS_HOME;
    } else {
        // echo $url . '<br>' . PHP_EOL;
        $arr_cat_social_parameter = array(
            'fbclid=',
            'gclid=',
            'fb_comment_id=',
            'add_to_wishlist=',
            '_wpnonce=',
            'utm_',
            'v',
        );
        foreach ($arr_cat_social_parameter as $v) {
            $url = explode('?' . $v, $url)[0];
            $url = explode('&' . $v, $url)[0];
        }

        //
        if (strlen($url) > 200) {
            // $url = md5($url);
            $url = substr($url, 0, 200);
        }
        // echo $url . '<br>' . PHP_EOL;
        // $url = preg_replace("/\/|\?|\&|\,|\=/", '-', $url);
        $url = str_replace([
            '&amp%3B',
            '&amp;',
            '/',
            '?',
            '&',
            ',',
            '=',
        ], '-', $url);
        // echo $url . '<br>' . PHP_EOL;

        // thay thế 2- thành 1-  
        $url = preg_replace('!\-+!', '-', $url);
        // echo $url . '<br>' . PHP_EOL;

        // cắt bỏ ký tự - ở đầu và cuối chuỗi
        $url = rtrim(ltrim($url, '-'), '-');
        $url = rtrim(ltrim($url, '.'), '.');
        $url = trim($url);
        // echo $url . '<br>' . PHP_EOL;
        if ($url == '') {
            $url = '-';
            // echo $url . '<br>' . PHP_EOL;
        }
    }

    //
    if ($cache_dir != '') {
        $cache_dir .= '/';
    }
    // $url = EB_THEME_CACHE . $cache_dir . $url;
    $url = EB_THEME_CACHE . $cache_dir . $url . '.txt';
    // echo $url . '<br>' . PHP_EOL;

    //
    return $url;
}

// thêm câu báo rằng đang lấy nội dung trong cache
function WGR_builder_eb_cache_note($note = '')
{
    return '<!-- Plugin by daidq - Theme by itvn9online
Cached page generated by WGR Cache (ebcache), an product of EB
Served from: ' . EB_PREFIX_CACHE . ':' . $_SERVER['REQUEST_URI'] . ' on ' . date('Y-m-d H:i:s') . '
Served to: ebcache all URI (' . $note . ')
Storage time: ' . EB_TIME_CACHE . '
Caching using ' . (EB_REDIS_CACHE == true ? 'redis' : 'hard disk') . ' drive. Recommendations using SSD for your website.
Compression = gzip -->';
}

// tạo file cache cho các tùy biến trong ux builder
function my_builder_path_cache($fname)
{
    if (defined('EB_THEME_CACHE')) {
        // return EB_THEME_CACHE . $fname;
        return EB_THEME_CACHE . $fname . '.txt';
    }
    return '';
}
