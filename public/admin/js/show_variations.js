/**
 * Nạp biến thể sản phẩm bằng AJAX rồi append dưới từng sản phẩm cha
 * trên màn hình danh sách product (edit.php?post_type=product).
 *
 * Config từ PHP: window.wgrShowVariations = { ajaxUrl, nonce }
 */
(function ($) {
	var cfg = window.wgrShowVariations || {};

	function wgrCollectProductIds() {
		var ids = [];
		$('#the-list > tr[id^="post-"]').each(function () {
			if ($(this).hasClass("wgr-admin-variation-row")) {
				return;
			}
			var idAttr = this.id || "";
			var m = idAttr.match(/^post-(\d+)$/);
			if (m) {
				ids.push(parseInt(m[1], 10));
			}
		});
		return ids;
	}

	function wgrCollectColumns() {
		var cols = [];
		$("#the-list")
			.closest("table")
			.find("thead tr .manage-column")
			.each(function () {
				var id = this.id || "";
				if (id) {
					cols.push(id);
				}
			});
		return cols;
	}

	function wgrLoadVariations() {
		var $list = $("#the-list");
		if (!$list.length || !cfg.ajaxUrl || !cfg.nonce) {
			return;
		}

		var ids = wgrCollectProductIds();
		if (!ids.length) {
			return;
		}

		$.ajax({
			url: cfg.ajaxUrl,
			type: "POST",
			dataType: "json",
			data: {
				action: "wgr_load_product_variations",
				nonce: cfg.nonce,
				product_ids: ids,
				columns: wgrCollectColumns(),
			},
		}).done(function (res) {
			if (!res || !res.success || !res.data || !res.data.rows) {
				return;
			}

			var rows = res.data.rows;
			Object.keys(rows).forEach(function (parentId) {
				var $parent = $list.find("tr#post-" + parentId);
				if (!$parent.length) {
					return;
				}
				// Xóa biến thể cũ (nếu có) rồi append lại.
				$list
					.find(
						'tr.wgr-admin-variation-row[data-parent="' + parentId + '"]',
					)
					.remove();
				$parent.after(rows[parentId]);
			});
		});
	}

	$(wgrLoadVariations);
})(jQuery);
