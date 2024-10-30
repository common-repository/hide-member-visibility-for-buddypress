<?php

/** 
 * Plugin Name:            Hide Member Visibility for BuddyPress
 * Description:            Hides BuddyPress Member Visibiity
 * Version:                1.0 
 * Author:                 Johnson Towoju (Figarts)
 * Author URI:             www.figarts.co
 * License:                GPL-2.0+
 * License URI:            http://www.gnu.org/licenses/gpl-2.0.txt
 */


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

function hmvbp_user_meta_box() {
 
	add_meta_box(
	    'metabox_id',
	    __( 'User Visibility', 'buddypress' ),
	    'hmvbp_user_inner_meta_box', // function that displays the contents of the meta box
	    get_current_screen()->id,
	    'side'
	);
}
add_action( 'bp_members_admin_user_metaboxes', 'hmvbp_user_meta_box' );

function hmvbp_user_inner_meta_box($user) {

        wp_nonce_field('wp_visibility_nonce', 'wp_visibility_nonce_field');
        $user_visibility = get_user_meta($user->ID,'user_visibility',true);
    ?>
    
    	<input name="user_visibility" id="user_visibility" type="checkbox" value="1" <?php checked('1', $user_visibility); ?> >
    	<label for="user_visibility">Hide User </label>
    	<?php
                 
}
function hmvbp_user_save_metabox() {

	$user_id = isset( $_GET['user_id'] ) ? $_GET['user_id'] : 0;

	if(!isset($_POST['wp_visibility_nonce_field'])){
            return $user_id;
        }

    // you will need to use a $_POST param and validate before saving
    $meta_val = isset( $_POST['user_visibility'] ) ? sanitize_text_field( $_POST['user_visibility'] ) : '';

    // the $meta_val would be a $_POST param from inner meta box form
    update_user_meta( $user_id, 'user_visibility', $meta_val );
}
add_action( 'bp_members_admin_update_user', 'hmvbp_user_save_metabox' );


/**
 * Exclude Users from BuddyPress Members List by WordPress role.
 *
 * @param array $args args.
 *
 * @return array
 */
function hmvbp_exclude_users_by_visibility( $args ) {
    // do not exclude in admin.
    if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
        return $args;
    }
 
    $excluded = isset( $args['exclude'] ) ? $args['exclude'] : array();
 
    if ( ! is_array( $excluded ) ) {
        $excluded = explode( ',', $excluded );
    }
 
    $user_ids = get_users( array( 'meta_key' => 'user_visibility', 'meta_value' => "1", 'meta_compare' => '=', 'fields' => 'ID') );

	$excluded = array_merge( $excluded, $user_ids);
 
    $args['exclude'] = $excluded;
 
    return $args;    
}
 
add_filter( 'bp_after_has_members_parse_args', 'hmvbp_exclude_users_by_visibility', 999);

function hmvbp_hide_user_profile() {
    global $bp;
    $user_id = $bp->displayed_user->id;
    $isVisible = get_user_meta( $user_id, 'user_visibility');

    if (!current_user_can( 'administrator' ) && bp_is_user() && !bp_is_my_profile() && $isVisible[0] > 0) {
        wp_redirect(home_url() . '/member');
        exit;
    }
}
add_action( 'wp', 'hmvbp_hide_user_profile');
