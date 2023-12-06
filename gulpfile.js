// Load all the modules
var gulp = require( 'gulp' ),
  plumber = require( 'gulp-plumber' ),
  autoprefixer = require('gulp-autoprefixer'),
  watch = require( 'gulp-watch' ),
  jshint = require( 'gulp-jshint' ),
  stylish = require( 'jshint-stylish' ),
  uglify = require( 'gulp-uglify' ),
  rename = require( 'gulp-rename' ),
  include = require( 'gulp-include' ),
  sass = require( 'gulp-sass' )(require('sass'));

// Default error handler
var onError = function( err ) {
  console.log( 'An error occured:', err.message );
  this.emit('end');
}

// var sass = require('gulp-sass')(require('sass'));
// Concatenates all files that it finds in scripts.js nd creates one minified version.  It is dependent on the jshint task to succeed.
gulp.task( 'scripts', async () => {
  return gulp.src( './src/js/article-voting-script.js' )
    .pipe( include() )
    .pipe( jshint() )
    .pipe( jshint.reporter( stylish ) )
    .pipe( uglify() )
    .pipe( rename( { suffix: '.min' } ) )
    .pipe( gulp.dest( './assets/js' ) );
});

//sass options
var options = {};
options.sass = {
  errLogToConsole: true,
  noCache: true,
  outputStyle: 'compressed'
};

// Sass-min - Release build minifies CSS after compiling Sass
const cssSourcePath = [
  './src/sass/article-voting-style.scss'
];

// Admin sass
const adminCssSourcePath = [
  './src/sass/article-voting-admin-style.scss'
];

gulp.task('sass', async () => {
  return gulp.src(cssSourcePath)
    .pipe(plumber())
    .pipe(sass(options.sass))
    .pipe(autoprefixer('last 2 versions'))
    .pipe( rename( { suffix: '.min' } ) )
    .pipe(gulp.dest('./assets/css/'));
});

gulp.task('admin-sass', async () => {
  return gulp.src(adminCssSourcePath)
    .pipe(plumber())
    .pipe(sass(options.sass))
    .pipe(autoprefixer('last 2 versions'))
    .pipe( rename( { suffix: '.min' } ) )
    .pipe(gulp.dest('./assets/css/'));
});

// Start the livereload server and watch files for changes
gulp.task( 'watch', async () => {

  // watch app files
  gulp.watch( [ './src/js/**/*.js', '!./assets/js/*.js' ], gulp.series( 'scripts' ) )
  gulp.watch( './src/sass/**/*.scss', gulp.series( 'sass' ) );
  gulp.watch( './src/sass/**/*.scss', gulp.series( 'admin-sass' ) );

});

gulp.task('default', gulp.series('scripts', 'sass', 'admin-sass', 'watch'));
