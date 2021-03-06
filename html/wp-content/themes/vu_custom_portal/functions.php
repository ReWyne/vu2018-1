<?php
/**
 * vu_custom_portal functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package vu_custom_portal
 */

if ( ! function_exists( 'vu_custom_portal_setup' ) ) :
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support for post thumbnails.
	 */
	function vu_custom_portal_setup() {
		/*
		 * Make theme available for translation.
		 * Translations can be filed in the /languages/ directory.
		 * If you're building a theme based on vu_custom_portal, use a find and replace
		 * to change 'vu_custom_portal' to the name of your theme in all the template files.
		 */
		load_theme_textdomain( 'vu_custom_portal', get_template_directory() . '/languages' );

		// Add default posts and comments RSS feed links to head.
		add_theme_support( 'automatic-feed-links' );

		/*
		 * Let WordPress manage the document title.
		 * By adding theme support, we declare that this theme does not use a
		 * hard-coded <title> tag in the document head, and expect WordPress to
		 * provide it for us.
		 */
		add_theme_support( 'title-tag' );

		/*
		 * Enable support for Post Thumbnails on posts and pages.
		 *
		 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		 */
		add_theme_support( 'post-thumbnails' );

		// This theme uses wp_nav_menu() in one location.
		register_nav_menus( array(
			'menu-1' => esc_html__( 'Primary', 'vu_custom_portal' ),
		) );

		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_theme_support( 'html5', array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
		) );

		// Set up the WordPress core custom background feature.
		add_theme_support( 'custom-background', apply_filters( 'vu_custom_portal_custom_background_args', array(
			'default-color' => 'ffffff',
			'default-image' => '',
		) ) );

		// Add theme support for selective refresh for widgets.
		add_theme_support( 'customize-selective-refresh-widgets' );

		/**
		 * Add support for core custom logo.
		 *
		 * @link https://codex.wordpress.org/Theme_Logo
		 */
		add_theme_support( 'custom-logo', array(
			'height'      => 250,
			'width'       => 250,
			'flex-width'  => true,
			'flex-height' => true,
		) );
	}
endif;
add_action( 'after_setup_theme', 'vu_custom_portal_setup' );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function vu_custom_portal_content_width() {
	// This variable is intended to be overruled from themes.
	// Open WPCS issue: {@link https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards/issues/1043}.
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	$GLOBALS['content_width'] = apply_filters( 'vu_custom_portal_content_width', 640 );
}
add_action( 'after_setup_theme', 'vu_custom_portal_content_width', 0 );

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function vu_custom_portal_widgets_init() {
	register_sidebar( array(
		'name'          => esc_html__( 'Sidebar', 'vu_custom_portal' ),
		'id'            => 'sidebar-1',
		'description'   => esc_html__( 'Add widgets here.', 'vu_custom_portal' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );
}
add_action( 'widgets_init', 'vu_custom_portal_widgets_init' );

/**
 * Enqueue scripts and styles.
 */
function vu_custom_portal_scripts() {

	//boostrap style
	//wp_enqueue_style( 'bootstrap_css', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css' );
		
	wp_enqueue_style( 'vu_custom_portal-style', get_stylesheet_uri() );

	//enqueue bootstrap scripts
	global $wp_scripts;
	//wp_enqueue_script( 'bootstrap_js', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js');

	wp_enqueue_script( 'vu_custom_portal-navigation', get_template_directory_uri() . '/js/navigation.js', array(), '20151215', true );
	wp_enqueue_script( 'vu_custom_portal-skip-link-focus-fix', get_template_directory_uri() . '/js/skip-link-focus-fix.js', array(), '20151215', true );

	wp_enqueue_script( 'vu_theme_js', get_template_directory_uri() . '/js/scripts.js');

	wp_enqueue_script( 'vu_plugin_js', dirname( get_template_directory_uri(), 2 )."/plugins/vu-panels/js/vu-scripts.js");

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'vu_custom_portal_scripts' );

 /**
  * Initialize the Better Font Awesome Library.
  *
  * (see usage notes below on proper hook priority)
  */
  add_action( 'init', 'vu_load_bfa' );
  function vu_load_bfa() {
 
	  // Include the main library file. Make sure to modify the path to match your directory structure.
	  require_once ( dirname( __FILE__ ) . '/better-font-awesome-library/better-font-awesome-library.php' );
 
	  // Set the library initialization args (defaults shown).
	  $args = array(
			  'version'             => 'latest',
			  'minified'            => true,
			  'remove_existing_fa'  => false,
			  'load_styles'         => true,
			  'load_admin_styles'   => true,
			  'load_shortcode'      => true,
			  'load_tinymce_plugin' => true,
	  );
 
	  // Initialize the Better Font Awesome Library.
	  Better_Font_Awesome_Library::get_instance( $args );
  }

	//display tweaks
  function vu_create_grid($classes) {
	$classes[] = 'grid';
    return $classes;
}
add_filter('body_class', 'vu_create_grid');


/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/template-functions.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Load Jetpack compatibility file.
 */
if ( defined( 'JETPACK__VERSION' ) ) {
	require get_template_directory() . '/inc/jetpack.php';
}

