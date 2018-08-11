<?php

defined( 'ABSPATH' ) or die(); //exit if accessed directly

/**
 * This file allows users to filter posts by user group in edit.php
 */

define('VU_UG_COLUMN_KEY', 'user_groups');

/**
 * Add the User Group dropdown, for filtering displayed posts to a particular user group, to edit.php
 * @param  none
 * @return none
 */
add_action( 'restrict_manage_posts', 'vu_display_by_user_group_filter' );
function vu_display_by_user_group_filter() {
    global $typenow;
    global $wp_query;
    if ( $typenow == 'post' || $typenow == 'link' ) {
        $taxonomy = VU_USER_GROUP;
        $vu_ug_taxonomy = get_taxonomy( $taxonomy );
        vu_dbg('vu_display_by_user_group_filter',$wp_query->query);
        wp_dropdown_categories(array(
            'show_option_all' =>  __("Show All {$vu_ug_taxonomy->label}"),
            'taxonomy'        =>  $taxonomy,
            'name'            =>  'vu_user_group',
            'orderby'         =>  'name',
            //'selected'        =>  $wp_query->query['term'], // allows vu-fbug dropdown to show current term
            'hierarchical'    =>  false,
            'show_count'      =>  false, // Don't show # user groups in parens
            'hide_empty'      =>  true, // Hide posts w/o user groups
        ));
    }
}

/**
 * Add the User Group dropdown, for filtering displayed posts to a particular user group, to edit.php
 * @param  none
 * @return none
 */
add_filter( 'parse_query','convert_id_to_taxonomy_term_in_query' );
function convert_id_to_taxonomy_term_in_query( $query ) {
    global $pagenow; global $typenow; //actually needs pagenow
    $qv = &$query->query_vars;
    if(VU_RESTRICT_DEBUG_LEVEL(3)) vu_dbg('convert_id_to_taxonomy_term_in_query', $pagenow, $typenow, $qv);
    // If this is the query we're looking for
    if ( $pagenow=='edit.php' &&
        isset( $qv['taxonomy'] ) && $qv['taxonomy'] == VU_USER_GROUP) {
        if(VU_RESTRICT_DEBUG_LEVEL(3)) vu_dbg("Is VU_USER_GROUP query");
        // If we received the term ID, get the term slug instead
        if (isset( $qv['term'] ) && is_numeric( $qv['term']) ) {
            if(VU_RESTRICT_DEBUG_LEVEL(3)) vu_dbg("Converting vu_ug term ID to slug...");
            $term = get_term_by( 'id', $qv['term'], 'business' );
            $qv['term'] = $term->slug;
        }
    }
}

/**
 * Add the User Group column, for displaying the current user group of each post, to edit.php
 * @param  none
 * @return none
 */
add_action( 'manage_listing_posts_columns', 'display_ug_column_in_listing' );
//add_action( 'manage_listing_links_columns', 'display_ug_column_in_listing' );
function display_ug_column_in_listing( $posts_columns ) {
    // Insert the new User Group column after the Author column
    global $pagenow; global $typenow;
    vu_dbg('display_ug_column_in_listing', $pagenow, $typenow);
    if (isset($posts_columns['author'])) {
        $new_posts_columns = array();
        $index = 0;
        foreach($posts_columns as $key => $posts_column) {
            if ($key=='author')
                $new_posts_columns[VU_UG_COLUMN_KEY] = null;
            $new_posts_columns[$key] = $posts_column;
        }
    } else {
    // If someone removed the author column, just put it at the end
        $new_posts_columns = $posts_columns;
    }
    $new_posts_columns[VU_UG_COLUMN_KEY] = 'User Groups';
    return $new_posts_columns;
}

/**
 * Add the User Group column, for displaying the current user group of each post, to edit.php
 * @param  none
 * @return none
 */
add_action('manage_posts_custom_column', 'print_to_ug_column_in_listing',10,2);
//add_action('manage_links_custom_column', 'print_to_ug_column_in_listing',10,2);
function print_to_ug_column_in_listing( $column_id, $post_id ) {
    global $pagenow; global $typenow; //actually needs typenow
    vu_dbg('print_to_ug_column_in_listing', $pagenow, $typenow);
    if ( in_array($typenow, ['post', 'link']) ) {
        $taxonomy = VU_USER_GROUP;
        // Find our custom column
        // Example more advanced formatting: switch ( "{$typenow}:{$column_id}" ) { case 'link:vu_user_group': ...
        if( $column_id == VU_UG_COLUMN_KEY ){
            $user_groups = vu_get_real_terms($post_id, $taxonomy);
            // Get and insert our user groups (there should only be one, tho)
            if ( is_array($user_groups) ) { 
                foreach( $user_groups as $key => $ug ) {
                    $edit_link = get_term_link($ug, $taxonomy);
                    $user_groups[$key] = '<a href="'.$edit_link.'">' . $ug->name . '</a>';
                }
                //echo implode("<br/>",$user_groups);
                echo implode(', ', $user_groups);
            }
        }
    }
}