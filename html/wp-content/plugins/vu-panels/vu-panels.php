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

// Debugging convenience globals and accessors
global $vu_panels_vars;
$vu_panels_vars['RESTRICT_DEBUG_LEVEL'] = 6; // 6 or higher removes all debugging except error/warning messages

// Misc Constants
define("VU_USER_GROUP","vu_user_group"); //taxonomy
define("VU_USER_PRIMARY_UG","vu_user_primary_ug"); //user metadata

//Setting the global to higher numbers should print less. Ohalso, floats are fine.
//roughly speaking, 0 == "print everything", 1 == "print reasonably important functions", 2 == "important", 3 == "VERY important", 4 == "print programmer's summaries" 5 == "layman's summaries", 
function VU_RESTRICT_DEBUG_LEVEL($level){ global $vu_panels_vars; if(IS_WP_DEBUG && $vu_panels_vars['RESTRICT_DEBUG_LEVEL'] <= $level) return true; else return false; }
include_once dirname( __FILE__ ) . '/vu-util.php';
include_once dirname( __FILE__ ) . '/vu-db.php';
include_once dirname( __FILE__ ) . '/vu-permissions.php'; 
//Misc dialogue items. Btw, is there a better way to handle this besides putting all the little php files in their own folder and adding them all with a for loop? (with a text file or similar to ensure order) I guess I could class them and include_once, but that seems a little...
include_once dirname( __FILE__ ) . '/vu-change-groups-for-user.php';
include_once dirname( __FILE__ ) . '/vu-alter-user-group.php';
include_once dirname( __FILE__ ) . '/vu-change-groups-for-post.php';
include_once dirname( __FILE__ ) . '/vu-filter-by-user-group.php';

// Link post type
// Just like regular posts except they also have the link_url_value metavalue
class vu_link_post_type {

  function __construct() {
    add_action( 'init', array($this, 'register_link_post_type')); //calls the function in this class
    add_action( 'add_meta_boxes_link', array($this,'add_link_custom_fields' ));
    add_action( 'save_post', array($this,'save_link_url'));
  }

  /**
   * Registers the link post type
   * @return none
   */
  function register_link_post_type() {
    if(VU_RESTRICT_DEBUG_LEVEL(0)) vu_dbg("register_link_post_type");

    register_post_type( 'link',
      array(
        'labels' => array(
          'name'               => _x( 'Links', 'link plural', 'vu-panels' ),
          'singular_name'      => _x( 'Link', 'link singular' ),
          //'add_new'            => __( 'Add New Link', 'vu-panels' ), //should be depreciated
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
        //'menu_icon' => 'dashicons-editor-unlink', //uncomment if you want a different symbol for links than exists for posts
      )
    );
  }

  /**
   * Callback from register_post_type
   * @return none
   */
  function add_link_custom_fields() {
    if(VU_RESTRICT_DEBUG_LEVEL(0)){vu_dbg("add_link_custom_fields");}

    vu_log("add_link_custom_fields");
    add_meta_box( 'link_meta_id', __('Link URL'), array($this, 'links_url_custom_field_display'), 'link', 'normal', 'high' );
  }

/**
 * Display the contents of the custom meta box
 * @return none
 */
  function links_url_custom_field_display(){
    if(VU_RESTRICT_DEBUG_LEVEL(0)){vu_dbg("links_url_custom_field_display");}

    wp_nonce_field( 'link_save', 'link_url_nonce' );
    $value = get_post_meta(get_the_ID(), 'link_url_value', true);
    echo '<label for="link_url">';
    echo 'URL for external link : ';
    echo '</label> ';
    echo '<input type="text" id="link_url_field" name="link_url_value" value="' . esc_url( $value ) . '" size="60" />';
  }

/**
 * Save the meta value entered
 * @param  int|string $post_id
 * @return int|none $post_id (on failure)
 */
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

$link_post_type = new vu_link_post_type(); // Doesn't work because CPTs must be registered each page load:   register_activation_hook( __FILE__, array('vu_link_post_type', 'register_link_post_type') );

/**
 * Add links custom post type to the front page main loop via hooks
 * @param  object $query (WP::Query)
 * @return none
 */
add_action( 'pre_get_posts', 'vu_generate_link_posts' );
function vu_generate_link_posts( $query ) {
  if(VU_RESTRICT_DEBUG_LEVEL(1)){vu_dbg("vu_generate_link_posts \$query", $query);}

    if( $query->is_main_query() && $query->is_home() ) {
      $query->set( 'post_type', array('link') );
    }
}

/**
 * Add general class AND the post's vu_user_group term for all our custom post types
 * @param  array $classes
 * @return array $classes
 */
function vu_mark_CPTs($classes){
  global $post; 
  if(VU_RESTRICT_DEBUG_LEVEL(1))vu_dbg('vu_mark_CPTs $classes', $classes, $post);
  if(!vu_is_custom_post_type($classes)){
    return $classes;
  }
  //get the user group that the post belongs to to add as a class
  $user_group = vu_get_real_object_terms( $post->ID, VU_USER_GROUP )[0]->name;

  $additional_classes = array('vu-panel', $user_group);

  $classes = $classes + $additional_classes;
  return $classes;
}
add_filter('post_class', 'vu_mark_CPTs');

/**
 * Add category nicenames in body and post class for all posts
 * @param  array $classes
 * @param  string $class
 * @param  int|string $post_id
 * @return array $classes
 */
function category_id_class( $classes, $class, $post_id = NULL ) {
  if($post_id === NULL){
    if(VU_RESTRICT_DEBUG_LEVEL(0)) vu_dbg('Notice: $post_id was NULL! Context... ',debug_backtrace());
    return $classes;
  }
  $post = get_post( $post_id );
  if(VU_RESTRICT_DEBUG_LEVEL(0)) vu_dbg('category_id_class \$post',$post);
	foreach ( ( get_the_category( $post->ID ) ) as $category ) {
    $classes[] = $category->category_nicename;
	}
	return $classes;
}
add_filter( 'post_class', 'category_id_class',10,3 );
add_filter( 'body_class', 'category_id_class',10,2 );

/**
 * Injects css to make the Role select in the user profile page unselectable
 * @return none
 */
add_action('admin_head', 'vu_custom_admin_css');
function vu_custom_admin_css(){
  if(VU_RESTRICT_DEBUG_LEVEL(0)) vu_dbg("vu_custom_admin_css");
?>
<style>
  span.vu-ajax-return, .vu-ajax-return, #vu_augt_return {
    font-family: monospace; 
    color: red; 
    white-space: pre;
  }

  /* role should be selected via user groups, not manually */
  /* Note: As this was done via css, this does not prevent altering this select via the tab key, but doing so will not actually alter the user\'s role */
  select#role{
    -webkit-touch-callout: none;
    -webkit-user-select: none;
    -khtml-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    -o-user-select: none;
    user-select: none;
    pointer-events: none;
    background-color: transparent;
    zoom: 1;
    filter: alpha(opacity=50);
    opacity: 0.5;
  }

  .log-entry.message{
    font-family: monospace; 
    white-space: pre;
  }
</style>
<?php
}