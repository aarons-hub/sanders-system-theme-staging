/**
 * Use this file for JavaScript code that you want to run in the front-end
 * on posts/pages that contain this block.
 *
 * When this file is defined as the value of the `viewScript` property
 * in `block.json` it will be enqueued on the front end of the site.
 *
 * Example:
 *
 * ```js
 * {
 *   "viewScript": "file:./view.js"
 * }
 * ```
 *
 * If you're not making any changes to this file because your project doesn't need any
 * JavaScript running in the front-end, then you should delete this file and remove
 * the `viewScript` property from `block.json`.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#view-script
 */

/* eslint-disable no-console */
/* eslint-enable no-console */

// Theme mega menu close button: set aria-expanded to false on click (robust, supports multiple close button classes)
(function () {
	document.addEventListener("DOMContentLoaded", function () {
		// Inject mega-menu-backdrop div after <body> tag
		if (!document.querySelector(".mega-menu-backdrop")) {
			var backdropDiv = document.createElement("div");
			backdropDiv.className = "mega-menu-backdrop";
			document.body.insertBefore(backdropDiv, document.body.firstChild);
		}
		const backdrop = document.querySelector(".mega-menu-backdrop");
		function updateBackdrop() {
			const anyOpen = document.querySelector(
				'.mega-menu-button[aria-expanded="true"]',
			);
			if (backdrop) {
				if (anyOpen) {
					backdrop.classList.add("active");
				} else {
					backdrop.classList.remove("active");
				}
			}
		}
		document.body.addEventListener("click", function (e) {
			// Toggle aria-expanded for mega menu button (including mobile)
			if (e.target && e.target.classList.contains("mega-menu-button")) {
				var isExpanded = e.target.getAttribute("aria-expanded") === "true";
				e.target.setAttribute("aria-expanded", (!isExpanded).toString());
				setTimeout(updateBackdrop, 10);
			}
			// Always set aria-expanded to false for close buttons
			if (
				e.target &&
				(e.target.classList.contains("theme-mega-menu-close-button") ||
					e.target.classList.contains("menu-container__close-button"))
			) {
				// Try to find the closest mega menu item (desktop)
				var menuItem = e.target.closest(".mega-menu-item");
				if (menuItem) {
					var button = menuItem.querySelector(".mega-menu-button");
					if (button) {
						button.setAttribute("aria-expanded", "false");
					}
				}
				// Try to find the closest mobile menu item (mobile)
				var mobileMenuItem = e.target.closest(".theme-mobile-menu-item");
				if (mobileMenuItem) {
					var mobileButton = mobileMenuItem.querySelector(
						".mega-menu-button.mobile",
					);
					if (mobileButton) {
						mobileButton.setAttribute("aria-expanded", "false");
					}
				}
				setTimeout(updateBackdrop, 10);
			}
			// Hide all menus if backdrop is clicked
			if (backdrop && e.target === backdrop) {
				const openButtons = document.querySelectorAll(
					'.mega-menu-button[aria-expanded="true"]',
				);
				openButtons.forEach((btn) =>
					btn.setAttribute("aria-expanded", "false"),
				);
				setTimeout(updateBackdrop, 10);
			}
		});
	});
})();
