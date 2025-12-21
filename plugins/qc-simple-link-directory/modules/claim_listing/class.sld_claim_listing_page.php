<?php 

//Custom Registration //
class sld_custom_registration_claim
{
	private static $instance;

	public static function instance() {
		if ( !isset( self::$instance ) ) {
			self::$instance = new sld_custom_registration_claim();
		}
		return self::$instance;
	}
	private function __construct()
	{

		add_shortcode( 'sld_claim_listing', array($this,'custom_registration_shortcode') );
		add_action( 'init', array($this,'claim_listing_form_handle') );
		add_role( 'slduser', __( 'SLD User' ), array( ) );
	}
	
	public function claim_listing_form_handle(){
		//session_start();
		if ( isset($_POST['submit'] ) && isset($_POST['sldclaimlisting']) ) {
			
			$this->registration_validation(
				$_POST['username'],
				$_POST['password'],
				$_POST['email'],
				$_POST['fname'],
				$_POST['lname']
				
			);
			 
			// sanitize user form input
			global $wpdb, $reg_errors, $username, $password, $email, $first_name, $last_name;
			$username   =   sanitize_user( $_POST['username'] );
			$password   =   esc_attr( $_POST['password'] );
			$email      =   sanitize_email( $_POST['email'] );
			
			$first_name =   sanitize_text_field( $_POST['fname'] );
			$last_name  =   sanitize_text_field( $_POST['lname'] );
		  
	 
			if ( 1 > count( $reg_errors->get_error_messages() ) ) {
				$userdata = array(
					'user_login'    =>   $username,
					'user_email'    =>   $email,
					'user_pass'     =>   $password,
					'first_name'    =>   $first_name,
					'last_name'     =>   $last_name,
					
				);
				
				$user = wp_insert_user( $userdata );
				wp_update_user( array ('ID' => $user, 'role' => 'slduser') ) ;
				$this->sld_new_user_notification($user, $password);
				
				
				// Inserting information
				$date = date('Y-m-d H:i:s');
				$table = $wpdb->prefix.'sld_claim_purchase';
				$wpdb->insert(
				    $table,
				    array(
					    'date'  => $date,
					    'user_id'   => $user,
					    'listid'   => $_POST['claim_list'],
					    'item'   => $_POST['claim_item'],
					    'status'   => 'pending',
					    
				    )
			    );
				$this->sld_claim_notification($user, $_POST['claim_item']);
				
				$dashboard = qc_sld_login_page()->sldcustom_login_get_translated_option_page( 'sld_dashboard_url','');
				
				$dashboard = esc_url( add_query_arg( 'action', 'claim', $dashboard ) );
				
				$creds = array();
				$creds['user_login'] = $username;
				$creds['user_password'] = $password;
				$creds['remember'] = true;

				$user = wp_signon( $creds, false );
				wp_safe_redirect( $dashboard );
				
				die();
				


			}
	 
	 
		}
	}
	
	public function sld_claim_notification( $user_id) {
        $user = new WP_User($user_id);

        $user_login = stripslashes($user->user_login);

        $sld_lan_email_claimed_list = sld_get_option('sld_lan_email_claimed_list') != '' ? sld_get_option('sld_lan_email_claimed_list') : __('A item has been claimed to your list', 'qc-opd');
        $sld_lan_email_claimed_list_msg = sld_get_option('sld_lan_email_claimed_list_msg') != '' ? sld_get_option('sld_lan_email_claimed_list_msg') : __('Please go to Simple Link Directory > Claimed Listing to view this claimed item.', 'qc-opd');
        $sld_lan_email_claimed_iem_by = sld_get_option('sld_lan_email_claimed_iem_by') != '' ? sld_get_option('sld_lan_email_claimed_iem_by') : __('Item Claimed By', 'qc-opd');

        $message  = sprintf(__('%s %s. %s'), $sld_lan_email_claimed_list, get_option('blogname'), $sld_lan_email_claimed_list_msg ) . "\r\n\r\n";
        $message .= sprintf(__('%s : %s'), $sld_lan_email_claimed_iem_by, $user_login) . "\r\n\r\n";


        @wp_mail(sld_get_option('sld_admin_email'), sprintf(__('[%s] %s!'), get_option('blogname'), $sld_lan_email_claimed_list ), $message);

    }
	
	public function custom_registration_shortcode() {
		wp_enqueue_script( 'qcopd-custom1-script'); //category tab
		wp_enqueue_script( 'qcopd-custom-script');
		wp_enqueue_script( 'qcopd-grid-packery');
		wp_enqueue_script( 'jq-slick.min-js');
		wp_enqueue_style( 'jq-slick-theme-css');
		wp_enqueue_style( 'jq-slick.css-css');
		wp_enqueue_script( 'qcopd-sldcustom-common-script');
		wp_enqueue_script( 'qcopd-sldcustom-2co-script');
		wp_enqueue_script( 'qcopd-custom-script-sticky');
		wp_enqueue_script('qcopd-embed-form-script');
		wp_enqueue_script( 'qcopd-tooltipster');
		wp_enqueue_script( 'qcopd-magpopup-js');
		wp_enqueue_style( 'sldcustom_login-css');
		wp_enqueue_style( 'qcopd-magpopup-css');
		wp_enqueue_style( 'sld-tab-css');
		wp_enqueue_style('qcopd-embed-form-css');
		wp_enqueue_style( 'qcopd-sldcustom-common-css');
		wp_enqueue_style( 'qcopd-custom-registration-css');
		wp_enqueue_style( 'qcopd-custom-rwd-css');
		wp_enqueue_style( 'qcopd-custom-css');
		wp_enqueue_style( 'qcfontawesome-css');
		wp_enqueue_style('qcopd-embed-form-css');
		wp_enqueue_script('qcopd-embed-form-script');
		ob_start();
		$this->custom_registration_function();
		return ob_get_clean();
	}
	
	public function custom_registration_function() {
		
	 
		$this->registration_form(
			@$username,
			@$password,
			@$email,
			@$first_name,
			@$last_name
		  
			
			);
	}

	public function sld_new_user_notification( $user_id, $plaintext_pass = '' ) {
		$user = new WP_User($user_id);

		$user_login = stripslashes($user->user_login);
		$user_email = stripslashes($user->user_email);

		$sld_lan_email_new_user_registra = sld_get_option('sld_lan_email_new_user_registra') != '' ? sld_get_option('sld_lan_email_new_user_registra') : __('New user registration on your blog', 'qc-opd');
		$sld_lan_email_username = sld_get_option('sld_lan_email_username') != '' ? sld_get_option('sld_lan_email_username') : __('Username', 'qc-opd');
		$sld_lan_email_email_text = sld_get_option('sld_lan_email_email_text') != '' ? sld_get_option('sld_lan_email_email_text') : __('E-mail', 'qc-opd');
		$sld_lan_email_new_msg_register_text = sld_get_option('sld_lan_email_new_msg_register_text') != '' ? sld_get_option('sld_lan_email_new_msg_register_text') : __('New User Registration', 'qc-opd');

		$message  = sprintf(__('%s %s:','qc-opd'), $sld_lan_email_new_user_registra, get_option('blogname')) . "\r\n\r\n";
		$message .= sprintf(__('%s: %s','qc-opd'), $sld_lan_email_username, $user_login) . "\r\n\r\n";
		$message .= sprintf(__('%s: %s','qc-opd'), $sld_lan_email_email_text, $user_email) . "\r\n";
		if(sld_get_option('sld_admin_email')!=''){
			@wp_mail(sld_get_option('sld_admin_email'), sprintf(__('[%s] %s','qc-opd'), get_option('blogname'), $sld_lan_email_new_msg_register_text), $message);
		}
		

		if ( empty($plaintext_pass) )
			return;

		$sld_lan_email_hi = sld_get_option('sld_lan_email_hi') != '' ? sld_get_option('sld_lan_email_hi') : __('Hi,', 'qc-opd');
		$sld_lan_email_welcome_to = sld_get_option('sld_lan_email_welcome_to') != '' ? sld_get_option('sld_lan_email_welcome_to') : __('Welcome to', 'qc-opd');
		$sld_lan_email_how_login_text = sld_get_option('sld_lan_email_how_login_text') != '' ? sld_get_option('sld_lan_email_how_login_text') : __('Here\'s how to log in', 'qc-opd');
		$sld_lan_email_u_p_text = sld_get_option('sld_lan_email_u_p_text') != '' ? sld_get_option('sld_lan_email_u_p_text') : __('Your username and password', 'qc-opd');

		$message  = __($sld_lan_email_hi) . "\r\n\r\n";
		$message .= sprintf(__("%s %s! %s:",'qc-opd'), $sld_lan_email_welcome_to, get_option('blogname'), $sld_lan_email_how_login_text) . "\r\n\r\n";
		$message .= qc_sld_login_page()->sldcustom_login_get_translated_option_page( 'sld_login_url','') . "\r\n";
		$message .= sprintf(__('%s: %s','qc-opd'), $sld_lan_email_username, $user_login) . "\r\n";
		//$message .= sprintf(__('Password: %s','qc-opd'), $plaintext_pass) . "\r\n\r\n";
		/*if(sld_get_option('sld_admin_email')!=''){
			$message .= sprintf(__('If you have any stuck, please contact webmaster at %s.'), sld_get_option('sld_admin_email')) . "\r\n\r\n";
		}*/
		

		wp_mail($user_email, sprintf(__('[%s] %s','qc-opd'), get_option('blogname'), $sld_lan_email_u_p_text ), $message);

	}




	public function registration_validation( $username, $password, $email, $first_name, $last_name)  {
		global $reg_errors;
		$reg_errors = new WP_Error;

		
		if ( empty( $username ) || empty( $password ) || empty( $email ) ) {
			$reg_errors->add('field', __('Required form field is missing','qc-opd'));
		}elseif( 4 > strlen( $username ) ){
			$reg_errors->add( 'username_length', __('Username too short. At least 4 characters is required','qc-opd') );
		}elseif( 5 > strlen( $password ) ){
			$reg_errors->add( 'password', __('Password length must be greater than 5','qc-opd') );
		}elseif( !is_email( $email ) ){
			$reg_errors->add( 'email_invalid', __('Email is not valid','qc-opd') );
		}elseif( email_exists( $email ) ){
			$reg_errors->add( 'email', __('Email Already in use','qc-opd') );
		}elseif( ! validate_username( $username ) ){
			 $reg_errors->add( 'username_invalid', __('Sorry, the username you entered is not valid','qc-opd') );
		}elseif(isset($_POST['ccode']) && strtolower($_POST['ccode'])!==strtolower($_SESSION['captcha']['code'])){
		    $reg_errors->add('captcha_invalid', __('Captcha does not match!','qc-opd'));
        }

		if ( username_exists( $username ) )
			$reg_errors->add('user_name', __('Sorry, that username already exists!','qc-opd') );



		/*if ( is_wp_error( $reg_errors ) ) {
		 
			foreach ( $reg_errors->get_error_messages() as $error ) {
			 
				echo '<div style="color: red;border: 1px solid #e38484;margin: 2px;padding: 2px;text-align: center;margin-bottom: 8px;">';
				echo '';
				echo $error . '<br/>';
				echo '</div>';
				 
			}
		 
		}*/
	}

	
	public function registration_form( $username, $password, $email, $first_name, $last_name ) {
		global $reg_errors;
		if ( is_wp_error( $reg_errors ) ) {
		 
			foreach ( $reg_errors->get_error_messages() as $error ) {
			 
				echo '<div style="color: red;border: 1px solid #e38484;margin: 2px;padding: 2px;text-align: center;margin-bottom: 8px;">';
				echo '';
				echo $error . '<br/>';
				echo '</div>';
				 
			}
		 
		}

		if(!is_user_logged_in()){
		
			$_SESSION['captcha'] = sld_simple_php_captcha();
			
			
		 
			if(sld_get_option('sld_lan_already_account')!=''){
				$srcplaceholder = sld_get_option('sld_lan_already_account');
			}else{
				$srcplaceholder = __('Already Have an Account?', 'qc-opd');
			}

			if( trim(sld_get_option('sld_lan_first_name')) != '' ){
	        	$firstname = sld_get_option('sld_lan_first_name');
	        }else{
	        	$firstname = __( 'First Name', 'qc-opd' );
	        }

	        if( trim(sld_get_option('sld_lan_last_name')) != '' ){
	        	$lastname = sld_get_option('sld_lan_last_name');
	        }else{
	        	$lastname = __( 'Last Name', 'qc-opd' );
	        }
		 
			echo ' <div style="margin: 10px auto;max-width: 500px;text-align:right"><a href="'.qc_sld_login_page()->sldcustom_login_get_translated_option_page( 'sld_login_url','').'">'.$srcplaceholder.'</a></div>
			<div class="cleanlogin-container">	<form autocomplete="off" class="cleanlogin-form" action="' . $_SERVER['REQUEST_URI'] . '" method="post" id="registration_form_sld">
			
			<h2>'. __( 'Account Info', 'qc-opd' ).'</h2>
			<fieldset><div class="cleanlogin-field">
			<input class="cleanlogin-field-username" type="text" name="fname" id="sldfname" placeholder="'. $firstname.' *" value="' . ( isset( $_POST['fname']) ? $first_name : null ) . '" required>
			<i class="fa fa-user"></i>
			</div></fieldset>
			 
			<fieldset><div class="cleanlogin-field">
		   
			<input class="cleanlogin-field-username" placeholder="'. $lastname .' *" type="text" name="lname" id="sldlname" value="' . ( isset( $_POST['lname']) ? $last_name : null ) . '" required>
			<i class="fa fa-user"></i>
			</div></fieldset>


			<fieldset><div class="cleanlogin-field">
			<input class="cleanlogin-field-username" placeholder="'. __( 'Username', 'qc-opd' ).' *" type="text" name="username" id="sldusername" value="' . ( isset( $_POST['username'] ) ? $username : null ) . '" required>
			<i class="fa fa-user"></i>
			</div></fieldset>
			
			<fieldset><div class="cleanlogin-field"><input type="password" style="display: none;" />
			<input class="cleanlogin-field-username" placeholder="'. __( 'Password', 'qc-opd' ).' *" type="password" name="password" id="sldpassword" value="' . ( isset( $_POST['password'] ) ? $password : null ) . '" required>
			<i class="fa fa-lock"></i>
			</div></fieldset>
			
			<fieldset><div class="cleanlogin-field">
			<input class="cleanlogin-field-username" placeholder="'. __( 'Email', 'qc-opd' ).' *" type="email" name="email" id="sldemail" value="' . ( isset( $_POST['email']) ? $email : null ) . '" required><input type="text" style="display: none;" />
			<i class="fa fa-envelope"></i>
			</div></fieldset>
			';
			echo '<h2>'. __( 'List Info', 'qc-opd' ).'</h2>';
			?>
			<fieldset><div class="cleanlogin-field">
			<label><?php echo __('Select List Name','qc-opd'); ?></label>
			<select style="" id="sld_claim_list" name="claim_list" required>
				<option value="">None</option>
				<?php 
				$list_args_total = array(
					'post_type' => 'sld',
					'posts_per_page' => -1,
				);
				$list_query = new WP_Query( $list_args_total );
				while ( $list_query->have_posts() )
				{
					$list_query->the_post();
					echo '<option value="'.get_the_ID().'">'.get_the_title().'</option>';
				}
				?>
			</select>
			</div></fieldset>
			 
			<fieldset><div class="cleanlogin-field">
		   <label><?php echo __('Select Item Name','qc-opd'); ?></label>
			<select style="" id="sld_list_item" name="claim_item" required>
				<option value="">None</option>
			</select>
			
			</div></fieldset>
			
			
			
			<?php
			if(sld_get_option( 'sld_enable_captcha')=='on'){
				echo '<fieldset><div class="cleanlogin-field">
				<img src="'.($_SESSION['captcha']['image_src']).'" alt="Captcha Code" id="sld_captcha_image" />
				<img style="width: 24px;cursor:pointer;" id="captcha_reload" src="'.SLD_QCOPD_IMG_URL.'/captcha_reload.png" />
				<input class="cleanlogin-field-username" placeholder="'. __( 'Code', 'qc-opd' ).'" type="text" name="ccode" id="sldcode" value="" required>
				</div></fieldset>
				 ';
			}

			
			echo '<fieldset style="    text-align: center; padding: 0px !important;"><div style="margin-top: 16px;margin-bottom: 0px;" class="cleanlogin-field">
			<input type="hidden" name="sldclaimlisting" value="sld"/>
			<input type="submit" class="submit_registration" name="submit" value="'. __( 'Claim List', 'qc-opd' ).'"/>
			</div></fieldset>
			
			</form></div>
			';
		}else{
			$dashboard = qc_sld_login_page()->sldcustom_login_get_translated_option_page( 'sld_dashboard_url','');
			echo 'You are already logged in. <a href="'.$dashboard.'?action=claim'.'">Click here</a> to go to claim listing page';
		}
	}
	
}

function sld_custom_registration_claim() {
	return sld_custom_registration_claim::instance();
}
sld_custom_registration_claim();

 