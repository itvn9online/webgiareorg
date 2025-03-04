// hiển thị nút nhân bản
(function ($) {
	jQuery("#wgr-for-duplicator").removeClass("d-none").show();
	jQuery(".show-if-duplicator-null div").click(function () {
		jQuery(".show-if-duplicator-null").fadeOut();
	});

	//
	jQuery(".click-set-nhanban").click(function () {
		// sử dụng plugin Post duplicator
		if (jQuery("#duplicator").length === 0) {
			jQuery(".show-if-duplicator-null").fadeIn();

			setTimeout(function () {
				jQuery(".show-if-duplicator-null").fadeOut();
			}, 5000);

			return false;
		}

		//
		if (confirm("Xác nhận nhân bản bài viết này") == false) {
			return false;
		}

		//
		jQuery("#duplicator a").trigger("click");

		//
		return true;
	});

	// sau khi nhân bản xong, chuyển sang bài đó luôn
	if (window.location.href.includes("&post-duplicated=") == true) {
		jQuery("#wpbody-content .updated a").each(function () {
			let a = jQuery(this).attr("href") || "";

			if (
				a != "" &&
				a.includes("/post.php?post=") &&
				a.includes("&action=edit")
			) {
				console.log("a", a);
				window.location = a;
				return false;
			}
		});
	}
})(jQuery);
