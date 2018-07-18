<?php

defined( 'ABSPATH' ) or die(); //exit if accessed directly

//include_once dirname( __FILE__ ) . '/vu-permissions.php';


/**
 * Create dialog box for adding/removing user groups from a user.
 * Note that this may result in changing the user's role.
 * The additional user metadata vu_my_ugs_array replicates a Set. Arranged like so: array("my_first_group"=>true, "my_second_group"=>true, ... )
 * @param  none
 * @return none
 */

add_action( 'show_user_profile', 'vu_show_extra_profile_fields' );
add_action( 'edit_user_profile', 'vu_show_extra_profile_fields' );

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
			$all_user_groups = get_terms( array(
				'taxonomy' => 'vu_user_group',
				'hide_empty' => false,  ) );

			//get our array of the user's user groups
			vu_debug('\$user->ID: ','',$user->ID);
			vu_debug('\get_the_author_meta( "vu_my_ugs_array", $user->ID ): ','',get_the_author_meta( 'vu_my_ugs_array', $user->ID ));
			$my_user_groups = json_decode( get_the_author_meta( 'vu_my_ugs_array', $user->ID ), false );
			if($my_user_groups === NULL){$my_user_groups = array();}
			vu_debug("\$my_user_groups: ",'',$my_user_groups);
			foreach($all_user_groups as $term_object){ //Note: in_array runs in [length of array] time; switch to key => value method for O(1) lookup if this is an issue
				vu_debug('\$term_object: ','',$term_object);
				vu_debug('\$term_object["term_id"]: ','',$term_object->term_id);
				vu_debug('<input type="checkbox" name="vu_cgfu_checkbox[]" value="'.$term_object->name.'" '.( array_key_exists($term_object->name, $my_user_groups) ? 'checked' : '  ' ).'>'.$term_object->name.'<br>');
				echo '<input type="checkbox" name="vu_cgfu_checkbox[]" value="'.$term_object->name.'" '.( array_key_exists($term_object->name, $my_user_groups) ? 'checked' : '  ' ).'>'.$term_object->name.'<br>';
			}
			?>
			<p class="description">Change which groups this user is a member of. WARNING: this may change the user's role (permissions)!</p>
			<span id="vu_cgfu_return" class="vu-ajax-return" style="font-family:monospace; color:red; white-space:pre"></span>
			</td>

		</tr>
	</table>
	<?php 
}
<<<'EOT'
<input type="text" name="User Groups" id="vu_cgfu_title" value="<?php echo esc_attr( get_the_author_meta( 'twitter', $user->ID ) ); ?>" class="regular-text" /><br />
EOT;

/**
 * Save user groups added/removed from a user.
 * Note that this may result in changing the user's role.
 * @param  $user_id
 * @return none
 */
add_action( 'personal_options_update', 'vu_change_groups_for_user_process_request' );
add_action( 'edit_user_profile_update', 'vu_change_groups_for_user_process_request' );
function vu_change_groups_for_user_process_request( $user_id ) {
	vu_debug("vu_alter_user_group_taxonomy_process_request \$_POST: ",'',$_POST);
	
	// if ( !current_user_can( vu_permission_level::Admin, $user_id ) )
	// 	return $user_id;

	// vu_debug("vu_cgfu verifying nonce...");

	// // Nonce validating code here 
	// if ( ! wp_verify_nonce( $_POST['vu_cgfu_nonce'], 'vu_cgfu_save' ) ) {
	// 	return $user_id;
	//   }
	// vu_debug("Verified!");

	// // get checkbox data from frontend and process it into a Set
	// $frontend_array = $_POST['vu_cgfu_checkbox']; //value-only array

	// // properly format array to go array('group'=>true, ...) instead of array('group', ...) for dat O(1) lookup
	// $new_ugs_array = array();
	// foreach($frontend_array as $group){
	// 	$new_ugs_array["$group"] = true;
	// }
	// vu_debug('\$new_ugs_array: ','',$new_ugs_array);
	// /* Copy and paste this line for additional fields. Make sure to change 'twitter' to the field ID. */
	// update_user_meta( $user_id, 'vu_my_ugs_array', json_encode($new_ugs_array) );

	// echo "Successfully updated user's vu_my_ugs_array data entry to: ".json_encode($new_ugs_array).
	// "\nUser role has been updated to: "/*TODO*/;
	// wp_die();
}