import { validator } from "./modules/validator";
import { validation } from "./modules/validation";
import { defaults } from "./modules/defaults";
// import { aosAnimations } from "./modules/aos";
// import { background } from "./modules/background";
// import { forms } from "./modules/forms";
// import { modals } from "./modules/modals";
// import { accordion } from "./modules/accordion";
// import { tabs } from "./modules/tabs";
// import { sliders } from "./modules/sliders";
import { config } from "./config";
// import "lazysizes";

var App = () => {};

App.prototype.init = () => {
	validator.init();
	validation.init();
	defaults.init();
	// aosAnimations.init();
	// background.init();
	// sliders.init();
	// forms.init();
	// tabs.init();
	// modals.init();
	// accordion.init();
	// config.log("app init");
};

export { App };
