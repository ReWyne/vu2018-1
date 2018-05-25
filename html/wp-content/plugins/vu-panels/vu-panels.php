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

abstract class UserType {
    const Admins = 'Admins';
    const Professors = 'Faculty';
    const Students = 'Students';
//    const Parents = 'Parents';
}

//Collections type post -- list of links

// function create_posttype() {
//     register_post_type( 'collections',
//     // CPT Options
//         array(
//             'labels' => array(
//                 'name' => __( 'Collections' ),
//                 'singular_name' => __( 'Collection' )
//             ),
//         'public' => validate_user(/*$user*/true, UserType::Professors), //#TODO
//             'has_archive' => true,
//             'rewrite' => array('slug' => 'movies'),
//         )
//     );
// }

// Hooking up our function to theme setup
add_action( 'init', 'create_posttype' );

//#temp
add_action( 'wp_head', 'test_function' );
function test_function() {
  echo 'vu-panels active';
}

?>