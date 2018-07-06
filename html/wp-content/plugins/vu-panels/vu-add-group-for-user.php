<?php

defined( 'ABSPATH' ) or die(); //exit if accessed directly

include_once dirname( __FILE__ ) . '/vu-permissions.php';


/**
 * Create dialog box for adding/removing user groups from a user.
 * Note that this may result in changing the user's role.
 * @param  none
 * @return none
 */
add_action( 'show_user_profile', 'vu_show_extra_profile_fields' );
add_action( 'edit_user_profile', 'vu_show_extra_profile_fields' );
function vu_show_extra_profile_fields( $user ) {
	?>
	<h3>Extra profile information</h3>

	<table class="form-table">

		<tr>
			<th><label for="twitter">Twitter</label></th>

			<td>
				<input type="text" name="twitter" id="twitter" value="<?php echo esc_attr( get_the_author_meta( 'twitter', $user->ID ) ); ?>" class="regular-text" /><br />
				<span class="description">Please enter your Twitter username.</span>
			</td>
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
add_action( 'personal_options_update', 'vu_save_extra_profile_fields' );
add_action( 'edit_user_profile_update', 'vu_save_extra_profile_fields' );
function vu_save_extra_profile_fields( $user_id ) {

	if ( !current_user_can( 'edit_user', $user_id ) )
		return $user_id;

	// TODO: nonce validating code here 

	/* Copy and paste this line for additional fields. Make sure to change 'twitter' to the field ID. */
	update_usermeta( $user_id, 'twitter', $_POST['twitter'] );
}