/**
 * Ô tìm kiếm nhanh Chuyên mục/ Danh mục sản phẩm/ Thương hiệu/ Thẻ/ Bài viết/ Trang/ Sản phẩm/ Portfolio
 * để thêm vào menu trên màn hình Giao diện > Menu (nav-menus.php).
 *
 * Config từ PHP: window.wgrNavMenuSearch = { ajaxUrl, nonce, labels }
 */
(function ($) {
	var cfg = window.wgrNavMenuSearch || {};
	var MIN_CHARS = 3;
	var timer = null;
	var xhr = null;
	// null = chưa tìm lần nào
	var lastKeyword = null;
	// kết quả khi chưa nhập từ khóa (bài mới nhất) -> xóa từ khóa thì hiển thị lại, không gọi AJAX
	var latestGroups = null;

	function wgrNormalizeKeyword(str) {
		return $.trim(String(str || "").replace(/\s+/g, " "));
	}

	// rỗng = danh sách mới nhất; còn lại phải đủ MIN_CHARS ký tự (không tính khoảng trắng)
	function wgrIsValidKeyword(keyword) {
		return keyword === "" || keyword.replace(/\s/g, "").length >= MIN_CHARS;
	}

	function wgrRenderResults($list, groups) {
		$list.empty();

		if (!groups.length) {
			$list.append(
				$('<li class="wgr-nav-menu-search-empty"></li>').text(
					"Không tìm thấy kết quả phù hợp.",
				),
			);
			return;
		}

		groups.forEach(function (group) {
			$list.append(
				$('<li class="wgr-nav-menu-search-group"></li>').text(
					group.label + " (" + group.items.length + ")",
				),
			);

			group.items.forEach(function (item) {
				$list.append(
					$('<li class="wgr-nav-menu-search-item"></li>')
						.attr({
							"data-kind": group.kind,
							"data-type": group.type,
							"data-id": item.id,
							title: "Bấm để thêm vào menu",
						})
						.text(item.title)
						.append(
							$('<span class="wgr-nav-menu-search-id"></span>').text(
								" #" + item.id,
							),
						),
				);
			});
		});
	}

	function wgrSearch($box, keyword) {
		var $list = $box.find(".wgr-nav-menu-search-results");
		var $spinner = $box.find(".spinner");

		if (keyword === lastKeyword) {
			return;
		}
		lastKeyword = keyword;

		if (xhr) {
			xhr.abort();
		}

		if (keyword === "" && latestGroups) {
			wgrRenderResults($list, latestGroups);
			$list.show();
			return;
		}

		$spinner.addClass("is-active");
		xhr = $.ajax({
			url: cfg.ajaxUrl,
			type: "POST",
			dataType: "json",
			data: {
				action: "wgr_nav_menu_search_posts",
				nonce: cfg.nonce,
				keyword: keyword,
			},
		})
			.done(function (res) {
				if (!res || !res.success || !res.data) {
					return;
				}
				var groups = res.data.groups || [];
				if (keyword === "") {
					latestGroups = groups;
				}
				wgrRenderResults($list, groups);
				$list.show();
			})
			.fail(function (jqXHR, status) {
				// lỗi thật (không phải do abort) -> cho phép tìm lại đúng từ khóa này
				if (status !== "abort" && lastKeyword === keyword) {
					lastKeyword = null;
				}
			})
			.always(function () {
				$spinner.removeClass("is-active");
			});
	}

	function wgrAddToMenu($item) {
		if ($item.hasClass("is-adding")) {
			return;
		}
		$item.addClass("is-adding");

		window.wpNavMenu.addItemToMenu(
			{
				"-1": {
					"menu-item-type": $item.attr("data-kind"),
					"menu-item-object": $item.attr("data-type"),
					"menu-item-object-id": $item.attr("data-id"),
				},
			},
			window.wpNavMenu.addMenuItemToBottom,
			function () {
				$item.removeClass("is-adding").addClass("is-added");
			},
		);
	}

	function wgrInit() {
		var $frame = $("#nav-menus-frame");
		// chỉ chạy khi đang sửa 1 menu cụ thể (chưa có menu thì WP không cho thêm item)
		if (
			!$frame.length ||
			!$("#menu-to-edit").length ||
			!window.wpNavMenu ||
			!cfg.ajaxUrl
		) {
			return;
		}

		var labels = (cfg.labels || []).filter(function (label, i, arr) {
			return arr.indexOf(label) === i;
		});

		var $box = $(
			'<div class="wgr-nav-menu-search">' +
				'<div class="wgr-nav-menu-search-input">' +
				'<input type="search" class="regular-text" autocomplete="off" />' +
				'<span class="spinner"></span>' +
				"</div>" +
				'<p class="description"></p>' +
				'<ul class="wgr-nav-menu-search-results"></ul>' +
				"</div>",
		);
		$box
			.find("input")
			.attr("placeholder", "Tìm kiếm danh mục, bài viết, sản phẩm... để thêm vào menu");
		$box
			.find(".description")
			.text(
				"* Tìm trong: " +
					labels.join(", ") +
					". Nhập tối thiểu " +
					MIN_CHARS +
					" ký tự của tên/ tiêu đề (có dấu hoặc không dấu) hoặc ID, sau đó bấm vào kết quả để thêm vào cuối menu. Nhớ bấm Lưu menu sau khi thêm.",
			);
		$frame.before($box);
		$("body").addClass("wgr-has-nav-menu-search");

		$box
			.find("input")
			.on("focus click", function () {
				if (lastKeyword === null) {
					var keyword = wgrNormalizeKeyword($(this).val());
					wgrSearch($box, wgrIsValidKeyword(keyword) ? keyword : "");
				}
			})
			.on("input", function () {
				var keyword = wgrNormalizeKeyword($(this).val());
				clearTimeout(timer);

				if (!wgrIsValidKeyword(keyword)) {
					return;
				}

				timer = setTimeout(function () {
					wgrSearch($box, keyword);
				}, 300);
			});

		$box.on("click", ".wgr-nav-menu-search-item", function () {
			wgrAddToMenu($(this));
		});
	}

	$(wgrInit);
})(jQuery);
