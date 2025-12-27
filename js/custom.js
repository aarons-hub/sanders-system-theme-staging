(function () {
	"use strict";
	// Scroll to top animation when #gototop is clicked
	const goToTopButton = document.querySelector("#gototop");
	if (goToTopButton) {
		goToTopButton.addEventListener("click", function (e) {
			e.preventDefault();
			window.scrollTo({ top: 0, behavior: "smooth" });
		});
	}

	// Toggle a class on header when scrolling past a certain point
	if (typeof ScrollTrigger !== "undefined") {
		ScrollTrigger.create({
			trigger: "header.wp-block-template-part",
			start: "top+=600 top",
			end: "top+=1000 top",
			scrub: true,
			markers: false,
			onEnter: () =>
				document
					.querySelector("header.wp-block-template-part")
					.classList.add("header-scrolled"),
			onLeaveBack: () =>
				document
					.querySelector("header.wp-block-template-part")
					.classList.remove("header-scrolled"),
		});
	}

	// Form control show .other-requirements when 'Other' is selected in the requirements select
	$(document).on("change", "select[name='select-700']", function () {
		var val = $(this).val();
		var $otherReq = $(".other-requirements");
		if (val === "Other") {
			$otherReq.addClass("visible");
		} else {
			$otherReq.removeClass("visible");
		}
	});

	// Open all accordion items and update attributes globally
	$(document).on("click", "#open-accordion a", function () {
		$(".wp-block-accordion-item").each(function () {
			$(this).addClass("is-open");
		});
		$(".wp-block-accordion-panel").each(function () {
			$(this).removeAttr("inert");
		});
		$(".wp-block-accordion-heading__toggle").each(function () {
			$(this).attr("aria-expanded", "true");
		});
	});
	// Close all accordion items and update attributes globally
	$(document).on("click", "#close-accordion a", function () {
		$(".wp-block-accordion-item").each(function () {
			$(this).removeClass("is-open");
		});
		$(".wp-block-accordion-panel").each(function () {
			$(this).attr("inert", "");
		});
		$(".wp-block-accordion-heading__toggle").each(function () {
			$(this).attr("aria-expanded", "false");
		});
	});

	// Improved: Clone and append matching images to .wcpa_radio labels (fixes for/label and duplicate issues)
	$(document).ready(function () {
		setTimeout(function () {
			var $thumbImages = $(".flex-control-nav.flex-control-thumbs img");
			// Build a map of filename => image jQuery object
			var imgMap = {};
			$thumbImages.each(function () {
				var imgSrc = $(this).attr("src");
				if (!imgSrc) return;
				var imgFilename = imgSrc.split("/").pop();
				imgMap[imgFilename] = $(this);
			});

			// For each radio input in .wcpa_radio
			$(".wcpa_radio input[type='radio']").each(function () {
				var $radio = $(this);
				var radioValue = $radio.val();
				// Only proceed if the value matches an image filename
				var $label = $radio.closest("label");
				if (!$label.length) {
					$label = $radio.parents(".wcpa_radio").find("label").first();
				}
				// Try to match by value (filename)
				var $img = imgMap[radioValue];
				// If not found, try to match by label text (after dash, trimmed, lowercased, spaces to dashes, remove special chars, add .png)
				if (!$img) {
					var labelText = $label.text() || "";
					var guess =
						labelText
							.split("-")
							.slice(1)
							.join("-")
							.trim()
							.toLowerCase()
							.replace(/[^a-z0-9]+/g, "-")
							.replace(/^-+|-+$/g, "") + ".png";
					$img = imgMap[guess];
				}
				if (
					$img &&
					$label.length &&
					$label.find(".wcpa-radio-img").length === 0
				) {
					var $imgClone = $img.clone().addClass("wcpa-radio-img").css({
						width: "60px",
						height: "auto",
						marginRight: "8px",
						verticalAlign: "middle",
					});
					$label.prepend($imgClone);
					console.log(
						"[WCPA IMG] Prepended image to label for value:",
						radioValue,
					);
				} else {
					console.log(
						"[WCPA IMG] No image match for radio value or label:",
						radioValue,
						$label.text(),
					);
				}
			});
			// Add event handler: when a radio is selected, trigger flexslider to go to the corresponding image
			$(".wcpa_radio input[type='radio']").on("change", function () {
				var $radio = $(this);
				var radioValue = $radio.val();
				var $thumbImages = $(".flex-control-nav.flex-control-thumbs img");
				var $thumbLis = $(".flex-control-nav.flex-control-thumbs li");
				var targetIndex = -1;
				$thumbImages.each(function (i) {
					var imgSrc = $(this).attr("src");
					if (!imgSrc) return;
					var imgFilename = imgSrc.split("/").pop();
					if (imgFilename === radioValue) {
						targetIndex = i;
						return false; // break
					}
				});
				// If not found by value, try by label guess (as in prepend logic)
				if (targetIndex === -1) {
					var $label = $radio.closest("label");
					if (!$label.length) {
						$label = $radio.parents(".wcpa_radio").find("label").first();
					}
					var labelText = $label.text() || "";
					var guess =
						labelText
							.split("-")
							.slice(1)
							.join("-")
							.trim()
							.toLowerCase()
							.replace(/[^a-z0-9]+/g, "-")
							.replace(/^-+|-+$/g, "") + ".png";
					$thumbImages.each(function (i) {
						var imgSrc = $(this).attr("src");
						if (!imgSrc) return;
						var imgFilename = imgSrc.split("/").pop();
						if (imgFilename === guess) {
							targetIndex = i;
							return false;
						}
					});
				}
				if (targetIndex !== -1) {
					var $thumbLi = $thumbLis.eq(targetIndex);
					if ($thumbLi.length) {
						$thumbLi.find("img").trigger("click");
					}
				}
			});
		}, 500);
	});
})();
