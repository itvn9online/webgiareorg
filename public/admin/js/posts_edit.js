// hỗ trợ tìm kiếm các danh mục khi soạn bài viết -> hữu dụng cho trường hợp xuất hiện quá nhiều
function WGR_find_taxonomy_for_edit(tax_id) {
	let tax = tax_id + "div";
	// lượng dữ liệu nhiều chút thì mới hỗ trợ tìm kiếm
	if (
		jQuery("#" + tax).length === 0 ||
		jQuery("#" + tax_id + "checklist li").length < 20
	) {
		return false;
	}
	console.log(
		"%c Hỗ trợ tìm kiếm taxonomy khi sửa bài #" + tax,
		"color: green;"
	);

	// thêm ô tìm kiếm
	let input_id = "WGR_search_taxonomy_" + tax_id;
	jQuery("#" + tax + " .postbox-header").after(
		'<div class="WGR-post-edit-search-taxnomy"><textarea placeholder="Tìm kiếm nhanh, mỗi từ khóa cách nhau bởi dấu xuống dòng" id="' +
			input_id +
			'" rows="3"></textarea></div>'
	);

	//
	jQuery("#" + tax_id + "checklist li label").each(function () {
		let a = jQuery(this).html() || "";

		if (a != "") {
			//console.log(a);
			//a = jQuery(a).find('input').remove().end();
			a = WGR_strip_tags(a);
			a = WGR_non_mark_seo(a);
			a = a.replace(/[^0-9a-zA-Z]/g, "");
			//console.log(a);

			//
			jQuery(this).attr({
				"data-key": a,
			});
		}
	});

	//
	jQuery("#" + input_id)
		.off("keyup")
		.keyup(function (e) {
			if (e.keyCode == 13) {
				// jQuery(this).trigger("change");
				return false;
			}

			let fix_id = tax_id + "checklist",
				keys = jQuery.trim(jQuery(this).val() || "");
			if (keys.length < 3) {
				jQuery("#" + fix_id + " li label").show();
				return false;
			}
			jQuery("#" + fix_id + " li label").hide();

			//
			keys = keys.split("\n");
			for (let i = 0; i < keys.length; i++) {
				let key = WGR_non_mark_seo(keys[i]);
				key = key.replace(/[^0-9a-zA-Z]/g, "");
				if (key == "") {
					continue;
				}

				//
				jQuery("#" + fix_id + " li label").each(function () {
					let a = jQuery(this).attr("data-key") || "";
					if (a != "" && a.includes(key) == true) {
						jQuery(this).show();
					}
				});

				//jQuery('#' + fix_id + ' li[data-show="1"]').show()
			}
		});
}

// tạo các quick menu cho phần edit post
function WGR_edit_post_quick_tab() {
	let str = "";
	jQuery(
		[
			"#normal-sortables .postbox",
			"#advanced-sortables .postbox",
			// "#side-sortables .postbox",
		].join(",")
	).each(function () {
		let a =
				jQuery(".postbox-header h2:first", this).html() ||
				jQuery("h2 span", this).html() ||
				"",
			jd = jQuery(this).attr("id") || "",
			cl = jQuery(this).attr("class") || "";
		a = jQuery.trim(a.split("<")[0]);
		if (a == "") {
			a = jQuery(".postbox-header h2:first span", this).html() || "";
			a = jQuery.trim(a.split("<")[0]);
		}
		// console.log(a);

		//
		if (
			a != "" &&
			jd != "" &&
			jd != "submitdiv" &&
			cl.includes("hide-if-js") == false
		) {
			str += '<li data-id="' + jd + '">' + a + "</li>";
		}
	});
	str += "<li>Về đầu trang ^</li>";
	//		console.log(str);

	//
	jQuery("body").append('<ul class="edit-post-wgr-tab">' + str + "</ul>");

	jQuery(".edit-post-wgr-tab li").click(function () {
		let jd = jQuery(this).attr("data-id") || "",
			to_id = 0;

		//
		if (jQuery(".edit-post-layout__content").length > 0) {
			if (jd != "") {
				to_id = jQuery(".edit-post-visual-editor").height();
			}

			//
			jQuery(".edit-post-layout__content").animate(
				{
					scrollTop: to_id,
				},
				600
			);
		} else {
			if (jd != "") {
				to_id = jQuery("#" + jd).offset().top - 90;

				jQuery(".postbox h2").removeClass("orgcolor");
				jQuery("#" + jd + " h2").addClass("orgcolor");
			}

			//
			jQuery("body,html").animate(
				{
					scrollTop: to_id,
				},
				600
			);
		}
	});
}

// hiển thị nút nhân bản
(function () {
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
})();

//
jQuery(document).ready(function () {
	WGR_find_taxonomy_for_edit("category");
	WGR_find_taxonomy_for_edit("product_cat");

	//
	WGR_edit_post_quick_tab();
});
