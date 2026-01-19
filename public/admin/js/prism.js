/**
 * Load Prism.js và CSS từ CDN
 */
(function () {
	var prismLoaded = false;
	var loadingCallbacks = [];

	window.loadPrismJS = function (callback) {
		// Nếu đã load rồi thì gọi callback luôn
		if (prismLoaded && window.Prism) {
			if (callback) callback();
			return;
		}

		// Thêm callback vào queue
		if (callback) {
			loadingCallbacks.push(callback);
		}

		// Nếu đang load thì không load lại
		if (window.Prism || document.getElementById("prism-css")) {
			return;
		}

		// Load CSS
		var css = document.createElement("link");
		css.id = "prism-css";
		css.rel = "stylesheet";
		css.href =
			"https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism.min.css";
		document.head.appendChild(css);

		// Load JS core
		var script = document.createElement("script");
		script.src =
			"https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js";
		script.onload = function () {
			// Load thêm các language components
			var languages = ["markup", "css"];
			// languages.push("javascript");
			// languages.push("php");
			// languages.push("json");
			// languages.push("sql");
			var loaded = 0;

			languages.forEach(function (lang) {
				if (lang === "markup" || lang === "javascript") {
					// Đã có sẵn trong core
					loaded++;
					return;
				}

				var langScript = document.createElement("script");
				langScript.src =
					"https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-" +
					lang +
					".min.js";
				langScript.onload = function () {
					loaded++;
					if (loaded === languages.length) {
						prismLoaded = true;
						// Gọi tất cả callbacks
						loadingCallbacks.forEach(function (cb) {
							cb();
						});
						loadingCallbacks = [];
					}
				};
				document.head.appendChild(langScript);
			});

			// Nếu chỉ có markup và javascript thì done luôn
			if (loaded === languages.length) {
				prismLoaded = true;
				loadingCallbacks.forEach(function (cb) {
					cb();
				});
				loadingCallbacks = [];
			}
		};
		document.head.appendChild(script);
	};
})();

/**
 * Init textarea highlighting với Prism.js
 * @param {string} textareaId - ID của textarea (không có dấu #)
 * @param {string} language - Ngôn ngữ: 'javascript', 'css', 'php', 'markup' (html), 'json', 'sql'
 * @param {object} options - Tùy chọn: {lineNumbers: true/false, theme: 'prism'/'okaidia'/'tomorrow'}
 */
window.initTextareaHighlight = function (textareaId, language, options) {
	language = language || "javascript";
	options = options || {};

	// Load Prism.js trước
	window.loadPrismJS(function () {
		var textarea = document.getElementById(textareaId);
		if (!textarea) {
			console.error("Không tìm thấy textarea với ID:", textareaId);
			return;
		}

		// Kiểm tra đã init chưa
		if (textarea.getAttribute("data-highlighted")) {
			console.warn("Textarea này đã được highlight rồi");
			return;
		}

		// Tạo wrapper container
		var wrapper = document.createElement("div");
		wrapper.className = "textarea-highlight-wrapper";
		wrapper.style.position = "relative";

		// Tạo pre > code cho highlighted content
		var pre = document.createElement("pre");
		pre.className = "textarea-highlight-backdrop";
		pre.style.cssText =
			"position: absolute; top: 0; left: 0; right: 0; bottom: 0; " +
			"margin: 0; padding: 10px 0 0 10px; border: 1px solid transparent; " +
			"pointer-events: none; overflow: hidden; white-space: pre-wrap; " +
			"word-wrap: break-word; font-family: sans-serif; " +
			"font-size: 13px; line-height: 1.6; background: #f5f5f5; " +
			"border-radius: 3px;";

		var code = document.createElement("code");
		code.className = "language-" + language;
		code.style.cssText = "font-family: sans-serif;";
		pre.appendChild(code);

		// Wrap textarea
		textarea.parentNode.insertBefore(wrapper, textarea);
		wrapper.appendChild(pre);
		wrapper.appendChild(textarea);

		// Lưu style gốc
		var originalStyle = {
			background: textarea.style.background || "#fff",
			color: textarea.style.color || "#000",
			padding: textarea.style.padding || "10px",
			border: textarea.style.border || "1px solid #ccc",
		};

		// Style cho textarea - làm trong suốt để thấy highlighting bên dưới
		textarea.style.cssText =
			"position: relative; z-index: 1; background: transparent !important; " +
			"padding: 10px; border: 1px solid #ccc; " +
			"font-family: sans-serif; " +
			"font-size: 13px; line-height: 1.6; resize: vertical; " +
			"color: transparent; caret-color: #000; outline: none; " +
			"width: 100%; box-sizing: border-box;";

		// Thêm CSS để text trong textarea hiện khi focus (cho caret)
		textarea.style.webkitTextFillColor = "transparent";

		// Function để update highlighting
		function updateHighlight() {
			var text = textarea.value;
			// Thêm newline ở cuối để tránh layout shift
			if (text[text.length - 1] === "\n") {
				text += " ";
			}

			code.textContent = text;
			Prism.highlightElement(code);

			// Sync scroll
			pre.scrollTop = textarea.scrollTop;
			pre.scrollLeft = textarea.scrollLeft;

			// Sync size
			pre.style.height = textarea.style.height || textarea.offsetHeight + "px";
			pre.style.width = textarea.style.width || textarea.offsetWidth + "px";
		}

		// Event listeners
		textarea.addEventListener("input", updateHighlight);
		textarea.addEventListener("scroll", function () {
			pre.scrollTop = textarea.scrollTop;
			pre.scrollLeft = textarea.scrollLeft;
		});

		// Tương thích với auto-resize
		var observer = new MutationObserver(function (mutations) {
			mutations.forEach(function (mutation) {
				if (
					mutation.type === "attributes" &&
					mutation.attributeName === "style"
				) {
					updateHighlight();
				}
			});
		});
		observer.observe(textarea, { attributes: true });

		// Mark as highlighted
		textarea.setAttribute("data-highlighted", "true");

		// Lưu originalStyle vào attribute để restore sau
		textarea.setAttribute("data-original-style", JSON.stringify(originalStyle));

		// Initial highlight
		updateHighlight();

		console.log(
			"✓ Textarea highlight initialized:",
			textareaId,
			"(" + language + ")",
		);
	});
};

/**
 * Remove highlighting từ textarea
 */
window.removeTextareaHighlight = function (textareaId) {
	var textarea = document.getElementById(textareaId);
	if (!textarea || !textarea.getAttribute("data-highlighted")) {
		return;
	}

	var wrapper = textarea.parentNode;
	if (wrapper.className === "textarea-highlight-wrapper") {
		// Restore original style
		var originalStyle = textarea.getAttribute("data-original-style");
		if (originalStyle) {
			try {
				originalStyle = JSON.parse(originalStyle);
				textarea.style.background = originalStyle.background;
				textarea.style.color = originalStyle.color;
				textarea.style.padding = originalStyle.padding;
				textarea.style.border = originalStyle.border;
			} catch (e) {
				// Fallback
				textarea.style.background = "#fff";
				textarea.style.color = "#000";
			}
		}

		// Unwrap textarea
		wrapper.parentNode.insertBefore(textarea, wrapper);
		wrapper.parentNode.removeChild(wrapper);

		// Reset properties
		textarea.style.webkitTextFillColor = "";
		textarea.removeAttribute("data-highlighted");
		textarea.removeAttribute("data-original-style");

		console.log("✓ Textarea highlight removed:", textareaId);
	}
};
