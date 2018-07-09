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
	// if(IS_WP_DEBUG){
	// 	global $wp_roles;

	// 	if ( ! isset( $wp_roles ) )
    // 		$wp_roles = new WP_Roles();

	// 	$t_all_roles = $wp_roles->get_names();
	// 	vu_debug("post-adding full roles list: ", array('err_log', 'pc_dbg'), $t_all_roles);
	// }

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

	if ( ! vu_term_exists( 'vu_administrator', 'vu_user_group' ) ){
		$output = "Inserted admin vu_user_group: " . print_r(wp_insert_term( 'vu_administrator', 'vu_user_group' ), true);
		vu_debug($output);
		
		vu_db_replace_ug2r_data('vu_administrator', vu_permission_level::Admin);
	}
		//explicitly add new caps to the appropriate role(s), if necessary (it shouldn't be)
		// $admins = get_role( 'administrator' );

		// $admins->add_cap( 'manage_vu_user_groups' );
		// $admins->add_cap( 'assign_vu_user_groups' ); 
		// $admins->add_cap( 'edit_vu_user_groups' );
		// $admins->add_cap( 'delete_vu_user_groups' );
}

/**
 * Return true if the string is an existing taxonomy term
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
		array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'we_value' => 1234 ) );

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
