// Chờ và nạp hightlight
function loadPrismOptionsFrameworkHighlight(
	textareaId = null,
	language = null,
	maxI = 22,
) {
	if (typeof initTextareaHighlight !== "function") {
		// Chưa load xong, chờ 500ms rồi thử lại
		setTimeout(function () {
			loadPrismOptionsFrameworkHighlight(textareaId, language, maxI - 1);
		}, 500);
		return false;
	}

	// nếu có textareaId thì chỉ nạp cho cái đó
	if (textareaId && document.getElementById(textareaId)) {
		// remove highlight cũ trước
		removeTextareaHighlight(textareaId);
		// Init lại với CSS highlighting
		initTextareaHighlight(
			textareaId,
			language
				? language
				: textareaId.includes("custom_css")
					? "css"
					: "markup",
		);
		return true;
	}

	// Đã load xong, gọi hàm init để nạp các textarea cần highlight
	let arrCssHighlight = [
		"html_custom_css",
		"html_custom_css_tablet",
		"html_custom_css_mobile",
	];
	for (let i = 0; i < arrCssHighlight.length; i++) {
		if (document.getElementById(arrCssHighlight[i])) {
			// remove highlight cũ trước
			removeTextareaHighlight(arrCssHighlight[i]);
			// Init lại với CSS highlighting
			initTextareaHighlight(arrCssHighlight[i], "css");
		}
	}

	// Đã load xong, gọi hàm init để nạp các textarea cần highlight
	if (1 > 2) {
		let arrHtmlHighlight = [
			"html_scripts_header",
			"html_scripts_footer",
			"html_scripts_after_body",
			"html_scripts_before_body",
			"html_shop_page",
			// "tab_content",
			"html_before_add_to_cart",
			"html_after_add_to_cart",
			"html_thank_you",
			// "catalog_mode_header",
			// "catalog_mode_product",
			// "catalog_mode_lightbox",
		];
		for (let i = 0; i < arrHtmlHighlight.length; i++) {
			if (document.getElementById(arrHtmlHighlight[i])) {
				// remove highlight cũ trước
				removeTextareaHighlight(arrHtmlHighlight[i]);
				// Init lại với HTML highlighting
				initTextareaHighlight(arrHtmlHighlight[i], "markup");
			}
		}
	}
}

// JavaScript xử lý cho trang Options Framework
jQuery(document).ready(function () {
	// khi thay đổi nội dung trong textarea thì thay đổi chiều cao của ô chứa dựa theo lượng text, tối thiểu 8 dòng
	function autoResizeTextarea(textarea) {
		// Đếm số dòng trong textarea
		var lines = textarea.value.split("\n").length;

		// Tối thiểu 8 dòng
		var rows = Math.max(lines, 8);

		// Set rows attribute
		textarea.rows = rows;
	}

	// Apply to all textareas in the section
	jQuery("#of_container #content .section.section-textarea textarea")
		.removeAttr("cols")
		.click(function () {
			// add class để tránh việc click nhiều lần
			if (!jQuery(this).hasClass("add-highlight-by-click")) {
				jQuery(this).addClass("add-highlight-by-click");
				// highlight lại nội dung trong textarea css theo id
				if (this.id.includes("custom_css")) {
					loadPrismOptionsFrameworkHighlight(this.id, "css");
				}
			}
		})
		.change(function () {
			// Update rows on change
			autoResizeTextarea(this);
			// highlight lại nội dung trong textarea css theo id
			if (this.id.includes("custom_css")) {
				loadPrismOptionsFrameworkHighlight(this.id, "css");
			}
		})
		.each(function () {
			// Set initial rows
			autoResizeTextarea(this);

			// jQuery(this).on("change", function () {});
		});

	//
	// loadPrismOptionsFrameworkHighlight();
});
