var config = {
	debug: true,

	animationEnd:
		"animationend webkitAnimationEnd oAnimationEnd MSAnimationEnd",

	transitionEnd: "transitionend webkitTransitionEnd oTransitionEnd",

	container: $("html, body"),

	getRandomInt: (min, max) => {
		return Math.floor(Math.random() * (max - min + 1)) + min;
	},

	getRandomArbitrary: (min, max) => {
		return Math.random() * (max - min) + min;
	},

	log: (...args) => {
		if (!config.debug) return false;

		// console.log(...args);
	},

	URLToArray: (url) => {
		var request = {};
		var pairs = url.substring(url.indexOf("?") + 1).split("&");
		for (var i = 0; i < pairs.length; i++) {
			if (!pairs[i]) continue;
			var pair = pairs[i].split("=");
			request[decodeURIComponent(pair[0])] = decodeURIComponent(pair[1]);
		}
		return request;
	},

	ArrayToURL: (array) => {
		var pairs = [];
		for (var key in array)
			if (array.hasOwnProperty(key))
				pairs.push(
					encodeURIComponent(key) +
						"=" +
						encodeURIComponent(array[key])
				);

		return pairs.join("&");
	},

	numberWithSpaces: (x) => {
		return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ");
	},

	delay: (fn, ms) => {
		let timer = 0;
		return function (...args) {
			clearTimeout(timer);
			timer = setTimeout(fn.bind(this, ...args), ms || 0);
		};
	},
};

export { config };
