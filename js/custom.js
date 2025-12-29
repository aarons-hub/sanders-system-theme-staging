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

	// Add 'immediate-scroll' class to header on first scroll
	let immediateScrollAdded = false;
	window.addEventListener("scroll", function onFirstScroll() {
		if (!immediateScrollAdded) {
			var header = document.querySelector("header.wp-block-template-part");
			if (header) {
				header.classList.add("immediate-scroll");
			}
			immediateScrollAdded = true;
			window.removeEventListener("scroll", onFirstScroll);
		}
	});

	// Add/remove 'immediate-scroll' class to header on scroll (using GSAP ScrollTrigger if available)
	function setImmediateScrollClass(add) {
		var header = document.querySelector("header.wp-block-template-part");
		if (header) {
			if (add) {
				header.classList.add("immediate-scroll");
			} else {
				header.classList.remove("immediate-scroll");
			}
		}
	}

	if (typeof ScrollTrigger !== "undefined") {
		ScrollTrigger.create({
			trigger: "body",
			start: "top top",
			end: "bottom bottom",
			onUpdate: (self) => {
				if (self.scroll() > 0) {
					setImmediateScrollClass(true);
				} else {
					setImmediateScrollClass(false);
				}
			},
		});
	} else {
		window.addEventListener("scroll", function () {
			if (window.scrollY > 0) {
				setImmediateScrollClass(true);
			} else {
				setImmediateScrollClass(false);
			}
		});
	}

	// Toggle a class on header when scrolling past a certain point
	if (typeof ScrollTrigger !== "undefined") {
		ScrollTrigger.create({
			trigger: "header.wp-block-template-part",
			start: "top+=300 top",
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

	// Clone and append matching images to .wcpa_radio labels (fixes for/label and duplicate issues)
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
		}, 1000);
	});
	// Uppercase transformation for student-name input
	$(document).ready(function () {
		var $studentInput = $(
			".woocommerce .wcpa_field_wrap.student-name input[type='text']",
		);
		// Transform to uppercase as user types
		$studentInput.on("input", function () {
			var start = this.selectionStart,
				end = this.selectionEnd;
			this.value = this.value.toUpperCase();
			this.setSelectionRange(start, end);
		});
		// Also transform to uppercase on add-to-cart button click
		$(document).on("click", "button.single_add_to_cart_button", function () {
			$studentInput.each(function () {
				this.value = this.value.toUpperCase();
			});
		});
	});

	// Swatch injection for table.variations select elements
	$(document).ready(function () {
		setTimeout(function () {
			$("table.variations select").each(function () {
				var $select = $(this);
				$select.addClass("wpss-hidden");

				var attrName =
					$select.data("attribute_name") || $select.attr("name") || "";
				var $options = $select.find("option");

				var $swatchContainer = $('<div class="wpss-product-container"></div>');
				if (attrName) {
					$swatchContainer.attr("swatches-attr", attrName);
				}

				$options.each(function () {
					var $option = $(this);
					var value = $option.attr("value");
					var label = $option.text();
					if (!value) return;

					var isSelected = $option.is(":selected");
					var $swatch = $("<div></div>")
						.addClass("wpss-swatches-option wpss-label-option")
						.attr("data-slug", value)
						.attr("data-title", label);

					if (isSelected) {
						$swatch.addClass("wpss-selected-swatch");
					}

					$swatch.append(
						$('<div class="wpss-swatch-inner"></div>').text(label),
					);
					$swatchContainer.append($swatch);
				});

				$select.after($swatchContainer);
				$swatchContainer.on("click", ".wpss-swatches-option", function () {
					var $clicked = $(this);
					var value = $clicked.attr("data-slug");
					$swatchContainer
						.find(".wpss-swatches-option")
						.removeClass("wpss-selected-swatch");
					$clicked.addClass("wpss-selected-swatch");
					$select.val(value).trigger("change");
				});
			});
		}, 1000);
	});
})();
