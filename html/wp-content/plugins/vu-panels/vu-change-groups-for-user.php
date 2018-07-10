<?php

defined( 'ABSPATH' ) or die(); //exit if accessed directly

//include_once dirname( __FILE__ ) . '/vu-permissions.php';


/**
 * Create dialog box for adding/removing user groups from a user.
 * Note that this may result in changing the user's role.
 * @param  none
 * @return none
 */
if(curret_user_can(vu_permission_level::Admin)){
	add_action( 'show_user_profile', 'vu_show_extra_profile_fields' );
	add_action( 'edit_user_profile', 'vu_show_extra_profile_fields' );
}
function vu_show_extra_profile_fields( $user ) {
	?>
	<h3>User Group Management (Admin Only)</h3>

	<table class="form-table">
		<?php wp_nonce_field( 'vu_cgfu_save', 'vu_cgfu_nonce' ) ?>
		<tr>
			<th><label for="vu_cgfu_checkboxes">User Groups</label></th>
			<td>
			<?php
			$terms = get_terms( array(
				'taxonomy' => 'vu_user_group',
				'hide_empty' => false,  ) );
			foreach($terms as $term_object){
				echo '<input type="checkbox" name="checkboxvar[]" value="'.$term_object["term_id"].'">'.$term_object["name"].'<br>';
			}
			?>
			<span class="description">Change which groups this user is a member of. WARNING: this may change the user's role (permissions)!</span>
			<span id="vu_cgfu_return" style="font-family:monospace; color:red; white-space:pre"></span>
			</td>
				<input type="text" name="User Groups" id="vu_cgfu_title" value="<?php echo esc_attr( get_the_author_meta( 'twitter', $user->ID ) ); ?>" class="regular-text" /><br />
			

		</tr>

	</table>
	<?php 
}

/**
 * Save user groups added/removed from a user.
 * Note that this may result in changing the user's role.
 * @param  $user_id
 * @return none
 */
if(curret_user_can(vu_permission_level::Admin)){
	add_action( 'personal_options_update', 'vu_save_extra_profile_fields' );
	add_action( 'edit_user_profile_update', 'vu_save_extra_profile_fields' );
}
function vu_save_extra_profile_fields( $user_id ) {

	if ( !current_user_can( vu_permission_level::Admin, $user_id ) )
		return $user_id;

	vu_debug("vu_cgfu verifying nonce...");

	// TODO: nonce validating code here 
	if ( ! wp_verify_nonce( $_POST['vu_cgfu_nonce'], 'vu_cgfu_save' ) ) {
		return $user_id;
	  }
	vu_debug("Verified!");



	/* Copy and paste this line for additional fields. Make sure to change 'twitter' to the field ID. */
	update_usermeta( $user_id, 'twitter', $_POST['twitter'] );
}