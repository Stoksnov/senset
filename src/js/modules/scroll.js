import LocomotiveScroll from "locomotive-scroll";

var scroll = {
	run: () => {
		setTimeout(() => {
			const scroll = new LocomotiveScroll({
				el: document.querySelector("[data-scroll-container]"),
				smooth: true,
			});
		}, 100);
	},
	init: () => {
		$(window).on("load", scroll.run);
	},
};

export { scroll };
