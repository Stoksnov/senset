import AOS from "aos";
import { config } from "../config";
var aosAnimations = {
	run: () => {
		// let $body = $("body");

		// config.log("aosAnimations run start");

		// if ($body.attr("data-aos-easing")) return false;

		// config.log("aosAnimations run init");

		AOS.init({
			duration: 600,
			offset: 600,
			once: true,
			disable: () => {
				let maxWidth = 1025;
				return window.innerWidth < maxWidth;
			},
		});
	},

	resize: () => {
		AOS.refresh();
	},

	init: () => {
		if (window.innerWidth < 1025) aosAnimations.run();

		$(window).on("load", () => {
			if (window.innerWidth > 1025) setTimeout(aosAnimations.run, 200);
		});

		// $(window).on("load", () => setTimeout(aosAnimations.run, 200));
		// $(window).on("resize", aosAnimations.resize);
	},
};

export { aosAnimations };
