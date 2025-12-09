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

// danh sách plugin trên github
$githubs_plugin = [
    'echbay-ai-optimize-seo' => '',
    'echbay-viettelpost-woocommerce' => '',
    'smtp-config-manager' => '',
    'mail-marketing-importer' => '',
    'echbay-email-queue' => '',
    'devvn-quick-buy' => '',
    'devvn-woocommerce-reviews' => '',
];

/**
 * Nếu tồn tại tham số download_github_plugin thì sẽ tải plugin từ github về
 * Ví dụ:
 * wp-admin/admin.php?page=eb-about&download_github_plugin=plugin-in-github
 * Sẽ tải plugin plugin-in-github từ github về thư mục wp-content/plugins/plugin-in-github-main
 * https://github.com/itvn9online/$_GET['download_github_plugin']/archive/refs/heads/main.zip
 */
if (isset($_GET['download_github_plugin']) && !empty($_GET['download_github_plugin'])) {
    $plugin_name = sanitize_text_field($_GET['download_github_plugin']);
    if (isset($githubs_plugin[$plugin_name])) {
        $dest = WP_PLUGIN_DIR . '/' . $plugin_name . '-main.zip';
        if (is_file($dest)) {
            unlink($dest);
        }

        if ($githubs_plugin[$plugin_name] == '') {
            $githubs_plugin[$plugin_name] = 'https://github.com/itvn9online/' . $plugin_name;
        }

        if (copy($githubs_plugin[$plugin_name] . '/archive/refs/heads/main.zip', $dest)) {
            echo 'Download plugin: <strong>' . $plugin_name . '</strong> success!<br>' . "\n";
            echo 'File has been save to: <strong>' . $dest . '</strong><br>' . "\n";

            // giải nén file zip
            if (class_exists('ZipArchive')) {
                $zip = new ZipArchive;
                if ($zip->open($dest) === TRUE) {
                    $zip->extractTo(WP_PLUGIN_DIR);
                    $zip->close();

                    // đổi tên thư mục
                    $myoldfolder = WP_PLUGIN_DIR . '/' . $plugin_name;
                    $mynewfolder = WP_PLUGIN_DIR . '/' . $plugin_name . '-' . date('Ymd-His');
                    if (is_dir($myoldfolder)) {
                        rename($myoldfolder, $mynewfolder);
                    }

                    echo 'Unzip to: <strong>' . $myoldfolder . '</strong><br>' . "\n";

                    // xóa file zip sau khi giải nén
                    unlink($dest);
                    echo 'Unzip file success!<br>' . "\n";
                    echo 'Plugin: <strong>' . $plugin_name . '</strong> has been updated!<br>' . "\n";
                } else {
                    echo 'Unzip file faild!<br>' . "\n";
                }
            } else {
                echo 'ZipArchive class not found!<br>' . "\n";
            }
        } else {
            echo 'Download plugin: <strong>' . $plugin_name . '</strong> faild!<br>' . "\n";
        }
    } else {
        echo 'Plugin: <strong>' . $plugin_name . '</strong> not found!<br>' . "\n";
    }
}

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
                echo 'update {my_domain.com} for ' . $path_htaccess . '<br>' . "\n";
            } else {
                echo 'WGR_DISABLE_AUTO_HTACCESS is defined' . '<br>' . "\n";
            }
        } else {
            echo 'content_htaccess has {my_domain.com}' . '<br>' . "\n";
        }
    }
}

// tạo file htaccess chặn truy cập vào file php trong thư mục uploads
// echo ABSPATH . '<br>' . "\n";
if (is_dir(ABSPATH . 'wp-content') && is_dir(ABSPATH . 'wp-content/uploads')) {
    $path_htaccess = ABSPATH . 'wp-content/uploads/.htaccess';
    if (!is_file($path_htaccess)) {
        // tạo file htaccess với nội dung chặn truy cập vào file php
        file_put_contents($path_htaccess, trim('
# Begin Really Simple Security
<Files *.php>
deny from all
</Files>
# End Really Simple Security
'), LOCK_EX);

        // 
        echo 'update uploads/*.php for ' . $path_htaccess . '<br>' . "\n";
    }
}

// kiểm tra và cập nhật phiên bản mới nếu có
function check_and_update_webgiareorg()
{
    $version = file_get_contents(WGR_BASE_PATH . 'VERSION');
    if (isset($_GET['update_wgr_code'])) {
        $remote_version = $version;
    } else {
        $remote_version = WGR_get_contents('https://raw.echbay.com/itvn9online/webgiareorg/main/VERSION');
        // $remote_version = file_get_contents('https://flatsome.webgiare.org/wp-content/webgiareorg/VERSION');
    }
    // $remote_version = '24.08.20';
    // echo EB_MY_CACHE_CONFIG . '<br>' . "\n";
    // echo EB_THEME_CACHE . '<br>' . "\n";

    //
    if (isset($_GET['update_wgr_code']) || version_compare($version, $remote_version, '<')) {
        // echo $version . "\n";
        // echo $remote_version . "\n";
        $dest = WP_CONTENT_DIR . '/upgrade/webgiareorg.zip';
        echo 'File zip has been save to: ' . $dest . '<br>' . "\n";
        if (is_file($dest)) {
            unlink($dest);
        }

        //
        $download_url = 'https://github.com/itvn9online/webgiareorg/archive/refs/heads/main.zip';

        //
        if (!copy($download_url, $dest)) {
            echo 'ERROR copy file from link: ' . $download_url . '<br>' . "\n";

            // 
            if (!file_put_contents($dest, file_get_contents($download_url))) {
                echo 'ERROR file_get_contents file from link: ' . $download_url . '<br>' . "\n";

                // 
                if (!file_put_contents($dest, fopen($download_url, 'r'))) {
                    echo 'ERROR fopen file from link: ' . $download_url . '<br>' . "\n";
                    return false;
                } else {
                    echo 'FOPEN file from link: ' . $download_url . '<br>' . "\n";
                }
            } else {
                echo 'GET_CONTENT file from link: ' . $download_url . '<br>' . "\n";
            }
        } else {
            echo 'COPY file from link: ' . $download_url . '<br>' . "\n";
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
                    echo $myoldfolder . '<br>' . "\n";

                    // đổi tên thư mục code cũ
                    $mynewfolder = '';
                    if (is_dir($myoldfolder)) {
                        $mynewfolder = $myoldfolder . '-' . date('Ymd-His');
                        echo $mynewfolder . '<br>' . "\n";

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
<p>Server IP: <strong><?php echo $_SERVER['SERVER_ADDR']; ?></strong> | Client IP: <strong><?php echo WGR_getIPAddress(); ?></strong></p>
<p>Server date: <strong><?php echo date('r'); ?></strong> | date_i18n: <strong><?php echo date_i18n('r'); ?></strong> | date_i18n (date_format time_format): <strong><?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format')); ?></strong> | current_time (mysql): <strong><?php echo current_time('mysql'); ?></strong></p>
<p>Wordpress timezone: <strong><?php echo get_option('timezone_string'); ?></strong></p>
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
<p>Thêm đoạn code này vào file wp-config.php nếu muốn cố định URL cho website:</p>
<div>
    <textarea rows="4" ondblclick="this.select();" readonly style="width: 90%;">
// set static siteurl
define('WP_SITEURL', 'https://<?php echo $_SERVER['HTTP_HOST']; ?>'); // add by Webgiareorg
define('WP_HOME', WP_SITEURL); // add by Webgiareorg</textarea>
</div>
<br />
<!-- -->
<p class="bluecolor">Khi chuyển host mà bị lỗi font, vào database (phpmyadmin) -> bảng options -> option_name -> tìm và Xóa hoặc Đổi tên: <span class="bold">kirki_downloaded_font_files</span></p>
<div>
    <textarea rows="2" ondblclick="this.select();" readonly style="width: 90%;">
UPDATE `<?php echo $wpdb->prefix; ?>options` SET `option_name` = 'kirki_downloaded_font_files_<?php echo time(); ?>' WHERE `<?php echo $wpdb->prefix; ?>options`.`option_name` = 'kirki_downloaded_font_files';</textarea>
</div>
<br />
<!-- -->
<p class="bluecolor">Khi chuyển host mà bị cảnh báo liên quan đến open_basedir, vào database (phpmyadmin) -> bảng options -> option_name -> tìm và Xóa hoặc Đổi tên: <span class="bold">_site_transient_woocommerce_blocks_patterns</span></p>
<div>
    <textarea rows="2" ondblclick="this.select();" readonly style="width: 90%;">
UPDATE `<?php echo $wpdb->prefix; ?>options` SET `option_name` = '_site_transient_woocommerce_blocks_patterns_<?php echo time(); ?>' WHERE `<?php echo $wpdb->prefix; ?>options`.`option_name` = '_site_transient_woocommerce_blocks_patterns';</textarea>
</div>
<br />
<!-- -->
<div>
    <h3>This theme recommends the following plugins</h3>
    <ol id="wgr-recommends-following-plugins">
        <?php
        // lấy thư mục plugin
        // echo WP_PLUGIN_DIR . '<br>' . "\n";

        // 
        foreach (
            [
                'advanced-custom-fields' => 'Advanced Custom Fields (ACF)',
                'tinymce-advanced' => 'Advanced Editor Tools',
                'amp' => 'AMP',
                'classic-editor' => 'Classic Editor',
                // 'classic-widgets' => 'Classic Widgets',
                'contact-form-7' => 'Contact Form 7',
                'echbay-admin-security' => 'EchBay Admin Security',
                'echbay-phonering-alo' => 'EchBay Phonering Alo',
                'echbay-search-everything' => 'EchBay Search Everything',
                'easy-table-of-contents' => 'Easy Table of Contents',
                'ajax-search-for-woocommerce' => 'FiboSearch - Ajax Search for WooCommerce',
                'flamingo' => 'Flamingo',
                'facebook-messenger-customer-chat' => 'Facebook Chat Plugin - Live Chat Plugin for WordPress',
                'facebook-for-woocommerce' => 'Facebook for WooCommerce',
                // 'nextend-facebook-connect' => 'Nextend Social Login and Register',
                'official-facebook-pixel' => 'Meta pixel for WordPress',
                'google-site-kit' => 'Google Site Kit',
                'polylang' => 'Polylang',
                'post-duplicator' => 'Post Duplicator',
                'seo-by-rank-math' => 'Rank Math SEO',
                'wp-smushit' => 'Smush Image Optimization',
                'speculation-rules' => 'Speculative Loading',
                'tiny-compress-images' => 'TinyPNG',
                'woocommerce' => 'WooCommerce',
                'woo-vietnam-checkout' => 'Woocommerce Vietnam Checkout',
                'wp-mail-smtp' => 'WP Mail SMTP',
                'yith-woocommerce-wishlist' => 'YITH WooCommerce Wishlist',
                // plugin in github
                'echbay-ai-optimize-seo' => '',
                'echbay-viettelpost-woocommerce' => '',
                'smtp-config-manager' => '',
                'mail-marketing-importer' => '',
                'echbay-email-queue' => '',
                'devvn-quick-buy' => '',
                'devvn-woocommerce-reviews' => '',
            ] as $k => $v
        ) {
            if ($v == '') {
                $v = str_replace('-', ' ', ucfirst($k));
            }

            // nếu thư mục code có rồi thì bỏ qua
            if (is_dir(WP_PLUGIN_DIR . '/' . $k)) {
        ?>
                <li><?php echo $v; ?></li>
                <?php
            } else if (isset($githubs_plugin[$k])) {
                if ($githubs_plugin[$k] == '') {
                    $githubs_plugin[$k] = 'https://github.com/itvn9online/' . $k;
                }

                if (is_dir(WP_PLUGIN_DIR . '/' . $k . '-main')) {
                ?>
                    <li><a href="<?php echo $githubs_plugin[$k]; ?>" target="_blank" rel="nofollow"><?php echo $v; ?></a></li>
                <?php
                } else {
                ?>
                    <li><a href="<?php echo $githubs_plugin[$k]; ?>" target="_blank" rel="nofollow" class="bold"><?php echo $v; ?></a> (<a href="<?php echo $_SERVER['REQUEST_URI']; ?>&download_github_plugin=<?php echo $k; ?>">Download now</a>)</li>
                <?php
                }
            } else {
                ?>
                <li><a href="#" data-name="<?php echo $k; ?>" class="thickbox bold"><?php echo $v; ?></a></li>
        <?php
            }
        }
        ?>
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
    // nếu tồn tại tham số update_wgr_code trên URL thì xóa nó đi sau đó tải lại trang sau 1 giây
    (function() {
        if (window.location.href.includes('update_wgr_code=') == true) {
            var url = new URL(window.location.href);
            url.searchParams.delete("update_wgr_code");
            window.history.replaceState({}, document.title, url);

            // tải lại trang sau khi cập nhật
            setTimeout(function() {
                window.location.reload();
            }, 1000);
        }
    })();

    // tạo danh sách các plugin khuyên dùng
    (function(arr) {
        let str = '',
            w = Math.ceil(jQuery(window).width() / 100 * 70),
            h = Math.ceil(jQuery(window).height() / 100 * 80);
        jQuery('#wgr-recommends-following-plugins a').each(function() {
            let x = jQuery(this).attr('data-name') || '';
            if (x != '') {
                jQuery(this).attr({
                    href: web_link + 'wp-admin/plugin-install.php?tab=plugin-information&plugin=' + x + '&TB_iframe=true&width=' + w + '&height=' + h
                })
            }
        });
    })();

    // nếu tồn tại tham số download_github_plugin trên URL thì xóa nó đi
    (function() {
        var url = new URL(window.location.href);
        url.searchParams.delete("download_github_plugin");
        window.history.replaceState({}, document.title, url);
    })();
</script>