<?php

defined( 'ABSPATH' ) or die(); //exit if accessed directly

//Callback from register_post_type
add_action( 'add_meta_boxes_post', 'add_vu_post_user_group_custom_fields');
add_action( 'add_meta_boxes_link', 'add_vu_post_user_group_custom_fields');
function add_vu_post_user_group_custom_fields() {
	if(VU_RESTRICT_DEBUG_LEVEL(0)) vu_dbg('add_vu_post_user_group_custom_fields');

	add_meta_box( 'vu_user_group_meta_id', __( 'Department', 'vu-panels' ), 'vu_add_post_user_group_display', array('post','link'), 'normal', 'high' );
}

/**
 * Display the contents of the add_post_user_group meta box
 * This function deals with the vu_user_group taxonomy
 * @param  none
 * @return none
 */
function vu_add_post_user_group_display(){
	if(VU_RESTRICT_DEBUG_LEVEL(0)) vu_dbg('vu_add_post_user_group_display');

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
		if(VU_RESTRICT_DEBUG_LEVEL(0)) vu_dbg('$post_terms',$post_terms);
		if(VU_RESTRICT_DEBUG_LEVEL(0)) vu_dbg('$primary_ug',$primary_ug);
		if(IS_WP_DEBUG && count($post_terms) > 1){
			vu_dbg("ERROR: Post".$current_post_id." has more than one user group! \$post_terms: ",$post_terms);
		}
		foreach($available_user_groups as $term_object){
			
			if($post_terms){ //if the post already has a user group (it should), use the first (and only) one from that list as the default
				$selected_text = ($post_terms[0]->name == $term_object->name) ? 'selected="selected"' : '';  //#TODO: $post_terms[0] *should* be getting the first and only term that the post has from the vu_user_group taxonomy
				if(VU_RESTRICT_DEBUG_LEVEL(1)) vu_dbg('using $post_terms[0]->name for ', $term_object);
			}
			else{ //otherwise, use whatever user group this particular user picked last
				$selected_text = ($primary_ug == $term_object->name) ? 'selected="selected"' : '';
				if(VU_RESTRICT_DEBUG_LEVEL(1)) vu_dbg('using $primary_ug for ', $term_object);
			}
			echo '<option '.$selected_text.' value="'.$term_object->term_id.'" >'.$term_object->name.'<br>';
		}
		?>
	</select>
	<?php
}

/**
 * Save the add_post_user_group meta value entered
 * This function deals with the vu_user_group taxonomy
 * @param  none
 * @return none
 */
add_action( 'save_post', 'vu_add_post_user_group_save');
function vu_add_post_user_group_save( $post_id ) {
	 vu_dbg('vu_show_extra_profile_fields, $post_id: ', $post_id);
	//only save meta value if hitting submit
	if ( IS_DOING_AUTOSAVE ){
	return $post_id;
	}
	vu_dbg('save_post_1',$_POST['vu_post_cgfp_nonce']);
	// Check if nonce is set
	if ( ! isset( $_POST['vu_post_cgfp_nonce'] ) ) {
	return $post_id;
	}
	vu_dbg('save_post_2',wp_verify_nonce( $_POST['vu_post_cgfp_nonce'], 'vu_post_cgfp_save' ));
	if ( ! wp_verify_nonce( $_POST['vu_post_cgfp_nonce'], 'vu_post_cgfp_save' ) ) {
	return $post_id;
	}
	vu_dbg('save_post_verified');
	// Check that the logged in user has permission to edit this post
	if ( ! current_user_can( 'edit_post' ) ) {
	return $post_id;
	}

	//get frontend's specified user group and update
	 vu_dbg('VU_USER_GROUP: ', get_terms( VU_USER_GROUP));	

	$user_id = get_current_user_id();
	$new_ug = (int) $_POST['vu_cgfp_value']; // calling sanitize_key() is unecessary only because of the cast to int
	 vu_dbg('$new_ug',$new_ug);

	if( wp_set_object_terms( $post_id, array($new_ug), VU_USER_GROUP ) ){
		//set user's default user group to whatever they decided to use here
		update_user_meta( $user_id, VU_USER_PRIMARY_UG, get_term($new_ug, VU_USER_GROUP)->name );

		if(VU_RESTRICT_DEBUG_LEVEL(4)) vu_dbg("Successfully updated post $post_id's vu_user_group data entry to: ".print_r(wp_get_object_terms($post_id, VU_USER_GROUP),true).
		"\n<br>User vu_user_primary_ug meta has been updated to: ".print_r(get_user_meta($user_id, VU_USER_PRIMARY_UG), true));
		return;
	}
}