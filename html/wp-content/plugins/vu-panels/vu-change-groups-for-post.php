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
add_action( 'add_meta_boxes_post', 'add_vu_user_group_post_custom_fields');
function add_vu_user_group_post_custom_fields() {
	add_meta_box( 'vu_user_group_meta_id', __( 'Owned by group: ', 'vu-panels' ), 'add_vu_user_group_post_custom_field_display', 'post', 'normal', 'high' );
//add_meta_box( string $id, string $title, callable $callback, string|array|WP_Screen $screen = null, string $context = 'advanced', string $priority = 'default', array $callback_args = null )
}

//Display the contents of the custom meta box
function add_vu_user_group_post_custom_field_display(){
	vu_log("links_url_custom_field_display");
	wp_nonce_field( 'link_save', 'link_url_nonce' );
	$value = get_post_meta(get_the_ID(), 'link_url_value', true);
	echo '<label for="link_url">';
	echo 'URL for external link :';
	echo '</label> ';
	echo '<input type="text" id="link_url_field" name="link_url_value" value="' . esc_url( $value ) . '" size="60" />';
	}

add_action( 'save_post', 'save_link_url');
//Save the meta value entered
function save_link_url( $post_id ) {
	vu_log("save_link_url pID ", $post_id);

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

	$link_url_value = sanitize_text_field( $_POST['link_url_value'] );

	update_post_meta( $post_id, 'link_url_value', $link_url_value );
}