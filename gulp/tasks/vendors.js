// =========================================================
// Gulp Task: vendors
// =========================================================

let path = require("../settings/path.json"),
	merge = require("merge-stream");

module.exports = (gulp, plugins) => {
	return () => {
		var fonts = gulp
			.src(path.src.fonts)
			.pipe(plugins.newer(path.build.fonts))
			.pipe(gulp.dest(path.build.fonts));

		var attach = gulp
			.src(path.src.attach)
			.pipe(plugins.newer(path.build.attach))
			.pipe(gulp.dest(path.build.attach));

		var json = gulp
			.src(path.src.json)
			.pipe(plugins.newer(path.build.json))
			.pipe(gulp.dest(path.build.json));

		return merge(fonts, attach, json);
	};
};
