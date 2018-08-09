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
	if(VU_RESTRICT_DEBUG_LEVEL(0)) vu_dbg('vu_show_extra_profile_fields, $user: ', $user);
	if (!isset($user)){ //when creating a new user, we don't have this parameter
		$user = new StdClass;
		$user->ID = 0;
	}
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
add_action( 'personal_options_update', 'vu_change_groups_for_user_process_request');
add_action( 'edit_user_profile_update', 'vu_change_groups_for_user_process_request');
add_action('user_register', 'vu_change_groups_for_user_process_request');
function vu_change_groups_for_user_process_request( $user_id ) {
	if(VU_RESTRICT_DEBUG_LEVEL(0)) vu_dbg("vu_alter_user_group_taxonomy_process_request \$_POST \$user_id: ", $_POST, $user_id);
	
	if ( !current_user_can( vu_permission_level::Admin, $user_id ) )
		return $user_id;

	// Nonce validating code here 
	if ( ! wp_verify_nonce( $_POST['vu_cgfu_nonce'], 'vu_cgfu_save' ) ) {
		return $user_id;
	  }
	// get checkbox data from frontend
	$frontend_array = array_key_exists('vu_cgfu_checkbox', $_POST) ? $_POST['vu_cgfu_checkbox'] : []; //value-only array
	array_map( function($a){ return (int)$a; }, $frontend_array ); // TODO: this line may be unnecessary

	//update user group data
	wp_set_object_terms( $user_id, $frontend_array, VU_USER_GROUP );

	if(VU_RESTRICT_DEBUG_LEVEL(0)) vu_dbg('get_role pre update',$user->roles);

	//update
	$new_role = vu_get_user_role($user_id);
	$user = get_user_by('id', $user_id);
	$user->set_role($new_role);
	$_POST['role'] = $new_role; //we should override whatever the previous value for the Role select was
	
	if(VU_RESTRICT_DEBUG_LEVEL(0)) vu_dbg('get_role post update',$user->roles);

	global $wp_roles;
	$all_roles = $wp_roles->roles;

	if(VU_RESTRICT_DEBUG_LEVEL(5)) vu_dbg("Successfully updated user $user_id's vu_my_ugs_array data entry to: ".print_r(wp_get_object_terms($user_id, VU_USER_GROUP),true).
	"\nUser role has been updated to: $new_role");
}