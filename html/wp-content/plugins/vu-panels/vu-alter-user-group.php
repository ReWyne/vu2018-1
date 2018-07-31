<?php

defined( 'ABSPATH' ) or die(); //exit if accessed directly

//include_once dirname( __FILE__ ) . '/vu-permissions.php';

/**
 * Display the contents of the alter_user_group_taxonomy meta box
 * Note that the role associated with each term is stored in a separate table on the database, user_group_to_role
 * @param  none
 * @return none
 */
add_action( 'manage_users_extra_tablenav', 'vu_alter_user_group_taxonomy_display' );
function vu_alter_user_group_taxonomy_display(){	
    global $pagenow;
    if( ! (is_admin() && $pagenow == 'users.php' && current_user_can(vu_permission_level::Admin))){
        return;
    }

	global $vu_alter_user_group_taxonomy_display_count;
		
	if(!isset($vu_alter_user_group_taxonomy_display_count)){ //manage_users_extra_tablenav hook shows up twice; we only want to add onto the second time
		$vu_alter_user_group_taxonomy_display_count = 1;
		return;
	}
	else{
		$vu_alter_user_group_taxonomy_display_count++;
	}
	//vu_debug("vaugt_display count: $vu_alter_user_group_taxonomy_display_count");
    
    // //#TEMP
    // vu_dbg("get_current_template()",get_current_template());

    ?>
  <div class="postbox container" style="margin-top:60px; padding:10px; padding-bottom:0px; clear:both;">
	<?php wp_nonce_field( 'vu_augt_save', 'vu_augt_nonce' ) ?>
	<label for="vu_augt_group"><b>User Group to add : </b></label>
    <input type="text" id="vu_augt_group_field" name="vu_augt_group_value" placeholder="Enter User Group" size="60">

    <p><label for="psw"><b>Group Permissions : </b></label>
	  <select name="vu_augt_role_value" id="vu_augt_role_select" class="postbox">';
	  <?php
      //generate options for our drop-down select
	  global $wp_roles;
		if ( ! isset( $wp_roles ) )
    		$wp_roles = new WP_Roles();

		// $t_all_roles = $wp_roles->get_names();
		// foreach($t_all_roles as $role){
		// 	echo '<option value="'.$role.'">'.$role.'</option>';
		// }

		$t_all_roles = $wp_roles->roles;
		foreach($t_all_roles as $key => $role){
			echo '<option value="'.$key.'">'.$role['name'].'</option>';
        }
        ?>
        </select>
    <button type="button" name="vu_augt_submit" value="vu_augt_submit" id="vu_augt_button" onclick="vu_alter_user_group_taxonomy_submit()">Submit</button>
	<span id="vu_augt_return" style="font-family:monospace; color:red; white-space:pre"></span>
  </div>
    <?php //button attr used instead of submit to prevent page reload without the js preventDefault() call
}

/**
 * Process an admin's request to alter the user group taxonomy
 * @param  none
 * @return none
 */
add_action('wp_ajax_vu_alter_user_group_taxonomy_process_request', 'vu_alter_user_group_taxonomy_process_request');
//vu_log("wp_ajax_vu_alter_user_group_taxonomy_process_request");
function vu_alter_user_group_taxonomy_process_request(){
    if ( isset($_POST['group']) ) {
        // //only save meta value if hitting submit
        // if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ){
        //     return $post_id;  
        // }

        $vu_augt_value = sanitize_key( $_POST['group'] );
        
        if($vu_augt_value == ''){
            echo 'Error: Please specify a group';
            wp_die();
        }

        $_POST['vu_augt_return'] = "Error: Permissions validation failed";
        // Check if nonce is set
        if ( ! isset( $_POST['vu_augt_nonce'] ) ) {
            return $_POST;
        }

        if ( ! wp_verify_nonce( $_POST['vu_augt_nonce'], 'vu_augt_save' ) ) {
            return $_POST;
        }

        // Check that the logged in user has permission to mess with permissions data
        if ( ! is_admin() || ! current_user_can( 'promote_users' ) ) {
            return $_POST;
        }

        if(is_numeric($vu_augt_value)){
            echo 'Error: group name cannot be a number';
            wp_die();
        }

        // IMPORTANT: 'administrator' is both a role and (in this case) a protected term in the vu_user_group taxonomy
        // You're not allowed to modify it because otherwise you could lock all admins out of being able to modify the site.
        if($vu_augt_value === VU_ADMIN_GROUP){
            echo 'Error: Modifying the '.VU_ADMIN_GROUP.' group is prohibited';
            wp_die();
        }

        // This is the actual inserting part
        // insert term
        if ( ! vu_term_exists( $vu_augt_value, VU_USER_GROUP ) ){
            $_POST['vu_augt_return'] = "Successully inserted term with return value: " . print_r(wp_insert_term( $vu_augt_value, VU_USER_GROUP ), true);
        }
        else {
            $t = print_r(wp_update_term( term_exists($vu_augt_value), VU_USER_GROUP ), true);
            $_POST['vu_augt_return'] = "WARNING: Term was replaced with return value: '''$t''' This may change the roles (permissions) of existing users";
        }

        // fun backend stuff
        vu_db_replace_ug2r_data($vu_augt_value, $_POST['role']);
        // //update_post_meta( $post_id, 'vu_alter_usr_grp_tax_url_value', $vu_alter_usr_grp_tax_url_value );
    }
    else{
        $_POST['vu_augt_return'] = "Error: Nothing submitted";
    }
    vu_dbg( $_POST['vu_augt_return'], get_terms(array(
         	'taxonomy' => 'vu_user_group',
         	'hide_empty' => false,  ) ));
        //example:
        //get_terms( array(
        // 	'taxonomy' => 'vu_user_group',
        // 	'hide_empty' => false,  ) ) );
    echo json_encode($_POST['vu_augt_return']);
    wp_die();
}







// /**
//  * Create dialog box for adding/removing terms from the vu_user_group.
//  * Note that the role associated with each term is stored in a separate table on the database, user_group_to_role
//  * @param  none
//  * @return none
//  */
// add_action( 'add_meta_boxes', 'vu_alter_user_group_taxonomy' );
// function vu_alter_user_group_taxonomy() {
// 	$screens = ['users'];
//     foreach ($screens as $screen) {
//         add_meta_box(
//             'vu_alter_user_group_taxonomy',           // Unique ID
//             __( 'Modfy vu_user_group Taxonomy', 'vu_textdomain' ),  // Box title
//             'vu_alter_user_group_taxonomy_display',  // Content callback, must be of type callable
// 			$screen,                   // screen to display on
// 			'normal', // display area
//         	'high' // display priority
//         );
//     }
// }

//add_action( 'add_meta_boxes', array($this,'add_link_custom_fields' )); //calls the function in this class





//  add_action( 'save_post', array($this,'save_link_url'));
// /**
//  * Save the submitted contents from the alter_user_group_taxonomy meta box
//  * Note that the role associated with each term is stored in a separate table on the database, user_group_to_role
//  * @param  none
//  * @return none
//  */
//   function save_vu_alter_usr_grp_tax_url( $post_id ) {
//     vu_log("save_vu_alter_usr_grp_tax_url");

//     //only save meta value if hitting submit
//     if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ){
//       return $post_id;  
//     }

//     // Check if nonce is set
//     if ( ! isset( $_POST['vu_alter_usr_grp_tax_url_nonce'] ) ) {
//       return $post_id;
//     }

//     if ( ! wp_verify_nonce( $_POST['vu_alter_usr_grp_tax_url_nonce'], 'vu_alter_usr_grp_tax_save' ) ) {
//       return $post_id;
//     }

//     // Check that the logged in user has permission to edit this post
//     if ( ! current_user_can( 'edit_post' ) ) {
//       return $post_id;
//     }

//     $vu_alter_usr_grp_tax_url_value = sanitize_text_field( $_POST['vu_alter_usr_grp_tax_url_value'] );

//     update_post_meta( $post_id, 'vu_alter_usr_grp_tax_url_value', $vu_alter_usr_grp_tax_url_value );
//   }