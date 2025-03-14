<?php

/**
 * 
 */
global $wpdb;

/**
 * Chỉnh lại nội dung cho file htaccess
 * Chức năng này sẽ tự động chỉnh nội dung trong file htaccess cho phù hợp
 * Để tắt chức năng này, hãy khai báo constant WGR_DISABLE_AUTO_HTACCESS trong function của child-theme
 */

// 
// print_r(explode('//', get_home_url()));
// var_dump(strpos(explode('//', get_home_url())[1], '/'));

// 
$path_htaccess = ABSPATH . ".htaccess";
// chỉ xử lý file htaccess đối với website chạy bằng tên miền chính, không xử lý khi web dạng sub-dir
if (is_file($path_htaccess) && strpos(explode('//', get_home_url())[1], '/') === false) {
    // echo __FILE__ . ':' . __LINE__;

    // lấy nội dùng file hiện tại
    $content_htaccess = file_get_contents($path_htaccess);

    // xóa file trong trường hợp lỗi htaccess
    if (strpos($content_htaccess, '{tmp.sub_dir}') !== false) {
        unlink($path_htaccess);
    }
    // nếu file còn tham số domain mẫu -> cập nhật lại
    else if (strpos($content_htaccess, '{my_domain.com}') !== false) {
        if (strpos($content_htaccess, '# Header always set Permissions-Policy ') !== false) {
            if (!defined('WGR_DISABLE_AUTO_HTACCESS')) {
                // thay thành domain hiện tại
                $content_htaccess = str_replace('{my_domain.com}', str_replace('www.', '', $_SERVER['HTTP_HOST']), $content_htaccess);

                // set header
                $content_htaccess = str_replace('# Header always set Permissions-Policy ', 'Header always set Permissions-Policy ', $content_htaccess);

                // 
                foreach (
                    [
                        'sub_dir' => '',
                        'sub2_dir' => '',
                    ] as $k => $v
                ) {
                    $content_htaccess = str_replace('{tmp.' . $k . '}', $v, $content_htaccess);
                }

                // cập nhật content
                file_put_contents($path_htaccess, $content_htaccess, LOCK_EX);
                echo 'update {my_domain.com} for ' . $path_htaccess . '<br>' . PHP_EOL;
            } else {
                echo 'WGR_DISABLE_AUTO_HTACCESS is defined' . '<br>' . PHP_EOL;
            }
        } else {
            echo 'content_htaccess has {my_domain.com}' . '<br>' . PHP_EOL;
        }
    }
}

// tạo file htaccess chặn truy cập vào file php trong thư mục uploads
// echo ABSPATH . '<br>' . PHP_EOL;
if (is_dir(ABSPATH . 'wp-content') && is_dir(ABSPATH . 'wp-content/uploads')) {
    $path_htaccess = ABSPATH . 'wp-content/uploads/.htaccess';
    if (!is_file($path_htaccess)) {
        // tạo file htaccess với nội dung chặn truy cập vào file php
        file_put_contents($path_htaccess, trim('
#Begin Really Simple Security
<Files *.php>
deny from all
</Files>
#End Really Simple Security
'), LOCK_EX);

        // 
        echo 'update uploads/*.php for ' . $path_htaccess . '<br>' . PHP_EOL;
    }
}

// kiểm tra và cập nhật phiên bản mới nếu có
function check_and_update_webgiareorg()
{
    $version = file_get_contents(WGR_BASE_PATH . 'VERSION');
    // $remove_version = WGR_get_contents('https://raw.echbay.com/itvn9online/webgiareorg/main/VERSION');
    if (isset($_GET['update_wgr_code'])) {
        $remove_version = $version;
    } else {
        $remove_version = file_get_contents('https://flatsome.webgiare.org/wp-content/webgiareorg/VERSION');
    }
    // $remove_version = '24.08.20';
    // echo EB_MY_CACHE_CONFIG . '<br>' . PHP_EOL;
    // echo EB_THEME_CACHE . '<br>' . PHP_EOL;

    //
    if (isset($_GET['update_wgr_code']) || version_compare($version, $remove_version, '<')) {
        //echo $version . PHP_EOL;
        //echo $remove_version . PHP_EOL;
        $dest = WP_CONTENT_DIR . '/upgrade/webgiareorg.zip';
        echo 'File zip has been save to: ' . $dest . '<br>' . PHP_EOL;
        if (is_file($dest)) {
            unlink($dest);
        }

        //
        $download_url = 'https://github.com/itvn9online/webgiareorg/archive/refs/heads/main.zip';

        //
        if (!copy($download_url, $dest)) {
            echo 'ERROR copy file from link: ' . $download_url . '<br>' . PHP_EOL;

            // 
            if (!file_put_contents($dest, file_get_contents($download_url))) {
                echo 'ERROR file_get_contents file from link: ' . $download_url . '<br>' . PHP_EOL;

                // 
                if (!file_put_contents($dest, fopen($download_url, 'r'))) {
                    echo 'ERROR fopen file from link: ' . $download_url . '<br>' . PHP_EOL;
                    return false;
                } else {
                    echo 'FOPEN file from link: ' . $download_url . '<br>' . PHP_EOL;
                }
            } else {
                echo 'GET_CONTENT file from link: ' . $download_url . '<br>' . PHP_EOL;
            }
        } else {
            echo 'COPY file from link: ' . $download_url . '<br>' . PHP_EOL;
        }
        chmod($dest, 0777);

        //
        if (filesize($dest) > 1000) {
            // kết quả giải nén
            $unzipfile = false;
            $dir_unzip_update_to = WP_CONTENT_DIR . '/';
            $dir_name_for_unzip_to = 'webgiareorg-main';

            //
            if (class_exists('ZipArchive')) {
                echo '<div>Using: <strong>ZipArchive</strong></div>';

                $zip = new ZipArchive;
                if (
                    $zip->open($dest) === TRUE
                ) {
                    $zip->extractTo($dir_unzip_update_to);
                    $zip->close();

                    //
                    $unzipfile = true;
                }
            } else {
                echo '<div>Using: <strong>unzip_file (wordpress)</strong></div>';

                $unzipfile = unzip_file($dest, $dir_unzip_update_to);
            }

            //
            if ($unzipfile == true) {
                echo '<div>Unzip to: <strong>' . $dir_unzip_update_to . $dir_name_for_unzip_to . '</strong></div>';

                if (!is_dir($dir_unzip_update_to . $dir_name_for_unzip_to)) {
                    echo '<h3 class="redcolor">Unzip faild...</strong></h3>';
                } else {
                    // thực hiện đổi tên thư mục
                    $myoldfolder = $dir_unzip_update_to . 'webgiareorg';
                    echo $myoldfolder . '<br>' . PHP_EOL;

                    // đổi tên thư mục code cũ
                    $mynewfolder = '';
                    if (is_dir($myoldfolder)) {
                        $mynewfolder = $myoldfolder . '-' . date('Ymd-His');
                        echo $mynewfolder . '<br>' . PHP_EOL;

                        // ưu tiên sử dụng PHP thuần cho nó nhanh
                        if (rename($myoldfolder, $mynewfolder)) {
                            echo '<div>Hoàn thành quá trình backup code (rename)!</div>';
                        }
                    }

                    // đổi tên thư mục code mới
                    if (rename($dir_unzip_update_to . $dir_name_for_unzip_to, $myoldfolder)) {
                        echo '<div>Hoàn thành quá trình cập nhật code (rename)!</div>';
                    }
                }
            } else {
                echo '<div>Do not unzip file, update faild!</div>';
            }
        } else {
            echo '<div>File bị xóa vì không đủ dung lượng cần thiết!</div>';
        }

        //
        unlink($dest);
    }
}

function WGR_getIPAddress()
{
    $client_ip = $_SERVER['REMOTE_ADDR'];
    if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        $client_ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
    } else if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // $client_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        $client_ip = trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
    } else if (isset($_SERVER['HTTP_X_REAL_IP'])) {
        $client_ip = $_SERVER['HTTP_X_REAL_IP'];
        // $client_ip = trim(explode(',', $_SERVER['HTTP_X_REAL_IP'])[0]);
    }
    return $client_ip;
}

?>
<h1>Về tác giả</h1>
<p>Phiên bản WebGiaRe code: <strong><?php echo file_get_contents(WGR_BASE_PATH . 'VERSION'); ?></strong></p>
<p>Mặc định, WebGiaRe code sẽ được cập nhật tự động mỗi khi có phiên bản mới. Bạn có thể <a href="<?php echo admin_url(); ?>admin.php?page=eb-about&update_wgr_code=1" class="bold">Bấm vào đây</a> để cập nhật lại WebGiaRe code thủ công.</p>
<p>PHP version: <strong><?php echo PHP_VERSION; ?></strong>.</p>
<p>Server IP: <strong><?php echo $_SERVER['SERVER_ADDR']; ?></strong></p>
<p>Client IP: <strong><?php echo WGR_getIPAddress(); ?></strong></p>
<?php

//
if (version_compare(PHP_VERSION, '8.1.0', '<')) {
?>
    <p class="redcolor">Với các website sử dụng Wordpress, phiên bản PHP tối ưu nên dùng là PHP 8.1 trở lên (khuyên dùng 8.2). Nếu có thể! hãy nâng cấp phiên bản PHP cho website của bạn.</p>
<?php
}

//
if (class_exists('Imagick')) {
?>
    <p class="greencolor">Xin chúc mừng, <strong>Imagick</strong> đã được cài đặt! Các chức năng xử lý hình ảnh sẽ hoạt động ổn định hơn.</p>
<?php
} else {
?>
    <p class="orgcolor">Vui lòng cài đăt thêm <strong>Imagick</strong> để các chức năng xử lý hình ảnh hoạt động ổn định hơn.</p>
<?php
}

?>
<!-- OPcache -->
<p><strong>OPcache:</strong>
    <?php
    if (function_exists('opcache_get_status') && is_array(opcache_get_status())) {
    ?>
        <span class="greencolor"> Xin chúc mừng, <strong>OPcache</strong> đã được cài đặt!</span>
    <?php
    } else {
    ?>
        <span class="orgcolor"> Nên bổ sung thêm OPcache sẽ giúp tăng đáng kể hiệu suất website của bạn.</span>
    <?php
    }
    ?>
</p>
<!-- END OPcache -->
<!-- Memcached -->
<p><strong>Memcached:</strong>
    <?php
    if (function_exists('Memcached')) {
    ?>
        Xin chúc mừng, <strong>Memcached</strong> đã được cài đặt!
    <?php
    }
    ?>
</p>
<!-- END Memcached -->
<!-- Redis -->
<div class="p"><strong>Redis (<?php echo phpversion('redis'); ?>):</strong>
    <?php
    if (!empty(phpversion('redis'))) {
    ?>
        <?php
        if (defined('WGR_REDIS_CACHE') && WGR_REDIS_CACHE == true) {
        ?>
            <span class="greencolor">
                Xin chúc mừng, <strong>Redis</strong> đã được thiết lập làm cache cho website này!
                <?php
                if (defined('WGR_CACHE_PREFIX')) {
                ?>
                    &nbsp;
                    Với cache prefix là: <?php echo WGR_CACHE_PREFIX; ?>
                <?php
                }
                ?>
            </span>
        <?php
        } else {
        ?>
            Hiện khả dụng trên hosting của bạn, hãy cân nhắc việc kích hoạt nó cho website này.
        <?php
        }
        ?>
        <ul>
            <li>Redis fixed (Host/ Port): <?php echo WGR_REDIS_HOST; ?>/ <?php echo WGR_REDIS_PORT; ?></li>
            <?php
            if (defined('REDIS_MY_HOST')) {
            ?>
                <li>Redis dynamic (Host/ Port): <?php echo REDIS_MY_HOST; ?>/ <?php echo REDIS_MY_PORT; ?></li>
            <?php
            }
            ?>
            <?php
            if (defined('EB_MY_CACHE_CONFIG')) {
            ?>
                <li>Cache config path: <?php echo EB_MY_CACHE_CONFIG; ?></li>
            <?php
            }
            ?>
        </ul>
        <div>
            <p>Để Bật/ Tắt chức năng cache qua redis thủ công, hãy thêm đoạn mã sau vào đầu file wp-config.php:</p>
            <div>
                <textarea rows="5" ondblclick="this.select();" readonly style="width: 90%;">
// bật tắt chức năng cache qua redis: true|false
defined('WGR_REDIS_CACHE') || define('WGR_REDIS_CACHE', true);
// thiết lập prefix cho cache để tránh xung đột với site khác
defined('WGR_CACHE_PREFIX') || define('WGR_CACHE_PREFIX', '<?php echo strtolower(str_replace(['www.', '.'], '', str_replace('-', '_', explode(':', $_SERVER['HTTP_HOST'])[0]))); ?>');</textarea>
            </div>
        </div>
    <?php
    }
    ?>
</div>
<!-- END Redis -->
<p class="bluecolor">Khi chuyển host mà bị lỗi font, vào database -> bảng options -> option_name -> tìm và xóa hoặc đổi tên: <span class="bold">kirki_downloaded_font_files</span></p>
<div>
    <textarea rows="2" ondblclick="this.select();" readonly style="width: 90%;">
UPDATE `<?php echo $wpdb->prefix; ?>options` SET `option_name` = 'kirki_downloaded_font_files_<?php echo time(); ?>' WHERE `<?php echo $wpdb->prefix; ?>options`.`option_name` = 'kirki_downloaded_font_files';</textarea>
</div>
<br />
<div class="bold">
    <h3>This theme recommends the following plugins</h3>
    <ol id="wgr-recommends-following-plugins">
    </ol>
</div>
<br />
<?php

//
if (defined('WGR_CHECKED_UPDATE_THEME')) {
?>
    <p class="bluecolor">Phiên bản Flatsome của bạn đang được cập nhật thông qua server của <span class="bold">WebGiaRe.org</span></p>
<?php
} else {
?>
    <p class="greencolor">Phiên bản Flatsome của bạn đang được cập nhật thông qua server của <span class="bold">themeforest.net</span></p>
<?php
}

//
check_and_update_webgiareorg();

?>
<script>
    if (window.location.href.includes('update_wgr_code=') == true) {
        window.history.pushState("", document.title, window.location.href.split('&update_wgr_code=')[0].split('?update_wgr_code=')[0]);
    }

    // tạo danh sách các plugin khuyên dùng
    (function(arr) {
        let str = '',
            w = Math.ceil(jQuery(window).width() / 100 * 70),
            h = Math.ceil(jQuery(window).height() / 100 * 80);
        for (let x in arr) {
            str += '<li>' +
                '<a href="' + web_link + 'wp-admin/plugin-install.php?tab=plugin-information&plugin=' + x + '&TB_iframe=true&width=' + w + '&height=' + h + '" class="thickbox">' + arr[x] + '</a>' +
                '</li>';
        }
        jQuery('#wgr-recommends-following-plugins').html(str);
    })({
        'advanced-custom-fields': 'Advanced Custom Fields (ACF)',
        'tinymce-advanced': 'Advanced Editor Tools',
        'amp': 'AMP',
        'classic-editor': 'Classic Editor',
        // 'classic-widgets': 'Classic Widgets',
        'contact-form-7': 'Contact Form 7',
        'echbay-admin-security': 'EchBay Admin Security',
        'echbay-phonering-alo': 'EchBay Phonering Alo',
        'echbay-search-everything': 'EchBay Search Everything',
        'ajax-search-for-woocommerce': 'FiboSearch – Ajax Search for WooCommerce',
        'flamingo': 'Flamingo',
        'polylang': 'Polylang',
        'post-duplicator': 'Post Duplicator',
        'seo-by-rank-math': 'Rank Math SEO',
        'wp-smushit': 'Smush Image Optimization',
        'speculation-rules': 'Speculative Loading',
        'tiny-compress-images': 'TinyPNG',
        'woocommerce': 'WooCommerce',
        'woo-vietnam-checkout': 'Woocommerce Vietnam Checkout',
        'wp-mail-smtp': 'WP Mail SMTP',
        'yith-woocommerce-wishlist': 'YITH WooCommerce Wishlist',
    });
</script>