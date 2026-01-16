/**
 * Khi người dùng nhập giá sản phẩm tự động đồng bộ về định dạng chuẩn của woocommerce
 * - Sử dụng các tham số định dạng từ WooCommerce:
 *   + currency_format_num_decimals: Số chữ số thập phân (VNĐ=0, USD=2)
 *   + currency_format_thousand_sep: Ký tự phân cách hàng nghìn (,)
 *   + currency_format_decimal_sep: Ký tự phân cách thập phân (.)
 * - Input: Giá trị chuẩn không format (12990000 hoặc 12990.99)
 * - Span: Hiển thị format để dễ đọc (12,990,000 hoặc 12,990.99)
 */

// Hàm lấy cài đặt định dạng tiền tệ từ WooCommerce
function getCurrencyFormat() {
	if (typeof woocommerce_admin_meta_boxes !== "undefined") {
		return {
			decimals:
				parseInt(woocommerce_admin_meta_boxes.currency_format_num_decimals) ||
				0,
			decimalSep:
				woocommerce_admin_meta_boxes.currency_format_decimal_sep || ".",
			thousandSep:
				woocommerce_admin_meta_boxes.currency_format_thousand_sep || ",",
		};
	}
	// Mặc định cho VNĐ
	return {
		decimals: 0,
		decimalSep: ".",
		thousandSep: ",",
	};
}

// Hàm format số tiền để hiển thị
function formatPrice(value, format) {
	if (!value) return "";

	// Tách phần nguyên và phần thập phân
	const parts = value.split(".");
	let integer = parts[0];
	let decimal = parts[1] || "";

	// Format phần nguyên với thousand separator
	if (format.thousandSep) {
		integer = integer.replace(/\B(?=(\d{3})+(?!\d))/g, format.thousandSep);
	}

	// Nếu có phần thập phân
	if (format.decimals > 0 && decimal) {
		// Giới hạn số chữ số thập phân
		decimal = decimal.substring(0, format.decimals);
		return integer + format.decimalSep + decimal;
	}

	return integer;
}

// Hàm chuẩn hóa giá trị nhập vào (loại bỏ format, chuyển về dạng chuẩn)
function normalizePrice(value, format) {
	if (!value) return "";

	let normalized = value;

	// Nếu không có phần thập phân (decimals=0), loại bỏ TẤT CẢ ký tự không phải số
	if (format.decimals === 0) {
		normalized = normalized.replace(/[^0-9]/g, "");
		return normalized;
	}

	// Với trường hợp có phần thập phân (USD, EUR...)
	// Loại bỏ thousand separator trước
	if (format.thousandSep) {
		normalized = normalized.replace(
			new RegExp("\\" + format.thousandSep, "g"),
			""
		);
	}

	// Chuyển decimal separator về dấu chấm chuẩn
	if (format.decimalSep !== ".") {
		// Chỉ thay thế dấu decimal separator CUỐI CÙNG
		const lastDecimalIndex = normalized.lastIndexOf(format.decimalSep);
		if (lastDecimalIndex !== -1) {
			normalized =
				normalized.substring(0, lastDecimalIndex) +
				"." +
				normalized.substring(lastDecimalIndex + 1);
		}
	}

	// Chỉ giữ số và dấu chấm
	normalized = normalized.replace(/[^0-9\.]/g, "");

	// Xử lý nhiều dấu chấm: Giữ dấu chấm cuối cùng làm decimal separator
	const parts = normalized.split(".");
	if (parts.length > 2) {
		// Nối tất cả phần trước dấu chấm cuối cùng
		const integerPart = parts.slice(0, -1).join("");
		const decimalPart = parts[parts.length - 1];
		normalized = integerPart + "." + decimalPart;
	}

	// Giới hạn số chữ số thập phân theo cài đặt
	if (normalized.includes(".")) {
		const [int, dec] = normalized.split(".");
		normalized = int + "." + (dec || "").substring(0, format.decimals);
	}

	return normalized;
}

// Hàm tạo hoặc update span hiển thị format
function updateFormatDisplay($input, normalized, format) {
	const displayId = $input.attr("id") + "_display";
	let $display = jQuery("#" + displayId);

	// Tạo span nếu chưa có
	if ($display.length === 0) {
		$display = jQuery(
			'<span id="' +
				displayId +
				'" style="margin-left: 10px; color: #2271b1;"></span>'
		);
		$input.after($display);
	}

	// Update nội dung
	if (normalized) {
		const formatted = formatPrice(normalized, format);
		$display.text("= " + formatted);
		$display.show();
	} else {
		$display.hide();
	}
}

// Khởi tạo display cho các input đã có giá trị
jQuery(document).ready(function () {
	const format = getCurrencyFormat();

	jQuery("#_regular_price, #_sale_price").each(function () {
		const $input = jQuery(this);
		const val = $input.val();

		if (val) {
			updateFormatDisplay($input, val, format);
		}
	});
});

// Xử lý khi người dùng nhập giá
jQuery("#_regular_price, #_sale_price").on("input", function () {
	const $input = jQuery(this);
	let val = $input.val();
	const format = getCurrencyFormat();

	// Chuẩn hóa giá trị (loại bỏ format, chỉ giữ số chuẩn)
	const normalized = normalizePrice(val, format);

	// Cập nhật input với giá trị chuẩn
	$input.val(normalized);

	// Update span hiển thị format
	updateFormatDisplay($input, normalized, format);
});
