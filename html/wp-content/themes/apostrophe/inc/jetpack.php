<?php
/**
 * Jetpack Compatibility File
 * See: http://jetpack.me/
 *
 * @package Apostrophe
 */

function apostrophe_jetpack_setup() {
	// Add theme support for Infinite Scroll.
	add_theme_support( 'infinite-scroll', array(
		'container'      => 'main',
		'footer'         => 'colophon',
		'footer_widgets' => 'footer-sidebar',
		'wrapper'        => false,
	) );

	// Add theme support for Responsive Videos.
	add_theme_support( 'jetpack-responsive-videos' );

	// Add theme support for Featured Content.
	add_theme_support( 'featured-content', array(
		'filter'    => 'apostrophe_get_featured_posts',
		'max_posts' => 2,
	) );

	// Add theme support for Site Logo.
	add_image_size( 'apostrophe-logo', 9999, 300 ); // Resize based on height!
	add_theme_support( 'site-logo', array( 'size' => 'apostrophe-logo' ) );

	// Add theme support for Content Options.
	add_theme_support( 'jetpack-content-options', array(
		'post-details'    => array(
			'stylesheet' => 'apostrophe-style',
			'date'       => '.entry-header .entry-meta, .entry-date',
			'categories' => '.post-categories',
			'tags'       => '.post-tags',
		),
		'featured-images' => array(
			'archive'    => true,
		),
	) );

}
add_action( 'after_setup_theme', 'apostrophe_jetpack_setup' );
