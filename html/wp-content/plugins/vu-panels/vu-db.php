<?php

defined( 'ABSPATH' ) or die(); //exit if accessed directly

global $vu_db_version;
$vu_db_version = '1.001';

/**
 * Update vu_db if vu_db_versione is new
 * @param  none
 * @return none
 */
function vu_update_db_check() {
    global $vu_db_version;
    $opt = get_site_option( 'vu_db_version' );
    if ( !$opt || $opt != $vu_db_version ) { //get_site_option returns false if option DNE
        vu_debug("Updating db version...");
        vu_db_install_ug2r();
    }
}
add_action( 'plugins_loaded', 'vu_update_db_check' );

/**
 * Create and/or update the user_group_to_role table
 * @param  none
 * @return none
 */
function vu_db_install_ug2r() {
	global $wpdb;
    global $vu_db_version;

    vu_log("vu_db_install_ug2r");

    $installed_ver = get_option( "vu_db_version" );

    if ( !$installed_ver || $installed_ver != $vu_db_version ) { //get_option returns false if option DNE

        $table_name = $wpdb->prefix . 'user_group_to_role';
        
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            tax_group varchar(100) NOT NULL,
            group_role tinytext NOT NULL,
            PRIMARY KEY  (tax_group)
        ) $charset_collate;";
    }

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

	$installed_ver ? update_option( 'vu_db_version', $vu_db_version ) : add_option( 'vu_db_version', $vu_db_version );
}

/**
 * Add an entry to the user_group_to_role table, overwriting any pre-existing entry
 * @param  string $tax_group, $group_role
 * @return none
 */
function vu_db_replace_ug2r_data($tax_group, $group_role) {
	global $wpdb;
	vu_debug("vu_db_replace_ug2r_data called with params: $tax_group, $group_role");
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
	global $wpdb;

    $table_name = $wpdb->prefix . 'user_group_to_role';

    $output = $wpdb->get_var( $wpdb->prepare( 
        "
            SELECT group_role 
            FROM $table_name 
            WHERE tax_group = %s
        ", 
        $tax_group
    ) );
    $escaped_output = esc_textarea($output);
    vu_log("vu_db_get_ug2r_role called with param $tax_group. Returned: $output escaped to $escaped_output");
    return $escaped_output;
}
