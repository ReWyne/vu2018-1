<?php

defined( 'ABSPATH' ) or die(); //exit if accessed directly

//include_once dirname( __FILE__ ) . '/vu-permissions.php';


/**
 * Create dialog box for adding/removing user groups from a user.
 * Note that this may result in changing the user's role.
 * @param  none
 * @return none
 */

add_action( 'show_user_profile', 'vu_show_extra_profile_fields' );
add_action( 'edit_user_profile', 'vu_show_extra_profile_fields' );
add_action( 'user_new_form', 'vu_show_extra_profile_fields' );
function vu_show_extra_profile_fields( $user ) {
	if( ! current_user_can(vu_permission_level::Admin, $user->ID)){return;}
	?>
	<h3>User Group Management (Admin Only)</h3>

	<table class="form-table">
		<?php wp_nonce_field( 'vu_cgfu_save', 'vu_cgfu_nonce' ) ?>
		<tr>
			<th><label for="vu_cgfu_checkboxes">User Groups</label></th>
			<td>
			<?php
			$all_user_groups = vu_get_real_terms( array(
				'taxonomy' => VU_USER_GROUP,
				'hide_empty' => false,  ) );
			
			//get our array of the user's user groups
			$my_user_groups = vu_get_real_object_terms($user->ID, VU_USER_GROUP);
			vu_debug('\$my_user_groups: ','',$my_user_groups);

			// parse $my_user_groups into a nice {"my_first_group"=>true, "my_second_group"=>true, ... } format to replicate a Set; avoids n^2 runtime and probably a bit easier to read
			$my_parsed_ugs = vu_terms_array_to_set( $my_user_groups, "name" );
			vu_debug('\$my_parsed_ugs: ','',$my_parsed_ugs);

			foreach($all_user_groups as $term_object){ //Note: in_array runs in [length of array] time; switch to key => value method for O(1) lookup if this is an issue
				echo '<input type="checkbox" name="vu_cgfu_checkbox[]" value="'.$term_object->term_id.'" '.( array_key_exists($term_object->name, $my_parsed_ugs) ? 'checked' : '  ' ).'>'.$term_object->name.'<br>';
			}
			?>
			<p class="description">Change which groups this user is a member of. WARNING: this may change the user's role (permissions)!</p>
			<span id="vu_cgfu_return" class="vu-ajax-return" style="font-family:monospace; color:red; white-space:pre"></span>
			</td>

		</tr>
	</table>
	<?php 
}

/**
 * Save user groups added/removed from a user.
 * Note that this may result in changing the user's role.
 * @param  int $user_id
 * @return none
 */
add_action( 'personal_options_update', 'vu_change_groups_for_user_process_request',100 );
add_action( 'edit_user_profile_update', 'vu_change_groups_for_user_process_request',100 );
add_action('user_register', 'vu_change_groups_for_user_process_request',100);
function vu_change_groups_for_user_process_request( $user_id ) {
	 vu_dbg("vu_alter_user_group_taxonomy_process_request \$_POST: ",$_POST);
	
	if ( !current_user_can( vu_permission_level::Admin, $user_id ) )
		return $user_id;

	vu_dbg("vu_cgfu verifying nonce...");

	// Nonce validating code here 
	if ( ! wp_verify_nonce( $_POST['vu_cgfu_nonce'], 'vu_cgfu_save' ) ) {
		return $user_id;
	  }
	vu_debug("Verified!");

	// get checkbox data from frontend
	$frontend_array = $_POST['vu_cgfu_checkbox']; //value-only array
	array_map( function($a){ return (int)$a; }, $frontend_array ); // TODO: this line may be unnecessary
	vu_dbg("\$frontend_array + gettype([0]): ",$frontend_array, gettype($frontend_array[0]));

	// // properly format array to go array('group'=>true, ...) instead of array('group', ...) for O(1) lookup
	// $new_ugs_array = array();
	// foreach($frontend_array as $group){
	// 	$new_ugs_array["$group"] = true;
	// }

	// vu_debug('\$new_ugs_array: ','',$new_ugs_array);
	// update_user_meta( $user_id, 'vu_my_ugs_array', json_encode($new_ugs_array) );

	//update user group data
	wp_set_object_terms( $user_id, $frontend_array, VU_USER_GROUP );

	// echo "Successfully updated user's vu_my_ugs_array data entry to: ".json_encode($new_ugs_array).
	// "\nUser role has been updated to: "/*TODO*/;
	vu_dbg('get_role pre update',$user->roles);
	//update
	$new_role = vu_get_user_role($user_id);
	$user = get_user_by('id', $user_id);
	vu_dbg('\$user_cgfu',$user);
	$user->set_role($new_role);
	vu_dbg('get_role',$user->roles);
	// $user->set_role('administrator');
	// vu_dbg('get_role2',$user->roles);

	global $wp_roles;

	$all_roles = $wp_roles->roles;
	vu_dbg('all roles',$all_roles);

	if(VU_RESTRICT_DEBUG_LEVEL(5)) vu_dbg("Successfully updated user $user_id's vu_my_ugs_array data entry to: ".print_r(wp_get_object_terms($user_id, VU_USER_GROUP),true).
	"\nUser role has been updated to: $new_role");
}