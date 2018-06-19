<?php

defined( 'ABSPATH' ) or die(); //exit if accessed directly

global $vu_db_version;
$vu_db_version = '1.0';

/**
 * Create and/or update the user_group_to_role table
 * @param  none
 * @return none
 */
function vu_db_install_ug2r() {
	global $wpdb;
	global $vu_db_version;

	$table_name = $wpdb->prefix . 'user_group_to_role';
	
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
		tax_group tinytext NOT NULL,
		group_role tinytext NOT NULL,
		PRIMARY KEY  (tax_group)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

	add_option( 'vu_db_version', $vu_db_version );
}

/**
 * Add an entry to the user_group_to_role table, overwriting any pre-existing entry
 * @param  string $tax_group, $group_role
 * @return none
 */
function vu_db_replace_ug2r_data($tax_group, $group_role) {
	global $wpdb;
	
	$table_name = $wpdb->prefix . 'user_group_to_role';
    $insert_data = array( 
        'tax_group' => $tax_group, 
        'group_role' => $group_role, 
    );
    
	$wpdb->replace( 
        $table_name, 
        $insert_data
    );
}

/**
 * Look up the role associated with a user group taxonomy term using the user_group_to_role table
 * @param  string $tax_group
 * @return string $group_role
 */
function vu_db_get_ug2r_role($tax_group) {

    $table_name = $wpdb->prefix . 'user_group_to_role';

    return $wpdb->get_var( $wpdb->prepare( 
        "
            SELECT group_role 
            FROM $table_name 
            WHERE tax_group = %s
        ", 
        $tax_group
    ) );
}
