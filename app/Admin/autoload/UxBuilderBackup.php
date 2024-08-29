<?php

/**
 * Tự động backup định kỳ phần UX builder
 */

function autoUxBuilderBackup($space_backup = 3600)
{
    //
    $ux_builder_dir = ABSPATH . 'wgr-backup-builder';
    //echo $ux_builder_dir . '<br>' . PHP_EOL;
    if (!is_dir($ux_builder_dir)) {
        WGR_create_dir($ux_builder_dir);
    }

    //
    if (!is_dir($ux_builder_dir)) {
        return false;
    }

    // file lưu tiến trình backup lần trước
    $last_backup_ux_builder = ABSPATH . 'wgr-last-backup-ux-builder.txt';
    //echo $last_backup_ux_builder . '<br>' . PHP_EOL;
    // giãn cách backup -> trong thời gian cho phép thì hủy bỏ việc backup luôn
    if (WGR_cache_expire($last_backup_ux_builder, $space_backup)) {
        //echo date( 'r', filemtime( $last_backup_ux_builder ) );
        return false;
    }

    // tạo file htaccess chặn truy cập vào thư mục này
    $htaccess_deny_all = file_get_contents(WGR_BASE_PATH . 'app/Helpers/templates/htaccess_deny_all.txt');
    $arr_deny_all = [
        'base_url' => get_home_url(),
    ];
    foreach ($arr_deny_all as $k => $v) {
        $htaccess_deny_all = str_replace('{{' . $k . '}}', $v, $htaccess_deny_all);
    }
    WGR_create_file($ux_builder_dir . '/.htaccess', $htaccess_deny_all);

    // bắt đầu backup dữ liệu
    global $wpdb;
    //die( $wpdb->posts );

    //
    $backup_ext = '.tpl';

    //
    /*
    $data = WGR_select( "SELECT post_type
    FROM
    `" . $wpdb->posts . "`
    GROUP BY
    post_type" );
    print_r( $data );
    die( __FILE__ . ':' . __LINE__ );
    */

    /**
     * backup menu
     */
    $mot_tuan = 24 * 3600 * 7;
    $data = wp_get_nav_menus();
    //print_r( $data );
    foreach ($data as $v) {
        //print_r( $v );

        //
        $file_backup = $ux_builder_dir . '/' . $v->taxonomy . '-' . $v->term_id . '-' . $v->slug . $backup_ext;
        //echo $file_backup . '<br>' . PHP_EOL;

        // nếu chưa có backup hoặc backup đủ lâu thì backup tiếp
        if (!WGR_cache_expire($file_backup, $mot_tuan)) {
            $html = wp_nav_menu([
                'echo' => false,
                // không echo -> lấy kết quả trả về để return
                'menu' => $v->term_id,
                //'menu_class' => 'eb-set-menu-selected eb-menu cf',

            ]);
            //echo $html . PHP_EOL;

            //
            WGR_create_file($file_backup, $html);
        }
    }


    //
    $data = WGR_select("SELECT *
    FROM
        `" . $wpdb->posts . "`
    WHERE
        post_status = 'publish'
        AND ( post_type = 'page' OR post_type = 'blocks' OR post_type = 'wpcf7_contact_form' )
    ORDER BY
        ID");
    //print_r( $data );

    //
    foreach ($data as $v) {
        //echo $v->post_date . '<br>' . PHP_EOL;
        //echo $v->post_date_gmt . '<br>' . PHP_EOL;
        //echo $v->post_modified . '<br>' . PHP_EOL;
        //echo $v->post_modified_gmt . '<br>' . PHP_EOL;
        //echo $v->post_type . '<br>' . PHP_EOL;
        //print_r( $v );
        //continue;

        // lấy ngày thay đổi cuối để tạo backup -> nếu không có thay đổi thì không tạo backup
        if (!isset($v->post_modified_gmt) || empty($v->post_modified_gmt)) {
            echo 'post_modified_gmt not found!: ' . $v->post_type . '#' . $v->ID . '<br>' . PHP_EOL;
            continue;
        }
        $post_modified_gmt = date('Ymd-H', strtotime($v->post_modified_gmt));
        //echo 'post_modified_gmt: ' . $post_modified_gmt . '<br>' . PHP_EOL;

        //
        if (trim($v->post_content) == '') {
            continue;
        }

        // file tồn tại rồi thì thôi, backup tầm 1 tiếng 1 lần
        $file_backup = $ux_builder_dir . '/' . $v->post_type . '-' . $v->ID . '-' . $v->post_name . '-' . $post_modified_gmt . $backup_ext;
        if (is_file($file_backup)) {
            //echo 'Backup exist: <em>' . basename( $file_backup ) . '</em><br>' . PHP_EOL;
            continue;
        }

        //
        WGR_create_file($file_backup, $v->post_content);
        //echo 'Create new: <strong>' . basename( $file_backup ) . '</strong><br>' . PHP_EOL;
    }

    //
    WGR_create_file($last_backup_ux_builder, time());

    //
    return true;
}

//
autoUxBuilderBackup();
