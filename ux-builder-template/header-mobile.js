// JavaScript Document
(function ($) {
	//
	jQuery(".click-toggle-mobile-menu").click(function () {
		jQuery("body").toggleClass("active-mobile-menu");
	});

	// tạo hiệu ứng mở menu cho bản mobile
	if (jQuery(window).width() < 768) {
		jQuery(".header-mobile-menu .sub-menu")
			.parent("li")
			.prepend(
				'<button class="toggle"><i class="icon-angle-down"></i></button>'
			);
	}
})(jQuery);
