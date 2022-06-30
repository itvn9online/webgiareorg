<?php

//
//echo __FILE__;

// chức năng này không chạy trong môi trường ajax
if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
    //echo 'it\'s an Ajax call';
} else {
    //echo __FILE__ . '<br>' . "\n";

    /*
     * đồng bộ code và database nếu có
     */
    include __DIR__ . '/Sync.php';
    WGR_vendor_sync();

    //
    include __DIR__ . '/UxBuilderBackup.php';
    autoUxBuilderBackup();

    // nạp header + footer cho admin
    include __DIR__ . '/Header.php';
    include __DIR__ . '/Footer.php';
}