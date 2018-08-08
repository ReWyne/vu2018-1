<?php
/**
 * Apostrophe functions and definitions
 *
 * @package Apostrophe
 */



/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
if ( ! function_exists( 'apostrophe_setup' ) ) :
	function apostrophe_setup() {
		global $content_width;

		/**
		 * Set the content width based on the theme's design and stylesheet.
		 */
		if ( ! isset( $content_width ) ) {
			$content_width = 723; /* pixels */
		}

		/*
		 * Make theme available for translation.
		 * Translations can be filed in the /languages/ directory.
		 */
		load_theme_textdomain( 'apostrophe', get_template_directory() . '/languages' );

		// Add default posts and comments RSS feed links to head.
		add_theme_support( 'automatic-feed-links' );

		// Load editor styles and custom fonts.
		add_editor_style( array( 'editor-style.css', apostrophe_fonts_url() ) );

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
		 * @link http://codex.wordpress.org/Function_Reference/add_theme_support#Post_Thumbnails
		 */
		add_theme_support( 'post-thumbnails' );
		set_post_thumbnail_size( 450, 450, true );
		add_image_size( 'apostrophe-featured', 930, 450, true );
		add_image_size( 'apostrophe-mini', 60, 60, true );
		add_image_size( 'apostrophe-gallery', 550, 550, true );

		// This theme two different nav menus: one for site navigation, and one for social media links.
		register_nav_menus( array(
			'primary' => __( 'Primary Menu', 'apostrophe' ),
			'social'  => __( 'Social Menu', 'apostrophe' ),
		) );

		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_theme_support( 'html5', array(
			'comment-list', 'comment-form', 'search-form', 'gallery', 'caption',
		) );

		/*
		 * Enable support for Post Formats.
		 * See http://codex.wordpress.org/Post_Formats
		 */
		add_theme_support( 'post-formats', array(
			'aside', 'image', 'video', 'quote', 'link', 'gallery', 'audio', 'chat', 'status',
		) );

		// Set up the WordPress core custom background feature.
		add_theme_support( 'custom-background', apply_filters( 'apostrophe_custom_background_args', array(
			'default-color' => 'ffffff',
			'default-image' => '',
		) ) );
	}
endif; // apostrophe_setup
add_action( 'after_setup_theme', 'apostrophe_setup' );

/**
 * Use a larger content width for full-width pages or if we have no sidebar.
 *
 */
if ( ! function_exists( 'apostrophe_content_width' ) ) :

	function apostrophe_content_width() {
			global $content_width;
		if ( is_page_template( 'full-width-page.php' ) OR
				 ( ! is_active_sidebar( 'sidebar-1' ) && ! is_active_sidebar( 'sidebar-2' ) && ! is_active_sidebar( 'sidebar-3' ) ) ) :
			$content_width = 1060; /* pixels */
			endif;
	}
endif; // apostrophe_content_width

add_action( 'template_redirect', 'apostrophe_content_width' );

/**
 * Register lots of widget areas.
 *
 * @link http://codex.wordpress.org/Function_Reference/register_sidebar
 */
function apostrophe_widgets_init() {
	register_sidebar( array(
		'name'          => __( 'Primary', 'apostrophe' ),
		'id'            => 'sidebar-1',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );

	register_sidebar( array(
		'name'          => __( 'Secondary', 'apostrophe' ),
		'id'            => 'sidebar-2',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );

	register_sidebar( array(
		'name'          => __( 'Tertiary', 'apostrophe' ),
		'id'            => 'sidebar-3',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );

	register_sidebar( array(
		'name'          => __( 'Footer Sidebar', 'apostrophe' ),
		'id'            => 'footer-sidebar',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );
}
add_action( 'widgets_init', 'apostrophe_widgets_init' );

/**
 * Load all Google fonts used in theme
 */
function apostrophe_fonts_url() {
	$fonts_url = '';

	/* Translators: If there are characters in your language that are not
	* supported by PT Serif, translate this to 'off'. Do not translate
	* into your own language.
	*/
	$pt_serif = _x( 'on', 'PT Serif font: on or off', 'apostrophe' );

	/* Translators: If there are characters in your language that are not
	* supported by Open Sans, translate this to 'off'. Do not translate
	* into your own language.
	*/
	$open_sans = _x( 'on', 'Open Sans font: on or off', 'apostrophe' );

	if ( 'off' !== $pt_serif || 'off' !== $open_sans ) :
		$font_families = array();

		if ( 'off' !== $pt_serif ) {
			$font_families[] = 'PT Serif:400,400italic,700,700italic';
		}

		if ( 'off' !== $open_sans ) {
			$font_families[] = 'Open Sans:300,300italic,400,400italic,600,600italic,700,700italic,800,800italic';
		}

		$query_args = array(
			'family' => urlencode( implode( '|', $font_families ) ),
			'subset' => urlencode( 'latin,latin-ext,cyrillic' ),
		);

		$fonts_url = add_query_arg( $query_args, 'https://fonts.googleapis.com/css' );

	endif;
	return $fonts_url;
}

/**
 * Enqueue scripts and styles.
 */
function apostrophe_scripts() {

	wp_enqueue_style( 'apostrophe-style', get_stylesheet_uri(), array(), '20140520' );
	wp_enqueue_style( 'genericons', get_template_directory_uri() . '/css/genericons.css', array(), '20131222' );
	wp_enqueue_style( 'apostrophe-fonts', apostrophe_fonts_url(), array(), null );

	wp_enqueue_script( 'apostrophe-skip-link-focus-fix', get_template_directory_uri() . '/js/skip-link-focus-fix.js', array(), '20150605', true );
	wp_enqueue_script( 'apostrophe-navigation', get_template_directory_uri() . '/js/navigation.js', array( 'jquery' ), '20161104', true );
	wp_enqueue_script( 'apostrophe', get_template_directory_uri() . '/js/apostrophe.js', array( 'jquery' ), '20150226', true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
	
	wp_localize_script( 'apostrophe-navigation', 'screenReaderText', array(
		'expand'   => esc_html__( 'expand child menu', 'apostrophe' ),
		'collapse' => esc_html__( 'collapse child menu', 'apostrophe' ),
	) );
}
add_action( 'wp_enqueue_scripts', 'apostrophe_scripts' );

/**
 * Use a specific size for the gallery shortcode.
 *
 * Unless a size is explicitly provided in the shortcode,
 * use a size registered with the theme if the number of columns
 * is more than a single one.
 */
function apostrophe_shortcode_atts_gallery( $out, $pairs, $atts ) {
	if ( empty( $atts['size'] ) && $out['columns'] >= 2 ) {
		$out['size'] = 'apostrophe-gallery';
	}
	return $out;
}
add_filter( 'shortcode_atts_gallery', 'apostrophe_shortcode_atts_gallery', 10, 3 );

/**
 * Add a body class to indicate when the user is viewing the site via the Customizer.
 * This allows us to hide the star-to-tag-as-featured-content buttons, since they're
 * largely irrelevant here, and may confuse users.
 */
function apostrophe_body_class( $classes ) {

	if ( ! is_active_sidebar( 'sidebar-1' ) && ! is_active_sidebar( 'sidebar-2' ) && ! is_active_sidebar( 'sidebar-3' ) ) {
		$classes[] = 'apostrophe-no-sidebar';
	}

	return $classes;
}
add_filter( 'body_class', 'apostrophe_body_class' );

/**
 * Add a class to all Featured Content posts.
 * This allows us to style them slightly differently in the CSS.
 */
function apostrophe_post_class( $classes, $class, $post_id ) {
	if ( apostrophe_is_featured( $post_id ) ) {
		$classes[] = 'apostrophe-featured';
	}
	return $classes;
}
add_filter( 'post_class', 'apostrophe_post_class', 10, 3 );

/**
 * Remove the "home" link from the default menu.
 */
function apostrophe_page_menu_args( $args ) {
	$args['show_home'] = true;
	return $args;
}
add_filter( 'wp_page_menu_args', 'apostrophe_page_menu_args' );

/**
 * Search post content for a link
 * This is used for "link" post formats, so we can automatically link from the archive page to the link itself.
 */
function apostrophe_get_url() {
	$post_link = get_the_permalink();
	if ( preg_match( '/<a (.+?)>/', get_the_content(), $match ) ) {
			$link = array();
		foreach ( wp_kses_hair( $match[1], array( 'http' ) ) as $attr ) {
				$link[ $attr['name'] ] = $attr['value'];
		}
			$post_link = $link['href'];
	}
	return $post_link;
}

/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Custom functions that act independently of the theme templates.
 */
require get_template_directory() . '/inc/extras.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Load Jetpack compatibility file.
 */
require get_template_directory() . '/inc/jetpack.php';

/**
 * Featured post stuff
 */
require get_template_directory() . '/inc/featured-posts.php';



/**
 * Load plugin enhancement file to display admin notices.
 */
require get_template_directory() . '/inc/plugin-enhancements.php';