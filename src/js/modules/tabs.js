import { config } from "../config";

var tabs = {
	selector: ".js-tabs",
	nav: ".js-tabs-nav > *",
	body: ".js-tabs-body > *",
	activeClass: "is-active",
	initClass: "is-init",

	run: (el) => {
		$(el).addClass(tabs.initClass);
		$(el).find(tabs.body).first().addClass(tabs.activeClass);
		$(el).find(tabs.nav).first().addClass(tabs.activeClass);
	},

	change: (e) => {
		e.preventDefault();

		let $this = $(e.currentTarget);

		let $parent = $this.closest(tabs.selector);

		let ind = $this.index();

		config.log("nav on click", ind);

		$parent
			.find(tabs.nav)
			.removeClass(tabs.activeClass)
			.eq(ind)
			.addClass(tabs.activeClass);

		$parent
			.find(tabs.body)
			.removeClass(tabs.activeClass)
			.eq(ind)
			.addClass(tabs.activeClass);

		if ($(e.currentTarget).hasClass("calculator__tabs-label")) {
			let calculator = $parent.find(tabs.body).eq(ind)[0];
			calc.reset(calculator);
		}

		// calc.bg($parent.find(tabs.body).eq(ind).find(calc.item)[0]);
	},

	init: () => {
		if (!$(tabs.selector).length) return false;

		$(window).on("load", (e) => {
			tabs.run(tabs.selector);

			$(tabs.selector).find(tabs.nav).on("click", tabs.change);
		});
	},
};

export { tabs };
