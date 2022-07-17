<?php

/*
 * Tự động backup định kỳ phần UX builder
 */

function autoUxBuilderBackup( $space_backup = 3600 ) {
    if ( !defined( 'WGR_CHILD_PATH' ) ) {
        return false;
    }

    //
    //echo WGR_CHILD_PATH . '<br>' . "\n";
    $ux_builder_dir = WGR_CHILD_PATH . 'ux-builder';
    //echo $ux_builder_dir . '<br>' . "\n";
    if ( !is_dir( $ux_builder_dir ) ) {
        WGR_create_dir( $ux_builder_dir );
    }

    //
    if ( !is_dir( $ux_builder_dir ) ) {
        return false;
    }

    // file lưu tiến trình backup lần trước
    $last_backup_ux_builder = WGR_CHILD_PATH . 'last-backup-ux-builder.txt';
    //echo $last_backup_ux_builder . '<br>' . "\n";
    // giãn cách backup -> trong thời gian cho phép thì hủy bỏ việc backup luôn
    if ( WGR_cache_expire( $last_backup_ux_builder, $space_backup ) ) {
        //echo date( 'r', filemtime( $last_backup_ux_builder ) );
        return false;
    }

    //
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

    /*
     * backup menu
     */
    $mot_tuan = 24 * 3600 * 7;
    $data = wp_get_nav_menus();
    //print_r( $data );
    foreach ( $data as $v ) {
        //print_r( $v );

        //
        $file_backup = $ux_builder_dir . '/' . $v->taxonomy . '-' . $v->term_id . '-' . $v->slug . $backup_ext;
        //echo $file_backup . '<br>' . "\n";

        // nếu chưa có backup hoặc backup đủ lâu thì backup tiếp
        if ( !WGR_cache_expire( $file_backup, $mot_tuan ) ) {
            $html = wp_nav_menu( [
                'echo' => false, // không echo -> lấy kết quả trả về để return
                'menu' => $v->term_id,
                //'menu_class' => 'eb-set-menu-selected eb-menu cf',
            ] );
            //echo $html . "\n";

            //
            WGR_create_file( $file_backup, $html );
        }
    }


    //
    $data = WGR_select( "SELECT *
    FROM
        `" . $wpdb->posts . "`
    WHERE
        post_status = 'publish'
        AND ( post_type = 'page' OR post_type = 'blocks' OR post_type = 'wpcf7_contact_form' )
    ORDER BY
        ID" );
    //print_r( $data );

    //
    foreach ( $data as $v ) {
        //echo $v->post_date . '<br>' . "\n";
        //echo $v->post_date_gmt . '<br>' . "\n";
        //echo $v->post_modified . '<br>' . "\n";
        //echo $v->post_modified_gmt . '<br>' . "\n";
        //echo $v->post_type . '<br>' . "\n";
        //print_r( $v );
        //continue;

        // lấy ngày thay đổi cuối để tạo backup -> nếu không có thay đổi thì không tạo backup
        if ( !isset( $v->post_modified_gmt ) || empty( $v->post_modified_gmt ) ) {
            echo 'post_modified_gmt not found!: ' . $v->post_type . '#' . $v->ID . '<br>' . "\n";
            continue;
        }
        $post_modified_gmt = date( 'Ymd-H', strtotime( $v->post_modified_gmt ) );
        //echo 'post_modified_gmt: ' . $post_modified_gmt . '<br>' . "\n";

        //
        if ( trim( $v->post_content ) == '' ) {
            continue;
        }

        // file tồn tại rồi thì thôi, backup tầm 1 tiếng 1 lần
        $file_backup = $ux_builder_dir . '/' . $v->post_type . '-' . $v->ID . '-' . $v->post_name . '-' . $post_modified_gmt . $backup_ext;
        if ( file_exists( $file_backup ) ) {
            //echo 'Backup exist: <em>' . basename( $file_backup ) . '</em><br>' . "\n";
            continue;
        }

        //
        WGR_create_file( $file_backup, $v->post_content );
        //echo 'Create new: <strong>' . basename( $file_backup ) . '</strong><br>' . "\n";
    }

    //
    WGR_create_file( $last_backup_ux_builder, time() );

    //
    return true;
}

//
autoUxBuilderBackup();