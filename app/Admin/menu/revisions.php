<?php

global $wpdb;

wp_enqueue_script('jquery-ui-datepicker');

$use_custom_datepicker_css = true;
$woo_jquery_ui_css_rel = 'plugins/woocommerce/assets/css/jquery-ui/jquery-ui.min.css';
$woo_jquery_ui_css_file = WP_CONTENT_DIR . '/' . $woo_jquery_ui_css_rel;

if (is_file($woo_jquery_ui_css_file)) {
    wp_enqueue_style(
        'wgr-woo-jquery-ui-theme',
        content_url($woo_jquery_ui_css_rel),
        [],
        (string) filemtime($woo_jquery_ui_css_file)
    );
    $use_custom_datepicker_css = false;
}

$per_page = 50;
$current_page = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
$offset = ($current_page - 1) * $per_page;

$revisions_table = $wpdb->posts;

$current_admin_page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : 'eb-revisions';

$filter_post_type = isset($_GET['filter_post_type']) ? sanitize_key(wp_unslash($_GET['filter_post_type'])) : '';
$filter_status = isset($_GET['filter_status']) ? sanitize_key(wp_unslash($_GET['filter_status'])) : '';
$filter_keyword = isset($_GET['filter_keyword']) ? sanitize_text_field(wp_unslash($_GET['filter_keyword'])) : '';
$filter_author_id = isset($_GET['filter_author_id']) ? absint($_GET['filter_author_id']) : 0;
$filter_date_from = isset($_GET['filter_date_from']) ? sanitize_text_field(wp_unslash($_GET['filter_date_from'])) : '';
$filter_date_to = isset($_GET['filter_date_to']) ? sanitize_text_field(wp_unslash($_GET['filter_date_to'])) : '';

if ($filter_date_from !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $filter_date_from)) {
    $filter_date_from = '';
}

if ($filter_date_to !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $filter_date_to)) {
    $filter_date_to = '';
}

$available_post_types = $wpdb->get_col(
    "SELECT DISTINCT p.post_type
    FROM {$revisions_table} AS r
    INNER JOIN {$revisions_table} AS p ON p.ID = r.post_parent
    WHERE r.post_type = 'revision'
    ORDER BY p.post_type ASC"
);

$available_statuses = $wpdb->get_col(
    "SELECT DISTINCT p.post_status
    FROM {$revisions_table} AS r
    INNER JOIN {$revisions_table} AS p ON p.ID = r.post_parent
    WHERE r.post_type = 'revision'
    ORDER BY p.post_status ASC"
);

$available_authors = $wpdb->get_results(
    "SELECT DISTINCT r.post_author AS author_id
    FROM {$revisions_table} AS r
    WHERE r.post_type = 'revision'
    ORDER BY r.post_author ASC"
);

$where_clauses = ["r.post_type = 'revision'"];
$query_params = [];

if ($filter_post_type !== '') {
    $where_clauses[] = 'p.post_type = %s';
    $query_params[] = $filter_post_type;
}

if ($filter_status !== '') {
    $where_clauses[] = 'p.post_status = %s';
    $query_params[] = $filter_status;
}

if ($filter_keyword !== '') {
    $where_clauses[] = 'p.post_title LIKE %s';
    $query_params[] = '%' . $wpdb->esc_like($filter_keyword) . '%';
}

if ($filter_author_id > 0) {
    $where_clauses[] = 'r.post_author = %d';
    $query_params[] = $filter_author_id;
}

if ($filter_date_from !== '') {
    $where_clauses[] = 'DATE(r.post_modified) >= %s';
    $query_params[] = $filter_date_from;
}

if ($filter_date_to !== '') {
    $where_clauses[] = 'DATE(r.post_modified) <= %s';
    $query_params[] = $filter_date_to;
}

$where_sql = 'WHERE ' . implode(' AND ', $where_clauses);

// Đếm tổng số revision toàn site (lọc theo revision thực sự có bài cha).
$count_sql = "SELECT COUNT(r.ID)
	FROM {$revisions_table} AS r
	INNER JOIN {$revisions_table} AS p ON p.ID = r.post_parent
	{$where_sql}";

if (!empty($query_params)) {
    $total_items = (int) $wpdb->get_var($wpdb->prepare($count_sql, $query_params));
} else {
    $total_items = (int) $wpdb->get_var($count_sql);
}

$total_pages = (int) ceil($total_items / $per_page);

// Lấy danh sách revision gần đây nhất theo thứ tự thời gian cập nhật.
$list_sql = "SELECT
			r.ID AS revision_id,
			r.post_parent,
			r.post_modified,
			r.post_modified_gmt,
			r.post_author AS revision_author_id,
			p.post_title AS parent_title,
			p.post_type AS parent_type,
            p.post_status AS parent_status
		FROM {$revisions_table} AS r
		INNER JOIN {$revisions_table} AS p ON p.ID = r.post_parent
        {$where_sql}
		ORDER BY r.post_modified DESC, r.ID DESC
        LIMIT %d OFFSET %d";

$list_query_params = $query_params;
$list_query_params[] = $per_page;
$list_query_params[] = $offset;

$revision_rows = $wpdb->get_results($wpdb->prepare($list_sql, $list_query_params));

?>
<style>
    .filter-table input[type="text"],
    .filter-table select {
        width: 90%;
        max-width: 250px;
    }
</style>
<h1>Lịch sử sửa toàn bộ bài viết</h1>

<p>
    Hiển thị toàn bộ lịch sử chỉnh sửa gần đây của tất cả bài viết/trang/custom post type.
    Mỗi trang có <strong><?php echo (int) $per_page; ?></strong> bản ghi.
</p>

<form action="" method="get">
    <input type="hidden" name="page" value="<?php echo esc_attr($current_admin_page); ?>">

    <table class="form-table filter-table" role="presentation">
        <tbody>
            <tr>
                <th scope="row"><label for="filter_post_type">Loại bài</label></th>
                <td>
                    <select name="filter_post_type" id="filter_post_type">
                        <option value="">Tất cả</option>
                        <?php foreach ($available_post_types as $post_type): ?>
                            <option value="<?php echo esc_attr($post_type); ?>" <?php selected($filter_post_type, $post_type); ?>>
                                <?php echo esc_html($post_type); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>

                <th scope="row"><label for="filter_status">Trạng thái</label></th>
                <td>
                    <select name="filter_status" id="filter_status">
                        <option value="">Tất cả</option>
                        <?php foreach ($available_statuses as $post_status): ?>
                            <option value="<?php echo esc_attr($post_status); ?>" <?php selected($filter_status, $post_status); ?>>
                                <?php echo esc_html($post_status); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>

            <tr>
                <th scope="row"><label for="filter_author_id">Người sửa</label></th>
                <td>
                    <select name="filter_author_id" id="filter_author_id">
                        <option value="0">Tất cả</option>
                        <?php foreach ($available_authors as $author_item): ?>
                            <?php
                            $author_id = (int) $author_item->author_id;
                            if ($author_id <= 0) {
                                continue;
                            }
                            ?>
                            <option value="<?php echo $author_id; ?>" <?php selected($filter_author_id, $author_id); ?>>
                                <?php echo esc_html($author_id); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>

                <th scope="row"><label for="filter_keyword">Từ khóa tiêu đề</label></th>
                <td>
                    <input type="text" name="filter_keyword" id="filter_keyword" value="<?php echo esc_attr($filter_keyword); ?>" placeholder="Nhập từ khóa tiêu đề">
                </td>
            </tr>

            <tr>
                <th scope="row"><label for="filter_date_from">Từ ngày</label></th>
                <td>
                    <input type="text" name="filter_date_from" id="filter_date_from" class="wgr-datepicker" value="<?php echo esc_attr($filter_date_from); ?>" placeholder="YYYY-MM-DD" autocomplete="off">
                </td>

                <th scope="row"><label for="filter_date_to">Đến ngày</label></th>
                <td>
                    <input type="text" name="filter_date_to" id="filter_date_to" class="wgr-datepicker" value="<?php echo esc_attr($filter_date_to); ?>" placeholder="YYYY-MM-DD" autocomplete="off">
                </td>
            </tr>
        </tbody>
    </table>

    <p class="submit">
        <button type="submit" class="button button-primary">Lọc dữ liệu</button>
        <a href="<?php echo esc_url(add_query_arg('page', $current_admin_page, admin_url('admin.php'))); ?>" class="button button-secondary">Xóa lọc</a>
    </p>
</form>

<?php if ($use_custom_datepicker_css): ?>
    <style>
        .ui-datepicker {
            z-index: 100000 !important;
            width: 280px;
            padding: 10px;
            border: 1px solid #ccd0d4;
            border-radius: 6px;
            background: #fff;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }

        .ui-datepicker .ui-datepicker-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
            padding-bottom: 8px;
            border-bottom: 1px solid #f0f0f1;
        }

        .ui-datepicker .ui-datepicker-prev,
        .ui-datepicker .ui-datepicker-next {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border: 1px solid #dcdcde;
            border-radius: 4px;
            cursor: pointer;
            color: #1d2327;
            background: #f6f7f7;
            text-decoration: none;
        }

        .ui-datepicker .ui-datepicker-prev:hover,
        .ui-datepicker .ui-datepicker-next:hover {
            border-color: #2271b1;
            color: #2271b1;
            background: #f0f6fc;
        }

        .ui-datepicker .ui-datepicker-title {
            display: flex;
            gap: 6px;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        .ui-datepicker .ui-datepicker-title select {
            min-width: auto;
            margin: 0;
        }

        .ui-datepicker table {
            width: 100%;
            margin: 0;
            border-collapse: collapse;
        }

        .ui-datepicker th,
        .ui-datepicker td {
            padding: 2px;
            text-align: center;
        }

        .ui-datepicker th {
            font-size: 12px;
            font-weight: 600;
            color: #50575e;
        }

        .ui-datepicker td a,
        .ui-datepicker td span {
            display: block;
            padding: 7px 0;
            border-radius: 4px;
            color: #1d2327;
            text-decoration: none;
        }

        .ui-datepicker td a:hover {
            background: #f0f6fc;
            color: #2271b1;
        }

        .ui-datepicker .ui-state-active,
        .ui-datepicker .ui-datepicker-current-day a {
            color: #fff;
            background: #2271b1;
        }

        .ui-datepicker .ui-state-highlight {
            box-shadow: inset 0 0 0 1px #72aee6;
        }

        .ui-datepicker .ui-state-disabled {
            opacity: 0.4;
        }
    </style>
<?php endif; ?>

<script>
    jQuery(function($) {
        $('.wgr-datepicker').datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true
        });
    });
</script>

<p>Tổng số bản ghi phù hợp: <strong><?php echo (int) $total_items; ?></strong></p>

<?php if (!empty($revision_rows)): ?>
    <table class="widefat striped">
        <thead>
            <tr>
                <th style="width: 70px;">#</th>
                <th>Thời gian sửa</th>
                <th>Bài viết</th>
                <th>Loại</th>
                <th>Trạng thái</th>
                <th>Người sửa</th>
                <th style="width: 240px;">Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $stt = $offset;
            foreach ($revision_rows as $row):
                $stt++;

                $parent_id = (int) $row->post_parent;
                $revision_id = (int) $row->revision_id;
                $parent_title = trim((string) $row->parent_title);
                if ($parent_title === '') {
                    $parent_title = '(Không có tiêu đề)';
                }

                $author_id = (int) $row->revision_author_id;
                $author_edit_url = $author_id > 0 ? admin_url('user-edit.php?user_id=' . $author_id) : '';

                $view_revision_url = admin_url('revision.php?revision=' . $revision_id);
                $edit_parent_url = get_edit_post_link($parent_id, '');
            ?>
                <tr>
                    <td><?php echo $stt; ?></td>
                    <td>
                        <?php
                        echo esc_html(get_date_from_gmt($row->post_modified_gmt, 'd/m/Y H:i:s'));
                        ?>
                    </td>
                    <td>
                        <?php if (!empty($edit_parent_url)): ?>
                            <a href="<?php echo esc_url($edit_parent_url); ?>" class="bold"><?php echo esc_html($parent_title); ?></a>
                        <?php else: ?>
                            <strong><?php echo esc_html($parent_title); ?></strong>
                        <?php endif; ?>
                    </td>
                    <td><?php echo esc_html($row->parent_type); ?></td>
                    <td><?php echo esc_html($row->parent_status); ?></td>
                    <td>
                        <?php if ($author_edit_url !== ''): ?>
                            <a href="<?php echo esc_url($author_edit_url); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($author_id); ?></a>
                        <?php else: ?>
                            <?php echo esc_html($author_id); ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="<?php echo esc_url($view_revision_url); ?>" class="button button-small">Xem bản sửa</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php if ($total_pages > 1): ?>
        <div class="tablenav">
            <div class="tablenav-pages" style="margin: 16px 0;">
                <?php
                echo wp_kses_post(
                    paginate_links([
                        'base' => add_query_arg('paged', '%#%'),
                        'format' => '',
                        'current' => $current_page,
                        'total' => $total_pages,
                        'prev_text' => '&laquo;',
                        'next_text' => '&raquo;',
                    ])
                );
                ?>
            </div>
        </div>
    <?php endif; ?>
<?php else: ?>
    <div class="notice notice-info">
        <p>Hiện chưa có dữ liệu lịch sử chỉnh sửa.</p>
    </div>
<?php endif; ?>