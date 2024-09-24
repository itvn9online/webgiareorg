jQuery(document).ready(function () {
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
