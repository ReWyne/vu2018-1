<?php

defined( 'ABSPATH' ) or die(); //exit if accessed directly

// 
// This file encompasses the user taxonomy and user permissions handling/meta boxes
// 

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
		'vu_staff', //like editor, but without ability to modify pages/html
		__( 'VU Staff' ),
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
		'user',
		array(
			'label' => __( 'VU User Group' ),
			//'rewrite' => array( 'slug' => 'person' ),
			'capabilities' => array(
				'assign_terms' => 'vu_user_group_assign_terms', //ability to add users to groups in this taxonomy; recommended restricted to admins
				'edit_terms' => 'vu_user_group_edit_terms', //ability to add groups to this taxonomy; recommended restricted to admins
			)
		)
	);
}
