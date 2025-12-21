<?php
/*
* Author : QuantumCloud
* class Handle Login, Password restore.
*/

class qc_sld_login
{
	private static $instance;
	public static function instance(){
		if(!isset(self::$instance)){
			self::$instance = new qc_sld_login();
		}
		return self::$instance;
	}
	private function __construct(){
		$this->loadResources();
		if( !is_admin() ){
			if( sld_get_option('sld_login_captcha') == 'on' && sld_get_option( 'sld_enable_recaptcha')=='off' ){
				if(!session_id()){
					session_start();
				}
			}
		}
	}
	
	/*
	* Load Resources
	*/
	
	public function loadResources(){
		
		//add_action( 'wp_enqueue_scripts', array($this,'sldcustom_login_enqueue_style') );
		add_action('template_redirect', array($this,'sldcustom_login_load_before_headers'));
		add_action( 'save_post', array($this,'sldcustom_login_get_pages_with_shortcodes') );
		add_shortcode('sld_login', array($this,'sldcustom_login_show'));
		add_shortcode('sld_restore', array($this,'sldcustom_login_restore_show'));
		// add_filter( 'wp_authenticate_user', array($this, 'sld_login_recaptcha_authenticate' ), 99, 2 );
		add_filter( 'authenticate', array($this, 'sld_login_recaptcha_authenticate' ), 99, 2 );
	}

	public function sld_login_recaptcha_authenticate( $user, $username ){
		global $wp_query; 
		// print_r($user);wp_die();
		if ( is_singular() ) { 
			$post = $wp_query->get_queried_object();
			
			if( sld_get_option('sld_login_captcha') == 'on' ){
				$url = $this->sldcustom_login_url_cleaner( wp_get_referer() );
				if( sld_get_option( 'sld_enable_recaptcha')=='off' ){
					if(isset($_POST['ccode']) && strtolower($_POST['ccode'])!==strtolower($_SESSION['captcha']['code'])){
						$url = esc_url( add_query_arg( 'authentication', 'invalid-captcha', $url ) );
				        wp_safe_redirect( $url );exit;
					    return new WP_Error('invalid_captcha', __('Robot verification failed, please try again.','qc-opd'));
					}
				}

				if( sld_get_option( 'sld_enable_recaptcha')=='on' ){
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

				        if (isset($parsedResponse['success']) && $parsedResponse['success'] === true) 
				        {
				            //$succMsg = 'Your contact request have submitted successfully.';
				        }
				        else
				        {
				        	$url = esc_url( add_query_arg( 'authentication', 'invalid-captcha', $url ) );
				        	wp_safe_redirect( $url );exit;
				        	return new WP_Error('invalid_captcha', __('Robot verification failed, please try again.','qc-opd'));
				        }
				    }else{
				    	$url = esc_url( add_query_arg( 'authentication', 'invalid-captcha', $url ) );
				    	wp_safe_redirect( $url );exit;
				    	return new WP_Error('invalid_captcha', __('Robot verification failed, please try again.','qc-opd'));
				    }
				}
			}

			// If contains any shortcode of our ones
			if ( $post && strpos($post->post_content, 'sld' ) !== false ) {
				$url = $this->sldcustom_login_url_cleaner( wp_get_referer() );
				if ( !is_wp_error( $user ) ){
					$url = $this->sldcustom_login_get_translated_option_page( 'sld_login_url','');
					wp_clear_auth_cookie();
				    wp_set_current_user ( $user->ID );
				    wp_set_auth_cookie  ( $user->ID );
					if(sld_get_option('sld_enable_anyusers')=='on'){
						$url = $this->sldcustom_login_get_translated_option_page( 'sld_dashboard_url','');
					}else{
						if(!in_array('slduser',$user->roles)){
							wp_logout();
							$url = esc_url( add_query_arg( 'authentication', 'disabled', $url ) );
						}else{
							$url = $this->sldcustom_login_get_translated_option_page( 'sld_dashboard_url','');
						}
					}
					
					wp_safe_redirect( $url );exit;
				}
			}
		}
	    return $user;
	}
	
	/*
	*Wp enqueue Script
	* Load stylesheet.
	*/
	public function sldcustom_login_enqueue_style(){
		wp_register_style( 'sldcustom_login-css', SLD_QCOPD_ASSETS_URL.'/css/style.css', __FILE__ );
		wp_enqueue_style( 'sldcustom_login-css' );
	}
	
	/*
	* Shortcode sldcustom_login.
	*/
	
	public function sldcustom_login_show($atts){
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
		
		if ( isset( $_GET['authentication'] ) ) {
			if ( $_GET['authentication'] == 'success' )
				echo "<div class='cleanlogin-notification success'><p>". __( 'Successfully logged in!', 'qc-opd' ) ."</p></div>";
			else if ( $_GET['authentication'] == 'failed' )
				echo "<div style='color: red;border: 1px solid #e38484;margin: 2px;padding: 2px;text-align: center;margin-bottom: 8px;'>". __( 'Wrong credentials or you are not allowed to log in.', 'qc-opd' ) ."</div>";
			else if ( $_GET['authentication'] == 'invalid-captcha' )
				echo "<div style='color: red;border: 1px solid #e38484;margin: 2px;padding: 2px;text-align: center;margin-bottom: 8px;'>". __( 'Robot verification failed. Invalid Captcha', 'qc-opd' ) ."</div>";
			else if ( $_GET['authentication'] == 'logout' )
				echo "<div class='cleanlogin-notification success'><p>". __( 'Successfully logged out!', 'qc-opd' ) ."</p></div>";
			else if ( $_GET['authentication'] == 'failed-activation' )
				echo "<div class='cleanlogin-notification error'><p>". __( 'Something went wrong while activating your user', 'qc-opd' ) ."</p></div>";
			else if ( $_GET['authentication'] == 'disabled' )
				echo "<div class='cleanlogin-notification error'><p>". __( 'You are not allowed to access this area.', 'qc-opd' ) ."</p></div>";
			else if ( $_GET['authentication'] == 'success-activation' )
				echo "<div class='cleanlogin-notification success'><p>". __( 'Successfully activated', 'qc-opd' ) ."</p></div>";
		}

		if ( is_user_logged_in() ) {

		$login_url = $this->sldcustom_login_get_translated_option_page( 'sld_login_url','');
		$current_user = wp_get_current_user();
		$edit_url = $this->sldcustom_login_get_translated_option_page( 'sld_edit_url', '');
		$show_user_information = get_option( 'cl_hideuser' ) == 'on' ? false : true;

        $sld_lan_login = sld_get_option('sld_lan_login')?sld_get_option('sld_lan_login'): esc_html('Login');
        $sld_lan_already_login = sld_get_option('sld_lan_already_login')?sld_get_option('sld_lan_already_login'): esc_html('User already logged in');
?>
		<div class="cleanlogin-container" >
			<div class="cleanlogin-preview">
				<div class="cleanlogin-preview-top">
					<h2><?php echo __($sld_lan_login, 'qc-opd') ?></h2>
				</div>
				<p style="font-size: 14px;font-weight: bold;margin-bottom: 6px;"><?php echo __($sld_lan_already_login, 'qc-opd') ?></p>

				<a class="sld_logout_button" href="<?php echo esc_url( add_query_arg( 'action', 'logout', $login_url) ); ?>" class="cleanlogin-preview-logout-link"><?php echo __( 'Log out', 'qc-opd' ); ?></a>	
			</div>		
		</div>
<?php
			
		} elseif(sld_get_option('sld_enable_add_new_item')=='on') {
			
		$login_url = $this->sldcustom_login_get_translated_option_page( 'sld_login_url','');
		$register_url = $this->sldcustom_login_get_translated_option_page( 'sld_register_url', '');
		$restore_url = $this->sldcustom_login_get_translated_option_page( 'sld_restore_url', '');

		$sld_lan_login = sld_get_option('sld_lan_login')?sld_get_option('sld_lan_login'): esc_html('Login');
        $sld_lan_username = sld_get_option('sld_lan_username')?sld_get_option('sld_lan_username'): esc_html('Username');
        $sld_lan_password = sld_get_option('sld_lan_password')?sld_get_option('sld_lan_password'): esc_html('Password');
        $sld_lan_create_account 	  = sld_get_option('sld_lan_create_account')?sld_get_option('sld_lan_create_account'): esc_html('Create account');
        $sld_lan_forgot_password 	  = sld_get_option('sld_lan_forgot_password')?sld_get_option('sld_lan_forgot_password'): esc_html('Forgot your password?');
		
?>
		<div class="cleanlogin-container">		

			<form class="cleanlogin-form" method="post" action="<?php echo $login_url;?>">
				<h2> <?php echo __($sld_lan_login, 'qc-opd') ?> </h2>
				
				<fieldset>
					<div class="cleanlogin-field">
						<input class="cleanlogin-field-username" type="text" name="log" placeholder="<?php echo __( $sld_lan_username, 'qc-opd' ); ?>">
					</div>
					
					<div class="cleanlogin-field">
						<input class="cleanlogin-field-password" type="password" name="pwd" placeholder="<?php echo __( $sld_lan_password, 'qc-opd' ); ?>">
					</div>

					<?php
						if( sld_get_option('sld_login_captcha') == 'on' ){
							if( sld_get_option( 'sld_enable_recaptcha')=='off' ){
								$_SESSION['captcha'] = sld_simple_php_captcha();

								echo '<fieldset><div class="cleanlogin-field">
					            <img src="'.($_SESSION['captcha']['image_src']).'" alt="Captcha Code" id="sld_captcha_image" />
					            <img style="width: 24px;cursor:pointer;" id="captcha_reload" src="'.SLD_QCOPD_IMG_URL.'/captcha_reload.png" />
					            <input class="cleanlogin-field-username" placeholder="'. __( 'Code', 'qc-opd' ).'" type="text" name="ccode" id="sldcode" value="" required>
					            </div></fieldset>
					             ';
					        }

				            if( sld_get_option( 'sld_enable_recaptcha')=='on' ){
				            	echo '<div class="cleanlogin-field"><div class="g-recaptcha" data-sitekey="'.sld_get_option( 'sld_recaptcha_site_key').'"></div></div>';
				            }
						}
					?>
					
				</fieldset>
				
				<fieldset style="text-align:center; padding:0 !important;">
					<input class="cleanlogin-field submit_registration" type="submit" value="<?php echo __( $sld_lan_login, 'qc-opd' ); ?>" name="submit">
					<input type="hidden" name="action" value="login">
					
				</fieldset>
				
				<div class="cleanlogin-form-bottom">
					
					<div class="cleanlogin-field-remember">
						<?php 
							echo "<a style='float: right;color: #18191f ;font-weight: bold; padding-left:15px;' href='$register_url' class='cleanlogin-form-pwd-link'>". __( $sld_lan_create_account, 'qc-opd' ) ."</a>";
						?>
					</div>

					<?php 
						echo "<a style='float: right;color: #666;font-weight: bold; padding-right:15px;' href='$restore_url' class='cleanlogin-form-pwd-link'>". __( $sld_lan_forgot_password, 'qc-opd' ) ."</a>";
					?>
								
				</div>
				
				<?php wp_nonce_field( 'qc-opd' ); ?>
			</form>

		</div>
<?php		
		}

		return ob_get_clean();
	}
	
	/**
	 * Custom code to be loaded before headers
	 */
	public function sldcustom_login_load_before_headers() {
		global $wp_query; 
		if ( is_singular() ) { 
			$post = $wp_query->get_queried_object();
			
			// If contains any shortcode of our ones
			if ( $post && strpos($post->post_content, 'sld' ) !== false ) {

				// Sets the redirect url to the current page 
				$url = $this->sldcustom_login_url_cleaner( wp_get_referer() );

				// LOGIN
				if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'login' ) {
					
					$url = $this->sldcustom_login_get_translated_option_page( 'sld_login_url','');

					$user = wp_signon();
					if ( is_wp_error( $user ) )
						$url = esc_url( add_query_arg( 'authentication', 'failed', $url ) );
					else {
						// if the user is not slduser.
						
						if(sld_get_option('sld_enable_anyusers')=='on'){
							$url = $this->sldcustom_login_get_translated_option_page( 'sld_dashboard_url','');
						}else{
							if(!in_array('slduser',$user->roles)){
								wp_logout();
								$url = esc_url( add_query_arg( 'authentication', 'disabled', $url ) );
							}else{
								$url = $this->sldcustom_login_get_translated_option_page( 'sld_dashboard_url','');
							}
						}
						
					}
					
					
					wp_safe_redirect( $url );

				// LOGOUT
				} else if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'logout' ) {
					wp_logout();
					$url = esc_url( add_query_arg( 'authentication', 'logout', $url ) );
					wp_safe_redirect( $url );
				}// RESTORE a password by sending an email with the activation link
				 else if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'restore' ) {

					$retrieved_nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field($_REQUEST['_wpnonce']) : '';
					if ( !wp_verify_nonce($retrieved_nonce, 'qc-opd' ) )
						die( 'Failed security check, expired Activation Link due to duplication or date.' );

					$url = esc_url( add_query_arg( 'sent', 'success', $url ) );
					
					$username = isset( $_POST['username'] ) ? sanitize_user( $_POST['username'] ) : '';
					$website = isset( $_POST['website'] ) ? sanitize_text_field( $_POST['website'] ) : '';
					// Since 1.1 (get username from email if so)
					if ( is_email( $username ) ) {
						$userFromMail = get_user_by( 'email', $username );
						if ( $userFromMail == false )
							$username = '';
						else
							$username = $userFromMail->user_login;
					}

					// honeypot detection
					if( $website != '.' )
						$url = esc_url( add_query_arg( 'sent', 'sent', $url ) );
					else if( $username == '' || !username_exists( $username ) )
						$url = esc_url( add_query_arg( 'sent', 'wronguser', $url ) );
					else {
						$user = get_user_by( 'login', $username );

						$url_msg = get_permalink();
						//$url_msg = esc_url( add_query_arg( 'restore', $user->ID, $url_msg ) );
						$url_msg = esc_url( add_query_arg( 'restore', $username, $url_msg ) );
						$url_msg = wp_nonce_url( $url_msg, $username );

						$email = $user->user_email;
						$blog_title = get_bloginfo();
						$message = sprintf( __( "Use the following link to restore your password: <a href='%s'>restore your password</a> <br/><br/>%s<br/>", 'qc-opd' ), $url_msg, $blog_title );
						$subject = "[$blog_title] " . __( 'Restore your password', 'qc-opd' );
						add_filter( 'wp_mail_content_type', array($this,'sldcustom_login_set_html_content_type') );
						// echo $message; die();
						if( !wp_mail( $email, $subject , $message ) )
							$url = esc_url( add_query_arg( 'sent', 'failed', $url ) );
						remove_filter( 'wp_mail_content_type', array($this,'sldcustom_login_set_html_content_type') );
					}
					wp_safe_redirect( $url );

				// When a user click the activation link goes here to RESTORE his/her password
				} else if ( isset( $_REQUEST['restore'] ) ) {
					$username = sanitize_text_field($_REQUEST['restore']);

					if ( is_email( $username ) ) {
						$userFromMail = get_user_by( 'email', $username );
						if ( $userFromMail == false )
							$username = '';
						else
							$username = $userFromMail->user_login;
					}

					$user = get_user_by( 'login', $username );
					$user_id = isset($user->ID) ? $user->ID : '';


					if ( is_wp_error( $user_id ) ) {
						$url = esc_url( add_query_arg( 'sent', 'wronguser', $url ) );
					}

					$retrieved_nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field($_REQUEST['_wpnonce']) : '';
					if ( !wp_verify_nonce($retrieved_nonce, $username ) )
						die( 'Failed security check, expired Activation Link due to duplication or date.' );

					$edit_url = $this->sldcustom_login_get_translated_option_page( 'sld_edit_url', '');
					
					// If edit profile page exists the user will be redirected there
					if( $edit_url != '') {
						wp_clear_auth_cookie();
						wp_set_current_user ( $user_id );
						wp_set_auth_cookie  ( $user_id );
						$url = $edit_url;

					// If not, a new password will be generated and notified
					} else {
						$url = $this->sldcustom_login_get_translated_option_page( 'sld_restore_url', '');
						// check if password complexity is checked
						$enable_passcomplex = get_option( 'sld_passcomplex' ) == 'on' ? true : false;
						
						if($enable_passcomplex)
							$new_password = wp_generate_password(12, true);
						else
							$new_password = wp_generate_password(8, false);

						//$user_id = wp_update_user( array( 'ID' => $user_id, 'user_pass' => $new_password ) );
						
						if ( is_wp_error( $user_id ) ) {
							$url = esc_url( add_query_arg( 'sent', 'wronguser', $url ) );
						} else {

							$url =  add_query_arg(
										array(
											'pass' 		=> $new_password,
											'uid' 		=> $username,
											'_wpnonce' 	=> $retrieved_nonce,
										), $url
									);
						}
					}

					wp_safe_redirect( $url );
				}elseif(
					(isset( $_POST['qc-restore-pwd'] ) && ($_POST['qc-restore-pwd'] == 'restore')) &&
					(isset( $_POST['qc-restore-pwd-type'] ) && ($_POST['qc-restore-pwd-type'] == 'user')) &&
					(isset( $_POST['qc-uid'] ) && !empty($_POST['qc-uid'])  ) &&
					( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'qc-opd' ) )
				){

					$retrieved_nonce = isset($_POST['_wpnonce']) ? $_POST['_wpnonce'] : '';
					if ( !wp_verify_nonce($retrieved_nonce, 'qc-opd' ) )
						die( 'Failed security check, expired Activation Link due to duplication or date.' );

					$username  = isset( $_POST['qc-uid'] ) ? sanitize_text_field($_POST['qc-uid']) : '';
					$pass 	  = isset( $_POST['pass'] ) ? sanitize_text_field($_POST['pass']) : '';
					$edit_url = $this->sldcustom_login_get_translated_option_page( 'sld_edit_url', '');

					if ( is_email( $username ) ) {
						$userFromMail = get_user_by( 'email', $username );
						if ( $userFromMail == false )
							$username = '';
						else
							$username = $userFromMail->user_login;
					}

					$user = get_user_by( 'login', $username );
					$user_id = isset($user->ID) ? $user->ID : '';
					
					// If edit profile page exists the user will be redirected there
					if( $edit_url != '') {
						wp_clear_auth_cookie();
						wp_set_current_user ( $user_id );
						wp_set_auth_cookie  ( $user_id );
						$url = $edit_url;

					// If not, a new password will be generated and notified
					} else {
						$url = $this->sldcustom_login_get_translated_option_page( 'sld_restore_url', '');
						
						$user_id = wp_update_user( array( 'ID' => $user_id, 'user_pass' => $pass ) );

						if ( is_wp_error( $user_id ) ) {
							$url = esc_url( add_query_arg( 'sent', 'wronguser', $url ) );
						} else {
							$url = $this->sldcustom_login_get_translated_option_page( 'sld_login_url', '');
						}
					}

					wp_safe_redirect( $url );
				}
			} 
		}
	}
	

	/**
	 * [sldcustom_restore] shortcode
	 */
	function sldcustom_login_restore_show($atts) {
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

		if ( isset( $_GET['sent'] ) ) {
			if ( $_GET['sent'] == 'success' )
				echo "<div class='cleanlogin-notification success'><p>". __( 'You will receive an email with the activation link', 'qc-opd' ) ."</p></div>";
			else if ( $_GET['sent'] == 'sent' )
				echo "<div class='cleanlogin-notification success'><p>". __( 'You may receive an email with the activation link', 'qc-opd' ) ."</p></div>";
			else if ( $_GET['sent'] == 'failed' )
				echo "<div class='cleanlogin-notification error'><p>". __( 'An error has ocurred sending the email', 'qc-opd' ) ."</p></div>";
			else if ( $_GET['sent'] == 'wronguser' )
				echo "<div class='cleanlogin-notification error'><p>". __( 'Username is not valid', 'qc-opd' ) ."</p></div>";
		}

		if ( !is_user_logged_in() ) {
			if ( isset( $_GET['pass'] ) ) {
				
				$new_password = sanitize_text_field( $_GET['pass'] );
				$login_url = $this->sldcustom_login_get_translated_option_page( 'sld_login_url','');
				$restore_url = $this->sldcustom_login_get_translated_option_page( 'sld_restore_url', '');
				$uid = isset($_REQUEST['uid']) ? sanitize_text_field($_REQUEST['uid']) : '';

				$retrieved_nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field($_REQUEST['_wpnonce']) : '';
				if ( !wp_verify_nonce($retrieved_nonce, $uid ) )
					die( 'Failed security check, expired Activation Link due to duplication or date.' );

			?>
				<div class="cleanlogin-container">
					<form action="<?php echo esc_url($restore_url); ?>" method="post" class="cleanlogin-form">
						
						<fieldset>
							<div class="cleanlogin-field">
								<label><?php echo __( 'New Password', 'qc-opd' ); ?></label>
								<input type="text" name="pass" value="<?php echo esc_attr($new_password); ?>">
								<input type="hidden" name="qc-restore-pwd" value="restore">
								<input type="hidden" name="qc-restore-pwd-type" value="user">
								<input type="hidden" name="qc-uid" value="<?php echo esc_attr($uid); ?>">
							</div>
						
						</fieldset>
						
						<div class="cleanlogin-form-bottom" style="background: none;">
								
							<?php if ( $login_url != '' )
								// echo "<a href='$login_url' class='cleanlogin-form-login-link sld_logout_button'>". __( 'Log in', 'qc-opd') ."</a>";
							?>
							<button type="submit" class='cleanlogin-form-login-link sld_logout_button'><?php echo esc_html__( 'Update Password', 'qc-opd'); ?></button>
							<?php wp_nonce_field( 'qc-opd' ); ?>	
						</div>
					</form>
				</div>
			<?php
				
			} else{

				

	$sld_lan_reset_password = sld_get_option('sld_lan_reset_password')?sld_get_option('sld_lan_reset_password'): esc_html('Reset password');
	$sld_lan_username_or_email = sld_get_option('sld_lan_username_or_email')?sld_get_option('sld_lan_username_or_email'): esc_html('Username (or E-mail)');
	$sld_lan_website = sld_get_option('sld_lan_website')?sld_get_option('sld_lan_website'): esc_html('Website');
	$sld_lan_restore_password = sld_get_option('sld_lan_restore_password')?sld_get_option('sld_lan_restore_password'): esc_html('Restore password');
	
?>
				<div class="cleanlogin-container">
					<form class="cleanlogin-form" method="post" action="">
						<h2><?php echo __($sld_lan_reset_password, 'qc-opd') ?></h2>
						<fieldset>
						
							<div class="cleanlogin-field">
								<input class="cleanlogin-field-username" type="text" name="username" value="" placeholder="<?php echo __( $sld_lan_username_or_email, 'qc-opd' ) ; ?>">
							</div>

							<div class="cleanlogin-field-website">
								<label for='website'><?php echo __($sld_lan_website, 'qc-opd') ?></label>
								<input type='text' name='website' value=".">
							</div>
						
						</fieldset>
						
						<div style="text-align:center">	
							<input type="submit" value="<?php echo __( $sld_lan_restore_password, 'qc-opd' ); ?>" name="submit">
							<input type="hidden" name="action" value="restore">	
							<?php wp_nonce_field( 'qc-opd' ); ?>	
						</div>

					</form>
				</div>
<?php
			}
		} else {
			
			$login_text_logged_in_lang =  sld_get_option('login_text_logged_in_lang') ? sld_get_option('login_text_logged_in_lang')  : esc_html( 'You are now logged in. It makes no sense to restore your account', 'qc-opd' );

			echo "<div class='cleanlogin-notification error'><p>". $login_text_logged_in_lang ."</p></div>";
			
			$login_url = $this->sldcustom_login_get_translated_option_page( 'sld_login_url','');
			$current_user = wp_get_current_user();
			$edit_url = $this->sldcustom_login_get_translated_option_page( 'sld_edit_url', '');
			$show_user_information = get_option( 'cl_hideuser' ) == 'on' ? false : true;
?>
			<div class="cleanlogin-container" >
				<div class="cleanlogin-preview">
					<div class="cleanlogin-preview-top">
						<h2><?php echo __('Login', 'qc-opd') ?></h2>
					</div>
					<p style="    font-size: 14px;
				font-weight: bold;
				margin-bottom: 6px;"><?php echo __('User already logged in', 'qc-opd') ?></p>

					<a class="sld_logout_button" href="<?php echo esc_url( add_query_arg( 'action', 'logout', $login_url) ); ?>" class="cleanlogin-preview-logout-link"><?php echo __( 'Log out', 'qc-opd' ); ?></a>	
				</div>		
			</div>
<?php
		}

		return ob_get_clean();

	}
	
	/**
	 * Cleans an url
	 * @param url to be cleaned
	 */
	public function sldcustom_login_url_cleaner( $url ) {
		$query_args = array(
			'authentication',
			'updated',
			'created',
			'sent',
			'restore'
		);
		return esc_url( remove_query_arg( $query_args, $url ) );
	}
	
	/**
	 * SLD redirection support
	 */
	public function sldcustom_login_get_translated_option_page($page, $param = false) {
		$url = get_option($page, $param);
		//if SLD is installed get the page translation
		if (!function_exists('icl_object_id')) {
			return $url;
		} else {
			//get the page ID
			$pid = url_to_postid( $url ); 
			//set the translated urls
			return get_permalink( icl_object_id( $pid, 'page', false, ICL_LANGUAGE_CODE ) );
		}
	}
	
	
	
	/**
	 * Set email format to html
	 */
	public function sldcustom_login_set_html_content_type()
	{
		return 'text/html';
	}
	
	/**
	 * Detect shortcodes and update the plugin options
	 * @param post_id of an updated post
	 */
	function sldcustom_login_get_pages_with_shortcodes( $post_id ) {

		$revision = wp_is_post_revision( $post_id );

		if ( $revision ) $post_id = $revision;
		
		$post = get_post( $post_id );

		if ( has_shortcode( $post->post_content, 'sld_login' ) ) {
			update_option( 'sld_login_url', get_permalink( $post->ID ) );
		}
		
		if ( has_shortcode( $post->post_content, 'sld_registration' ) ) {
			update_option( 'sld_register_url', get_permalink( $post->ID ) );
		}
		
		if ( has_shortcode( $post->post_content, 'sld_dashboard' ) ) {
			update_option( 'sld_dashboard_url', get_permalink( $post->ID ) );
		}
		
		if ( has_shortcode( $post->post_content, 'sld_restore' ) ) {
			update_option( 'sld_restore_url', get_permalink( $post->ID ) );
		}
		
		if ( has_shortcode( $post->post_content, 'sld_claim_listing' ) ) {
			update_option( 'sld_claim_url', get_permalink( $post->ID ) );
		}

	}
	
	
}

function qc_sld_login_page() {
	return qc_sld_login::instance();
}
qc_sld_login_page();