<?php

defined( 'ABSPATH' ) or die(); //exit if accessed directly

//include_once dirname( __FILE__ ) . '/vu-db.php';

// 
// This file encompasses the vu_user_group taxonomy and user permissions handling/meta boxes
// 

//list of available roles
abstract class vu_permission_level {
	const Admin = 'administrator';
	const Department = 'vu_department';
	const Basic = 'subscriber';
  }

define("VU_ADMIN_GROUP", "vu_admin");

add_action( 'init', 'vu_register_permissions', 0 );
function vu_register_permissions(){
	// if(IS_WP_DEBUG){
	// 	vu_log("vu_register_permissions");

	// 	global $wp_roles;

	// 	if ( ! isset( $wp_roles ) )
    // 		$wp_roles = new WP_Roles();

	// 	$t_all_roles = $wp_roles->get_names();
	// 	vu_debug("pre-adding full roles list: ", array('err_log', 'pc_dbg'), $t_all_roles);
	// }
	//the intended capabilities of standard (non-admin) VU staff
	add_role(
		'vu_department', //like editor, but without ability to modify pages/html
		__( 'VU Department' ),
		array(
			'read' => true,
			'delete_posts' => true,
			'delete_others_posts' => true,
			'delete_private_posts' => true,
			'delete_published_posts' => true,
			'edit_others_posts' => true,
			'edit_posts' => true,
			'edit_private_posts' => true,
			'edit_published_posts' => true,
			'moderate_comments' => true,
			'publish_posts' => true,
			'read_private_pages' => true,
			'read_private_posts' => true,
			'upload_files' => true,
			//#TODO questionable permissions
			'manage_categories' => true,
			'manage_links' => true,
			//'unfiltered_html' => false, //definitely not
		)
	);

	// //if we want our own admin role
	// $t_role = get_role('administrator');
	// $admin_caps = $t_role['capabilities'];
	// //list of capabilities we're adding to the vanilla admin role
	// array_push($t_caps,'vu_user_group_assign_terms','vu_user_group_edit_terms');
	// add_role(
	// 	'vu_admin', //admin equivalent
	// 	__( 'VU Admin' ),
	// 	$admin_caps
	// );
	
	register_taxonomy(
		'vu_user_group',
		array('user'),
		array(
			'label' => __( 'VU User Group' ),
			//'rewrite' => array( 'slug' => 'person' ),
			'capabilities' => array(
				'manage_terms' => 'manage_vu_user_groups',
				'assign_terms' => 'assign_vu_user_groups', //ability to add users to groups in this taxonomy; recommended restricted to admins
				'edit_terms' => 'edit_vu_user_groups', //ability to add groups to this taxonomy; recommended restricted to admins
				'delete_terms' => 'delete_vu_user_groups',
			),
			'map_meta_cap' => true,
			'hierarchical' => false,
			'show_ui' => true,
			'show_admin_column' => true,
			'query_var' => true,
		)
	);

	if ( ! vu_term_exists( VU_ADMIN_GROUP, 'vu_user_group' ) ){
		$output = "Inserted admin vu_user_group: " . print_r(wp_insert_term( VU_ADMIN_GROUP, 'vu_user_group' ), true);
		vu_debug($output);
		
		vu_db_replace_ug2r_data(VU_ADMIN_GROUP, vu_permission_level::Admin);
	}
		//explicitly add new caps to the appropriate role(s), if necessary (it shouldn't be)
		// $admins = get_role( 'administrator' );

		// $admins->add_cap( 'manage_vu_user_groups' );
		// $admins->add_cap( 'assign_vu_user_groups' ); 
		// $admins->add_cap( 'edit_vu_user_groups' );
		// $admins->add_cap( 'delete_vu_user_groups' );

	if(IS_WP_DEBUG){
		global $wp_roles;

		if ( ! isset( $wp_roles ) )
			$wp_roles = new WP_Roles();

		$t_all_roles = $wp_roles->get_names();
		vu_debug("post-adding full roles list: ", array('err_log', 'pc_dbg'), $t_all_roles);

		$terms = get_terms( array(
			'taxonomy' => 'vu_user_group',
			'hide_empty' => false,  ) );
		vu_debug("vu_user_group current terms: ", '', $terms);
	}

}

/**
 * Boolean wrapper for wp's term_exists(). Return true if the string is an existing taxonomy term
 * @param  string $term, $taxonomy
 * @return boolean
 */
function vu_term_exists($term, $taxonomy){
	$exists = term_exists( $term, $taxonomy );
	if ( $exists !== 0 && $exists !== null ){
		return true;
	}
	return false;
}

/**
 * Add js scripts to permissions management
 * @param  none
 * @return none
 */
add_action( 'admin_enqueue_scripts', 'vu_selectively_enqueue_admin_scripts' );
function vu_selectively_enqueue_admin_scripts( $hook ) {
    if ( 'users.php' != $hook && 'profile.php' != $hook ) {
        return;
    }
	wp_enqueue_script( 'vu_plugin_admin_js', plugins_url( '/js/vu-admin-scripts.js', __FILE__ ), array('jquery'));
	//error_log("admin enqueue script " . plugin_dir_url( __FILE__ ) . 'js/vu-admin-scripts.js');

	// in JavaScript, accessed as (ex) ajax_object.ajax_url
	wp_localize_script( 'vu_plugin_admin_js', 'ajax_object',
		array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'IS_WP_DEBUG' => (IS_WP_DEBUG === true) ) );

}


/**
 * Compute what role a user should have by looking at what vu_user_group terms it has and getting the associated roles from user_group_to_role
 * Recommend setting the user's role right after
 * @param  object $user
 * @return string $role
 */
function vu_get_user_role($user = ''){
	if('' === $user){
		$user = wp_get_current_user();
	}
	$terms = get_the_terms( $user, 'vu_user_group');
	vu_debug("vu_get_user_role terms for $user: ", '', $terms);

	$permission_level = 0;
	$permission_role = '';

	foreach($terms as $term => $data){
		$role = vu_db_get_ug2r_role($term);
		if($role === vu_permission_level::Admin){
			$permission_level = 2;
			$permission_role = $role;
		}
		else if($role === vu_permission_level::Department && vu_permission_level < 2){
			$permission_level = 1;
			$permission_role = $role;
		}
		else if($role === vu_permission_level::Basic && vu_permission_level < 1){
			$permission_level = 0;
			$permission_role = $role;
		}
		else{
			vu_debug("vu_get_user_role error, got role $role for term ",'',$term);
		}
	}
	return $permission_role;
}  


/**
 * Block access to posts that the user should not have access to based on their user group membership
 * TODO
 * @param  none
 * @return none
 */
add_action( 'admin_init', 'vu_custom_dashboard_access_handler');
 
function vu_custom_dashboard_access_handler() {
	global $pagenow;

	if($pagenow != 'post.php'){
		return;
	}

   // Exit if the user cannot edit any posts
   if ( is_admin() && ! current_user_can( 'edit_posts' ) && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX )) {
      wp_redirect( home_url() );
      exit;
   }

   //Exit if the user cannot edit *this* post, due to lacking group membership.



   global $post;
	$id = $post->ID;

	
// 	//reference
//    /* Get the meta key. */
//    $meta_key = 'smashing_flautist_access';

//    /* Get the meta value of the custom field key. */
//    $meta_value = get_post_meta( $post_id, $meta_key, true );

//    /* If a new meta value was added and there was no previous value, add it. */
//    if ( $new_meta_value && '' == $meta_value )
//       {
//       add_post_meta( $post_id, $meta_key, $new_meta_value, true );
//       $wpdb->query($wpdb->prepare("UPDATE $wpdb->posts SET post_status = 'private' WHERE ID = ".$post_id." AND post_type ='post'"));
//       }
	
}

/**
 * Prevent posts that the user does not have permission to modify from showing up on the All Posts page
 * TODO
 * @param  none
 * @return none
 */
add_action('restrict_manage_posts', 'rudr_filter_by_the_author');
function vu_filter_by_the_author() {
	$params = array(
		'name' => 'author', // this is the "name" attribute for filter <select>
		'show_option_all' => 'All authors' // label for all authors (display posts without filter)
	);
 
	if ( isset($_GET['user']) )
		$params['selected'] = $_GET['user']; // choose selected user by $_GET variable
 
	wp_dropdown_users( $params ); // print the ready author list
}
 
