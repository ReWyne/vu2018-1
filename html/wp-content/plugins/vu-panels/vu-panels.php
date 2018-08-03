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

defined( 'ABSPATH' ) or die(); //canonical way to exit if accessed directly

//Misc convenience definitions
//WP_DEBUG* should already be defined in wp-settings.php
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
$vu_panels_vars['RESTRICT_DEBUG_LEVEL'] = 0;

//Setting the global to higher numbers should print less. Ohalso, floats are fine.
//roughly speaking, 0 == "print everything", 1 == "print reasonably important functions", 2 == "important", 3 == "VERY important", 4 == "print programmer's summaries" 5 == "layman's summaries", 
function VU_RESTRICT_DEBUG_LEVEL($level){ global $vu_panels_vars; if(IS_WP_DEBUG && $vu_panels_vars['RESTRICT_DEBUG_LEVEL'] <= $level) return true; else return false; }

include_once dirname( __FILE__ ) . '/vu-util.php';
include_once dirname( __FILE__ ) . '/vu-db.php';
include_once dirname( __FILE__ ) . '/vu-permissions.php'; 
//is there a better way to handle this besides putting all the little php files in their own folder and adding them all with a for loop? (with a text file or summit to ensure order) I guess I could class them and include_once, but that seems a little...
include_once dirname( __FILE__ ) . '/vu-change-groups-for-user.php';
include_once dirname( __FILE__ ) . '/vu-alter-user-group.php';
include_once dirname( __FILE__ ) . '/vu-choose-user-initial-group.php';
include_once dirname( __FILE__ ) . '/vu-change-groups-for-post.php';



// register_activation_hook's in other files
// register_activation_hook( __FILE__, 'vu_register_permissions' );

//link post type
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


// add links custom post type to front page shortcode function

// function vu_display_link_posts( $atts = null, $content = null, $tag = null ) {
// vu_log('vu_display_link_posts');
//   $out = '';

//   $args = array( 
//       'numberposts' => '99', 
//       'post_status' => 'publish', 
//       'post_type' => 'link' ,
//   );

//   $recent = wp_get_recent_posts( $args );

//   if ( $recent ) {

//       $out .= '<section class="overview">';

//       $out .= '<h1>Recent Projects</h1>';

//       $out .= '<div class="overview">';

//       foreach ( $recent as $item ) {

//           $out .= '<a href="' . get_permalink( $item['ID'] ) . '">';
//           $out .= get_the_post_thumbnail( $item['ID'] ); 
//           $out .= '</a>';
//       }

//       $out .= '</div></section>';
//   }

//   if ( $tag ) {
//       return $out;
//   } else {
//       echo $out;
//   }
// }
// add_shortcode( 'recentposts', 'vu_display_link_posts' );


//add general class for all our custom post types
function vu_mark_CPTs($classes){
  if(VU_RESTRICT_DEBUG_LEVEL(0)){vu_dbg("vu_mark_CPTs \$classes", $classes);}

  if(!vu_is_custom_post_type($classes)){
    return $classes;
  }

  $additional_classes = array('vu-panel');

  $classes = $classes + $additional_classes;
  return $classes;
}
add_filter('post_class', 'vu_mark_CPTs');

// add category nicenames in body and post class
function category_id_class( $classes ) {
	global $post;
	foreach ( ( get_the_category( $post->ID ) ) as $category ) {
    $classes[] = $category->category_nicename;
	}
	return $classes;
}
add_filter( 'post_class', 'category_id_class' );
add_filter( 'body_class', 'category_id_class' );





//handle custom meta boxes for setting people as admins
//#TODO, currently copy-paste from https://www.smashingmagazine.com/2012/01/limiting-visibility-posts-username/
/* Fire our meta box setup function on the post editor screen. */
<<<'EOT'
add_action( 'load-post.php', 'smashing_post_meta_boxes_setup' );
add_action( 'load-post-new.php', 'smashing_post_meta_boxes_setup' );

/* Meta box setup function. */
function smashing_post_meta_boxes_setup() {

   /* Add meta boxes on the 'add_meta_boxes' hook. */
   add_action( 'add_meta_boxes', 'smashing_add_post_meta_boxes' );

   /* Save post meta on the 'save_post' hook. */
   add_action( 'save_post', 'smashing_flautist_access_save_meta', 10, 2 );
}

/* Create one or more meta boxes to be displayed on the post editor screen. */
function smashing_add_post_meta_boxes() {

   add_meta_box(
      'smashing-flautist-access',         // Unique ID
      esc_html__( 'Post Viewing Permission', 'smashing_flautist' ),     // Title
      'smashing_flautist_access_meta_box',      // Callback function
      'post',              // Admin page (or post type)
      'normal',               // Context
      'default'               // Priority
   );
}

/* Display the post meta box. */
function smashing_flautist_access_meta_box( $object, $box ) { ?>

   <?php wp_nonce_field( basename( __FILE__ ), 'smashing_flautist_access_nonce' ); ?>

   <p>
      <label for='smashing-flautist-access'><?php _e( 'Enter the username of the subscriber that you want to view this content.', 'smashing_flautist' ); ?></label>
      <br />
      <input class='widefat' type='text' name='smashing-flautist-access' id='smashing-flautist-access' value='<?php echo esc_attr( get_post_meta( $object->ID, 'smashing_flautist_access', true ) ); ?>' size='30' />
   </p>
<?php }








//still need to add "adding ease to the selection" code block








/* Save post meta on the 'save_post' hook. */
add_action( 'save_post', 'smashing_flautist_access_save_meta', 10, 2 );

/* Save the meta box's post metadata. */
function smashing_flautist_access_save_meta( $post_id, $post ) {

   /* Make all $wpdb references within this function refer to this variable */
   global $wpdb;

   /* Verify the nonce before proceeding. */
   if ( !isset( $_POST['smashing_flautist_access_nonce'] ) || !wp_verify_nonce( $_POST['smashing_flautist_access_nonce'], basename( __FILE__ ) ) )
      return $post_id;

   /* Get the post type object. */
   $post_type = get_post_type_object( $post->post_type );

   /* Check if the current user has permission to edit the post. */
   if ( !current_user_can( $post_type->cap->edit_post, $post_id ) )
      return $post_id;

   /* Get the posted data and sanitize it for use as an HTML class. */
   $new_meta_value = ( isset( $_POST['smashing-flautist-access'] ) ? sanitize_html_class( $_POST['smashing-flautist-access'] ) : '' );

   /* Get the meta key. */
   $meta_key = 'smashing_flautist_access';

   /* Get the meta value of the custom field key. */
   $meta_value = get_post_meta( $post_id, $meta_key, true );

   /* If a new meta value was added and there was no previous value, add it. */
   if ( $new_meta_value && '' == $meta_value )
      {
      add_post_meta( $post_id, $meta_key, $new_meta_value, true );
      $wpdb->query($wpdb->prepare("UPDATE $wpdb->posts SET post_status = 'private' WHERE ID = ".$post_id." AND post_type ='post'"));
      }
   /* If the new meta value does not match the old value, update it. */
   elseif ( $new_meta_value && $new_meta_value != $meta_value )
      {
      update_post_meta( $post_id, $meta_key, $new_meta_value );
      $wpdb->query($wpdb->prepare("UPDATE $wpdb->posts SET post_status = 'private' WHERE ID = ".$post_id." AND post_type ='post'"));
      }
   /* If there is no new meta value but an old value exists, delete it. */
   elseif ( '' == $new_meta_value && $meta_value )
      {
      delete_post_meta( $post_id, $meta_key, $meta_value );
      $wpdb->query($wpdb->prepare("UPDATE $wpdb->posts SET post_status = 'public' WHERE ID = ".$post_id." AND post_type ='post'"));
      }
}


EOT;









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
// }s