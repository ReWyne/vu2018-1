<?php

defined( 'ABSPATH' ) or die(); //exit if accessed directly

// 
// This file encompasses the vu_user_group taxonomy and user permissions handling/meta boxes
// 

abstract class vu_permission_level {
	const Admin = 'administrator';
	const Department = 'vu_department';
	const Basic = 'subscriber';
  }

  //called via register_activation_hook
function vu_register_permissions(){
	if(IS_WP_DEBUG){
		vu_log("vu_register_permissions");

		global $wp_roles;

		if ( ! isset( $wp_roles ) )
    		$wp_roles = new WP_Roles();

		$t_all_roles = $wp_roles->get_names();
		vu_debug("pre-adding full roles list: ", array('err_log', 'pc_dbg'), $t_all_roles);
	}

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
	if(IS_WP_DEBUG){
		global $wp_roles;

		if ( ! isset( $wp_roles ) )
    		$wp_roles = new WP_Roles();

		$t_all_roles = $wp_roles->get_names();
		vu_debug("post-adding full roles list: ", array('err_log', 'pc_dbg'), $t_all_roles);
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
	$exists = term_exists( $term, 'post_tag' );
	if ( $exists !== 0 && $exists !== null ){
		return true;
	}
	return false;
}

// /**
//  * Create dialog box for adding/removing terms from the vu_user_group.
//  * Note that the role associated with each term is stored in a separate table on the database, user_group_to_role
//  * @param  none
//  * @return none
//  */
// add_action( 'add_meta_boxes', 'vu_alter_user_group_taxonomy' );
// function vu_alter_user_group_taxonomy() {
// 	$screens = ['users'];
//     foreach ($screens as $screen) {
//         add_meta_box(
//             'vu_alter_user_group_taxonomy',           // Unique ID
//             __( 'Modfy vu_user_group Taxonomy', 'vu_textdomain' ),  // Box title
//             'vu_alter_user_group_taxonomy_display',  // Content callback, must be of type callable
// 			$screen,                   // screen to display on
// 			'normal', // display area
//         	'high' // display priority
//         );
//     }
// }



//add_action( 'add_meta_boxes', array($this,'add_link_custom_fields' )); //calls the function in this class




/**
 * Display the contents of the alter_user_group_taxonomy meta box
 * Note that the role associated with each term is stored in a separate table on the database, user_group_to_role
 * @param  none
 * @return none
 */
//TODO: currently called on all admin pages
add_action( 'manage_users_extra_tablenav', 'vu_alter_user_group_taxonomy_display' ); //calls the function in this class
function vu_alter_user_group_taxonomy_display(){	
	global $pagenow;
	if ($pagenow != 'users.php') {
		return;	
		}
	vu_debug("vu_alter_user_group_taxonomy_display IS users.php");



	
	echo '<div class="postbox container">';
	wp_nonce_field( 'vu_augt_save', 'vu_augt_nonce' );
    echo '<label for="vu_augt_group"><p><p><p><b>User Group to add :</b></label>
    <input type="text" id="vu_augt_group_field" name="vu_augt_group_value" placeholder="Enter User Group" size="60" required>

    <label for="psw"><p><b>Group Permissions :</b></label>
	  <select name="vu_augt_role_value" id="vu_augt_role_select" class="postbox">';
	  //generate options for our drop-down select
	  global $wp_roles;
		if ( ! isset( $wp_roles ) )
    		$wp_roles = new WP_Roles();

		$t_all_roles = $wp_roles->get_names();
		foreach($t_all_roles as $role){
			echo '<option value="'.$role.'">'.$role.'</option>';
		}
echo '</select>
    <button type="submit">Submit</button>
  </div>';
}

  //register_activation_hook( __FILE__, array('save_vu_alter_usr_grp_taxPostType', 'register_vu_alter_usr_grp_tax_post_type') );
  //Save the meta value entered
//  add_action( 'save_post', array($this,'save_link_url'));

  function save_vu_alter_usr_grp_tax_url( $post_id ) {
    vu_log("save_vu_alter_usr_grp_tax_url");

    //only save meta value if hitting submit
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ){
      return $post_id;  
    }

    // Check if nonce is set
    if ( ! isset( $_POST['vu_alter_usr_grp_tax_url_nonce'] ) ) {
      return $post_id;
    }

    if ( ! wp_verify_nonce( $_POST['vu_alter_usr_grp_tax_url_nonce'], 'vu_alter_usr_grp_tax_save' ) ) {
      return $post_id;
    }

    // Check that the logged in user has permission to edit this post
    if ( ! current_user_can( 'edit_post' ) ) {
      return $post_id;
    }

    $vu_alter_usr_grp_tax_url_value = sanitize_text_field( $_POST['vu_alter_usr_grp_tax_url_value'] );

    update_post_meta( $post_id, 'vu_alter_usr_grp_tax_url_value', $vu_alter_usr_grp_tax_url_value );
  }






/**
 * Create dialog box for adding/removing user groups from a user.
 * Note that this may result in changing the user's role.
 * @param  none
 * @return none
 */
add_action( 'add_meta_boxes', 'product_price_box' );
function product_price_box() {
    // add_meta_box( 
    //     'product_price_box',
    //     __( 'Product Price', 'myplugin_textdomain' ),
    //     'product_price_box_content',
    //     'product',
    //     'side',
    //     'high'
    // );
}