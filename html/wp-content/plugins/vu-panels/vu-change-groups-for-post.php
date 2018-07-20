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
function add_vu_post_user_group_custom_fields() {
	add_meta_box( 'vu_user_group_meta_id', __( 'Department', 'vu-panels' ), 'vu_add_post_user_group_display', 'post', 'normal', 'high' );
//add_meta_box( string $id, string $title, callable $callback, string|array|WP_Screen $screen = null, string $context = 'advanced', string $priority = 'default', array $callback_args = null )
}

/**
 * Display the contents of the add_post_user_group meta box
 * This function deals with the vu_user_group taxonomy
 * @param  none
 * @return none
 */
function vu_add_post_user_group_display(){
	wp_nonce_field( 'vu_post_ug_save', 'vu_post_ug_nonce' );
	$value = get_post_meta(get_the_ID(), 'link_url_value', true);
	?>
	<label for="vu_post_ug">Group in charge of managing this post : </label>
	<select name="vu_cgfp_value" name="vu_cgfp_field">

		<?php
		//get all existing groups again
		//TODO: should only be allowed to make user groups they hav access to
		$user = wp_get_current_user(); //NOTE: $user_id global should be defined here, if you'd rather use that
		$available_user_groups;
		if( current_user_can(vu_permission_level::Admin, $user->ID) )
		{
			$available_user_groups = vu_get_real_terms( array(
				'taxonomy' => 'vu_user_group',
				'hide_empty' => false,  ) );				
		}
		else{
			$available_user_groups = vu_get_real_object_terms($user->ID, 'vu_user_group');
		}

		//and print
		foreach($available_user_groups as $term_object){ //Note: in_array runs in [length of array] time; switch to key => value method for O(1) lookup if this is an issue
			echo '<option name="vu_cgfu_checkbox[]"'.
				(/*TODO get first nonadmin user group returned for user*/VU_ADMIN_GROUP == $term_object->name ? 'selected="selected"`' : '' ).
				' value="'.$term_object->term_id.'" >'.$term_object->name.'<br>';
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
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ){
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

	//set user's default user group to whatever they decided to use here
	update_user_meta( $user_id, 'vu_user_primary_ug', $primary_ug );

	//$link_url_value = sanitize_text_field( $_POST['link_url_value'] );

	//update_post_meta( $post_id, 'link_url_value', $link_url_value );
}