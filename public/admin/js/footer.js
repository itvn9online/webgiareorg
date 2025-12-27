/**
 * Closure Compiler EchBay - Auto Minify CSS/JS
 * Tự động nén các file CSS/JS thông qua Closure Compiler API
 */

// Biến theo dõi trạng thái
var isCompiling = false;

/**
 * Bắt đầu quá trình tự động nén file
 */
function start_closure_compiler_echbay() {
	var $btn = jQuery(".start-compiler-closure");

	if (isCompiling) {
		// Dừng nén
		isCompiling = false;
		$btn
			.text("Bắt đầu nén file")
			.removeClass("btn-warning")
			.addClass("btn-primary");
		console.log("✗ Đã dừng nén file");
	} else {
		// Bắt đầu nén
		var $firstFile = jQuery(
			"#wgr-list-backup-css-js a.closure-compiler-echbay"
		).first();

		if ($firstFile.length === 0) {
			WGR_alert("Không có file nào cần nén!", "warning");
			return;
		}

		isCompiling = true;
		$btn.text("Dừng nén").removeClass("btn-primary").addClass("btn-warning");
		console.log("✓ Bắt đầu nén file...");

		// Click vào file đầu tiên
		$firstFile[0].click();
	}
}

/**
 * Xử lý khi nén file thành công
 */
function after_closure_compiler_echbay(type, mesage = null, result_url = null) {
	// Tìm file đang được nén và xóa class và attr href tương ứng
	var $currentFile = jQuery(
		"#wgr-list-backup-css-js a.closure-compiler-echbay"
	).first();
	$currentFile
		.removeClass("closure-compiler-echbay")
		.addClass("compiled-success");

	if (type === "error") {
		console.warn("✗ Minification failed: " + $currentFile.text());
		$currentFile.addClass("orgcolor");
	} else {
		console.log("✓ Minification successful: " + $currentFile.text());
		$currentFile.addClass("greencolor").removeAttr("href");
	}
	if (result_url !== null) {
		$currentFile.attr({
			href: result_url + "?v=" + new Date().getTime(),
			target: "_blank",
		});
	}
	if (mesage !== null) {
		WGR_alert(mesage, type);
	}

	// Nếu vẫn đang chạy, tìm file tiếp theo
	if (isCompiling) {
		var $nextFile = jQuery(
			"#wgr-list-backup-css-js a.closure-compiler-echbay"
		).first();

		if ($nextFile.length > 0) {
			// Delay 300ms rồi nén file tiếp theo
			setTimeout(function () {
				$nextFile[0].click();
			}, 300);
		} else {
			// Đã hết file
			isCompiling = false;
			jQuery(".start-compiler-closure")
				.text("Bắt đầu nén file")
				.removeClass("btn-warning")
				.addClass("btn-success");
			console.log("✓ Hoàn thành nén tất cả file!");

			// Reset về primary sau 3s
			setTimeout(function () {
				jQuery(".start-compiler-closure")
					.removeClass("btn-success")
					.addClass("btn-primary");
			}, 3000);
		}
	}
}

// Khi tài liệu sẵn sàng
jQuery(document).ready(function () {
	// Tự động điều chỉnh chiều cao textarea
	jQuery(".form-field textarea")
		.on("change", function () {
			let a = jQuery(this).val();
			a = a.split("\n");
			jQuery(this).attr({
				rows: a.length + 1,
			});
		})
		.click(function () {
			jQuery(this).trigger("change");
		});
});
