<?php

/**
 * shortcode tạo breadcrumb cho trang tin tức
 */
// shorcode tạo breadcrumb không h1 -> cách sử dụng -> vào phần nội dung bài viết rồi nhập: [wgr_breadcrumb]
add_shortcode('wgr_breadcrumb', 'action_wgr_default_breadcrumb');

// shorcode tạo breadcrumb có h1 -> cách sử dụng -> vào phần nội dung bài viết rồi nhập: [wgr_h1_breadcrumb]
add_shortcode('wgr_h1_breadcrumb', 'action_wgr_h1_breadcrumb');

// shorcode tạo breadcrumb có h2 -> cách sử dụng -> vào phần nội dung bài viết rồi nhập: [wgr_h2_breadcrumb]
add_shortcode('wgr_h2_breadcrumb', 'action_wgr_h2_breadcrumb');

//
function action_wgr_breadcrumb($entry_tag = '')
{
    if (is_single()) {
        return wgr_details_breadcrumb($entry_tag);
    } else if (is_archive()) {
        $category = get_queried_object();
        if (empty($category) || !isset($category->term_id)) {
            return '';
        }
        // print_r($category);

        return wgr_list_breadcrumb($category->name, [[
            'title' => $category->name,
            'url' => get_category_link($category->term_id),
        ]], $entry_tag);
        //} else if (is_page_template() || is_page() || get_option('page_for_posts') == get_the_ID()) {
        //return wgr_list_breadcrumb($category->post_title, get_the_permalink($category->ID), $entry_tag);
    }

    //
    $category = get_queried_object();
    if (empty($category)) {
        return '';
    }
    // print_r($category);
    $arr_breadcrumbs = [];

    //
    if (isset($category->ID)) {
        if ($category->post_parent > 0) {
            $parent_data = get_post_parent($category->ID);
            //print_r($parent_data);

            //
            if (!empty($parent_data)) {
                if ($parent_data->post_parent > 0) {
                    $parents_data = get_post_parent($parent_data->ID);
                    //print_r($parents_data);

                    //
                    if (!empty($parents_data)) {
                        $arr_breadcrumbs[] = [
                            'title' => $parents_data->post_title,
                            'url' => get_the_permalink($parents_data->ID),
                        ];
                    }
                }

                $arr_breadcrumbs[] = [
                    'title' => $parent_data->post_title,
                    'url' => get_the_permalink($parent_data->ID),
                ];
            }
        }

        //
        $arr_breadcrumbs[] = [
            'title' => $category->post_title,
            'url' => get_the_permalink($category->ID),
        ];
    }
    return wgr_list_breadcrumb($category->post_title, $arr_breadcrumbs, $entry_tag);
}

function action_wgr_default_breadcrumb()
{
    return action_wgr_breadcrumb();
}

function action_wgr_h1_breadcrumb()
{
    return action_wgr_breadcrumb('h1');
}

function action_wgr_h2_breadcrumb()
{
    return action_wgr_breadcrumb('h2');
}

// danh sách tin tức
function wgr_list_breadcrumb($title, $arr_breadcrumbs, $entry_tag = '')
{
    ob_start();

    // xác định tên trang chủ dựa theo option page_on_front
    $page_on_front = get_option('page_on_front');
    if ($page_on_front > 0) {
        $front_title = get_the_title($page_on_front);
    } else {
        $front_title = __('Home', 'flatsome');
    }

?>
    <div class="wgr_list_breadcrumb">
        <div class="wgr_list_breadcrumb-inner">
            <?php
            //
            if ($entry_tag != '') {
            ?>
                <entry_title_tag class="breadcrumb-title entry-term-title"><?php echo $title; ?></entry_title_tag>
            <?php
            }
            ?>
            <ul aria-label="breadcrumbs" class="cf wgr-breadcrumb" itemscope="" itemtype="http://schema.org/BreadcrumbList">
                <li itemprop="itemListElement" itemscope="" itemtype="http://schema.org/ListItem"><a href="./" itemprop="item" title="<?php echo $front_title; ?>" class="breadcrumb-home"><span itemprop="name"><?php echo $front_title; ?></span></a>
                    <meta itemprop="position" content="1">
                </li>
                <?php

                //
                $i = 2;
                foreach ($arr_breadcrumbs as $v) {
                ?>
                    <li itemprop="itemListElement" itemscope="" itemtype="http://schema.org/ListItem"><a href="<?php echo $v['url']; ?>" itemprop="item" title="<?php echo $v['title']; ?>" class="active-menu-item"><span itemprop="name"><?php echo $v['title']; ?></span></a>
                        <meta itemprop="position" content="<?php echo $i; ?>">
                    </li>
                <?php

                    //
                    $i++;
                }

                ?>
            </ul>
        </div>
    </div>
<?php

    //
    $result = ob_get_contents();
    ob_end_clean();

    //
    return str_replace('entry_title_tag', $entry_tag, $result);
}

// chi tiết tin tức hoặc sản phẩm
function wgr_details_breadcrumb($entry_tag = '')
{
    if (is_singular('post')) {
        $category = get_the_category();
    } else if (is_singular('product')) {
        $category = get_the_terms(get_the_ID(), 'product_cat');
    } else {
        return '';
    }
    // print_r($category);
    if (empty($category)) {
        return '';
    }

    // xác định tên trang chủ dựa theo option page_on_front
    $page_on_front = get_option('page_on_front');
    if ($page_on_front > 0) {
        $front_title = get_the_title($page_on_front);
    } else {
        $front_title = __('Home', 'flatsome');
    }

    //
    ob_start();

?>
    <div class="wgr_details_breadcrumb">
        <div class="wgr_details_breadcrumb-inner">
            <?php

            // nếu là h1 -> hiển thị title của post hiện tại
            if ($entry_tag == 'h1') {
            ?>
                <h1 class="breadcrumb-title entry-post-title"><?php the_title(); ?></h1>
            <?php
            }
            // các thẻ khác sẽ hiển thị danh mục
            else if ($entry_tag != '') {
            ?>
                <entry_title_tag class="breadcrumb-title entry-post-title"><?php echo $category[0]->name; ?></entry_title_tag>
            <?php
            }
            ?>
            <ul class="cf wgr-breadcrumb" itemscope="" itemtype="http://schema.org/BreadcrumbList">
                <li itemprop="itemListElement" itemscope="" itemtype="http://schema.org/ListItem"><a href="./" itemprop="item" title="<?php echo $front_title; ?>" class="breadcrumb-home"><span itemprop="name"><?php echo $front_title; ?></span></a>
                    <meta itemprop="position" content="1">
                </li>
                <?php

                //
                $i = 2;
                foreach ($category as $v) {
                ?>
                    <li itemprop="itemListElement" itemscope="" itemtype="http://schema.org/ListItem"><a href="<?php echo get_category_link($v->term_id); ?>" itemprop="item" title="<?php echo $v->name; ?>" class="active-menu-item"><span itemprop="name"><?php echo $v->name; ?></span></a>
                        <meta itemprop="position" content="<?php echo $i; ?>">
                    </li>
                <?php

                    //
                    $i++;
                }
                ?>
            </ul>
        </div>
    </div>
<?php

    //
    $result = ob_get_contents();
    ob_end_clean();

    //
    return str_replace('entry_title_tag', $entry_tag, $result);
}
