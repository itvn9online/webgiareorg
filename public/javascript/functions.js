var web_link =
	jQuery("base").attr("href") ||
	window.location.protocol + "//" + document.domain + "/";
var eb_this_current_url = window.location.href.split("#")[0];

// định dạng số -> tương tự number_format trong php
var numFormatter = new Intl.NumberFormat("en-US");

// định dạng tiền tệ
// var moneyFormatter = numFormatter;

// chờ vuejs nạp xong để khởi tạo nội dung
function WGR_vuejs(app_id, obj, _callBack, max_i) {
	if (typeof max_i != "number") {
		max_i = 100;
	} else if (max_i < 0) {
		console.log("%c Max loaded Vuejs", "color: red");
		return false;
	}

	//
	if (typeof Vue != "function") {
		setTimeout(function () {
			WGR_vuejs(app_id, obj, _callBack, max_i - 1);
		}, 100);
		return false;
	}

	// chưa tìm ra hàm định dạng ngày tháng tương tự angular -> tự viết hàm riêng vậy
	// -> xác định giờ theo múi giờ hiện tại của user
	let tzoffset = new Date().getTimezoneOffset() * 60000; // offset in milliseconds
	//console.log('tzoffset:', tzoffset);
	obj.datetime = function (t, len) {
		if (typeof len != "number") {
			len = 19;
		}
		return new Date(t - tzoffset)
			.toISOString()
			.split(".")[0]
			.replace("T", " ")
			.slice(0, len);
	};
	obj.date = function (t) {
		return new Date(t - tzoffset).toISOString().split("T")[0];
	};
	obj.time = function (t, len) {
		if (typeof len != "number") {
			len = 8;
		}
		return new Date(t - tzoffset)
			.toISOString()
			.split(".")[0]
			.split("T")[1]
			.slice(0, len);
	};
	obj.number_format = function (n) {
		return new Intl.NumberFormat().format(n);
	};

	//
	//console.log(obj);
	//console.log(obj.data);
	new Vue({
		el: app_id,
		data: obj,
		mounted: function () {
			jQuery(app_id + ".ng-main-content").addClass("loaded");

			//
			if (typeof _callBack == "function") {
				_callBack();
			}

			//
			if (taxonomy_ids_unique.length == 0) {
				action_each_to_taxonomy();
			}
		},
	});
}

function WGR_non_mark(str) {
	str = str.toLowerCase();
	str = str.replace(
		/\u00e0|\u00e1|\u1ea1|\u1ea3|\u00e3|\u00e2|\u1ea7|\u1ea5|\u1ead|\u1ea9|\u1eab|\u0103|\u1eb1|\u1eaf|\u1eb7|\u1eb3|\u1eb5/g,
		"a"
	);
	str = str.replace(
		/\u00e8|\u00e9|\u1eb9|\u1ebb|\u1ebd|\u00ea|\u1ec1|\u1ebf|\u1ec7|\u1ec3|\u1ec5/g,
		"e"
	);
	str = str.replace(/\u00ec|\u00ed|\u1ecb|\u1ec9|\u0129/g, "i");
	str = str.replace(
		/\u00f2|\u00f3|\u1ecd|\u1ecf|\u00f5|\u00f4|\u1ed3|\u1ed1|\u1ed9|\u1ed5|\u1ed7|\u01a1|\u1edd|\u1edb|\u1ee3|\u1edf|\u1ee1/g,
		"o"
	);
	str = str.replace(
		/\u00f9|\u00fa|\u1ee5|\u1ee7|\u0169|\u01b0|\u1eeb|\u1ee9|\u1ef1|\u1eed|\u1eef/g,
		"u"
	);
	str = str.replace(/\u1ef3|\u00fd|\u1ef5|\u1ef7|\u1ef9/g, "y");
	str = str.replace(/\u0111/g, "d");
	return str;
}

function WGR_strip_tags(input, allowed) {
	if (typeof input == "undefined" || input == "") {
		return "";
	}

	//
	if (typeof allowed == "undefined") {
		allowed = "";
	}

	//
	allowed = (
		((allowed || "") + "").toLowerCase().match(/<[a-z][a-z0-9]*>/g) || []
	).join("");
	let tags = /<\/?([a-z][a-z0-9]*)\b[^>]*>/gi,
		cm = /<!--[\s\S]*?-->|<\?(?:php)?[\s\S]*?\?>/gi;
	return input.replace(cm, "").replace(tags, function ($0, $1) {
		return allowed.indexOf("<" + $1.toLowerCase() + ">") > -1 ? $0 : "";
	});
}

function WGR_non_mark_seo(str) {
	str = WGR_non_mark(str);
	str = str.replace(/\s/g, "-");
	str = str.replace(
		/!|@|%|\^|\*|\(|\)|\+|\=|\<|\>|\?|\/|,|\.|\:|\;|\'|\"|\&|\#|\[|\]|~|$|_/g,
		""
	);
	str = str.replace(/-+-/g, "-");
	str = str.replace(/^\-+|\-+$/g, "");
	for (let i = 0; i < 5; i++) {
		str = str.replace(/--/g, "-");
	}
	str = (function (s) {
		let str = "",
			re = /^\w+$/,
			t = "";
		for (let i = 0; i < s.length; i++) {
			t = s.slice(i, i + 1);
			if (t == "-" || t == "+" || re.test(t) == true) {
				str += t;
			}
		}
		return str;
	})(str);
	return str;
}

function WGR_html_alert(m, lnk) {
	return WGR_alert(m, lnk);
}

function WGR_alert(m, lnk) {
	if (typeof m == "undefined") {
		m = "";
	}
	if (typeof lnk == "undefined") {
		lnk = "";
	}
	//console.log(m);
	//console.log(lnk);

	//
	if (top != self) {
		top.WGR_alert(m, lnk);
	} else {
		if (m != "") {
			// class thể hiện màu sắc của alert
			let cl = "";
			if (lnk == "error") {
				cl = "redbg";
			} else if (lnk == "warning") {
				cl = "orgbg";
			}

			// id dùng để hẹn giờ tự ẩn
			let jd = "_" + Math.random().toString(32).replace(/\./gi, "_");

			//
			let htm = [
				'<div id="' +
					jd +
					'" class="' +
					cl +
					'" onClick="jQuery(this).fadeOut();">',
				m,
				"</div>",
			].join(" ");
			//console.log(htm);

			//
			if (jQuery("#my_custom_alert").length < 1) {
				jQuery("body").append('<div id="my_custom_alert"></div>');
			}
			jQuery("#my_custom_alert").append(htm).show();

			//
			setTimeout(function () {
				jQuery("#" + jd).remove();

				// nếu không còn div nào -> ẩn luôn
				if (jQuery("#my_custom_alert div").length < 1) {
					jQuery("#my_custom_alert").fadeOut();
				}
			}, 6000);
		} else if (lnk != "") {
			return WGR_redirect(lnk);
		}
	}

	//
	return false;
}

function WGR_redirect(l) {
	if (top != self) {
		top.WGR_redirect(l);
	} else if (typeof l != "undefined" && l != "") {
		window.location = l;
	}
}

function WGR_number_format(str) {
	// loại bỏ số 0 ở đầu chuỗi số
	str = str.toString().replace(/\,/g, "") * 1;
	// console.log(str);
	if (isNaN(str)) {
		return "NaN";
	} else if (str < 1000 && str > -999) {
		return str;
	}
	return numFormatter.format(str);
}
