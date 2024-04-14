//console.log(typeof jQuery);

function a_ez_toc_link() {
	$("a.ez-toc-link")
		.each(function () {
			let a = $(this).attr("href") || "";
			if (a.substr(0, 1) == "#") {
				$(this).attr({
					href: eb_this_current_url + a,
				});
			}
		})
		.off("click")
		.on("click", function (e) {
			//console.log(Math.random());
			//e.preventDefault();
			let a = $(this).attr("href") || "";
			if (a != "") {
				a = a.split("#");
				if (a.length > 1) {
					window.location.hash = a[1];

					//
					let o = jQuery('.ez-toc-section[id="' + a[1] + '"]');
					if (o.length > 0) {
						o = o.offset().top;
						o -= jQuery("#header").height() || jQuery("header").height() || 90;
						o -= jQuery("#wpadminbar").height() || 0;
						o = Math.ceil(o);
						//console.log(o);
						window.scroll(0, o);
						//jQuery("html, body").animate({ scrollTop: o }, 500);
					}
				}
			}
			return false;
		});
}

//
(function ($) {
	// tạo menu cho phần my account
	$(".json-to-menu").each(function () {
		let a = $.trim($(this).html());

		//
		a = JSON.parse(a);
		//console.log(a);

		//
		let str = "";
		if (typeof a.arr != "undefined") {
			let arr = a.arr;
			//console.log(arr);
			for (let x in arr) {
				str += '<li><a href="' + a.link + x + '">' + arr[x] + "</a></li>";
			}
			str = '<ul class="sub-menu">' + str + "</ul>";
		}

		//
		str = '<li><a href="' + a.link + '">' + a.name + "</a>" + str + "</li>";

		//
		let cl = "actived";
		if (typeof a.class != "undefined") {
			cl += " " + a.class;
		}

		//
		$(this).html(str).removeClass("d-none").addClass(cl);
	});

	//
	jQuery('a[href="#"]').attr({
		href: "javascript:;",
		rel: "nofollow",
		/*
		})
		.click(function () {
			return false;
			*/
	});

	//
	jQuery("a").each(function () {
		// chỉnh lại link cho phone call
		let a = $(this).attr("href") || "";
		if (a.includes("tel:") == true) {
			$(this).attr({
				href: "tel:" + a.split("tel:")[1],
			});
		}
		if (a.includes("mailto:") == true) {
			$(this).attr({
				href: "mailto:" + a.split("mailto:")[1],
			});
		}

		//
		if ((jQuery(this).attr("aria-label") || "") == "") {
			$(this).attr({
				"aria-label":
					$(this).attr("title") || $(this).attr("data-title") || "External",
			});
		}
	});

	//
	$(document).ready(function () {
		$("body").addClass("document-ready");

		//
		//a_ez_toc_link();
	});
})(jQuery);

//
setInterval(function () {
	let new_scroll_top = window.scrollY || jQuery(window).scrollTop();

	//
	if (new_scroll_top > 120) {
		jQuery("body").addClass("ebfixed-top-menu");

		//
		//WGR_lazyload_footer_content();

		//
		if (new_scroll_top > 500) {
			//		if ( new_scroll_top < old_scroll_top ) {
			jQuery("body").addClass("ebshow-top-scroll");

			//
			//_global_js_eb.ebBgLazzyLoad(new_scroll_top);
		} else {
			jQuery("body").removeClass("ebshow-top-scroll");
		}
	} else {
		jQuery("body")
			.removeClass("ebfixed-top-menu")
			.removeClass("ebshow-top-scroll");
	}
}, 200);
