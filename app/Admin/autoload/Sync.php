<?php

/**
 * Đồng bộ code và database
 */

//
function WGR_vendor_sync($check_thirdparty_exist = true)
{
    $last_sync_vendor = WGR_BASE_PATH . 'last-sync-vendor.txt';
    // echo $last_sync_vendor . '<br>' . "\n";
    // giãn cách sync -> trong thời gian cho phép thì hủy bỏ việc sync luôn
    if (WGR_cache_expire($last_sync_vendor)) {
        // echo __FILE__ . ':' . __LINE__ . '<br>' . "\n";
        return false;
    }

    // đồng bộ vendor CSS, JS -> đặt tên là thirdparty để tránh trùng lặp khi load file tĩnh ngoài frontend
    WGR_action_vendor_sync(WGR_BASE_PATH . 'public/thirdparty', $check_thirdparty_exist);
    // đồng bộ vendor php
    WGR_action_vendor_sync(WGR_BASE_PATH . 'vendor', $check_thirdparty_exist);
    // đồng bộ ThirdParty php (code php của bên thứ 3)
    WGR_action_vendor_sync(WGR_BASE_PATH . 'app/ThirdParty', $check_thirdparty_exist);

    //
    WGR_create_file($last_sync_vendor, time());
}

/**
 * daidq: chức năng này sẽ giải nén các code trong thư mục vendor dể sử dụng nếu chưa có
 */
function WGR_action_vendor_sync($dir, $check_thirdparty_exist = true)
{
    $dir = rtrim($dir, '/');
    //echo $dir . '<br>' . "\n";
    if (!is_dir($dir)) {
        return false;
    }
    $test_permission = $dir . '/test_permission.txt';
    //echo $test_permission . '<br>' . "\n";

    // thử tạo file trong thư mục unzip xem có tạo được không
    if (!WGR_create_file($test_permission, time())) {
        // nếu không tạo được -> báo lỗi luôn
        echo 'Please set permistion for folder: ' . $dir . '<br>' . "\n";
        die(__FILE__ . ':' . __LINE__);
        /*
    } else {
        unlink( $test_permission );
        */
    }
    //die( __FILE__ . ':' . __LINE__ );
    //return false;

    //
    foreach (glob($dir . '/*.zip') as $filename) {
        //echo $filename . '<br>' . "\n";
        //continue;

        //
        $file = basename($filename, '.zip');
        $check_dir = $dir . '/' . $file;
        //echo $check_dir . '<br>' . "\n";
        //continue;

        // nếu chưa có thư mục -> giải nén
        if (!is_dir($check_dir)) {
            if (WGR_MY_unzip($filename, $dir) === TRUE) {
                echo 'DONE! sync code ' . $file . ' <br>' . "\n";
            } else {
                echo 'ERROR! sync code ' . $file . ' <br>' . "\n";
            }
            /*
        } else {
            echo $file . ' has been sync <br>' . "\n";
            */
        }
    }
}

/**
 * unzip file
 */
function WGR_MY_unzip($file, $dir)
{
    echo $file . '<br>' . "\n";
    echo $dir . '<br>' . "\n";

    //
    $zip = new \ZipArchive();
    if ($zip->open($file) === TRUE) {
        $zip->extractTo(rtrim($dir, '/') . '/');
        $zip->close();
        return TRUE;
    }
    return false;
}

/**
 * Tạo file header, footer nếu chưa có
 * Mục đích là để nạp cache và các file css, js trong child theme
 */
function WGR_create_header_footer_file()
{
    $header_file = WGR_CHILD_PATH . 'header.php';
    if (!is_file($header_file)) {
        copy(WGR_CHILD_PATH . 'themes-mau/header.php', $header_file);
    }

    //
    $footer_file = WGR_CHILD_PATH . 'footer.php';
    if (!is_file($footer_file)) {
        copy(WGR_CHILD_PATH . 'themes-mau/footer.php', $footer_file);
    }
}

//
WGR_vendor_sync();
WGR_create_header_footer_file();
