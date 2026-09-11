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
 * Highlight từ khóa trong chuỗi (escape HTML an toàn).
 *
 * @param string $text    Chuỗi gốc.
 * @param string $keyword Từ khóa cần bôi.
 * @return string HTML đã escape + <mark>.
 */
function WGR_search_by_title_highlight($text, $keyword)
{
    $text    = (string) $text;
    $keyword = trim((string) $keyword);

    if ('' === $text) {
        return '';
    }

    if ('' === $keyword) {
        return esc_html($text);
    }

    $pattern = '/(' . preg_quote($keyword, '/') . ')/iu';
    $parts   = preg_split($pattern, $text, -1, PREG_SPLIT_DELIM_CAPTURE);

    if (!is_array($parts)) {
        return esc_html($text);
    }

    $out = '';
    foreach ($parts as $i => $part) {
        if ('' === $part) {
            continue;
        }

        // Phần lẻ = đoạn khớp từ khóa (PREG_SPLIT_DELIM_CAPTURE).
        if ($i % 2 === 1) {
            $out .= '<mark class="wgr-search-by-title-mark">' . esc_html($part) . '</mark>';
        } else {
            $out .= esc_html($part);
        }
    }

    return $out;
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

    $slug_hint       = sanitize_title($keyword);
    $post_type_obj   = get_post_type_object($post_type);
    $post_type_label = $post_type_obj && !empty($post_type_obj->labels->name)
        ? $post_type_obj->labels->name
        : $post_type;
?>
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
                        <?php echo WGR_search_by_title_highlight($post->post_title, $keyword); ?>
                    </a>
                    <span class="wgr-search-by-title-slug">
                        — <?php echo WGR_search_by_title_highlight($post->post_name, $slug_hint); ?>
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
