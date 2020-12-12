import "magnific-popup";
import { defaults } from "./defaults";
import { config } from "../config";

var modals = {
	close: (e) => {
		if (!e) return false;

		e.preventDefault();

		config.log("close modal");

		$.magnificPopup.close();
	},

	open: (e, modal) => {
		e = e || false;

		if (e) e.preventDefault();

		defaults.closeMenu();

		// modals.close();

		// $.magnificPopup.close();

		modal =
			modal ||
			(e != false
				? $(e.currentTarget).attr("href")
					? $(e.currentTarget).attr("href")
					: $(e.currentTarget).data("modal")
				: e);

		if (!modal) return false;

		let open = $.magnificPopup.instance.isOpen;

		if (open) {
			var mfp = $.magnificPopup.instance;

			mfp.items = [];

			// modify the items array (push/remove/edit)
			mfp.items.push({
				src: modal,
				type: "inline",
			});

			config.log("updateItemHTML");

			// call update method to refresh counters (if required)
			mfp.updateItemHTML();
		} else {
			if (e && $(e.currentTarget).attr("data-youtube")) {
				$(modal + " iframe").attr(
					"src",
					"https://www.youtube.com/embed/" +
						$(e.currentTarget).data("youtube") +
						"?autoplay=1&showinfo=0&rel=0&controls=0"
				);
			}

			if (e && $(e.currentTarget).attr("data-input")) {
				$(modal + ' input[name="form"]').val(
					$(e.currentTarget).data("input")
				);
			}

			$.magnificPopup.open(
				{
					tClose: "Закрыть",
					removalDelay: 600,
					fixedContentPos: true,
					fixedBgPos: true,
					overflowY: "scroll",
					closeMarkup:
						'<div class="modals__close close js-close-modal"><svg class="icon icon-close close2" viewBox="0 0 43 43"><use xlink:href="/wp-content/themes/karanikola-veritas/app/icons/sprite.svg#close"></use></svg></div>',
					mainClass: "css-modal-animate",
					items: {
						src: modal,
						type: "inline",
					},
					callbacks: {
						beforeOpen: () => {},

						beforeClose: () => {},
					},
				},
				0
			);
		}
	},

	init: (e) => {
		$(document).on("click", ".js-close-modal", modals.close);

		$(document).on("click", ".js-modal", modals.open);
	},
};

export { modals };
