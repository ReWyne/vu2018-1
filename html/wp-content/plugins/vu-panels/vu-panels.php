<?php
/**
 * Plugin Name: VU Panels
 * Plugin URI: 
 * Description: Adds the Link post type, and a variety of backend panels helpful for managing posts by user group in the VU portal.
 * Version: 1.0.0
 * Author: Anthony Nelson
 * Author URI: https://github.com/ReWyne/anthony-nelson
 * License: GPL2 or later
 */

defined( 'ABSPATH' ) or die(); //canonical way to exit if accessed directly

//Misc convenience definitions
//WP_DEBUG* should already be defined in wp-settings.php. Code here should override those settings, however.
// define( 'WP_DEBUG', true );
// define( 'WP_DEBUG_LOG', true );
// define( 'WP_DEBUG_DISPLAY', true );
define( 'IS_WP_DEBUG', defined('WP_DEBUG') && true === WP_DEBUG );
define( 'IS_DOING_AJAX', defined( 'DOING_AJAX' ) && true === DOING_AJAX );
define( 'IS_DOING_AUTOSAVE', defined( 'DOING_AUTOSAVE' ) && true === DOING_AUTOSAVE );

// Misc Constants
define("VU_USER_GROUP","vu_user_group"); //taxonomy
define("VU_USER_PRIMARY_UG","vu_user_primary_ug"); //user metadata

// Debugging convenience globals and accessors
global $vu_panels_vars;
$vu_panels_vars['RESTRICT_DEBUG_LEVEL'] = 3;

//Setting the global to higher numbers should print less. Ohalso, floats are fine.
//roughly speaking, 0 == "print everything", 1 == "print reasonably important functions", 2 == "important", 3 == "VERY important", 4 == "print programmer's summaries" 5 == "layman's summaries", 
function VU_RESTRICT_DEBUG_LEVEL($level){ global $vu_panels_vars; if(IS_WP_DEBUG && $vu_panels_vars['RESTRICT_DEBUG_LEVEL'] <= $level) return true; else return false; }
include_once dirname( __FILE__ ) . '/vu-util.php';
include_once dirname( __FILE__ ) . '/vu-db.php';
include_once dirname( __FILE__ ) . '/vu-permissions.php'; 
//is there a better way to handle this besides putting all the little php files in their own folder and adding them all with a for loop? (with a text file or summit to ensure order) I guess I could class them and include_once, but that seems a little...
include_once dirname( __FILE__ ) . '/vu-change-groups-for-user.php';
include_once dirname( __FILE__ ) . '/vu-alter-user-group.php';
include_once dirname( __FILE__ ) . '/vu-change-groups-for-post.php';
// Link post type
// Just like regular posts except they also have the link_url_value metavalue
class vu_link_post_type {

  function __construct() {
    add_action( 'init', array($this, 'register_link_post_type')); //TODO: find way to make this run once and still work? register_activation_hook method didn't work
    add_action( 'add_meta_boxes_link', array($this,'add_link_custom_fields' )); //calls the function in this class
    add_action( 'save_post', array($this,'save_link_url'));
  }

  /*static*/ function register_link_post_type() {
    if(VU_RESTRICT_DEBUG_LEVEL(0)){vu_dbg("register_link_post_type");}

    register_post_type( 'link',
      array(
        'labels' => array(
          'name'               => _x( 'Links', 'link plural', 'vu-panels' ),
          'singular_name'      => _x( 'Link', 'link singular' ),
          //'add_new'            => __( 'Add New Link', 'vu-panels' ),
          'add_new_item'       => __( 'Add New Link', 'vu-panels' ),
          'edit_item'          => __( 'Edit Link', 'vu-panels' ),
          'new_item'           => __( 'New Link', 'vu-panels' ),
          'all_items'          => __( 'All Links', 'vu-panels' ),
          'view_item'          => __( 'View Link', 'vu-panels' ),
          'search_items'       => __( 'Search Links', 'vu-panels' ),
        ),
        'public' => true,
        'has_archive' => false,
        'show_ui' => true,
        'show_in_admin_bar' => true, //defaults to show_ui
        'menu_position' => 5,
        'register_meta_box_cb' => array($this,'add_link_custom_fields'), //would use $this if func weren't static
        'supports' => array( 'title', 'editor', 'thumbnail' ),
        'taxonomies' => array('post_tag', 'category'),
        //'menu_icon' => 'dashicons-editor-unlink',
      )
    );
  }

  //Callback from register_post_type
  function add_link_custom_fields() {
    if(VU_RESTRICT_DEBUG_LEVEL(0)){vu_dbg("add_link_custom_fields");}

    vu_log("add_link_custom_fields");
    add_meta_box( 'link_meta_id', __('Link URL'), array($this, 'links_url_custom_field_display'), 'link', 'normal', 'high' );
  }

  //Display the contents of the custom meta box
  function links_url_custom_field_display(){
    if(VU_RESTRICT_DEBUG_LEVEL(0)){vu_dbg("links_url_custom_field_display");}

    wp_nonce_field( 'link_save', 'link_url_nonce' );
    $value = get_post_meta(get_the_ID(), 'link_url_value', true);
    echo '<label for="link_url">';
    echo 'URL for external link : ';
    echo '</label> ';
    echo '<input type="text" id="link_url_field" name="link_url_value" value="' . esc_url( $value ) . '" size="60" />';
  }

  //Save the meta value entered
  function save_link_url( $post_id ) {
    if(VU_RESTRICT_DEBUG_LEVEL(2)){vu_dbg("save_link_url \$post_id ", $post_id);}

    //only save meta value if hitting submit
    if ( IS_DOING_AUTOSAVE ){
      return $post_id;  
    }

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

//register_activation_hook( __FILE__, array('vu_link_post_type', 'register_link_post_type') );
$link_post_type = new vu_link_post_type();

// add links custom post type to the front page main loop via hooks
add_action( 'pre_get_posts', 'vu_generate_link_posts' );
function vu_generate_link_posts( $query ) {
  if(VU_RESTRICT_DEBUG_LEVEL(1)){vu_dbg("vu_generate_link_posts \$query", $query);}

    if( $query->is_main_query() && $query->is_home() ) {
      $query->set( 'post_type', array('link') );
    }
}

//add general class for all our custom post types
function vu_mark_CPTs($classes){
  if(VU_RESTRICT_DEBUG_LEVEL(0))vu_dbg('vu_mark_CPTs $classes', $classes);
  global $post; vu_dbg('vu_mark_CPTs $classes', $classes, $post);
  if(!vu_is_custom_post_type($classes)){
    return $classes;
  }

  $additional_classes = array('vu-panel');

  $classes = $classes + $additional_classes;
  return $classes;
}
add_filter('post_class', 'vu_mark_CPTs');

// add category nicenames in body and post class
function category_id_class( $classes, $class, $post_id = NULL ) {
  if($post_id === NULL){
    if(VU_RESTRICT_DEBUG_LEVEL(0))vu_dbg('Notice: $post_id was NULL! Context... ',debug_backtrace());
    return $classes;
  }
  $post = get_post( $post_id );
  if(VU_RESTRICT_DEBUG_LEVEL(0))vu_dbg('category_id_class \$post',$post);
	foreach ( ( get_the_category( $post->ID ) ) as $category ) {
    $classes[] = $category->category_nicename;
	}
	return $classes;
}
add_filter( 'post_class', 'category_id_class',10,3 );
add_filter( 'body_class', 'category_id_class',10,2 ); //