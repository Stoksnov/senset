var accordion = {
	selector: ".js-accordion",
	body: ".js-accordion-body",
	activeClass: "is-active",

	run: (el) => {
		$(accordion.selector)
			.not(el)
			.removeClass(accordion.activeClass)
			.find(accordion.body)
			.slideUp(400);

		$(el).toggleClass(accordion.activeClass);

		$(el).find(accordion.body).slideToggle(400);
	},

	init: () => {
		$(accordion.selector).on("click", (e) =>
			accordion.run(e.currentTarget)
		);
	},
};

export { accordion };
