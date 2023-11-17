// hiển thị nút nhân bản
(function ($) {
	$("#wgr-for-duplicator").removeClass("d-none").show();
	jQuery(".show-if-duplicator-null div").click(function () {
		jQuery(".show-if-duplicator-null").fadeOut();
	});

	//
	jQuery(".click-set-nhanban").click(function () {
		// sử dụng plugin Post duplicator
		if ($("#duplicator").length === 0) {
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
		jQuery("#duplicator a").click();

		//
		return true;
	});

	// sau khi nhân bản xong, chuyển sang bài đó luôn
	if (window.location.href.split("&post-duplicated=").length > 1) {
		let a = jQuery("#wpbody-content .updated a").attr("href") || "";

		if (a != "") {
			window.location = a;
			return false;
		}
	}
})(jQuery);
