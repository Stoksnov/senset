import "owl.carousel";
import { config } from "../config";
import { defaults } from "./defaults";

var sliders = {
	selector: ".js-slider",

	settings: {
		items: 1,
		nav: true,
		dots: false,
		loop: true,
		autoplay: false,
		smartSpeed: 600,
		margin: 20,
		// navText: [
		// 	'<a class="slider-arrows__right slider-arrows__item owl-next" href="#arrow-right" role="presentation"><span class="slider-arrows__inner"><svg class="icon icon-longArrowLeft" viewBox="0 0 65 16"><use xlink:href="/app/icons/sprite.svg#longArrowLeft"></use></svg></span> </a>',
		// 	'<a class="slider-arrows__left slider-arrows__item owl-prev" href="#arrow-left" role="presentation"><span class="slider-arrows__inner"><svg class="icon icon-longArrowLeft" viewBox="0 0 65 16"><use xlink:href="/app/icons/sprite.svg#longArrowLeft"></use></svg></span></a>',
		// ],
	},

	build: (selector) => {
		let data = $(selector).attr("data-settings")
			? $(selector).data("settings")
			: {};

		let clone = JSON.parse(JSON.stringify(sliders.settings));

		let current = Object.assign(clone, data);

		config.log("slider settings:", current);

		$(selector)
			.addClass("owl-carousel")
			.on("initialized.owl.carousel", (e) => {
				let $slider = $(e.target);
				let $logos = $slider.find(".js-logo:not([style])");

				if ($logos.length) {
					$logos.each((i, el) => {
						if ($(el).hasClass("is-changed")) return false;

						defaults.logoLoading(el);
					});
				}

				// counter
				let $counter = $(e.target).find(".owl-counter");
				let carousel = e.relatedTarget;
				let length = carousel.items().length;
				let current = carousel.relative(carousel.current()) + 1;

				if (!$counter.length)
					$(e.target).append(
						`<div class="owl-counter"><span>${current}</span> / ${length}</div>`
					);
			})

			.on("drag.owl.carousel", (event) => {
				document.ontouchmove = (e) => {
					e.preventDefault();
				};
			})
			.on("dragged.owl.carousel", (event) => {
				document.ontouchmove = (e) => {
					return true;
				};
			})
			.on("changed.owl.carousel", (e) => {
				if (!e.namespace) {
					return;
				}
				let carousel = e.relatedTarget;
				let length = carousel.items().length;
				let current = carousel.relative(carousel.current()) + 1;

				$(e.target)
					.find(".owl-counter")
					.html(`<span>${current}</span> / ${length}`);
			})
			.owlCarousel(current);
	},

	destroy: (selector) => {
		if ($(selector).hasClass("owl-loaded"))
			$(selector)
				.trigger("destroy.owl.carousel")
				.removeClass("owl-carousel");
		$(selector).find(".owl-counter").remove();
	},

	customArrow: (selector) => {
		$(selector).closest('section').find('.owl-next').on('click', () => {
			$(selector).trigger('next.owl.carousel');
		});
		$(selector).closest('section').find('.owl-prev').on('click', () => {
			$(selector).trigger('prev.owl.carousel');
		});
	},

	run: (selector) => {
		let windowWidth = $(window).width();

		if ($(selector).hasClass('magazine-card')) {
			windowWidth > 580 ? sliders.build(selector) : '';
		} else {
			sliders.build(selector);
		}
		if ($(selector).data('custom-arrow')) sliders.customArrow(selector);
	},

	init: () => {
		if (!$(sliders.selector).length) return false;

		$(window).on("load", (e) => {
			$(sliders.selector).each((i, el) => {
				sliders.run(el);
			});
		});
	},
};

export { sliders };
