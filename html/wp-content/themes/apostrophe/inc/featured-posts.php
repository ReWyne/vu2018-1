<?php
	/**
 * @package Apostrophe
 *
 * Featured post functionality
 */

/**
 * Looks up featured posts via a filter or uses ones provided by Jetpack.
 *
 * @return WP_Query
 */
function apostrophe_get_featured_posts() {
	$featured_posts = array();

	$jetpack_featured_posts = apply_filters( 'apostrophe_get_featured_posts', false );

	if ( ! empty( $jetpack_featured_posts ) ) {
		$featured_posts = array_map( 'absint', wp_list_pluck( $jetpack_featured_posts, 'ID' ) );
	}

	if ( empty( $featured_posts ) ) {
		return new WP_Query;
	}

	return new WP_Query( array(
		'post__in'            => $featured_posts,
		'posts_per_page'      => 2,
		'ignore_sticky_posts' => true,
	) );
}

/**
 * Featured Content should be included on the front page.
 * By default, Featured Content are excluded from the main query.
 * This leads to unexpected behaviour, since Apostrophe displays
 * Featured Content inline with other posts. If we want to include
 * those posts in our main query, they'll display as expected.
 */
function apostrophe_add_featured_content_to_blog() {
	remove_action( 'pre_get_posts', array( 'Featured_Content', 'pre_get_posts' ) );
}
add_action( 'init', 'apostrophe_add_featured_content_to_blog', 31 ); // Immediately after FC hooks in.


/**
 * Returns true if the given post is featured.
 *
 * @return bool Whether the given post is featured or not.
 */
function apostrophe_is_featured( $post_id = null ) {
	$post = get_post( $post_id );
	$featured = false;

	$term_id = apostrophe_get_jetpack_featured_content_term_id();
	if ( ! $term_id ) {
		return $featured;
	}

	$post_tags = wp_get_object_terms( $post->ID, 'post_tag' );

	if ( in_array( $term_id, wp_list_pluck( $post_tags, 'term_id' ) ) ) {
		$featured = true;
	}

	return $featured;
}

/*
 * Render an inline control for current post.
 * This allows the user to dynamically feature or unfeature a post from the UI,
 * and it's worth keeping since it makes it a great deal easier to build a
 * balanced-looking page.
 */
function apostrophe_inline_controls() {
	$post = get_post();

	// If the current user isn't able to edit posts, or if feature-starring isn't enabled as a theme option,
	// or if we're on the customizer, return early and don't show the feature-starring panel
	if ( ! current_user_can( 'edit_post', $post->ID ) OR
		   1 != get_theme_mod( 'apostrophe_enable_feature_starring' ) OR is_customize_preview() ) {
		return;
	}

	// Build URL to toggle the feature-star
	$toggle_featured_link = add_query_arg( array(
		'apostrophe_action' => 'toggle_featured',
		'apostrophe_post_id' => $post->ID,
	) );

	// Remove Infinite Scroll query if exists. If Infinite Scroll isn't enabled, this won't do anything.
	$toggle_featured_link = remove_query_arg( 'infinity', $toggle_featured_link );

	echo '<div class="apostrophe-inline-controls">';

	// Only if Featured Content is active and configured.
	if ( apostrophe_get_jetpack_featured_content_term_id() ) {
		printf( '<a href="%s" class="apostrophe-featured-toggle genericon genericon-star"></a>', esc_url( $toggle_featured_link ) );
	}
	printf( '<a href="%s" class="genericon genericon-edit"></a>', esc_url( get_edit_post_link( $post ) ) );
	echo '</div>';
}

/**
 * Fires during init, looks for a apostrophe_action.
 */
function apostrophe_inline_controls_handler() {
	if ( empty( $_GET['apostrophe_action'] ) || ! is_user_logged_in() ) {
		return;
	}

	$action = strtolower( esc_html( $_GET['apostrophe_action'] ) );
	if ( ! in_array( $action, array( 'toggle_featured' ) ) ) {
		return;
	}

	// Powered by Jetpack's Featured Content.
	if ( 'toggle_featured' === $action ) {
		if ( empty( $_GET['apostrophe_post_id'] ) ) {
			return; }

		$post_id = absint( $_GET['apostrophe_post_id'] );
		$post = get_post( $post_id );

		if ( ! current_user_can( 'edit_post', $post->ID ) ) {
			return;
		}

		// Only if the featured content tag has been set.
		$term_id = apostrophe_get_jetpack_featured_content_term_id();
		if ( ! $term_id ) {
			return;
		}

		// Toggle the featured content tag.
		if ( apostrophe_is_featured( $post->ID ) ) {
			wp_remove_object_terms( $post->ID, $term_id, 'post_tag' );
		} else {
			wp_set_object_terms( $post->ID, $term_id, 'post_tag', true );
		}

		if ( method_exists( 'Featured_Content', 'delete_transient' ) ) {
			Featured_Content::delete_transient();
		}

		$redirect_url = remove_query_arg( array( 'apostrophe_action', 'apostrophe_post_id' ) );
		$redirect_url .= sprintf( '#apostrophe-post-%d', $post->ID );

		wp_safe_redirect( esc_url_raw( $redirect_url ) );
	}
}
add_action( 'init', 'apostrophe_inline_controls_handler' );

/*
 * Gets the featured content by ID.
 */
function apostrophe_get_jetpack_featured_content_term_id() {
	if ( ! method_exists( 'Featured_Content', 'get_setting' ) ) {
		return 0;
	}
	$term = get_term_by( 'name', Featured_Content::get_setting( 'tag-name' ), 'post_tag' );
	if ( ! $term ) {
		return 0;
	}
	return $term->term_id;
}

