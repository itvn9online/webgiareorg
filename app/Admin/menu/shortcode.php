<h1>Shortcode</h1>
<p>* Danh sách các shortcode có sẵn trong webgiareorg</p>
<p>* Các shortcode bắt đầu bằng echbay_ thường chỉ sử dụng trong UX Builder.</p>
<table>
    <?php
    foreach (
        [
            'wgr_breadcrumb' => 'Tạo breadcrumb không h1',
            'wgr_h1_breadcrumb' => 'Tạo breadcrumb có h1',
            'wgr_h2_breadcrumb' => 'Tạo breadcrumb có h2',
            // 
            'wgr_same_cat' => 'Trả về post với 3 col trên row',
            'wgr_same_col4_cat' => 'Trả về post với 4 col trên row',
            'wgr_same_col6_cat' => 'Trả về post với 2 col trên row',
            'wgr_same_vertical_cat' => 'Trả về post với 1 col trên row và hiển thị hình ảnh dọc (dạng vertical)',
            // 
            'WGR_product_comment' => 'Trả về danh sách bình luận của sản phẩm',
            // 
            'WGR_product_content' => 'In ra nội dung của sản phẩm',
            // 
            'wgr_product_same_cat' => 'Trả về product với 3 col trên row',
            'wgr_product_same_col4_cat' => 'Trả về product với 4 col trên row',
            'wgr_product_same_col6_cat' => 'Trả về product với 2 col trên row',
            'wgr_product_same_vertical_cat' => 'Trả về product với 1 col trên row và hiển thị hình ảnh dọc (dạng vertical)',
            // 
            'WGR_get_quick_register' => 'Tạo from đăng ký nhanh',
            // 
            'echbay_call_function' => 'Chức năng gọi tới các function dựng sẵn của webgiareorg',
            'echbay_call_menu' => 'Chức năng gọi tới các menu dựng sẵn của webgiareorg',
            'echbay_call_shortcode' => 'Echo shortcode trong flatsome thay vì dùng text của flatsome, hay bị đính kèm thẻ P',
            'echbay_facebook_like_box' => 'Chức năng tạo like box facebook',
            'echbay_google_map' => 'Chức năng tạo google map',
            'echbay_item_contact' => 'Chức năng tạo menu liên hệ, hỗ trợ icon và link',
            'echbay_menu_contact' => 'Dùng để tạo menu tương tự ux_menu của flatsome nhưng hỗ trợ cả text thuần để tối ưu SEO',
            // 'echbay_menu_link' => '',
            'echbay_youtube_video' => 'Chức năng tạo video youtube',
            // 'aaaaaaaaaaaa' => '',
        ] as $k => $v
    ) {
    ?>
        <tr>
            <td>
                <input type="text" value="[<?php echo $k; ?>]" onclick="this.select();" readonly />
            </td>
            <td><?php echo $v; ?></td>
        </tr>
    <?php
    }
    ?>
</table>