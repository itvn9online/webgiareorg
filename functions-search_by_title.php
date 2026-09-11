<?php

/**
 * Khi admin search (?s=) trên bất kỳ danh sách bài viết nào,
 * tìm thêm theo post_title / post_name (slug đã bỏ dấu)
 * của post_type đang xem, và hiển thị kết quả ngay phía trên
 * bảng wp-list-table nếu có.
 */

/**
 * Lấy từ khóa search hiện tại trên trang danh sách admin.
 *
 * @return string
 */
function WGR_search_by_title_keyword()
{
    if (empty($_GET['s'])) {
        return '';
    }

    return sanitize_text_field(wp_unslash($_GET['s']));
}

/**
 * Lấy post_type từ URL (mặc định 'post' như màn hình Bài viết của WP).
 *
 * @return string
 */
function WGR_search_by_title_post_type()
{
    $post_type = isset($_GET['post_type']) ? sanitize_key(wp_unslash($_GET['post_type'])) : 'post';

    if ('' === $post_type || !post_type_exists($post_type)) {
        return 'post';
    }

    return $post_type;
}

/**
 * Chỉ chạy trên màn hình danh sách bài viết (edit.php).
 *
 * @return bool
 */
function WGR_search_by_title_is_list_screen()
{
    global $pagenow;

    return is_admin() && 'edit.php' === $pagenow;
}

/**
 * Tìm bài viết theo tiêu đề hoặc slug (đã chuẩn hóa tiếng Việt).
 * Ví dụ: "việt nam" → slug "viet-nam".
 *
 * @param string $keyword   Từ khóa gốc.
 * @param string $post_type Post type cần tìm.
 * @param int    $limit     Số kết quả tối đa.
 * @return array<int, object>
 */
function WGR_search_posts_by_title($keyword, $post_type = 'post', $limit = 50)
{
    $keyword = trim((string) $keyword);
    if ('' === $keyword) {
        return array();
    }

    $post_type = sanitize_key($post_type);
    if ('' === $post_type || !post_type_exists($post_type)) {
        $post_type = 'post';
    }

    global $wpdb;

    $slug = sanitize_title($keyword);
    if ('' === $slug) {
        $slug = $keyword;
    }

    $like_title = '%' . $wpdb->esc_like($keyword) . '%';
    $like_slug  = '%' . $wpdb->esc_like($slug) . '%';

    $sql = $wpdb->prepare(
        "SELECT ID, post_title, post_name, post_status
         FROM {$wpdb->posts}
         WHERE post_type = %s
           AND post_status NOT IN ('trash', 'auto-draft', 'inherit')
           AND (
                post_title LIKE %s
                OR post_name LIKE %s
           )
         ORDER BY post_title ASC
         LIMIT %d",
        $post_type,
        $like_title,
        $like_slug,
        absint($limit)
    );

    $rows = $wpdb->get_results($sql);

    return is_array($rows) ? $rows : array();
}

/**
 * In CSS + HTML kết quả, rồi đẩy khối này lên ngay trước table.wp-list-table.
 */
function WGR_search_by_title_render()
{
    if (!WGR_search_by_title_is_list_screen()) {
        return;
    }

    $keyword = WGR_search_by_title_keyword();
    if ('' === $keyword || mb_strlen($keyword) < 3) {
        return;
    }

    $post_type = WGR_search_by_title_post_type();
    $posts     = WGR_search_posts_by_title($keyword, $post_type);
    if (empty($posts)) {
        return;
    }

    $slug_hint      = sanitize_title($keyword);
    $post_type_obj  = get_post_type_object($post_type);
    $post_type_label = $post_type_obj && !empty($post_type_obj->labels->name)
        ? $post_type_obj->labels->name
        : $post_type;
?>
    <style>
        #wgr-search-by-title {
            margin: 12px 0 16px;
            padding: 12px 14px;
            background: #fff;
            border: 1px solid #c3c4c7;
            border-left: 4px solid #2271b1;
            box-shadow: 0 1px 1px rgba(0, 0, 0, .04);
        }

        #wgr-search-by-title h2 {
            margin: 0 0 8px;
            font-size: 14px;
            line-height: 1.4;
        }

        #wgr-search-by-title .wgr-search-by-title-meta {
            margin: 0 0 8px;
            color: #646970;
            font-size: 12px;
        }

        #wgr-search-by-title ul {
            margin: 0;
            padding-left: 1.25em;
        }

        #wgr-search-by-title li {
            margin: 0 0 4px;
            line-height: 1.5;
        }

        #wgr-search-by-title .wgr-search-by-title-slug {
            color: #646970;
            font-size: 12px;
        }
    </style>
    <div id="wgr-search-by-title">
        <h2>
            <?php
            echo esc_html(
                sprintf(
                    '%1$s khớp tiêu đề/slug với “%2$s” (%3$d)',
                    $post_type_label,
                    $keyword,
                    count($posts)
                )
            );
            ?>
        </h2>
        <ol>
            <?php foreach ($posts as $post) : ?>
                <?php
                $edit_link = get_edit_post_link((int) $post->ID);
                if (!$edit_link) {
                    continue;
                }
                ?>
                <li>
                    <a href="<?php echo esc_url($edit_link); ?>">
                        <?php echo esc_html($post->post_title); ?>
                    </a>
                    <span class="wgr-search-by-title-slug">
                        — <?php echo esc_html($post->post_name); ?>
                        (#<?php echo (int) $post->ID; ?>, <?php echo esc_html($post->post_status); ?>)
                    </span>
                </li>
            <?php endforeach; ?>
        </ol>
    </div>
    <script>
        (function() {
            var box = document.getElementById('wgr-search-by-title');
            var table = document.querySelector('table.wp-list-table');
            if (box && table && table.parentNode) {
                table.parentNode.insertBefore(box, table);
            }
        })();
    </script>
<?php
}
add_action('admin_footer-edit.php', 'WGR_search_by_title_render');
