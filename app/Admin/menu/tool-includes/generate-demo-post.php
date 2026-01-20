<?php

/**
 * Công cụ tạo dữ liệu demo
 * Mặc định là tạo post, nếu có thêm tham số GET for_type thì tạo theo for_type đó (chỉ chấp nhận post và product)
 * Yêu cầu:
 * Cần tạo mới 1 tài khoản với username là userdemo (không cần md5) và email là emaildemo@$_SERVER['HTTP_HOST'] với mật khẩu là md5 timestamp hiện tại để insert dữ liệu. Phân quyền:
 * - Nếu website có WooCommerce thì tạo phân quyền là: `shop_manager`
 * - Mặc định sẽ tạo phân quyền là: `editor`
 * Danh mục Bài viết/ Sản phẩm sẽ tạo 1 mảng dạng `'taxonomy' => ['category', 'Danh mục B', 'Danh mục C', ...]` và gán ngẫu nhiên, trong đó `taxonomy` là tên taxonomy (category, product_cat, ...) Mỗi lần sẽ tạo khoảng 3-5 danh mục con.
 * Tên Bài viết/ Sản phẩm sẽ là: `Demo Post #${i}` hoặc `Demo Product #${i}` trong đó `${i}` là số ngẫu nhiên từ 5-10 (đây cũng chính là số sản phẩm sẽ tạo trên mỗi danh mục)
 * Ảnh Bài viết/ Sản phẩm sẽ tải ngẫu nhiên từ `https://img1.webgiare.org/random/demo-${i}.jpg` trong đó `${i}` là số từ 1 đến 20 (cần kiểm tra nếu ảnh đã tải về rồi thì không tải nữa, Bài viết/ Sản phẩm sau sẽ dùng lại ảnh đã tải về)
 * Với sản phẩm sẽ thêm giá ngẫu nhiên từ 100.000đ đến 1.000.000đ (với bước nhảy là 1.000đ) và ngẫu nhiên đặt thêm giá khuyến mãi.
 * Với sản phẩm sẽ thêm gallery khoảng 3-5 ảnh.
 * Sau khi submit form, với mỗi post_type sẽ kiểm tra xem có sản phẩm demo chưa, nếu có rồi thì không tạo nữa (kiểm tra post_type tạo bởi demo user).
 * 
 * Có chức năng XÓA toàn bộ dữ liệu demo đã tạo (dữ liệu được tạo bởi user demo). Dữ liệu XÓA bao gồm cả user demo, hình ảnh demo, danh mục demo, Bài viết và Sản phẩm demo... Tất cả dữ liệu liên quan đến user demo trong module này.
 */

// Lấy post_type từ GET, mặc định là post
$post_type = isset($_GET['for_type']) && in_array($_GET['for_type'], ['post', 'product'])
    ? $_GET['for_type']
    : 'post';

// Kiểm tra xem có WooCommerce không
$has_woocommerce = class_exists('WooCommerce');

?>
<div class="wrap">
    <h2>Tạo dữ liệu <?php echo $post_type; ?> demo</h2>

    <?php
    if (isset($_GET['action']) && $_GET['action'] === 'generate') {
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

        // Kiểm tra xem đã có dữ liệu demo cho post_type này chưa
        $existing_demo = get_posts([
            'post_type'      => $post_type,
            'author'         => $user_id,
            'posts_per_page' => 1,
            'fields'         => 'ids',
        ]);

        if (!empty($existing_demo)) {
            $post_type_name = $post_type === 'product' ? 'sản phẩm' : 'bài viết';
            echo '<div class="notice notice-warning"><p><strong>Thông báo:</strong> Đã tồn tại dữ liệu demo ' . $post_type_name . ' được tạo bởi tài khoản <strong>' . $demo_username . '</strong>. Không tạo thêm dữ liệu mới.</p></div>';
            echo '<p><a href="' . admin_url('edit.php' . ($post_type === 'product' ? '?post_type=product' : '')) . '" class="button button-primary">Xem danh sách ' . $post_type . '</a></p>';
    ?>
</div>
<?php
            return;
        }

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
                $term = wp_insert_term($cat_name, $taxonomy);
            }

            if (!is_wp_error($term)) {
                $category_ids[] = $term['term_id'];
                echo '<div class="notice notice-success"><p>✓ Đã tạo danh mục: <strong>' . $cat_name . '</strong></p></div>';
            }
        }

        // Tạo bài viết/sản phẩm (5-10 items cho mỗi danh mục)
        $total_created = 0;

        // Cache ảnh đã tải để tái sử dụng
        $image_cache = [];

        // Hàm helper để lấy hoặc tải ảnh
        $get_or_download_image = function ($image_number, $post_id, $user_id) use (&$image_cache) {
            $image_url = 'https://img1.webgiare.org/random/demo-' . $image_number . '.jpg';
            $image_filename = 'demo-' . $image_number . '.jpg';

            // Kiểm tra trong cache
            if (isset($image_cache[$image_filename])) {
                return $image_cache[$image_filename];
            }

            // Kiểm tra trong database
            $existing_image = get_posts([
                'post_type'      => 'attachment',
                'author'         => $user_id,
                'name'           => pathinfo($image_filename, PATHINFO_FILENAME),
                'posts_per_page' => 1,
                'fields'         => 'ids',
            ]);

            if (!empty($existing_image)) {
                $image_cache[$image_filename] = $existing_image[0];
                return $existing_image[0];
            }

            // Tải ảnh mới
            require_once(ABSPATH . 'wp-admin/includes/media.php');
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/image.php');

            $tmp = download_url($image_url);

            if (!is_wp_error($tmp)) {
                $file_array = [
                    'name'     => $image_filename,
                    'tmp_name' => $tmp
                ];

                $attachment_id = media_handle_sideload($file_array, $post_id);

                if (!is_wp_error($attachment_id)) {
                    $image_cache[$image_filename] = $attachment_id;
                    return $attachment_id;
                } else {
                    @unlink($file_array['tmp_name']);
                }
            }

            return null;
        };

        foreach ($category_ids as $cat_id) {
            $num_posts = rand(5, 10);

            for ($i = 1; $i <= $num_posts; $i++) {
                // Tên bài viết
                $post_title = $post_type === 'product'
                    ? 'Demo Product #' . $i
                    : 'Demo Post #' . $i;

                // Nội dung mẫu
                $post_content = '<p>Đây là nội dung demo cho ' . $post_title . '</p>';
                $post_content .= '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>';

                // Tạo post
                $post_data = [
                    'post_title'    => $post_title,
                    'post_content'  => $post_content,
                    'post_status'   => 'publish',
                    'post_author'   => $user_id,
                    'post_type'     => $post_type,
                ];

                $post_id = wp_insert_post($post_data);

                if (!is_wp_error($post_id) && $post_id > 0) {
                    // Gán danh mục
                    wp_set_object_terms($post_id, (int) $cat_id, $taxonomy);

                    // Tải và gán ảnh đại diện
                    $image_number = rand(1, 20);
                    $attachment_id = $get_or_download_image($image_number, $post_id, $user_id);

                    // Gán ảnh đại diện cho post
                    if ($attachment_id && !is_wp_error($attachment_id)) {
                        set_post_thumbnail($post_id, $attachment_id);
                    }

                    // Nếu là product, thêm gallery và giá
                    if ($post_type === 'product' && $has_woocommerce) {
                        // Thêm gallery images (3-5 ảnh)
                        $num_gallery_images = rand(3, 5);
                        $gallery_ids = [];

                        for ($g = 1; $g <= $num_gallery_images; $g++) {
                            $gallery_image_number = rand(1, 20);
                            $gallery_attachment_id = $get_or_download_image($gallery_image_number, $post_id, $user_id);

                            if ($gallery_attachment_id && !is_wp_error($gallery_attachment_id)) {
                                $gallery_ids[] = $gallery_attachment_id;
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
                    }

                    $total_created++;
                }
            }
        }

        echo '<div class="notice notice-success"><p><strong>✓ Hoàn tất!</strong> Đã tạo thành công ' . $total_created . ' ' . $post_type . '</p></div>';
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
            $demo_posts = get_posts([
                'post_type'      => 'post',
                'author'         => $user_id,
                'posts_per_page' => -1,
                'fields'         => 'ids',
            ]);

            foreach ($demo_posts as $post_id) {
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
                $demo_products = get_posts([
                    'post_type'      => 'product',
                    'author'         => $user_id,
                    'posts_per_page' => -1,
                    'fields'         => 'ids',
                ]);

                foreach ($demo_products as $product_id) {
                    // Xóa ảnh đại diện
                    $thumbnail_id = get_post_thumbnail_id($product_id);
                    if ($thumbnail_id) {
                        wp_delete_attachment($thumbnail_id, true);
                        $deleted_images++;
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

            // Xóa danh mục demo (category)
            $demo_categories = get_terms([
                'taxonomy'   => 'category',
                'hide_empty' => false,
            ]);

            foreach ($demo_categories as $term) {
                // Kiểm tra nếu tên chứa 'Category Demo' hoặc chỉ có 'Demo'
                if (strpos($term->name, 'Category Demo') !== false) {
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
                    // Kiểm tra nếu tên chứa 'Product cat Demo' hoặc chỉ có 'Demo'
                    if (strpos($term->name, 'Product cat Demo') !== false) {
                        wp_delete_term($term->term_id, 'product_cat');
                        $deleted_categories++;
                    }
                }
            }

            // Xóa demo user
            require_once(ABSPATH . 'wp-admin/includes/user.php');
            wp_delete_user($user_id);

            echo '<div class="notice notice-success"><p><strong>✓ Đã xóa thành công:</strong></p>';
            echo '<ul style="list-style: disc; margin-left: 20px;">';
            echo '<li>Tài khoản demo: <strong>' . $demo_username . '</strong></li>';
            echo '<li>Bài viết: <strong>' . $deleted_posts . '</strong></li>';
            if ($has_woocommerce) {
                echo '<li>Sản phẩm: <strong>' . $deleted_products . '</strong></li>';
            }
            echo '<li>Hình ảnh: <strong>' . $deleted_images . '</strong></li>';
            echo '<li>Danh mục: <strong>' . $deleted_categories . '</strong></li>';
            echo '</ul></div>';
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
                    <option value="">-- Chọn loại nội dung --</option>
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

            // Đếm số lượng dữ liệu
            $count_posts = count(get_posts([
                'post_type'      => 'post',
                'author'         => $user_id,
                'posts_per_page' => -1,
                'fields'         => 'ids',
            ]));

            $count_products = 0;
            if ($has_woocommerce) {
                $count_products = count(get_posts([
                    'post_type'      => 'product',
                    'author'         => $user_id,
                    'posts_per_page' => -1,
                    'fields'         => 'ids',
                ]));
            }

            $count_attachments = count(get_posts([
                'post_type'      => 'attachment',
                'author'         => $user_id,
                'posts_per_page' => -1,
                'fields'         => 'ids',
            ]));

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