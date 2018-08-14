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

define("VU_ADMIN_GROUP", "vu_admin"); //name of the term in the vu_user_group taxonomy that specifies the user in question as an admin

add_action( 'init', 'vu_register_permissions', 0 );
function vu_register_permissions(){	
	//the intended capabilities of standard (non-admin) VU staff
	add_role(
		'vu_department', //like editor, but without ability to modify pages/html
		__( 'VU Department', 'vu-panels' ),
		array(
			'read' => true,
			'delete_posts' => true,
			'delete_others_posts' => true,
			'delete_private_posts' => true,
			'delete_published_posts' => true,
			'edit_others_posts' => true, //#IMPORTANT: this is the official cap that makes you a member of VU_Deparatment, when other plugins etc. (ex - Dashboard Access plugin) need to go off a single capability
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

	// remove unused roles for convenience
	foreach(['editor', 'author', 'contributor'] as $old_role){
		if( get_role($old_role) ){
			remove_role( $old_role );
		}
	}

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
		VU_USER_GROUP,
		array('user','post','link'),
		array(
			'label' => __( 'VU User Group', 'vu-panels' ),
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
			'show_admin_column' => true, //#TEMP
		)
	);

	if ( ! vu_term_exists( VU_ADMIN_GROUP, VU_USER_GROUP ) ){
		$output = "Inserted admin vu_user_group: " . print_r(wp_insert_term( VU_ADMIN_GROUP, VU_USER_GROUP ), true);
		vu_dbg($output);
		
		vu_db_replace_ug2r_data(VU_ADMIN_GROUP, vu_permission_level::Admin);
	}
		//explicitly add new caps to the appropriate role(s), if necessary (it shouldn't be)
		// $admins = get_role( 'administrator' );

		// $admins->add_cap( 'manage_vu_user_groups' );
		// $admins->add_cap( 'assign_vu_user_groups' ); 
		// $admins->add_cap( 'edit_vu_user_groups' );
		// $admins->add_cap( 'delete_vu_user_groups' );

	if(IS_WP_DEBUG && VU_RESTRICT_DEBUG_LEVEL(1)){
		global $wp_roles;

		if ( ! isset( $wp_roles ) )
			$wp_roles = new WP_Roles();

		$t_all_roles = $wp_roles->get_names();
		vu_dbg("post-adding full roles list: ", $t_all_roles);

		$terms = get_terms( array(
			'taxonomy' => VU_USER_GROUP,
			'hide_empty' => false,  ) );
		vu_dbg("vu_user_group current terms: ", $terms);
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
	if(VU_RESTRICT_DEBUG_LEVEL(0))vu_dbg('vu_selectively_enqueue_admin_scripts');

	if ( 'users.php' != $hook && 'profile.php' != $hook ) {
        return;
	}
	wp_enqueue_script( 'vu_plugin_admin_js', plugins_url( '/js/vu-admin-scripts.js', __FILE__ ), array('jquery'), null, true);
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
	$terms = vu_get_real_object_terms($user, VU_USER_GROUP);
	vu_debug("vu_get_user_role terms for $user: ", '', $terms);

	$permission_level = 0;
	$permission_role = '';

	foreach($terms as $term){
		$role = vu_db_get_ug2r_role($term->name);
		vu_dbg($role);
		if($role === vu_permission_level::Admin){
			$permission_level = 2;
			$permission_role = $role;
			vu_dbg("set to vu_permission_level::Admin");
		}
		else if($role === vu_permission_level::Department && $permission_level < 2){
			$permission_level = 1;
			$permission_role = $role;
			vu_dbg("set to vu_permission_level::Department");
		}
		else if($role === vu_permission_level::Basic && $permission_level < 1){
			$permission_level = 0;
			$permission_role = $role;
			vu_dbg("set to vu_permission_level::Basic");
		}
		else{
			vu_debug("vu_get_user_role else condition, got role $role for term ".$term->name."current permission role: $permission_role");
		}
	}
	return $permission_role;
}  

/**
 * Get all vu_user_group terms that the user has access to.
 * Note that admins always have access to all terms, regardless of what is actually attached to their user object.
 * @param  object $user
 * @return array $accesible_ugs array of wp term objects
 */
function vu_get_accesible_user_groups($user = ''){
	//allow calling w/o params
	if('' === $user){
		$user = wp_get_current_user();
	}

	//must be user ID
	$user_id = $user;
	if( ! is_numeric($user) ){
		$user_id = $user->ID;
	}

	$available_user_groups;
	if( current_user_can(vu_permission_level::Admin, $user_id) ) //admins
	{
		$available_user_groups = vu_get_real_terms( array(
			'taxonomy' => VU_USER_GROUP,
			'hide_empty' => false,  ) );				
	}
	else{
		$available_user_groups = vu_get_real_object_terms($user_id, VU_USER_GROUP);
	}
	if(VU_RESTRICT_DEBUG_LEVEL(5)){vu_dbg("vu_get_accesible_user_groups for user $user_id returned ",wp_list_pluck( $available_user_groups, 'name' ));}
	return $available_user_groups;
}  

/**
 * Get what vu_user_group should be considered the user's "default" one for the purpose of adding posts (this is UNRELATED to their actual permissions)
 * @param  object $user
 * @return string $primary_ug
 */
function vu_get_primary_user_group($user = ''){
	//allow calling w/o params
	if('' === $user){
		$user = wp_get_current_user();
	}

	//must be user ID
	$user_id = $user;
	if( ! is_int($user) ){
		$user_id = $user->ID;
	}

	//Get user group from remembered value...
	$primary_ug = get_user_meta($user_id, $key = VU_USER_PRIMARY_UG, $single = true);
	//...or just pick one semi-arbitrarily
	if($primary_ug == ''){
		$terms = vu_get_real_object_terms($user, VU_USER_GROUP);
		vu_debug("vu_get_primary_user_group fallback; terms for $user: ", '', $terms);
		$primary_ug = $terms[0]->name; // #TODO does this work when $terms is empty ? (should try to guarantee users always have at least one user_group term)
	}
	return $primary_ug;
}

/**
 * Returns an array of the intersecting keys of the two objects' vu_user_group terms. Values are ignored.
 * Distinct from the more general vu_get_object_tax_intersection because the latter does not consider admins to be a member of every group unless they actually are.
 * @param  int $left_id
 * @param  int $right_id
 * @param  int $term_field Name of field to use in the comparison 
 * @return array $intersection Array of shared terms
 */

function vu_get_object_user_group_intersection($left_id, $right_id, $term_field){
	//get term (should be singular!) associated with post
	$compare_terms = []; //array with 2 items, that holds the two sets whose intersection we are checking
	$count = -1;
	foreach( [$left_id, $right_id] as $id ){
		++$count;
		if( get_userdata( $id ) ){ // if the object is a user, use the accessor function
			//vu_dbg("itsauser",vu_terms_array_to_set( vu_get_accesible_user_groups($id), $term_field ) );
			$compare_terms[$count] = vu_terms_array_to_set( vu_get_accesible_user_groups($id), $term_field );
		}
		else{
			//vu_dbg("itsanobject",vu_terms_array_to_set( vu_get_real_object_terms( $id, VU_USER_GROUP ), $term_field ) );
			$compare_terms[$count] = vu_terms_array_to_set( vu_get_real_object_terms( $id, VU_USER_GROUP ), $term_field );
		}
	}

	vu_dbg("vu_get_object_tax_intersection \$left_terms, \$right_terms", $compare_terms[0], $compare_terms[1]);

	return vu_get_set_intersection($compare_terms[0], $compare_terms[1]);
}

/**
 * Block access to posts that the user should not have access to based on their user group membership
 * @param  none
 * @return none
 */
add_action( 'admin_init', 'vu_post_group_access_handler');
function vu_post_group_access_handler() {
	global $pagenow;
	if(VU_RESTRICT_DEBUG_LEVEL(0)){vu_dbg("vu_post_group_access_handler \$pagenow",$pagenow);}

	if($pagenow != 'post.php'){
		if(VU_RESTRICT_DEBUG_LEVEL(1))vu_dbg("$pagenow != post.php");
		return;
	}

   // Exit if the user cannot edit any posts
   if ( is_admin() && ! current_user_can( 'edit_posts' ) && ! IS_DOING_AJAX) {
      wp_redirect( home_url() );
      exit;
   }


}
add_action( 'admin_head', 'vu_post_group_access_handler_helper' );
function vu_post_group_access_handler_helper() {
	   //get post id
	   global $post;
	   global $wp_query;
	   $current_post_id;
	   if(array_key_exists('post', $_GET)){
		$current_post_id = $_GET['post'];
	   }
	   else if(isset($post)){
		$current_post_id = $post->ID;
	   }
	   else if(isset($wp_query) && $current_post_id = $wp_query->get_queried_object_id()){
	   }
	   else{
		vu_dbg('ERROR: $_GET["post"] and fallbacks failed', $_GET, $wp_query);
	   }
	   $current_post_id = $_GET['post']; //get_the_ID() doesn't work out of the loop
	   
	   $current_user_id = get_current_user_id();
	   //Exit if the user cannot edit *this* post, due to lacking group membership. (second && says "posts without groups are visible by everyone")
	   if ( ! vu_get_object_user_group_intersection($current_post_id, $current_user_id, 'name') &&
		 ! empty( vu_get_real_object_terms( $current_post_id, VU_USER_GROUP ) ) ){
			wp_redirect( home_url() );
			exit;
	   }
}

/**
 * Prevent posts that the user does not have permission to modify from showing up on the All Posts page
 * @param  none
 * @return none
 */
// IMPORTANT: If you want to create a WP::Query object within this action, you need to call remove_action( 'pre_get_posts', __FUNCTION__ ); at the beginning of the function to avoid an infinite loop
if(is_admin()){ //only filter posts in the query if this is done in the admin panel
    add_action('pre_get_posts', 'custom_post_listing');
}
function custom_post_listing($query){
	//first, skip all this if user is an admin; they do what they want
	if(current_user_can(vu_permission_level::Admin)){
		return $query;
	}

	// [_builtin => true] as a first param returns only WordPress default post types. 
	// [_builtin => false] as a first param returns only registered custom post types. 
	$post_types = get_post_types('', 'objects'); //all post types

	$post_type = $query->get('post_type'); //queried post type

    /* Check that queried post type actually exists, then check specifically if it's a link or post */
    if(in_array($post_type, array_keys($post_types)) && ($post_type == 'post' || $post_type == 'link')){

		$query->set( 'tax_query', array(
			array(
				'taxonomy' => VU_USER_GROUP,
				'field' => 'name',
				'terms' => wp_list_pluck( vu_get_accesible_user_groups(), 'name' ), // get the names of the vu_user_groups the user has access to
				'operator' => 'IN'
			)
		) );
	}
	
	return $query;
}