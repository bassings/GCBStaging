<?php 

//Custom Registration //
class sld_custom_registration
{
	private static $instance;

	public static function instance() {
		if ( !isset( self::$instance ) ) {
			self::$instance = new sld_custom_registration();
		}
		return self::$instance;
	}
	private function __construct()
	{
		if( !is_admin() ){
			if( sld_get_option('sld_enable_captcha') == 'on' && sld_get_option( 'sld_enable_recaptcha')=='off' ){
				if(!session_id()){
					@session_start();
				}
			}
		}
		add_shortcode( 'sld_registration', array($this,'custom_registration_shortcode') );
		add_role( 'slduser', __( 'SLD User' ), array( ) );
	}
	
	public function custom_registration_shortcode() {
		wp_enqueue_script('qcld_google_recaptcha', 'https://www.google.com/recaptcha/api.js', array('jquery'), '1.0.0', true);
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
		
		
		
		if(!is_user_logged_in() && sld_get_option('sld_enable_add_new_item')=='on'){
			$this->custom_registration_function();
		}else{
			$dashboard = qc_sld_login_page()->sldcustom_login_get_translated_option_page( 'sld_dashboard_url','');
			/*echo sprintf(
				esc_html__('You are already logged in. %s Click here %s to enter your dashboard.', 'qc-opd'),
				'<a href="'.$dashboard.'">',
				'</a>'
			);*/
			
            echo sld_get_option('login_text_to_already_logged_in_lang') ? sld_get_option('login_text_to_already_logged_in_lang') : sprintf(__('You have to login to view this content. <a href="%s">Click Here</a> to log in.','qc-opd'),$dashboard);
		}
		
		return ob_get_clean();
	}
	
	public function custom_registration_function() {
		if ( isset($_POST['submit'] ) ) {
			
			$this->registration_validation(
				$_POST['username'],
				$_POST['password'],
				$_POST['email'],
				$_POST['fname'],
				$_POST['lname']
			);
			 
			// sanitize user form input
			global $username, $password, $email, $first_name, $last_name;
			$username   =   sanitize_user( $_POST['username'] );
			$password   =   esc_attr( $_POST['password'] );
			$email      =   sanitize_email( $_POST['email'] );
			
			$first_name =   sanitize_text_field( $_POST['fname'] );
			$last_name  =   sanitize_text_field( $_POST['lname'] );
		  
	 
			// call @function complete_registration to create the user
			// only when no WP_error is found
			$this->complete_registration(
				$username,
				$password,
				$email,
				$first_name,
				$last_name
			);
		}
	 
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
			@wp_mail(sld_get_option('sld_admin_email'), sprintf(__('[%s] %s','qc-opd'), get_option('blogname'), $sld_lan_email_new_msg_register_text ), $message);
		}
		

		if ( empty($plaintext_pass) )
			return;

		$sld_lan_email_hi = sld_get_option('sld_lan_email_hi') != '' ? sld_get_option('sld_lan_email_hi') : __('Hi,', 'qc-opd');
		$sld_lan_email_welcome_to = sld_get_option('sld_lan_email_welcome_to') != '' ? sld_get_option('sld_lan_email_welcome_to') : __('Welcome to', 'qc-opd');
		$sld_lan_email_how_login_text = sld_get_option('sld_lan_email_how_login_text') != '' ? sld_get_option('sld_lan_email_how_login_text') : __('Here\'s how to log in', 'qc-opd');
		$sld_lan_email_u_p_text = sld_get_option('sld_lan_email_u_p_text') != '' ? sld_get_option('sld_lan_email_u_p_text') : __('Your username and password', 'qc-opd');

		$message  = __($sld_lan_email_hi) . "\r\n\r\n";
		$message .= sprintf(__("%s %s! %s:",'qc-opd'), $sld_lan_email_welcome_to, get_option('blogname'), $sld_lan_email_how_login_text ) . "\r\n\r\n";
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
		}elseif(isset($_POST['ccode']) && base64_encode(strtolower($_POST['ccode']))!==base64_encode(strtolower($_SESSION['captcha']['code'])) ){
			if( sld_get_option( 'sld_enable_captcha')=='on' && sld_get_option( 'sld_enable_recaptcha')=='off' ){
		    	$reg_errors->add('captcha_invalid', __('Captcha does not match!','qc-opd'));
			}
        }

		if( sld_get_option( 'sld_enable_captcha')=='on' && sld_get_option( 'sld_enable_recaptcha')=='on' ){
			if(isset($_POST['g-recaptcha-response']) && !empty($_POST['g-recaptcha-response'])) {
		        $secret = sld_get_option( 'sld_recaptcha_secret_key');
		       // $verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$secret.'&response='.$_POST['g-recaptcha-response']);
		        //$responseData = json_decode($verifyResponse);
		        //if($responseData->success)

		        $falseResponse = [
		            'success' => false,
		            'error-codes' => ['general-fail']
		        ];

				$rcpUrl = sprintf('https://www.recaptcha.net/recaptcha/api/siteverify?secret=%s&response=%s', $secret, $_POST['g-recaptcha-response']);
				$response = (array)wp_remote_get($rcpUrl);
		        $parsedResponse = isset($response['body']) ? json_decode($response['body'], 1) : $falseResponse;
		        //var_dump($parsedResponse);
		       // wp_die();
		        if (isset($parsedResponse['success']) && $parsedResponse['success'] === true)
		        {
		            //$succMsg = 'Your contact request have submitted successfully.';
		        }
		        else
		        {
		        	$reg_errors->add('robot_verification_failed', __('Robot verification failed, please try again.','qc-opd'));
		        }
		    }else{
		    	$reg_errors->add('robot_verification_failed', __('Robot verification failed, please try again.','qc-opd'));
		    }
		}

		if ( username_exists( $username ) )
			$reg_errors->add('user_name', __('Sorry, that username already exists!','qc-opd') );



		if ( is_wp_error( $reg_errors ) ) {
		 
			foreach ( $reg_errors->get_error_messages() as $error ) {
			 
				echo '<div style="color: red;border: 1px solid #e38484;margin: 2px;padding: 2px;text-align: center;margin-bottom: 8px;">';
				echo '';
				echo $error . '<br/>';
				echo '</div>';
				 
			}
		 
		}
	}
	public function complete_registration() {
		global $reg_errors, $username, $password, $email, $first_name, $last_name, $bio;
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


			$qcopd_custom_js = "
				jQuery(document).ready(function($){
				$('#sldfname').val('');
				$('#sldlname').val('');
				$('#sldemail').val('');
				$('#sldusername').val('');
				$('#sldpassword').val('');

				})";

			wp_add_inline_script( 'qcopd-custom-script', $qcopd_custom_js);

	?>
	
		<?php
            if(sld_get_option('sld_enable_user_approval')=='off'){
	            echo '<div style="color: green;border: 1px solid green;margin: 2px;padding: 2px;text-align: center;margin-bottom: 8px;">'.__('User Information submitted! Waiting for approval.','qc-opd').'</div>.';
            }else{
	            echo '<div style="color: green;border: 1px solid green;margin: 2px;padding: 2px;text-align: center;margin-bottom: 8px;">'.__('Registration Successful!','qc-opd').' <a href="' . qc_sld_login_page()->sldcustom_login_get_translated_option_page( 'sld_login_url','') . '">'.__('Go to login page','qc-opd').'</a></div>.';
            }

        }
	}
	
	public function registration_form( $username, $password, $email, $first_name, $last_name ) {

        $_SESSION['captcha'] = sld_simple_php_captcha();

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

        $sld_lan_username = sld_get_option('sld_lan_username')?sld_get_option('sld_lan_username'): esc_html('Username');
        $sld_lan_password = sld_get_option('sld_lan_password')?sld_get_option('sld_lan_password'): esc_html('Password');
        $sld_lan_email 	  = sld_get_option('sld_lan_email')?sld_get_option('sld_lan_email'): esc_html('Email');
        $sld_lan_register 	  = sld_get_option('sld_lan_register')?sld_get_option('sld_lan_register'): esc_html('Register');
        $sld_lan_create_account 	  = sld_get_option('sld_lan_create_account')?sld_get_option('sld_lan_create_account'): esc_html('Create account');

		
	 
		echo '
		<div class="cleanlogin-container">	<form autocomplete="off" class="cleanlogin-form" action="' . $_SERVER['REQUEST_URI'] . '" method="post" id="registration_form_sld">
		<h2>'. __( $sld_lan_create_account, 'qc-opd' ).'</h2>
		<fieldset><div class="cleanlogin-field">
		<input class="cleanlogin-field-username" type="text" name="fname" id="sldfname" placeholder="'. $firstname.' *" value="' . ( isset( $_POST['fname']) ? $first_name : null ) . '" required>
		<i class="fa fa-user"></i>
		</div></fieldset>
		 
		<fieldset><div class="cleanlogin-field">
	   
		<input class="cleanlogin-field-username" placeholder="'. $lastname.' *" type="text" name="lname" id="sldlname" value="' . ( isset( $_POST['lname']) ? $last_name : null ) . '" required>
		<i class="fa fa-user"></i>
		</div></fieldset>


		<fieldset><div class="cleanlogin-field">
		<input class="cleanlogin-field-username" placeholder="'. __( $sld_lan_username, 'qc-opd' ).' *" type="text" name="username" id="sldusername" value="' . ( isset( $_POST['username'] ) ? $username : null ) . '" required>
		<i class="fa fa-user"></i>
		</div></fieldset>
		
		<fieldset><div class="cleanlogin-field"><input type="password" style="display: none;" />
		<input class="cleanlogin-field-username" placeholder="'. __( $sld_lan_password, 'qc-opd' ).' *" type="password" name="password" id="sldpassword" value="' . ( isset( $_POST['password'] ) ? $password : null ) . '" required>
		<i class="fa fa-lock"></i>
		</div></fieldset>
		
		<fieldset><div class="cleanlogin-field">
		<input class="cleanlogin-field-username" placeholder="'. __( $sld_lan_email, 'qc-opd' ).' *" type="email" name="email" id="sldemail" value="' . ( isset( $_POST['email']) ? $email : null ) . '" required><input type="text" style="display: none;" />
		<i class="fa fa-envelope"></i>
		</div></fieldset>
		';

		if(sld_get_option( 'sld_enable_captcha')=='on'){
			if( sld_get_option( 'sld_enable_recaptcha')=='off' ){
	            echo '<fieldset><div class="cleanlogin-field">
	            <img src="'.($_SESSION['captcha']['image_src']).'" alt="Captcha Code" id="sld_captcha_image" />
	            <img style="width: 24px;cursor:pointer;" id="captcha_reload" src="'.SLD_QCOPD_IMG_URL.'/captcha_reload.png" />
	            <input class="cleanlogin-field-username" placeholder="'. __( 'Code', 'qc-opd' ).'" type="text" name="ccode" id="sldcode" value="" required>
	            </div></fieldset>
	             ';
	         }

            if( sld_get_option( 'sld_enable_recaptcha')=='on' ){
            	echo '<fieldset><div class="cleanlogin-field"><div class="g-recaptcha" data-sitekey="'.sld_get_option( 'sld_recaptcha_site_key').'"></div></div></fieldset>';
            }
        }

		
		echo '<fieldset style="text-align: center; padding: 0px !important;"><div style="margin-top: 16px;margin-bottom: 0px;" class="cleanlogin-field">
		<input type="hidden" name="sldregistration" value="sld"/>
		<input type="submit" class="submit_registration" name="submit" value="'. __( $sld_lan_register, 'qc-opd' ).'"/>
		</div></fieldset>
		
		</form></div>
		';
	}
	
}

function sld_registration_page() {
	return sld_custom_registration::instance();
}
sld_registration_page();

 