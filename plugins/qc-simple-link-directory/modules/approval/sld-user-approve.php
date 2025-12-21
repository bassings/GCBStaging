<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
class qc_sld_user_approval {

	/**
	 * The only instance of qc_sld_user_approval.
	 *
	 * @var qc_sld_user_approval
	 */
	private static $instance;

	/**
	 * Returns the main instance.
	 *
	 * @return qc_sld_user_approval
	 */
	 
	public static function instance() {
		if ( !isset( self::$instance ) ) {
			self::$instance = new qc_sld_user_approval();
		}
		return self::$instance;
	}

	private function __construct() {


		// Actions

		add_action( 'wp_loaded', array( $this, 'admin_loaded' ) );
		add_action( 'rightnow_end', array( $this, 'dashboard_stats' ) );
		add_action( 'user_register', array( $this, 'delete_qc_sld_transient' ), 11 );
		add_action( 'qc_sld_approve_user', array( $this, 'delete_qc_sld_transient' ), 11 );
		add_action( 'qc_sld_deny_user', array( $this, 'delete_qc_sld_transient' ), 11 );
		add_action( 'deleted_user', array( $this, 'delete_qc_sld_transient' ) );
		add_action( 'lostpassword_post', array( $this, 'lost_password' ) );
		add_action( 'user_register', array( $this, 'add_user_status' ) );
		add_action( 'qc_sld_approve_user', array( $this, 'approve_user' ), 10 );
		add_action( 'qc_sld_deny_user', array( $this, 'update_deny_status' ) );
		add_action( 'admin_init', array( $this, 'verify_settings' ) );
		add_action( 'wp_login', array( $this, 'login_user' ), 10, 2 );
		add_filter( 'wp_authenticate_user', array( $this, 'authenticate_user' ) );
		//add_filter( 'registration_errors', array( $this, 'show_user_pending_message' ) );
		add_filter( 'qc_sld_validate_status_update', array( $this, 'validate_status_update' ), 10, 3 );
		add_filter( 'shake_error_codes', array( $this, 'failure_shake' ) );

	}



	public function get_plugin_url() {
		return plugin_dir_url( __FILE__ );
	}

	public function get_plugin_dir() {
		return plugin_dir_path( __FILE__ );
	}


	/**
	 * Verify settings upon activation
	 *
	 * @uses admin_init
	 */
	public function verify_settings() {
		// make sure the membership setting is turned on
		if ( get_option( 'users_can_register' ) != 1 ) {
			add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		}
	}

	/**
	 * Show admin notice if the membership setting is turned off.
	 */
	public function admin_notices() {
		$user_id = get_current_user_id();

		// update the setting for the current user
		if ( isset( $_GET['sld-settings-notice'] ) && '1' == $_GET['sld-settings-notice'] ) {
			add_user_meta( $user_id, 'qc_sld_user_approval_settings_notice', '1', true );
		}

		// Don't show the error if the s2member plugin is active
		if ( class_exists( 'c_ws_plugin__s2member_constants' ) ) {
			return;
		}

		// Check that the user hasn't already clicked to ignore the message
		if ( ! get_user_meta( $user_id, 'qc_sld_user_approval_settings_notice' ) ) {
			/*echo '<div class="error"><p>';
			printf( __( 'The Membership setting must be turned on in order for SLD User Approve to work correctly. <a href="%1$s">Update in settings</a>. | <a href="%2$s">Hide Notice</a>', 'qc-opd' ), admin_url( 'options-general.php' ), add_query_arg( array( 'sld-settings-notice' => 1 ) ) );
			echo "</p></div>";*/
		}
	}

	/**
	 * Makes it possible to disable the user admin integration. Must happen after
	 * WordPress is loaded.
	 *
	 * @uses wp_loaded
	 */
	 
	public function admin_loaded() {
		$user_admin_integration = apply_filters( 'qc_sld_user_admin_integration', true );
		if ( $user_admin_integration ) {
			require_once( dirname( __FILE__ ) . '/sld-user-list.php' );
		}

	}

	/**
	 * Get the status of a user.
	 *
	 * @param int $user_id
	 * @return string the status of the user
	 */
	 
	public function get_user_status( $user_id ) {
		$user_status = get_user_meta( $user_id, 'sld_user_status', true );

		if ( empty( $user_status ) ) {
			$user_status = 'approved';
		}

		return $user_status;
	}

	/**
	 * Update the status of a user. The new status must be either 'approve' or 'deny'.
	 *
	 * @param int $user
	 * @param string $status
	 *
	 * @return boolean
	 */
	public function update_user_status( $user, $status ) {
		$user_id = absint( $user );
		if ( !$user_id ) {
			return false;
		}

		if ( !in_array( $status, array( 'approve', 'deny' ) ) ) {
			return false;
		}

		$do_update = apply_filters( 'qc_sld_validate_status_update', true, $user_id, $status );
		if ( !$do_update ) {
			return false;
		}
		
		// where it all happens
		do_action( 'qc_sld_' . $status . '_user', $user_id );
		do_action( 'qc_sld_user_status_update', $user_id, $status );

		return true;
	}

	public function get_valid_statuses() {
		return array( 'pending', 'approved', 'denied' );
	}


	public function validate_status_update( $do_update, $user_id, $status ) {
		$current_status = qc_sld_user_approval()->get_user_status( $user_id );

		if ( $status == 'approve' ) {
			$new_status = 'approved';
		} else {
			$new_status = 'denied';
		}

		if ( $current_status == $new_status ) {
			$do_update = false;
		}

		return $do_update;
	}

	/**
	 * The default message that is shown to a user depending on their status
	 * when trying to sign in.
	 *
	 * @return string
	 */
	public function default_authentication_message( $status ) {
		$message = '';

		if ( $status == 'pending' ) {
			$message = __( '<strong>ERROR</strong>: Your account is still pending approval.', 'qc-opd' );
			$message = apply_filters( 'qc_sld_pending_error', $message );
		} else if ( $status == 'denied' ) {
			$message = __( '<strong>ERROR</strong>: Your account has been denied access to this site.', 'qc-opd' );
			$message = apply_filters( 'qc_sld_denied_error', $message );
		}

		$message = apply_filters( 'qc_sld_default_authentication_message', $message, $status );

		return $message;
	}

	/**
	 * Determine if the user is good to sign in based on their status.
	 *
	 * @uses wp_authenticate_user
	 * @param array $userdata
	 */
	public function authenticate_user( $userdata ) {
		$status = $this->get_user_status( $userdata->ID );

		if ( empty( $status ) ) {
			// the user does not have a status so let's assume the user is good to go
			return $userdata;
		}

		$message = false;
		switch ( $status ) {
			case 'pending':
				$pending_message = $this->default_authentication_message( 'pending' );
				$message = new WP_Error( 'pending_approval', $pending_message );
				break;
			case 'denied':
				$denied_message = $this->default_authentication_message( 'denied' );
				$message = new WP_Error( 'denied_access', $denied_message );
				break;
			case 'approved':
				$message = $userdata;
				break;
		}

		return $message;
	}

	public function _get_user_statuses() {
		$statuses = array();

		foreach ( $this->get_valid_statuses() as $status ) {
			// Query the users table
			if ( $status != 'approved' ) {
				// Query the users table
				$query = array( 'meta_key' => 'sld_user_status', 'meta_value' => $status, );
				$wp_user_search = new WP_User_Query( $query );
			} else {
				// get all approved users and any user without a status
				$query = array( 'meta_query' => array( 'relation' => 'OR', array( 'key' => 'sld_user_status', 'value' => 'approved', 'compare' => '=' ), array( 'key' => 'sld_user_status', 'value' => '', 'compare' => 'NOT EXISTS' ), ), );
				$wp_user_search = new WP_User_Query( $query );
			}

			$statuses[$status] = $wp_user_search->get_results();
		}

		return $statuses;
	}
	/**
	 * Get a status of all the users and save them using a transient
	 */
	public function get_user_statuses() {
		$user_statuses = get_transient( 'qc_sld_user_statuses' );

		if ( false === $user_statuses ) {
			$user_statuses = $this->_get_user_statuses();
			set_transient( 'qc_sld_user_statuses', $user_statuses );
		}

		foreach ( $this->get_valid_statuses() as $status ) {
			$user_statuses[$status] = apply_filters( 'qc_sld_user_status', $user_statuses[$status], $status );
		}

		return $user_statuses;
	}


	public function delete_qc_sld_transient() {
		delete_transient( 'qc_sld_user_statuses' );
	}


	public function dashboard_stats() {
		$user_status = $this->get_user_statuses();
		?>
		<div>
			<p><span style="font-weight:bold;"><a
						href="<?php echo apply_filters( 'qc_sld_dashboard_link', 'users.php' ); ?>"><?php _e( 'Users', 'qc-opd' ); ?></a></span>:
				<?php foreach ( $user_status as $status => $users ) :
					print count( $users ) . " " . __( $status, 'qc-opd' ) . "&nbsp;&nbsp;&nbsp;";
				endforeach; ?>
			</p>
		</div>
	<?php
	}




	/**
	 * Determine whether a password needs to be reset.
	 *
	 * @return boolean
	 */
	public function do_password_reset( $user_id ) {
		$do_password_reset = true;

		$user_status = get_user_meta( $user_id, 'sld_user_status' );

		// if no status is set, don't reset password
		if ( empty( $user_status ) ) {
			$do_password_reset = false;
		}

		// if user has signed in, don't reset password
		$user_has_signed_in = get_user_meta( $user_id, 'qc_sld_user_approval_has_signed_in' );
		if ( $user_has_signed_in ) {
			$do_password_reset = false;
		}

		// for backward compatability
		$bypass_password_reset = apply_filters( 'qc_sld_bypass_password_reset', !$do_password_reset );

		return apply_filters( 'qc_sld_do_password_reset', !$bypass_password_reset );
	}

	/**
	 * Admin approval of user
	 *
	 * @uses qc_sld_approve_user
	 */
	public function approve_user( $user_id ) {

		$user = new WP_User( $user_id );

		wp_cache_delete( $user->ID, 'users' );
		wp_cache_delete( $user->data->user_login, 'userlogins' );
		
		// send email to user telling of approval
		$user_login = stripslashes( $user->data->user_login );
		$user_email = stripslashes( $user->data->user_email );

		// format the message
		$sld_lan_email_hi = sld_get_option('sld_lan_email_hi') != '' ? sld_get_option('sld_lan_email_hi') : __('Hi,', 'qc-opd');
		$sld_lan_email_welcome_to = sld_get_option('sld_lan_email_welcome_to') != '' ? sld_get_option('sld_lan_email_welcome_to') : __('Welcome to', 'qc-opd');
		$sld_lan_email_registration_subject = sld_get_option('sld_lan_email_registration_subject') != '' ? sld_get_option('sld_lan_email_registration_subject') : __(' Your registration has been approved successfully.', 'qc-opd');
		$sld_lan_email_click_to_login_text = sld_get_option('sld_lan_email_click_to_login_text') != '' ? sld_get_option('sld_lan_email_click_to_login_text') : __('Click the link to login in to your', 'qc-opd');
		$sld_lan_email_dashboard_text = sld_get_option('sld_lan_email_dashboard_text') != '' ? sld_get_option('sld_lan_email_dashboard_text') : __('dashboard', 'qc-opd');
		$sld_lan_email_regis_approved_text = sld_get_option('sld_lan_email_regis_approved_text') != '' ? sld_get_option('sld_lan_email_regis_approved_text') : __('Registration Approved!', 'qc-opd');
		
		$message  = __($sld_lan_email_hi) . "\r\n\r\n";
		$item = isset($item) ? $item : '';
		$message .= sprintf(__("<p>%s %s! %s</p>",'qc-opd'), $sld_lan_email_welcome_to, get_option('blogname'),$sld_lan_email_registration_subject, $item) . "\r\n\r\n";
		
		$message .= sprintf(__("<p> %s <a href='%s'>%s</a>.</p>",'qc-opd'), $sld_lan_email_click_to_login_text, qc_sld_login_page()->sldcustom_login_get_translated_option_page( 'sld_login_url',''), $sld_lan_email_dashboard_text ) . "\r\n";
		

		$subject = sprintf( __( '[%s] %s', 'qc-opd' ), get_option( 'blogname' ), $sld_lan_email_regis_approved_text );
		$subject = apply_filters( 'qc_sld_approve_user_subject', $subject );

		// send the mail
		wp_mail( $user_email, $subject, $message, $this->email_message_headers() );

		// change usermeta tag in database to approved
		update_user_meta( $user->ID, 'sld_user_status', 'approved' );

		//do_action( 'qc_sld_user_approved', $user );
	}


	/**
	 * Update user status when denying user.
	 *
	 * @uses qc_sld_deny_user
	 */
	public function update_deny_status( $user_id ) {
		$user = new WP_User( $user_id );

		// change usermeta tag in database to denied
		update_user_meta( $user->ID, 'sld_user_status', 'denied' );

		do_action( 'qc_sld_user_denied', $user );
	}

	public function email_message_headers() {
		$admin_email = get_option( 'admin_email' );
		if ( empty( $admin_email ) ) {
			$admin_email = 'support@' . $_SERVER['SERVER_NAME'];
		}

		$from_name = get_option( 'blogname' );

		$headers = array(
			"From: \"{$from_name}\" <{$admin_email}>\n",
			"Content-Type: text/html; charset=\"" . get_option( 'blog_charset' ) . "\"\n",
		);

		$headers = apply_filters( 'qc_sld_email_header', $headers );

		return $headers;
	}

	/**
	 * Display a message to the user after they have registered
	 *
	 * @uses registration_errors
	 */
	public function show_user_pending_message( $errors ) {
		if ( !empty( $_POST['redirect_to'] ) ) {
			// if a redirect_to is set, honor it
			wp_safe_redirect( $_POST['redirect_to'] );
			exit();
		}
		// if there is an error already, let it do it's thing
		if ( $errors->get_error_code() ) {
			return $errors;
		}

		$message = '';

		$errors->add( 'registration_required', $message, 'message' );
		$success_message = __( 'Registration successful.', 'qc-opd' );

		login_header( __( 'Pending Approval', 'qc-opd' ), '<p class="message register">' . $success_message . '</p>', $errors );
		login_footer();

		// an exit is necessary here so the normal process for user registration doesn't happen
		exit();
	}

	/**
	 * Only give a user their password if they have been approved
	 *
	 * @uses lostpassword_post
	 */
	public function lost_password() {
		$is_email = strpos( $_POST['user_login'], '@' );
		if ( $is_email === false ) {
			$username = sanitize_user( $_POST['user_login'] );
			$user_data = get_user_by( 'login', trim( $username ) );
		} else {
			$email = is_email( $_POST['user_login'] );
			$user_data = get_user_by( 'email', $email );
		}

		if ( $user_data->sld_user_status && $user_data->sld_user_status != 'approved' ) {
			wp_redirect( 'wp-login.php' );
			exit();
		}
	}


	/**
	 * Give the user a status
	 *
	 * @uses user_register
	 * @param int $user_id
	 */
	public function add_user_status( $user_id ) {

		if(isset($_REQUEST['sldregistration']) and $_REQUEST['sldregistration']=='sld'){
			$status = 'pending';
		}else{
			$status = 'approved';
		}
		// This check needs to happen when a user is created in the admin
		if ( isset( $_REQUEST['action'] ) && 'createuser' == $_REQUEST['action'] ) {
			$status = 'approved';
		}
	
		update_user_meta( $user_id, 'sld_user_status', $status );
	}

	/**
	 * Add error codes to shake the login form on failure
	 *
	 * @uses shake_error_codes
	 * @param $error_codes
	 * @return array
	 */
	public function failure_shake( $error_codes ) {
		$error_codes[] = 'pending_approval';
		$error_codes[] = 'denied_access';

		return $error_codes;
	}

	/**
	 * After a user successfully logs in, record in user meta. This will only be recorded
	 * one time. The password will not be reset after a successful login.
	 *
	 * @uses wp_login
	 * @param $user_login
	 * @param $user
	 */
	public function login_user( $user_login, $user = null ) {
		if ( $user != null && is_object( $user ) ) {
			if ( ! get_user_meta( $user->ID, 'qc_sld_user_approval_has_signed_in' ) ) {
				add_user_meta( $user->ID, 'qc_sld_user_approval_has_signed_in', time() );
			}
		}
	}
} // End Class

function qc_sld_user_approval() {

	if(sld_get_option('sld_enable_user_approval')=='off'){
		return qc_sld_user_approval::instance();
	}
}

add_action('after_setup_theme', 'qc_sld_user_approval', 10);



