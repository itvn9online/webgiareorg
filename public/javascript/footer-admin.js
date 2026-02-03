/**
 * Code trong này dành riêng cho admin
 */

// lấy ID menu và tạo link dẫn tới trnag edit menu trong admin
jQuery(".eb-set-menu-selected").each(function () {
	var menu_id = jQuery(this).attr("id") || "";
	if (menu_id != "") {
		jQuery(this).before(
			'<a href="' +
				web_admin_link +
				"admin.php?page=eb-tools&goto_menu=" +
				menu_id +
				'" target="_blank" rel="noopener noreferrer" class="eb-tools-goto_menu">Edit Menu</a>',
		);
	}
});
