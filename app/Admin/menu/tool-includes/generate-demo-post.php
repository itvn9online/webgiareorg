<?php

/**
 * Công cụ tạo dữ liệu demo
 * Mặc định là tạo post, nếu có thêm tham số GET for_type thì tạo theo for_type đó (chỉ chấp nhận post và product)
 * Quy trinh tạo dữ liệu demo:
 * Cần tạo mới 1 tài khoản với username là userdemo (không cần md5) và email là emaildemo@$_SERVER['HTTP_HOST'] với mật khẩu là md5 timestamp hiện tại để insert dữ liệu. Phân quyền:
 * - Nếu website có WooCommerce thì tạo phân quyền là: `shop_manager`
 * - Mặc định sẽ tạo phân quyền là: `editor`
 * 
 * Ảnh Bài viết/ Sản phẩm sẽ chạy vòng lặp từ 1 đến 20 để tải từ `https://img1.webgiare.org/random/demo-${i}.jpg` trong đó `${i}` là số từ 1 đến 20 ( trong cùng 1 tháng, cần kiểm tra nếu ảnh đã tải về rồi thì không tải nữa, url ảnh thường có dạng `https://$_SERVER['HTTP_HOST']/wp-content/uploads/${year}/${month}/demo-${i}.jpg` )
 * 
 * Danh mục Bài viết/ Sản phẩm sẽ tạo 1 mảng dạng `'taxonomy' => ['category', 'Danh mục B', 'Danh mục C', ...]` và gán ngẫu nhiên, trong đó `taxonomy` là tên taxonomy (category, product_cat, ...) Mỗi lần chạy sẽ tạo khoảng 3-5 danh mục.
 * - 50% danh mục vừa tạo sẽ tạo thêm 3-5 danh mục con trong nó và cũng gán ngẫu nhiên Bài viết/ Sản phẩm vào danh mục con này.
 * Tên Bài viết/ Sản phẩm sẽ là: `Demo Post #${i}` hoặc `Demo Product #${i}` trong đó `${i}` là số ngẫu nhiên từ 5-10 (đây cũng chính là số sản phẩm sẽ tạo trên mỗi danh mục)
 * Nội dung Bài viết/ Sản phẩm sẽ là đoạn văn bản mẫu (Lorem ipsum...) kèm theo 1 đến 3 ảnh ngẫu nhiên trong khoảng 1 đến 20, ảnh ngẫu nhiên thường có dạng `<p><img class="alignnone wp-image-${attachment_id} size-full" src="https://$_SERVER['HTTP_HOST']/wp-content/uploads/${year}/${month}/demo-${i}.jpg" alt="" width="450" height="450" /></p>`.
 * Ảnh Bài viết/ Sản phẩm sẽ tạo ngẫu nhiên 1 số từ 1 đến 20 rồi tạo url ảnh và lấy ID ảnh đã tải về gán làm ảnh đại diện.
 * Với sản phẩm sẽ thêm giá ngẫu nhiên từ 100.000đ đến 1.000.000đ (với bước nhảy là 1.000đ) và Ngẫu nhiên 50% sản phẩm có giá khuyến mãi.
 * - Với sản phẩm sẽ thêm gallery khoảng 3-5 ảnh.
 * - Ngẫu nhiên 50% sản phẩm sẽ thiết lập `_bubble_new` là `Enabled` và đồng thời thiết lập `_bubble_text` ngẫu nhiên là `NEW` hoặc `HOT`.
 * Sau khi submit form, với mỗi post_type sẽ kiểm tra xem có sản phẩm demo chưa, nếu có rồi thì không tạo nữa (kiểm tra post_type tạo bởi demo user).
 * 
 * Yêu cầu:
 * Mỗi request chỉ tạo tối đa ${max_post_request} Bài viết/ Sản phẩm để tránh timeout, nếu chưa đủ ${max_post_demo} Bài viết/ Sản phẩm thì reload lại trang để tiếp tục tạo Bài viết/ Sản phẩm.
 * Có chức năng XÓA toàn bộ dữ liệu demo đã tạo (dữ liệu được tạo bởi user demo). Dữ liệu XÓA bao gồm cả user demo, hình ảnh demo, danh mục demo, Bài viết và Sản phẩm demo... Tất cả dữ liệu liên quan đến user demo trong module này.
 */

// Lấy post_type từ GET, mặc định là post
$post_type = isset($_GET['for_type']) && in_array($_GET['for_type'], ['post', 'product'])
    ? $_GET['for_type']
    : 'post';

// Kiểm tra xem có WooCommerce không
$has_woocommerce = class_exists('WooCommerce');
// Giới hạn số lượng Bài viết/ Sản phẩm mỗi request và tổng số cần tạo
$max_post_request = 30;
$max_post_demo = $max_post_request * 2; // Tăng tổng số cần tạo lên gấp đôi

?>
<div class="wrap">
    <h2>Tạo dữ liệu <?php echo $post_type; ?> demo</h2>

    <?php
    if (isset($_GET['action']) && $_GET['action'] === 'generate') {
        // tăng thời gian thực thi tối đa
        set_time_limit(99);
        // hiển thị lỗi để dễ debug
        ini_set('display_errors', 1);
        error_reporting(E_ALL);

        // Tạo tài khoản demo
        $demo_email = 'emaildemo@' . $_SERVER['HTTP_HOST'];
        $demo_password = md5(time());
        $demo_username = 'userdemo';

        // Kiểm tra user đã tồn tại chưa
        $user_id = username_exists($demo_username);

        if (!$user_id) {
            $user_id = wp_create_user($demo_username, $demo_password, $demo_email);

            if (!is_wp_error($user_id)) {
                // Phân quyền
                $user = new WP_User($user_id);
                $role = $has_woocommerce && $post_type === 'product' ? 'shop_manager' : 'editor';
                $user->set_role($role);

                echo '<div class="notice notice-success"><p>✓ Đã tạo tài khoản: <strong>' . $demo_username . '</strong> / <strong>' . $demo_password . '</strong> (Phân quyền: ' . $role . ')</p></div>';
            }
        } else {
            echo '<div class="notice notice-info"><p>Tài khoản demo đã tồn tại: <strong>' . $demo_username . '</strong></p></div>';
            $user_id = get_user_by('login', $demo_username)->ID;
        }

        // Kiểm tra số lượng dữ liệu demo đã có cho post_type này (dùng SQL trực tiếp để nhanh hơn)
        global $wpdb;
        $existing_demo_count = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_author = %d",
            $post_type,
            $user_id
        ));

        // Nếu đã đủ 100 bài viết/sản phẩm thì dừng lại
        if ($existing_demo_count > 100) {
            $post_type_name = $post_type === 'product' ? 'sản phẩm' : 'bài viết';
            echo '<div class="notice notice-success"><p><strong>✓ Hoàn tất!</strong> Đã có đủ ' . $existing_demo_count . ' ' . $post_type_name . ' demo được tạo bởi tài khoản <strong>' . $demo_username . '</strong>.</p></div>';
            echo '<p><a href="' . admin_url('edit.php' . ($post_type === 'product' ? '?post_type=product' : '')) . '" class="button button-primary">Xem danh sách ' . $post_type . '</a></p>';
    ?>
</div>
<?php
            return;
        }

        // Hiển thị số lượng hiện có
        if ($existing_demo_count > 0) {
            $post_type_name = $post_type === 'product' ? 'sản phẩm' : 'bài viết';
            echo '<div class="notice notice-info"><p>Đã có <strong>' . $existing_demo_count . '</strong> ' . $post_type_name . ' demo. Tiếp tục tạo thêm...</p></div>';
        }

        // ===== BƯỚC 1: KIỂM TRA VÀ TẢI ẢNH =====
        // Kiểm tra số lượng ảnh đã có
        $existing_images = get_posts([
            'post_type'      => 'attachment',
            'author'         => $user_id,
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'meta_query'     => [
                [
                    'key'     => '_wp_attached_file',
                    'value'   => 'demo-',
                    'compare' => 'LIKE'
                ]
            ]
        ]);

        $existing_count = count($existing_images);

        // Nếu chưa đủ 20 ảnh, tải thêm
        if ($existing_count < 20) {
            // set_time_limit(30); // Giới hạn 30 giây

            echo '<div class="notice notice-info"><p>Đang tải ảnh demo... (' . $existing_count . '/20)</p></div>';

            // Tạo danh sách ảnh đã có
            $existing_image_numbers = [];
            foreach ($existing_images as $img_id) {
                $file = get_attached_file($img_id);
                if (preg_match('/demo-(\d+)\.jpg$/', $file, $matches)) {
                    $existing_image_numbers[] = (int)$matches[1];
                }
            }

            // Tải thêm ảnh chưa có
            $downloaded = 0;
            for ($img_i = 1; $img_i <= 20; $img_i++) {
                if (in_array($img_i, $existing_image_numbers)) {
                    continue; // Đã có ảnh này rồi
                }

                $image_url = 'https://img1.webgiare.org/random/demo-' . $img_i . '.jpg';
                $image_filename = 'demo-' . $img_i . '.jpg';

                require_once(ABSPATH . 'wp-admin/includes/media.php');
                require_once(ABSPATH . 'wp-admin/includes/file.php');
                require_once(ABSPATH . 'wp-admin/includes/image.php');

                $tmp = download_url($image_url);

                if (!is_wp_error($tmp)) {
                    $file_array = [
                        'name'     => $image_filename,
                        'tmp_name' => $tmp
                    ];

                    $attachment_id = media_handle_sideload($file_array, 0, null, ['post_author' => $user_id]);

                    if (!is_wp_error($attachment_id)) {
                        $downloaded++;
                        echo '<div class="notice notice-success"><p>✓ Đã tải: demo-' . $img_i . '.jpg</p></div>';
                    } else {
                        @unlink($file_array['tmp_name']);
                    }
                }

                // Kiểm tra thời gian, nếu gần hết thì dừng lại
                if ($downloaded >= 5) {
                    break; // Tải tối đa 5 ảnh mỗi lần để tránh timeout
                }
            }

            // Kiểm tra lại số lượng sau khi tải
            $new_count = $existing_count + $downloaded;

            // if ($new_count < 20) {
            // Chưa đủ, reload lại trang
            echo '<div class="notice notice-warning"><p>Đã tải thêm ' . $downloaded . ' ảnh. Đang tiếp tục... (' . $new_count . '/20)</p></div>';
            echo '<script>setTimeout(function(){ window.location.reload(); }, 5000);</script>';
            echo '<p><em>Trang sẽ tự động tải lại sau 5 giây...</em></p>';
?>
    </div>
<?php
            return;
            // } else {
            // echo '<div class="notice notice-success"><p>✓ Đã tải đủ 20 ảnh demo!</p></div>';
            // }
        } else {
            echo '<div class="notice notice-success"><p>✓ Đã có sẵn ' . $existing_count . ' ảnh demo</p></div>';
        }

        // Lấy lại cache ảnh từ database (tái sử dụng $existing_images đã query ở trên)
        $image_cache = [];

        foreach ($existing_images as $img_id) {
            $file = get_attached_file($img_id);
            if (preg_match('/demo-(\d+)\.jpg$/', $file, $matches)) {
                $image_cache[(int)$matches[1]] = $img_id;
            }
        }
        // $view_image_cache = print_r($image_cache, true);
        // echo '<pre>' . htmlspecialchars($view_image_cache) . '</pre>';
        // die(__FILE__ . ':' . __LINE__);

        // echo '<div class="notice notice-success"><p>✓ Đã tải sẵn ' . count($image_cache) . ' ảnh để sử dụng</p></div>';

        // 
        $lipsum_stext = [
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam tincidunt semper faucibus. Morbi non elit sapien. Cras sem ex, sagittis at varius dictum, condimentum non enim. Nulla ullamcorper id massa placerat pulvinar. In quam tortor, scelerisque hendrerit sagittis in, ultrices ut arcu. Duis consequat porta mattis. Praesent facilisis lacus at ligula rutrum fermentum. Quisque id lacus vitae diam pellentesque vehicula vitae eu nisi. Vivamus elementum turpis sapien, in pretium lectus tristique eu.',
            'Phasellus et vehicula ex, quis bibendum ante. Integer vel eros rhoncus, aliquam nunc nec, feugiat dui. Sed pellentesque urna eu interdum dictum. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Nunc varius consectetur dui, varius varius sapien venenatis sit amet. Mauris ultrices laoreet sapien vel tincidunt. Sed id diam varius, sodales purus a, venenatis neque. Praesent dictum arcu eu facilisis vestibulum. Mauris nec mauris tellus. Integer bibendum est urna. Curabitur in sapien a nulla luctus vehicula. Sed odio tortor, iaculis sed velit bibendum, placerat congue orci.',
            'Donec ultricies tincidunt dolor, a finibus ante maximus at. Quisque vel ullamcorper ex. Aliquam cursus eget sem sed varius. Nullam molestie tempus neque ut semper. In faucibus mauris sed massa faucibus imperdiet. Cras purus ex, ornare sit amet leo quis, aliquam venenatis nisi. Donec dapibus, felis sit amet iaculis sodales, tortor augue eleifend erat, non ultricies augue tellus sed velit. Integer porta laoreet quam non facilisis. Curabitur sapien turpis, finibus et tortor in, vulputate elementum nisi. Morbi at tempor mi. Etiam quis ante a tellus posuere efficitur.',
            'Cras dolor sapien, faucibus eu purus eget, scelerisque euismod nulla. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse potenti. Sed luctus nibh quis euismod sodales. Nulla ac lacus ipsum. Morbi mollis augue non ullamcorper accumsan. Curabitur mattis viverra sem, eu iaculis felis efficitur vel. Vivamus ac enim volutpat, fringilla mi vel, maximus mi. Donec venenatis a velit sit amet sagittis. Mauris pharetra egestas porta. Vivamus viverra, arcu vitae consequat pellentesque, orci risus fermentum lacus, eget lacinia neque lectus molestie felis. Etiam consectetur sed ante sit amet posuere. In a est lectus. Donec et enim blandit, hendrerit mi in, viverra justo.',
            'Phasellus auctor molestie magna, in posuere augue tincidunt vel. Phasellus sed tortor aliquet, ultricies magna a, dictum metus. Morbi at nulla nisl. Nulla molestie volutpat nunc at vulputate. In hac habitasse platea dictumst. Mauris venenatis felis et ipsum condimentum, nec convallis augue accumsan. Fusce fringilla ligula lectus, et luctus elit elementum ac. Nulla eget suscipit ante.',
            'Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Aenean euismod, risus in facilisis congue, erat libero efficitur erat, in convallis ligula odio a nunc. Curabitur euismod, augue in facilisis cursus, libero erat venenatis urna, a tincidunt libero risus nec libero. In hac habitasse platea dictumst. Nullam euismod, nisi vel consectetur interdum, nisl nunc consectetur nunc, nec gravida nunc nisl at libero. Sed euismod, nisl vel consectetur interdum, nisl nunc consectetur nunc, nec gravida nunc nisl at libero.',
            'Nam euismod, nisi vel consectetur interdum, nisl nunc consectetur nunc, nec gravida nunc nisl at libero. Sed euismod, nisl vel consectetur interdum, nisl nunc consectetur nunc, nec gravida nunc nisl at libero. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Aenean euismod, risus in facilisis congue, erat libero efficitur erat, in convallis ligula odio a nunc.',
            'Curabitur euismod, augue in facilisis cursus, libero erat venenatis urna, a tincidunt libero risus nec libero. In hac habitasse platea dictumst. Nullam euismod, nisi vel consectetur interdum, nisl nunc consectetur nunc, nec gravida nunc nisl at libero. Sed euismod, nisl vel consectetur interdum, nisl nunc consectetur nunc, nec gravida nunc nisl at libero.',
        ];

        // ===== BƯỚC 2: TẠO DANH MỤC VÀ BÀI VIẾT =====
        // Xác định taxonomy
        $taxonomy = $post_type === 'product' ? 'product_cat' : 'category';
        // tạo tên danh mục theo taxonomy + viết HOA chữ cái đầu
        $taxonomy_name = ucfirst(str_replace('_', ' ', $taxonomy));

        // Tạo danh mục (3-5 danh mục)
        $num_categories = rand(3, 5);
        $category_ids = [];

        echo '<div class="notice notice-info"><p>Đang tạo ' . $num_categories . ' danh mục...</p></div>';

        for ($i = 1; $i <= $num_categories; $i++) {
            $cat_name = $taxonomy_name . ' Demo ' . chr(64 + $i); // A, B, C, D, E

            // Kiểm tra danh mục đã tồn tại chưa
            $term = term_exists($cat_name, $taxonomy);

            if (!$term) {
                $term = wp_insert_term($cat_name, $taxonomy, [
                    'description' => 'Danh mục demo tự động tạo bởi hệ thống. ' . $lipsum_stext[array_rand($lipsum_stext)]
                ]);
            }

            if (!is_wp_error($term)) {
                $category_ids[] = $term['term_id'];
                echo '<div class="notice notice-success"><p>✓ Đã tạo danh mục: <strong>' . $cat_name . '</strong></p></div>';
            }
        }

        // Tạo danh mục con (50% cơ hội cho mỗi danh mục cha)
        $all_category_ids = $category_ids; // Lưu tất cả category IDs (bao gồm cả con)

        foreach ($category_ids as $parent_id) {
            // 50% cơ hội tạo danh mục con
            if (rand(0, 1) === 1) {
                $num_sub_categories = rand(3, 5);
                $parent_term = get_term($parent_id, $taxonomy);

                echo '<div class="notice notice-info"><p>Đang tạo ' . $num_sub_categories . ' danh mục con cho <strong>' . $parent_term->name . '</strong>...</p></div>';

                for ($j = 1; $j <= $num_sub_categories; $j++) {
                    $sub_cat_name = $parent_term->name . ' - Con ' . $j;

                    // Kiểm tra danh mục con đã tồn tại chưa
                    $sub_term = term_exists($sub_cat_name, $taxonomy);

                    if (!$sub_term) {
                        $sub_term = wp_insert_term($sub_cat_name, $taxonomy, [
                            'parent' => $parent_id,
                            'description' => 'Danh mục con demo tự động tầng bởi hệ thống. ' . $lipsum_stext[array_rand($lipsum_stext)]
                        ]);
                    }

                    if (!is_wp_error($sub_term)) {
                        $all_category_ids[] = $sub_term['term_id'];
                        echo '<div class="notice notice-success"><p>✓ Đã tạo danh mục con: <strong>' . $sub_cat_name . '</strong></p></div>';
                    }
                }
            }
        }

        // Tạo bài viết/sản phẩm (5-10 items cho mỗi danh mục, bao gồm cả danh mục con)
        $total_created = 0;
        $max_per_request = rand($max_post_request - 10, $max_post_request); // Giới hạn tối đa ${max_post_request} bài/sản phẩm mỗi request

        // Hàm helper để lấy thông tin ảnh để chèn vào content
        $get_image_html = function ($attachment_id) {
            $image_url = wp_get_attachment_url($attachment_id);
            return '<p><img class="alignnone wp-image-' . $attachment_id . ' size-full" src="' . $image_url . '" alt="" width="450" height="450" /></p>';
        };

        foreach ($all_category_ids as $cat_id) {
            // Kiểm tra nếu đã tạo đủ ${max_post_request} bài trong request này thì dừng lại
            if ($total_created >= $max_per_request) {
                break;
            }

            $num_posts = rand(5, 10);

            for ($i = 1; $i <= $num_posts; $i++) {
                // Kiểm tra nếu đã tạo đủ ${max_post_request} bài trong request này thì dừng lại
                if ($total_created >= $max_per_request) {
                    break;
                }
                // Tên bài viết
                $post_title = $post_type === 'product'
                    ? 'Demo Product #' . $i
                    : 'Demo Post #' . $i;

                // Nội dung mẫu với 3-5 ảnh ngẫu nhiên
                $num_content_images = rand(3, 5);
                $post_content = '<h2>Đây là nội dung demo cho ' . $post_title . '</h2>';
                // lấy một đoạn văn bản mẫu ngẫu nhiên trong mảng $lipsum_stext
                $post_content .= '<p>' . $lipsum_stext[array_rand($lipsum_stext)] . '</p>';

                // Thêm ảnh ngẫu nhiên vào nội dung
                for ($ci = 0; $ci < $num_content_images; $ci++) {
                    $random_img_num = rand(1, 20);
                    if (isset($image_cache[$random_img_num])) {
                        // tạo thẻ heading ngẫu nhiên
                        $random_heading_level = rand(2, 3);
                        $post_content .= '<h' . $random_heading_level . '>Heading ' . $random_heading_level . ' demo ' . ($ci + 1) . '</h' . $random_heading_level . '>';

                        // chèn ảnh vào content
                        $post_content .= $get_image_html($image_cache[$random_img_num]);
                        // lấy một đoạn văn bản mẫu ngẫu nhiên trong mảng $lipsum_stext
                        $post_content .= '<p>' . $lipsum_stext[array_rand($lipsum_stext)] . '</p>';
                    }
                }

                // tạo title với độ dài ngẫu nhiên
                $lorem_words = explode(' ', $lipsum_stext[array_rand($lipsum_stext)]);
                $random_title_length = rand(5, 10);
                $random_title = implode(' ', array_slice($lorem_words, 0, $random_title_length));

                // Tạo post
                $post_data = [
                    'post_title'    => $post_title . ' - ' . $random_title,
                    'post_content'  => $post_content,
                    'post_status'   => 'publish',
                    'post_author'   => $user_id,
                    'post_type'     => $post_type,
                ];

                $post_id = wp_insert_post($post_data);

                if (!is_wp_error($post_id) && $post_id > 0) {
                    // Gán danh mục
                    wp_set_object_terms($post_id, (int) $cat_id, $taxonomy);

                    // Gán ảnh đại diện (chọn ngẫu nhiên từ 1-20)
                    $featured_img_num = rand(1, 20);
                    if (isset($image_cache[$featured_img_num])) {
                        set_post_thumbnail($post_id, $image_cache[$featured_img_num]);
                    }

                    // Nếu là product, thêm gallery và giá
                    if ($post_type === 'product' && $has_woocommerce) {
                        // Thêm gallery images (3-5 ảnh)
                        $num_gallery_images = rand(3, 5);
                        $gallery_ids = [];

                        for ($g = 1; $g <= $num_gallery_images; $g++) {
                            $gallery_image_number = rand(1, 20);
                            if (isset($image_cache[$gallery_image_number])) {
                                $gallery_ids[] = $image_cache[$gallery_image_number];
                            }
                        }

                        // Lưu gallery IDs (WooCommerce sử dụng chuỗi IDs phân cách bằng dấu phẩy)
                        if (!empty($gallery_ids)) {
                            update_post_meta($post_id, '_product_image_gallery', implode(',', $gallery_ids));
                        }

                        // Giá gốc từ 100.000 đến 1.000.000 (bước nhảy 1.000)
                        $regular_price = rand(100, 1000) * 1000;
                        update_post_meta($post_id, '_regular_price', $regular_price);

                        // Ngẫu nhiên 50% sản phẩm có giá khuyến mãi
                        if (rand(0, 1) === 1) {
                            // Giá khuyến mãi giảm từ 10% đến 50%
                            $discount_percent = rand(10, 50);
                            $sale_price = $regular_price * (100 - $discount_percent) / 100;
                            // Làm tròn về bội số của 1.000
                            $sale_price = round($sale_price / 1000) * 1000;

                            update_post_meta($post_id, '_sale_price', $sale_price);
                            update_post_meta($post_id, '_price', $sale_price);
                        } else {
                            update_post_meta($post_id, '_price', $regular_price);
                        }

                        // Ngẫu nhiên 50% sản phẩm có bubble new/hot
                        if (1 > 2 && rand(0, 1) === 1) {
                            update_post_meta($post_id, '_bubble_new', 'Enabled');
                            // Ngẫu nhiên chọn text là NEW hoặc HOT
                            $bubble_text = rand(0, 1) === 1 ? 'NEW' : 'HOT';
                            update_post_meta($post_id, '_bubble_text', $bubble_text);
                        }
                    }

                    $total_created++;
                }
            }
        }

        // Tính tổng số bài viết/sản phẩm hiện có (bao gồm cả mới tạo)
        $total_demo_count = $existing_demo_count + $total_created;

        echo '<div class="notice notice-success"><p><strong>✓ Hoàn tất request này!</strong> Đã tạo thành công ' . $total_created . ' ' . $post_type . ' trong lần này. Tổng cộng: <strong>' . $total_demo_count . '/' . $max_post_demo . '</strong></p></div>';

        // Nếu chưa đủ ${max_post_demo} bài viết/sản phẩm thì reload lại trang
        if ($total_demo_count < $max_post_demo) {
            echo '<div class="notice notice-info"><p>Đang tiếp tục tạo thêm dữ liệu... (' . $total_demo_count . '/' . $max_post_demo . ')</p></div>';
            echo '<script>setTimeout(function(){ window.location.reload(); }, 5000);</script>';
            echo '<p><em>Trang sẽ tự động tải lại sau 5 giây để tiếp tục tạo dữ liệu...</em></p>';
?>
    </div>
<?php
            return;
        }

        echo '<p><a href="' . admin_url('edit.php' . ($post_type === 'product' ? '?post_type=product' : '')) . '" class="button button-primary">Xem danh sách ' . $post_type . '</a></p>';
    } elseif (isset($_GET['action']) && $_GET['action'] === 'delete') {
        // Xóa toàn bộ dữ liệu demo
        $demo_username = 'userdemo';
        $demo_user = get_user_by('login', $demo_username);

        if (!$demo_user) {
            echo '<div class="notice notice-error"><p><strong>Lỗi:</strong> Không tìm thấy tài khoản demo.</p></div>';
        } else {
            $user_id = $demo_user->ID;
            $deleted_posts = 0;
            $deleted_products = 0;
            $deleted_images = 0;
            $deleted_categories = 0;

            // Xóa tất cả posts của demo user
            global $wpdb;
            $demo_posts = $wpdb->get_results($wpdb->prepare(
                "SELECT ID FROM {$wpdb->posts} 
                WHERE post_type = 'post' 
                AND post_author = %d 
                AND post_title LIKE %s 
                AND post_name LIKE %s",
                $user_id,
                '%Demo Post%',
                '%demo-post%'
            ));

            foreach ($demo_posts as $post) {
                $post_id = $post->ID;

                // Xóa ảnh đại diện
                $thumbnail_id = get_post_thumbnail_id($post_id);
                if ($thumbnail_id) {
                    wp_delete_attachment($thumbnail_id, true);
                    $deleted_images++;
                }

                // Xóa post
                wp_delete_post($post_id, true);
                $deleted_posts++;
            }

            // Xóa tất cả products của demo user (nếu có WooCommerce)
            if ($has_woocommerce) {
                $demo_products = $wpdb->get_results($wpdb->prepare(
                    "SELECT ID FROM {$wpdb->posts} 
                    WHERE post_type = 'product' 
                    AND post_author = %d 
                    AND post_title LIKE %s 
                    AND post_name LIKE %s",
                    $user_id,
                    '%Demo Product%',
                    '%demo-product%'
                ));

                foreach ($demo_products as $product) {
                    $product_id = $product->ID;

                    // Xóa ảnh đại diện
                    $thumbnail_id = get_post_thumbnail_id($product_id);
                    if ($thumbnail_id) {
                        wp_delete_attachment($thumbnail_id, true);
                        $deleted_images++;
                    }

                    // Xóa ảnh gallery
                    $gallery_ids = get_post_meta($product_id, '_product_image_gallery', true);
                    if (!empty($gallery_ids)) {
                        $gallery_ids_array = explode(',', $gallery_ids);
                        foreach ($gallery_ids_array as $gallery_id) {
                            if ($gallery_id) {
                                wp_delete_attachment($gallery_id, true);
                                $deleted_images++;
                            }
                        }
                    }

                    // Xóa product
                    wp_delete_post($product_id, true);
                    $deleted_products++;
                }
            }

            // Xóa tất cả hình ảnh của demo user
            $demo_attachments = get_posts([
                'post_type'      => 'attachment',
                'author'         => $user_id,
                'posts_per_page' => -1,
                'fields'         => 'ids',
            ]);

            foreach ($demo_attachments as $attachment_id) {
                wp_delete_attachment($attachment_id, true);
                $deleted_images++;
            }

            // Kiểm tra còn posts/products nào của user demo không (để quyết định có xóa user)
            $remaining_posts = $wpdb->get_results($wpdb->prepare(
                "SELECT post_type, post_status, COUNT(*) as count 
                FROM {$wpdb->posts} 
                WHERE post_author = %d 
                GROUP BY post_type, post_status",
                $user_id
            ));

            // Tính tổng số và tạo thống kê chi tiết
            $remaining_posts_count = 0;
            $remaining_posts_stats = [];
            foreach ($remaining_posts as $stat) {
                $remaining_posts_count += $stat->count;

                $remaining_posts_stats[] = sprintf(
                    '%s %s x %d',
                    $stat->post_type,
                    $stat->post_status,
                    $stat->count
                );
            }

            // Xóa danh mục demo (category)
            $demo_categories = get_terms([
                'taxonomy'   => 'category',
                'hide_empty' => false,
            ]);

            foreach ($demo_categories as $term) {
                // Kiểm tra nếu tên chứa 'Category Demo' và slug chứa 'category-demo'
                if (strpos($term->name, 'Category Demo') !== false && strpos($term->slug, 'category-demo') !== false) {
                    wp_delete_term($term->term_id, 'category');
                    $deleted_categories++;
                }
            }

            // Xóa danh mục demo (product_cat)
            if ($has_woocommerce) {
                $demo_product_cats = get_terms([
                    'taxonomy'   => 'product_cat',
                    'hide_empty' => false,
                ]);

                foreach ($demo_product_cats as $term) {
                    // Kiểm tra nếu tên chứa 'Product cat Demo' và slug chứa 'product-cat-demo'
                    if (strpos($term->name, 'Product cat Demo') !== false && strpos($term->slug, 'product-cat-demo') !== false) {
                        wp_delete_term($term->term_id, 'product_cat');
                        $deleted_categories++;
                    }
                }
            }

            // Chỉ xóa demo user nếu không còn posts/products nào
            if ($remaining_posts_count === 0) {
                require_once(ABSPATH . 'wp-admin/includes/user.php');
                wp_delete_user($user_id);
            }

            echo '<div class="notice notice-success"><p><strong>✓ Đã xóa thành công:</strong></p>';
            echo '<ul style="list-style: disc; margin-left: 20px;">';
            if ($remaining_posts_count === 0) {
                echo '<li>Tài khoản demo: <strong>' . $demo_username . '</strong></li>';
            }
            echo '<li>Bài viết: <strong>' . $deleted_posts . '</strong></li>';
            if ($has_woocommerce) {
                echo '<li>Sản phẩm: <strong>' . $deleted_products . '</strong></li>';
            }
            echo '<li>Hình ảnh: <strong>' . $deleted_images . '</strong></li>';
            echo '<li>Danh mục: <strong>' . $deleted_categories . '</strong></li>';
            echo '</ul></div>';

            // Hiển thị cảnh báo nếu còn dữ liệu (không xóa user)
            if ($remaining_posts_count > 0) {
                echo '<div class="notice notice-warning"><p><strong>⚠ Cảnh báo:</strong> Phát hiện <strong>' . $remaining_posts_count . '</strong> bài viết/sản phẩm đã được chỉnh sửa (bỏ qua không xóa).</p>';
                if (!empty($remaining_posts_stats)) {
                    echo '<ul style="list-style: disc; margin-left: 20px;">';
                    foreach ($remaining_posts_stats as $stat_line) {
                        echo '<li>' . $stat_line . '</li>';
                    }
                    echo '</ul>';
                }
                echo '<p>Tài khoản <strong>' . $demo_username . '</strong> đã được giữ lại vì còn dữ liệu không phải demo thuần túy.</p></div>';
            }
        }

?>
<p><a href="<?php echo admin_url('admin.php?page=' . $_GET['page'] . '&tool_action=' . $_GET['tool_action']); ?>" class="button button-primary">Quay lại</a></p>
<?php
    } else {
?>
    <div class="notice notice-warning">
        <p><strong>Cảnh báo:</strong> Công cụ này sẽ tạo dữ liệu demo cho website của bạn.</p>
    </div>
    <!-- form tạo dữ liệu demo -->
    <form method="get">
        <input type="hidden" name="page" value="<?php echo $_GET['page']; ?>">
        <input type="hidden" name="tool_action" value="<?php echo $_GET['tool_action']; ?>">
        <input type="hidden" name="action" value="generate">

        <p>
            <label>
                <strong>Loại nội dung:</strong><br>
                <select name="for_type" aria-required="true" required>
                    <!-- <option value="">-- Chọn loại nội dung --</option> -->
                    <option value="post">Bài viết (Post)</option>
                    <option value="product" <?php if (!$has_woocommerce) echo 'disabled'; ?>>Sản phẩm (Product) <?php if (!$has_woocommerce) echo '- Cần cài WooCommerce'; ?></option>
                </select>
            </label>
        </p>

        <p>
            <strong>Quy trình tạo dữ liệu:</strong>
        </p>
        <ol>
            <li>Tạo tài khoản demo: <code>userdemo</code> / <code>emaildemo@<?php echo $_SERVER['HTTP_HOST']; ?></code></li>
            <li>Tạo 3-5 danh mục ngẫu nhiên</li>
            <li>Tạo 5-10 Bài viết/ Sản phẩm cho mỗi danh mục</li>
            <li>Gán ảnh đại diện ngẫu nhiên từ webgiare.org</li>
            <li>Gán giá ngẫu nhiên cho sản phẩm</li>
            <li>Ngẫu nhiên 50% sản phẩm có giá khuyến mãi</li>
            <!-- <li>Ngẫu nhiên 50% sản phẩm Enabled _bubble_new</li> -->
        </ol>

        <p>
            <button type="submit" class="button button-primary button-large">
                Bắt đầu tạo dữ liệu demo
            </button>
        </p>
    </form>

    <hr style="margin: 30px 0;">

    <!-- form XÓA dữ liệu demo -->
    <h3 style="color: #d63638;">Xóa dữ liệu demo</h3>
    <?php
        // Kiểm tra xem có dữ liệu demo không
        $demo_username = 'userdemo';
        $demo_user = get_user_by('login', $demo_username);

        if ($demo_user) {
            $user_id = $demo_user->ID;

            // Đếm số lượng dữ liệu (dùng SQL trực tiếp)
            global $wpdb;
            $count_posts = (int) $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'post' AND post_author = %d",
                $user_id
            ));

            $count_products = 0;
            if ($has_woocommerce) {
                $count_products = (int) $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'product' AND post_author = %d",
                    $user_id
                ));
            }

            $count_attachments = (int) $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'attachment' AND post_author = %d",
                $user_id
            ));

            if ($count_posts > 0 || $count_products > 0 || $count_attachments > 0) {
    ?>
            <div class="notice notice-warning">
                <p><strong>Dữ liệu demo hiện có:</strong></p>
                <ul style="list-style: disc; margin-left: 20px;">
                    <li>Tài khoản: <strong><?php echo $demo_username; ?></strong></li>
                    <?php if ($count_posts > 0): ?>
                        <li>Bài viết: <strong><?php echo $count_posts; ?></strong></li>
                    <?php endif; ?>
                    <?php if ($count_products > 0): ?>
                        <li>Sản phẩm: <strong><?php echo $count_products; ?></strong></li>
                    <?php endif; ?>
                    <?php if ($count_attachments > 0): ?>
                        <li>Hình ảnh: <strong><?php echo $count_attachments; ?></strong></li>
                    <?php endif; ?>
                </ul>
            </div>

            <form method="get" onsubmit="return confirm('CẢNH BÁO: Bạn có chắc chắn muốn xóa TOÀN BỘ dữ liệu demo?\n\nHành động này KHÔNG THỂ HOÀN TÁC!\n\nTất cả bài viết, sản phẩm, hình ảnh, danh mục demo và tài khoản demo sẽ bị xóa vĩnh viễn.');">
                <input type="hidden" name="page" value="<?php echo $_GET['page']; ?>">
                <input type="hidden" name="tool_action" value="<?php echo $_GET['tool_action']; ?>">
                <input type="hidden" name="action" value="delete">

                <p>
                    <button type="submit" class="button button-large" style="background: #d63638; color: white; border-color: #d63638;">
                        Xóa toàn bộ dữ liệu demo
                    </button>
                </p>
            </form>
    <?php
            } else {
                echo '<p style="color: #999;">Không có dữ liệu demo nào để xóa.</p>';
            }
        } else {
            echo '<p style="color: #999;">Chưa có tài khoản demo.</p>';
        }
    ?>
<?php
    }
?>
</div>