/**
 * Flatsome Slider JS
 * Dùng khi muốn tạo slider thông qua `row` trong UX-Builder
 * Thêm vào `row` class `wgr-slider` xong copy đoạn code bên dưới cho vào file `d.js` trong child-theme
 * Tinh chỉnh lại `addClass` theo ý muốn
 * Tinh chỉnh lại `flickity-options` theo ý muốn
 * Trong UX-Builder, có thể tạo 1 element là `Blog post` dạng slider tương tự xong xem mã HTML mà nó tạo ra rồi tinh chỉnh lại class và options cho phù hợp
 */
jQuery(".wgr-slider")
	.addClass([
		// "large-columns-3",
		// "medium-columns-1",
		// "small-columns-1",
		"slider",
		"row-slider",
		// nút điều hướng hình tròn
		"slider-nav-circle",
		// nút điều hướng đơn giản
		// "slider-nav-simple",
		// khi muốn cho nút điều hướng ra ngoài thì dùng class này
		// "slider-nav-outside",
		"slider-nav-push",
	])
	.attr({
		"data-flickity-options": JSON.stringify({
			imagesLoaded: true,
			groupCells: "100%",
			// dragThreshold: 5,
			cellAlign: "left",
			wrapAround: true,
			prevNextButtons: true,
			percentPosition: true,
			pageDots: false,
			rightToLeft: false,
			autoPlay: false,
		}),
	});
