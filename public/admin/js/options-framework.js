jQuery(document).ready(function () {
	// khi thay đổi nội dung trong textarea thì thay đổi chiều cao của ô chứa dựa theo lượng text, tối thiểu 8 dòng
	function autoResizeTextarea(textarea) {
		// Reset height to auto to get the correct scrollHeight
		textarea.style.height = "auto";

		// Calculate the number of lines (minimum 7 lines)
		var lineHeight = parseInt(jQuery(textarea).css("line-height"));
		var minHeight = lineHeight * 7;
		var newHeight = Math.max(textarea.scrollHeight, minHeight);

		// Set the new height + 1 line for padding
		newHeight += lineHeight;
		textarea.style.height = newHeight + "px";
	}

	// Apply to all textareas in the section
	jQuery("#of_container #content .section.section-textarea textarea").each(
		function () {
			// Set initial height
			autoResizeTextarea(this);

			// Update height on input
			jQuery(this).on("input change keyup", function () {
				autoResizeTextarea(this);
			});
		},
	);
});
