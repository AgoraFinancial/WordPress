var gulp = require('gulp');
var $    = require('gulp-load-plugins')();

var sassPaths = [
  'bower_components/normalize.scss/sass',
  'bower_components/foundation-sites/scss',
  'bower_components/motion-ui/src'
];

gulp.task('sass', function() {
  return gulp.src('scss/*.scss')
    .pipe($.sass({
      includePaths: sassPaths,
      outputStyle: 'compressed'
    })
    .on('error', $.sass.logError))
    .pipe($.autoprefixer({
      browsers: ['last 2 versions', 'ie >= 9']
    }))
    .pipe(gulp.dest('css'));
});

gulp.task('js', function() {
  return gulp.src([
      'js/lib/**/*.js'
    ])
    .pipe($.concat('app.js'))
    .pipe(gulp.dest('js'))
    .pipe($.rename('app.min.js'))
    .pipe($.uglify('app.min.js'))
    .pipe(gulp.dest('js'));
});

gulp.task('images', function() {
  return gulp.src('img/**/*')
    .pipe($.imagemin([
      $.imagemin.svgo({
        plugins: [
          { removeUselessDefs: false },
          { cleanupIDs: false }
        ]
      }),
      $.imagemin.gifsicle(),
      $.imagemin.jpegtran(),
      $.imagemin.optipng()
   ]))
    .pipe(gulp.dest('img'))
});

gulp.task('default', ['sass'], function() {
  gulp.watch(['scss/**/*.scss'], ['sass']);
  gulp.watch(['js/lib/**/*.js'], ['js']);
});