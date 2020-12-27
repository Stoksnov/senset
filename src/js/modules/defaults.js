import { config } from "../config";

var defaults = {
	toggleMenu: (e) => {
		$("html, body").toggleClass("js-lock");
		$('.mobile-menu').toggleClass('open');
	},


	init: () => {
		// $(".ham").on("click", defaults.toggleMenu);

		


	},
};

export { defaults };
