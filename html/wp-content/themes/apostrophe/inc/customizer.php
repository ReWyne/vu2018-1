<?php
/**
 * Apostrophe Theme Customizer
 *
 * @package Apostrophe
 */

/**
 * Add postMessage support for site title and description for the Theme Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function apostrophe_customize_register( $wp_customize ) {
	$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
	$wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';
	$wp_customize->get_setting( 'background_image' )->transport = 'postMessage';
	$wp_customize->get_setting( 'background_color' )->transport = 'postMessage';

	/* Theme Options */
	$wp_customize->add_section( 'apostrophe_theme_options', array(
		'title'    => __( 'Theme', 'apostrophe' ),
		'priority' => 130,
	) );

	/* Option to show or hide author panel */
	$wp_customize->add_setting( 'apostrophe_hide_author', array(
		'sanitize_callback' => 'apostrophe_sanitize_checkbox',
	) );

	$wp_customize->add_control( 'apostrophe_hide_author', array(
		'label'             => __( 'Hide author bio panel on individual post pages.', 'apostrophe' ),
		'section'           => 'apostrophe_theme_options',
		'priority'          => 20,
		'type'              => 'checkbox',
	) );

	/* Option to enable feature-starring functionality */
	$wp_customize->add_setting( 'apostrophe_enable_feature_starring', array(
		'sanitize_callback' => 'apostrophe_sanitize_checkbox',
	) );

	$wp_customize->add_control( 'apostrophe_enable_feature_starring', array(
		'label'             => __( 'Enable front end feature-starring for easier tagging of Featured Content.', 'apostrophe' ),
		'section'           => 'apostrophe_theme_options',
		'priority'          => 20,
		'type'              => 'checkbox',
	) );
}
add_action( 'customize_register', 'apostrophe_customize_register' );

/**
 * Sanitize the checkbox.
 *
 * @param boolean $input.
 * @return boolean (true|false).
 */
function apostrophe_sanitize_checkbox( $input ) {
	if ( 1 == $input ) {
		return true;
	} else {
		return false;
	}
}
add_action( 'customize_register', 'apostrophe_customize_register' );

/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 */
function apostrophe_customize_preview_js() {
	wp_enqueue_script( 'apostrophe_customizer', get_template_directory_uri() . '/js/customizer.js', array( 'customize-preview' ), '20130508', true );
}
add_action( 'customize_preview_init', 'apostrophe_customize_preview_js' );
