<?php


// error_log("vu-users-permissions-ajax accessing");

// defined( 'ABSPATH' ) or die(); //exit if accessed directly

// include 'vu-permissions.php';
// error_log("vu-users-permissions-ajax ERROR_LOG");
// vu_debug("vu-users-permissions-ajax.php ","",$_POST); // print
// if (isset($_POST['action'])) {
//     switch ($_POST['action']) {
//         case 'vu_augt_submit':
//             vu_augt_submit();
//             break;
//     }
// }
// else{
//     vu_debug("vu-users-permissions-ajax.php failed to get action");
// }

// /**
//  * Save the submitted contents from the alter_user_group_taxonomy meta box
//  * Note that the role associated with each term is stored in a separate table on the database, user_group_to_role
//  * @param  none
//  * @return none
//  */
// //notes to self: return intelligent result when nothing was passed, validation fails, or duplicate exists
// function vu_augt_submit() {
//     vu_debug( "The vu_augt_submit function is called.");
//     if (isset($_POST['group']) && isset($_POST['group'])) {
//         // //only save meta value if hitting submit
//         // if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ){
//         //     return $post_id;  
//         // }
//         $_POST['vu_augt_return'] = "Error: Permissions validation failed";
//         // Check if nonce is set
//         if ( ! isset( $_POST['vu_augt_nonce'] ) ) {
//             return $_POST;
//         }
    
//         if ( ! wp_verify_nonce( $_POST['vu_augt_nonce'], 'vu_augt_save' ) ) {
//             return $_POST;
//         }
    
//         // Check that the logged in user has permission to mess with permissions data
//         if ( ! current_user_can( 'promote_users' ) ) {
//             return $_POST;
//         }
    
//         // This is the actual inserting part
//         // insert term
//         $vu_augt_value = sanitize_key( $_POST['group'] );

//         if ( ! vu_term_exists( $vu_augt_value, 'vu_user_group' ) ){
//             $_POST['vu_augt_return'] = "Successully inserted term: " . print_r(wp_insert_term( $vu_augt_value, 'vu_user_group' ), true);
//         }
//         else {
//             $t = print_r(wp_insert_term( $vu_augt_value, 'vu_user_group' ), true);
//             $_POST['vu_augt_return'] = "WARNING: Term $t was replaced. This may change the roles (permissions) of existing users";
//         }

//         // fun backend stuff
//         // IMPORTANT: 'administrator' is both a role and a protected term in the vu_user_group taxonomy
//         // You're not allowed to modify it because otherwise you could lock all admins out of being able to modify the site.
//         if($vu_augt_value === vu_permission_level::Admin){
//             $_POST['vu_augt_return'] = 'Error: Modifying the '.vu_permission_level::Admin.' group is prohibited';
//             exit;
//         }
//         vu_db_replace_ug2r_data($vu_augt_value, $_POST['role']);
//         // //update_post_meta( $post_id, 'vu_alter_usr_grp_tax_url_value', $vu_alter_usr_grp_tax_url_value );
//     }
//     else{
//         $_POST['vu_augt_return'] = "Error: Nothing submitted";
//     }
//     exit;
// }