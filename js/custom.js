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
})();
