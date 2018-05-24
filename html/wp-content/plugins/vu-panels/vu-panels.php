<?php
/**
 * Plugin Name: VU Panels
 * Plugin URI: 
 * Description: Adds the Panel post type, encompassing custom post types used by the VU portal.
 * Version: 1.0.0
 * Author: Anthony Nelson
 * Author URI: https://github.com/ReWyne/anthony-nelson
 * License: GPL2 or later
 */

define( 'TESTING', true );

global $vu_panels_vars;

add_action( 'wp_head', 'test_function' );
function test_function() {
  echo 'vu-panels active';
}