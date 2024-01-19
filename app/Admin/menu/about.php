<?php


/**
 * Chỉnh lại nội dung cho file htaccess
 * Chức năng này sẽ tự động chỉnh nội dung trong file htaccess cho phù hợp
 * Để tắt chức năng này, hãy khai báo constant WGR_DISABLE_AUTO_HTACCESS trong function của child-theme
 */
$path_htaccess = ABSPATH . ".htaccess";
$content_htaccess = file_get_contents($path_htaccess);

if (strpos($content_htaccess, '{my_domain.com}') !== false) {
    if (strpos($content_htaccess, '# Header always set Permissions-Policy ') !== false) {
        if (!defined('WGR_DISABLE_AUTO_HTACCESS')) {
            // thay thành domain hiện tại
            $content_htaccess = str_replace('{my_domain.com}', str_replace('www.', '', $_SERVER['HTTP_HOST']), $content_htaccess);
            // set header
            $content_htaccess = str_replace('# Header always set Permissions-Policy ', 'Header always set Permissions-Policy ', $content_htaccess);
            // cập nhật content
            file_put_contents($path_htaccess, $content_htaccess);
            echo 'update {my_domain.com} for ' . $path_htaccess . '<br>' . PHP_EOL;
        } else {
            echo 'WGR_DISABLE_AUTO_HTACCESS is defined' . '<br>' . PHP_EOL;
        }
    } else {
        echo 'content_htaccess has {my_domain.com}' . '<br>' . PHP_EOL;
    }
}

// kiểm tra và cập nhật phiên bản mới nếu có
function check_and_update_webgiareorg()
{
    $version = file_get_contents(WGR_BASE_PATH . 'VERSION');
    $remove_version = file_get_contents('https://raw.githubusercontent.com/itvn9online/webgiareorg/main/VERSION');

    //
    if (isset($_GET['update_wgr_code']) || version_compare($version, $remove_version, '<')) {
        //echo $version . PHP_EOL;
        //echo $remove_version . PHP_EOL;
        $dest = WP_CONTENT_DIR . '/upgrade/webgiareorg.zip';
        echo $dest . '<br>' . PHP_EOL;
        if (is_file($dest)) {
            unlink($dest);
        }

        //
        $download_url = 'https://github.com/itvn9online/webgiareorg/archive/refs/heads/main.zip';

        //
        if (!copy($download_url, $dest)) {
            return false;
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

?>
<h1>Về tác giả</h1>
<p>Phiên bản WebGiaRe code: <strong><?php echo file_get_contents(WGR_BASE_PATH . 'VERSION'); ?></strong></p>
<p>Mặc định, WebGiaRe code sẽ được cập nhật tự động mỗi khi có phiên bản mới. Bạn có thể <a href="<?php echo admin_url(); ?>admin.php?page=eb-about&update_wgr_code=1" class="bold">Bấm vào đây</a> để cập nhật lại WebGiaRe code thủ công.</p>
<p>PHP version: <strong><?php echo PHP_VERSION; ?></strong>.</p>
<p>Server IP: <strong><?php echo $_SERVER['SERVER_ADDR']; ?></strong></p>
<?php

//
if (version_compare(PHP_VERSION, '8.0.0', '<')) {
?>
    <p class="redcolor">Với các website sử dụng Wordpress, phiên bản PHP tối ưu nên dùng là PHP 8.0 trở lên (khuyên dùng 8.1). Nếu có thể! hãy nâng cấp phiên bản PHP cho website của bạn.</p>
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
    if (phpversion('redis') != '') {
    ?>
        Hiện khả dụng trên hosting của bạn, hãy cân nhắc việc kích hoạt nó cho website này.
        <ul>
            <li>Redis host: <?php echo WGR_REDIS_HOST; ?></li>
            <li>Redis port: <?php echo WGR_REDIS_PORT; ?></li>
        </ul>
        <div>Để tắt chức năng cache qua redis, hãy thêm đoạn mã sau vào file functions.php trong child-theme:</div>
        <textarea cols="1" readonly style="width: 90%; max-width: 555px;">define('EB_REDIS_CACHE', false);</textarea>
    <?php
    }
    ?>
</div>
<!-- END Redis -->
<p class="bluecolor">Khi chuyển host mà bị lỗi font, vào database -> bảng options -> option_name -> tìm và xóa hoặc đổi tên: <span class="bold">kirki_downloaded_font_files</span></p>
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
    if (window.location.href.split('update_wgr_code=').length > 1) {
        window.history.pushState("", document.title, window.location.href.split('&update_wgr_code=')[0].split('?update_wgr_code=')[0]);
    }
</script>