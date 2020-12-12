import { config } from "../config";

var background = {
	selector: ".js-gallery-background",

	step: (el) => {
		config.log("step background");
		$(el).on(config.animationEnd, (e) => {
			config.log("done background");

			let index = $(el).index();

			let count = $(background.selector).length;

			let next = count - (index + 1) > 0 ? index + 1 : 0;

			$(background.selector).eq(next).addClass("is-active is-next");

			$(el)
				.addClass("is-done")
				.on(config.transitionEnd, (e) => {
					if (e.originalEvent.propertyName == "opacity") {
						$(background.selector)
							.eq(next)
							.addClass("is-visible")
							.removeClass("is-next");

						$(el)
							.off(config.transitionEnd)
							.removeClass(
								"is-active is-next is-done is-visible"
							);
					}
				});
		});
	},

	init: () => {
		if (!$(background.selector).length) return false;

		$(background.selector).each((i, el) => background.step(el));
	},
};

export { background };
