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

defined( 'ABSPATH' ) or die( 'Error: Hacking is illegal and does far more harm than good. Why are you doing this?' );

define( 'TESTING', true );

global $vu_panels_vars;

abstract class UserType {
    const Admins = 'Admins';
    const Professors = 'Faculty';
    const Students = 'Students';
//    const Parents = 'Parents';
}


function vu_create_link_posttype() {

    //Link type post -- Limited visibility item redirecting to an external page

      // Set the labels, this variable is used in the $args array
  $labels = array(
    'name'               => _x( 'Links', 'link plural' ),
    'singular_name'      => _x( 'Link', 'link singular' ),
    //'add_new'            => __( 'Add New Link' ),
    'add_new_item'       => __( 'Add New Link' ),
    'edit_item'          => __( 'Edit Link' ),
    'new_item'           => __( 'New Link' ),
    'all_items'          => __( 'All Links' ),
    'view_item'          => __( 'View Link' ),
    'search_items'       => __( 'Search Links' ),
    'featured_image'     => 'Icon',
    'set_featured_image' => 'Add Icon',
  );
 
  // The arguments for our post type, to be entered as parameter 2 of register_post_type()
  $args = array(
    'labels'            => $labels,
    'description'       => 'Holds our movies and movie specific data',
    'public'            => true,
    //'menu_position'     => 5,
    'show_in_menu'      => 'post.php',
    'supports'          => array( 'title', 'editor', 'thumbnail', 'custom-fields'), //#TODO custom field: tags array (if categories don't work fine)
    'has_archive'       => false,                                                   //#TODO add 'url' custom field
    'show_in_admin_bar' => false,
    'show_in_nav_menus' => false,
    'query_var'         => 'link',
  );

  register_post_type( 'link', $args);
}

// Hooking up our function to theme setup
add_action( 'init', 'vu_create_link_posttype' );



//#temp
add_action( 'wp_head', 'test_function' );
function vu_test_function() {
  echo 'vu-panels active';
}

?>