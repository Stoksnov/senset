// import Inputmask from "inputmask";
import validate from "jquery-validation";
import { config } from "../config";
import { modals } from "./modals";
import "jquery-mask-plugin";

var forms = {
	mask: () => {
		// var selector = document.querySelectorAll("input[name='phone']");
		// var im = new Inputmask({
		// 	mask: "+7 (999) 999-99-99",
		// 	clearMaskOnLostFocus: true,
		// 	clearIncomplete: true,
		// });
		// im.mask(selector);
		$("input[name='phone']")
			.mask("+7 000 000 00 00")
			.keyup(function (e) {
				var this_val = $(this).val();
				var val_length = this_val.length;

				if (val_length == 4 && this_val[3] === "8") {
					$(this).val(this_val.slice(0, 2));
				}
			});
	},

	num: (event) => {
		$(event.currentTarget).val(
			$(event.currentTarget)
				.val()
				.replace(/[^\d].+/, "")
		);

		if (event.which < 48 || event.which > 57) {
			event.preventDefault();
		}
	},

	focusUpdate: (set = false, field) => {
		let input = $(field);

		let val = Number(
			input
				.val()
				.replace(/\s/g, "")
				.match(/[+-]?([0-9]*[.])?[0-9]+/g)
		);

		if (set) {
			if (val == 0) input.val("").trigger("change");
			else input.val(config.numberWithSpaces(val)).trigger("change");
		} else {
			if (val == 0) input.val("").trigger("change");
			else input.val(val).trigger("change");
		}
	},

	validate: () => {
		$("form").each((i, el) => {
			var $form = $(el);

			$form.validate({
				errorPlacement: function (error, element) {
					//just nothing, empty
				},
				highlight: (element, errorClass, validClass) => {
					$(element)
						.parent()
						.addClass("is-error")
						.removeClass("is-valid");

					var options = $(element).closest(".flex");
					if ($(element).is(":radio") || $(element).is(":checkbox")) {
						options.addClass("is-error").removeClass("is-valid");
					} else {
						$(element).addClass("is-error").removeClass("is-valid");
					}
				},
				unhighlight: (element, errorClass, validClass) => {
					$(element)
						.parent()
						.removeClass("is-error")
						.addClass("is-valid");

					var options = $(element).closest(".flex");
					if ($(element).is(":radio") || $(element).is(":checkbox")) {
						options.removeClass("is-error").addClass("is-valid");
					} else {
						$(element).removeClass("is-error").addClass("is-valid");
					}
				},
				submitHandler: (form) => {
					var data = $(form).serialize();

					$.ajax({
						type: "POST",
						url: $(form).attr("action"),
						data: data,
						success: function (data) {
							$(form)[0].reset();
						},
					});

					let $target = $(form).find('input[name="target"]');
					let targetName = $target.val();

					if ($target.length) {
						yaCounter64705324.reachGoal(targetName);
						// ym(64705324, "reachGoal", targetName);
					}

					if ($(form).hasClass("js-calculator-form")) {
						$(form).addClass("is-done");
					} else {
						modals.open(false, "#thanks");
					}
				},
				// ignore: ".is-deactive",
				ignore: "input.is-deactive",
				debug: false,
				rules: {
					phone: {
						required: true,
						minlength: 10,
					},
				},
			});
		});
	},

	events: () => {
		$(".input__field")
			.on("focus", (e) => {
				let $input = $(e.target);
				$input.parent().addClass("is-focus");
			})
			.on("blur change", (e) => {
				let $input = $(e.target);

				if ($input.val() == "") $input.parent().removeClass("is-focus");
			});
	},

	init: () => {
		forms.mask();
		forms.validate();
		forms.events();

		// $(".js-num").on("keypress keyup", forms.num);

		$(".js-num").on({
			"keypress keyup": forms.num,
			focus: (e) => forms.focusUpdate(0, e.currentTarget),
			focusout: (e) => forms.focusUpdate(1, e.currentTarget),
		});
	},
};

export { forms };
