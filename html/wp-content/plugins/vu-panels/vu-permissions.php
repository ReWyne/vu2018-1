<?php

defined( 'ABSPATH' ) or die(); //exit if accessed directly

// 
// This file encompasses the user taxonomy and user permissions handling/meta boxes
// 

abstract class vu_permission_level {
	const Admin = 'administrator';
	const Department = 'vu_department';
	const Basic = 'subscriber';
  }

function vu_register_permissions(){
	//the intended capabilities of standard (non-admin) VU staff
	if(IS_WP_DEBUG){
		vu_log("vu_register_permissions");

		global $wp_roles;

		if ( ! isset( $wp_roles ) )
    		$wp_roles = new WP_Roles();

		$t_all_roles = $wp_roles->get_names();
		vu_pc_debug("pre-adding full roles list: ", $t_all_roles);
	}

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
	if(IS_WP_DEBUG){
		global $wp_roles;

		if ( ! isset( $wp_roles ) )
    		$wp_roles = new WP_Roles();

		$t_all_roles = $wp_roles->get_names();
		vu_pc_debug("post-adding full roles list: ", $t_all_roles);
	}
	// //if we want out own admin role
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
				'assign_terms' => 'vu_user_group_assign_terms', //ability to add users to groups in this taxonomy; recommended restricted to admins
				'edit_terms' => 'vu_user_group_edit_terms', //ability to add groups to this taxonomy; recommended restricted to admins
			),
			'hierarchical' => false,
			'show_ui' => true,
			'show_admin_column' => true,
			'query_var' => true,
		)
	);
}

/**
 * Return true if the string is an existing taxonomy term
 * @param  string $term, $taxonomy
 * @return boolean
 */
function vu_term_exists($term, $taxonomy){
	$exists = term_exists( $term, 'post_tag' );
	if ( $exists !== 0 && $exists !== null ){
		return true;
	}
	return false;
}
