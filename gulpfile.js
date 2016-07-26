var gulp = require('gulp'),
    watch = require('gulp-watch'),
    phpunit = require('gulp-phpunit');


gulp.task('test', function() {
  gulp.src('').pipe(phpunit());
});

gulp.task('watch', function() {
    gulp.watch(['**/*.php'], ['test']);
});
