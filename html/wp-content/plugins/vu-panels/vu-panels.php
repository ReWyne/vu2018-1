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

include 'vu-util.php';

global $vu_panels_vars;

abstract class vu_UserType {
  const Admins = 'Admins';
  const Professors = 'Faculty';
  const Students = 'Students';
//    const Parents = 'Parents';
}

class vu_LinkPostType {

  function __construct() {
      add_action( 'init', array($this, 'register_link_post_type'));
      add_action( 'add_meta_boxes', array($this,'add_link_custom_fields' )); //calls the function in this class
      add_action( 'save_post', array($this,'save_link_url'));
  }

  function register_link_post_type() {
    vu_log("register_link_post_type");
    register_post_type( 'link',
      array(
        'labels' => array(
          'name'               => _x( 'Links', 'link plural' ),
          'singular_name'      => _x( 'Link', 'link singular' ),
          //'add_new'            => __( 'Add New Link' ),
          'add_new_item'       => __( 'Add New Link' ),
          'edit_item'          => __( 'Edit Link' ),
          'new_item'           => __( 'New Link' ),
          'all_items'          => __( 'All Links' ),
          'view_item'          => __( 'View Link' ),
          'search_items'       => __( 'Search Links' ),
        ),
        'public' => true,
        'has_archive' => false,
        'show_ui' => true,
        'show_in_admin_bar' => true, //defaults to show_ui
        'menu_position' => 5,
        'register_meta_box_cb' => array($this,'add_link_custom_fields'),
        'supports' => array( 'title', 'editor', 'thumbnail' ),
        'taxonomies' => array('post_tag'),
        //'menu_icon' => 'dashicons-editor-unlink',
      )
    );
  }


  //Callback from register_post_type
  function add_link_custom_fields() {
    vu_log("add_link_custom_fields");
    add_meta_box( 'meta_id', 'link_url_value', array($this, 'links_url_custom_field_display'), 'link', 'normal', 'high' );
  }

  //Display the contents of the custom meta box
  function links_url_custom_field_display(){
    vu_log("links_url_custom_field_display");
      wp_nonce_field( 'link_save', 'link_url_nonce' );
      $value = get_post_meta(get_the_ID(), 'link_url_value', true);
      echo '<label for="link_url">';
      echo 'URL for external link :';
      echo '</label> ';
      echo '<input type="text" id="link_url_field" name="link_url_value" value="' . esc_url( $value ) . '" size="60" />';
  }

  //Save the meta value entered
  function save_link_url( $post_id ) {
    vu_log("save_link_url");
      // Check if nonce is set
      if ( ! isset( $_POST['link_url_nonce'] ) ) {
        return $post_id;
      }

      if ( ! wp_verify_nonce( $_POST['link_url_nonce'], 'link_save' ) ) {
        return $post_id;
      }

      // Check that the logged in user has permission to edit this post
      if ( ! current_user_can( 'edit_post' ) ) {
        return $post_id;
      }

      $link_url_value = sanitize_text_field( $_POST['link_url_value'] );

      update_post_meta( $post_id, 'link_url_value', $link_url_value );
  }

}

$link_post_type = new vu_LinkPostType();

// add links custom post type to the front page main loop via hooks

add_action( 'pre_get_posts', 'vu_generate_link_posts' );

function vu_generate_link_posts( $query ) {
  error_log('vu_generate_link_posts');
  vu_log('vu_generate_link_posts');
  if(function_exists('vu_log')){vu_log("vu_generate_link_posts");}
  else{$message = "ERROR: vu_log function DNE";
    echo "<script type='text/javascript'>alert('$message');</script>";}

    if( $query->is_main_query() && $query->is_home() ) {
        $query->set( 'post_type', array( 'post', 'link') );
    }
}


// add links custom post type to front page shortcode function

function vu_display_link_posts( $atts = null, $content = null, $tag = null ) {
vu_log('vu_display_link_posts');
  $out = '';

  $args = array( 
      'numberposts' => '6', 
      'post_status' => 'publish', 
      'post_type' => 'link' ,
  );

  $recent = wp_get_recent_posts( $args );

  if ( $recent ) {

      $out .= '<section class="overview">';

      $out .= '<h1>Recent Projects</h1>';

      $out .= '<div class="overview">';

      foreach ( $recent as $item ) {

          $out .= '<a href="' . get_permalink( $item['ID'] ) . '">';
          $out .= get_the_post_thumbnail( $item['ID'] ); 
          $out .= '</a>';
      }

      $out .= '</div></section>';
  }

  if ( $tag ) {
      return $out;
  } else {
      echo $out;
  }

}

add_shortcode( 'recentposts', 'vu_display_link_posts' );






// add_action( 'init', 'vu_create_link_posttype' );
// function vu_create_link_posttype() {

//     //Link type post -- Limited visibility item redirecting to an external page

//       // Set the labels, this variable is used in the $args array
//   $labels = array(
    // 'name'               => _x( 'Links', 'link plural' ),
    // 'singular_name'      => _x( 'Link', 'link singular' ),
    // //'add_new'            => __( 'Add New Link' ),
    // 'add_new_item'       => __( 'Add New Link' ),
    // 'edit_item'          => __( 'Edit Link' ),
    // 'new_item'           => __( 'New Link' ),
    // 'all_items'          => __( 'All Links' ),
    // 'view_item'          => __( 'View Link' ),
    // 'search_items'       => __( 'Search Links' ),
//     'featured_image'     => 'Icon',
//     'set_featured_image' => 'Add Icon',
//   );
 
//   // The arguments for our post type, to be entered as parameter 2 of register_post_type()
//   $args = array(
//     'labels'            => $labels,
//     'description'       => 'Holds our movies and movie specific data',
//     'public'            => true,
//     //'menu_position'     => 5,
//     'show_in_menu'      => 'post.php',
//     'supports'          => array( 'title', 'editor', 'thumbnail', 'custom-fields'), //#TODO custom field: tags array (if categories don't work fine)
//     'has_archive'       => false,                                                   //#TODO add 'url' custom field
//     'show_in_admin_bar' => false,
//     'show_in_nav_menus' => false,
//     'query_var'         => 'link',
//   );

//   register_post_type( 'link', $args);
// }
?>