<?php

defined( 'ABSPATH' ) or die(); //exit if accessed directly

// add_action( 'init', 'register_link_post_type'); //TODO: find way to make this run once and still work? register_activation_hook method didn't work
// function register_link_post_type() {
// 	register_post_type( 'link',
// 		array(
// 				'labels' => array(
// 				'name'               => _x( 'Links', 'link plural' ),
// 				'singular_name'      => _x( 'Link', 'link singular' ),
// 				//'add_new'            => __( 'Add New Link' ),
// 				'add_new_item'       => __( 'Add New Link' ),
// 				'edit_item'          => __( 'Edit Link' ),
// 				'new_item'           => __( 'New Link' ),
// 				'all_items'          => __( 'All Links' ),
// 				'view_item'          => __( 'View Link' ),
// 				'search_items'       => __( 'Search Links' ),
// 			),
// 			'public' => true,
// 			'has_archive' => false,
// 			'show_ui' => true,
// 			'show_in_admin_bar' => true, //defaults to show_ui
// 			'menu_position' => 5,
// 			'register_meta_box_cb' => array($this,'add_link_custom_fields'),
// 			'supports' => array( 'title', 'editor', 'thumbnail' ),
// 			'taxonomies' => array('post_tag', 'category'),
// 			//'menu_icon' => 'dashicons-editor-unlink',
// 		)
// 	);
// }

//Callback from register_post_type
add_action( 'add_meta_boxes_post', 'add_vu_post_user_group_custom_fields');
add_action( 'add_meta_boxes_link', 'add_vu_post_user_group_custom_fields');
function add_vu_post_user_group_custom_fields() {
	add_meta_box( 'vu_user_group_meta_id', __( 'Department', 'vu-panels' ), 'vu_add_post_user_group_display', array('post','link'), 'normal', 'high' );
//add_meta_box( string $id, string $title, callable $callback, string|array|WP_Screen $screen = null, string $context = 'advanced', string $priority = 'default', array $callback_args = null )
}

/**
 * Display the contents of the add_post_user_group meta box
 * This function deals with the vu_user_group taxonomy
 * @param  none
 * @return none
 */
function vu_add_post_user_group_display(){
	wp_nonce_field( 'vu_post_cgfp_save', 'vu_post_cgfp_nonce' );
	?>
	<label for="vu_post_ug">Group in charge of managing this post : </label>
	<select name="vu_cgfp_value" name="vu_cgfp_field">

		<?php
		//get all existing groups again
		//TODO: should only be allowed to make user groups they hav access to
		$user = wp_get_current_user(); //NOTE: $user_id global should be defined here, if you'd rather use that
		$available_user_groups = vu_get_accesible_user_groups($user->ID);
		$current_post_id = get_the_ID();

		//and print
		$selected_text;
		$post_terms = vu_get_real_object_terms($current_post_id, VU_USER_GROUP);
		$primary_ug = vu_get_primary_user_group($user->ID);
		if(IS_WP_DEBUG && count($post_terms) > 1){
			vu_dbg("ERROR: Post".$current_post_id." has more than one user group!",$post_terms);
		}
		foreach($available_user_groups as $term_object){ //Note: in_array runs in [length of array] time; switch to key => value method for O(1) lookup if this is an issue
			
			if($post_terms){ //if the post already has ugs, use the first (and only) one from that list as the default
				$selected_text = $post_terms[0]->name == $term_object->name ? 'selected="selected"' : '';  //#TODO: $post_terms[0] *should* be getting the first and only term that the post has from the vu_user_group taxonomy
			}
			else{ //otherwise, use whatever user group this particular user picked last
				$selected_text = $primary_ug == $term_object->name ? 'selected="selected"' : '';
			}
			echo '<option '.$selected_text.' value="'.$term_object->term_id.'" >'.$term_object->name.'<br>';
		}
		?>
	</select>
	<?php
}

add_action( 'save_post', 'vu_add_post_user_group_save');
//
/**
 * #TODO
 * Save the add_post_user_group meta value entered
 * This function deals with the vu_user_group taxonomy
 * @param  none
 * @return none
 */
function vu_add_post_user_group_save( $post_id ) {

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

	// $new_ug = sanitize_key( $_POST['vu_cgfp_value'] );
	// update_post_meta( $post_id, 'link_url_value', $new_ug );

	//get frontend's specified user group and update
	vu_dbg('VU_USER_GROUP',get_terms( VU_USER_GROUP));

	$new_ug = (int) $_POST['vu_cgfp_value'];
	$user_id = get_current_user_id();
	vu_dbg('$new_ug',$new_ug);
	wp_set_object_terms( $post_id, array($new_ug), VU_USER_GROUP );

	//set user's default user group to whatever they decided to use here
	update_user_meta( $user_id, VU_USER_PRIMARY_UG, get_term($new_ug, VU_USER_GROUP)->name );

	if(VU_RESTRICT_DEBUG_LEVEL(4)) vu_debug("Successfully updated post $post_id's vu_user_group data entry to: ".print_r(wp_get_object_terms($post_id, VU_USER_GROUP),true).
	"\n<br>User vu_user_primary_ug meta has been updated to: ".get_user_meta($user_id, VU_USER_PRIMARY_UG));

	//$link_url_value = sanitize_text_field( $_POST['link_url_value'] );

	//update_post_meta( $post_id, 'link_url_value', $link_url_value );
}