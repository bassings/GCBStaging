<?php
/*
* Author: QuantumCloud.
* Dashboard class.
*/

if ( ! defined( 'ABSPATH' ) ) exit;
class qc_sld_dashboard
{
	private static $instance;

	protected $allow_item_submit; //Allow item submission
	protected $show_package; //Allow package
    protected $total_item;
    protected $submited_item;
    protected $remain_item;



	public static function instance(){
		if(!isset(self::$instance)){
			self::$instance = new qc_sld_dashboard();
		}
		return self::$instance;
	}
	private function __construct(){
		
		if ( ! class_exists( 'WP_List_Table' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
		} 
		require(SLD_QCOPD_DIR_MOD.'/dashboard/qc-subscriber-entry-approve.php');
		require(SLD_QCOPD_DIR_MOD.'/dashboard/qc-subscriber_entry_list.php');

		//add_action('pre_get_posts',array($this,'sld_users_own_attachments'));
		
        add_action('template_redirect', array($this,'sldcustom_redirect_load_before_headers'));

		add_action('init', array($this, 'sld_plugin_init'));

		//add_action( 'wp_enqueue_scripts', array($this,'sldcustom_dashboard_enqueue_style') );
		add_shortcode('sld_dashboard', array($this,'sld_dashboard_show'));
		//add_action('wp_loaded', array($this,'sldcustom_user_permission_add'));
		
		add_action( 'wp_ajax_qcld_sld_category_filter', array($this,'qcld_sld_category_filter_fnc') ); // ajax for logged in users
		add_action( 'wp_ajax_nopriv_qcld_sld_category_filter', array($this,'qcld_sld_category_filter_fnc') ); // ajax for not logged in users
		add_action('plugins_loaded',array($this,'qc_sld_admin_area'));

	}
    function sld_plugin_init(){
	    global $wpdb;
		
		if(!function_exists('wp_get_current_user')) {
			include(ABSPATH . "wp-includes/pluggable.php"); 
		}
		
	    $current_user = wp_get_current_user();
		
	    $table             = $wpdb->prefix.'sld_package_purchased';
	    $table1             = $wpdb->prefix.'sld_package';

	    if(isset($_GET['packagesave']) and $_GET['packagesave']!=''){
			$pid = sanitize_text_field($_GET['packagesave']);
		    $package     = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table1 WHERE 1 and id=%d", $pid ) );

		    if($package->duration=='1-year'){
			    $package->duration = 12;
		    }
		    if($package->duration=='2-years'){
			    $package->duration = 24;
		    }
		    if($package->duration=='3-years'){
			    $package->duration = 36;
		    }
		    if($package->duration=='4-years'){
			    $package->duration = 48;
		    }
		    if($package->duration=='5-years'){
			    $package->duration = 60;
		    }
		    if($package->duration=='lifetime'){
			    $package->duration = 120;
		    }

		    $userid = sanitize_text_field($_GET['user']);
		    if(!empty($_POST)){
		        $txn_id = sanitize_text_field($_POST['txn_id']);
		        $name 	= sanitize_text_field($_POST['first_name']).' '.sanitize_text_field($_POST['last_name']);
		        $payer_email = sanitize_text_field($_POST['payer_email']);
		        $amount = sanitize_text_field($_POST['mc_gross']);
		        $status = sanitize_text_field($_POST['payment_status']);
			    $date 	= date('Y-m-d H:i:s');
				if(isset($_REQUEST['custom']) && $_REQUEST['custom']=='recurring'){
					$custom = 1;
					$package->duration = 120;
				}else{
					$custom = 0;
				}
			    $expire_date = date("Y-m-d", strtotime("+$package->duration month", strtotime($date)));

			    $wpdb->insert(
				    $table,
				    array(
					    'date'  => $date,
					    'package_id'   => $package->id,
					    'user_id'   => $userid,
					    'paid_amount'   => $amount,
					    'transaction_id' => $txn_id,
					    'payer_name'   => $name,
					    'payer_email'   => $payer_email,
					    'status'   => $status,
                        'expire_date' => $expire_date,
						'recurring' => $custom
				    )
			    );
				wp_reset_query();
            }
        }



        if(isset($_GET['packagerenew']) and $_GET['packagerenew']!=''){

	        $pkg = sanitize_text_field($_GET['packagerenew']);
	        $package1     = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE 1 and id =%d", $pkg ) );
			$package     = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table1 WHERE 1 and id=%d", $package1->package_id ) );


		    if($package->duration=='1-year'){
			    $package->duration = 12;
		    }
		    if($package->duration=='2-years'){
			    $package->duration = 24;
		    }
		    if($package->duration=='3-years'){
			    $package->duration = 36;
		    }
		    if($package->duration=='4-years'){
			    $package->duration = 48;
		    }
		    if($package->duration=='5-years'){
			    $package->duration = 60;
		    }

	        if($package->duration=='lifetime'){
		        $package->duration = 120;
            }

	        $expire_date = date("Y-m-d H:i:s", strtotime("+$package->duration month", strtotime($package1->expire_date)));



	        if(!empty($_POST)){
		        $txn_id = sanitize_text_field($_POST['txn_id']);
		        $name = sanitize_text_field($_POST['first_name']).' '.sanitize_text_field($_POST['last_name']);
		        $payer_email = sanitize_text_field($_POST['payer_email']);
		        $amount = sanitize_text_field($_POST['mc_gross']);
		        $status = sanitize_text_field($_POST['payment_status']);
		        $date = date('Y-m-d H:i:s');


		        $wpdb->update(
			        $table,
			        array(
				        'renew'  => $date,
				        'expire_date'=>$expire_date,
				        'transaction_id' => $txn_id,
			        ),
                    array('id'=>$pkg),
                    array(
                        '%s',
                        '%s',
                        '%s',
                    ),
                    array('%d')
		        );
				wp_reset_query();
	        }
        }
		
		//Stripe payment
		if(isset($_GET['payment']) and $_GET['payment']=='stripe-save'){
			
			require_once(SLD_QCOPD_INC_DIR.'/stripe-php-master/init.php');
			\Stripe\Stripe::setApiKey(sld_get_option('sld_stripe_sectet_key'));

			$table          = $wpdb->prefix.'sld_package_purchased';
			$table1         = $wpdb->prefix.'sld_package';
			$package_id 	= sanitize_text_field($_GET['package']);
			$package     	= $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table1 WHERE 1 and id=%d",$package_id ) );


		    if($package->duration=='1-year'){
			    $package->duration = 12;
		    }
		    if($package->duration=='2-years'){
			    $package->duration = 24;
		    }
		    if($package->duration=='3-years'){
			    $package->duration = 36;
		    }
		    if($package->duration=='4-years'){
			    $package->duration = 48;
		    }
		    if($package->duration=='5-years'){
			    $package->duration = 60;
		    }
		    if($package->duration=='lifetime'){
			    $package->duration = 120;
		    }
			
			$amount = ($package->Amount*100);
			$currency = $package->currency;
		    $userid = sanitize_text_field($_GET['userid']);
			$token  = sanitize_text_field($_POST['stripeToken']);
			$email  = sanitize_text_field($_POST['stripeEmail']);
			$customer = \Stripe\Customer::create(array(
				  'email' => $email,
				  'source'  => $token
			 ));
			
			$charge = \Stripe\Charge::create(array(
			  'customer' => $customer->id,
			  'amount'   => $amount,
			  'currency' => $currency
			));
			
			
			
			
		    if(!empty($_POST)){
		        $txn_id = $token;
		        $name = $email;
		        $payer_email = $email;
		        $amount = $package->Amount;
		        $status = 'success';
			    $date = date('Y-m-d H:i:s');
				
			    $expire_date = date("Y-m-d", strtotime("+$package->duration month", strtotime($date)));

			    $wpdb->insert(
				    $table,
				    array(
					    'date'  => $date,
					    'package_id'   => $package->id,
					    'user_id'   => $userid,
					    'paid_amount'   => $amount,
					    'transaction_id' => $txn_id,
					    'payer_name'   => $name,
					    'payer_email'   => $payer_email,
					    'status'   => $status,
                        'expire_date' => $expire_date,
						
				    )
			    );
				wp_reset_query();
            }
			
		}
		
		if(isset($_GET['payment']) and $_GET['payment']=='2co-save'){
			require_once(SLD_QCOPD_INC_DIR.'/2co-lib/Twocheckout.php');
			$pid = sanitize_text_field($_GET['package']);
			Twocheckout::privateKey(sld_get_option('sld_2checkout_sectet_key')); //Private Key
			Twocheckout::sellerId(sld_get_option('sld_2checkout_seller_id')); // 2Checkout Account Number
			if(sld_get_option('sld_enable_2checkout_sandbox')=="on"){
				Twocheckout::sandbox(true); // Set to false for production accounts.
			}else{
				Twocheckout::sandbox(false); // Set to false for production accounts.
			}
			$table             	= $wpdb->prefix.'sld_package_purchased';
			$table1             = $wpdb->prefix.'sld_package';
			
			$package     		= $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table1 WHERE 1 and id=%d", $pid ) );
			
			$currency 			= $package->currency;
		    $userid 			= $current_user->ID;
			$useremail 			= $current_user->user_email;
			try {
				$charge = Twocheckout_Charge::auth(array(
					"merchantOrderId" => substr(md5(microtime()),rand(0,26),5),
					"token"      => sanitize_text_field($_POST['token']),
					"currency"   => $currency,
					"total"      => sanitize_text_field($_POST['amount']),
					"billingAddr" => array(
						"name" => sanitize_text_field($_POST['name']),
						"addrLine1" => sanitize_text_field($_POST['address']),
						"city" => sanitize_text_field($_POST['city']),
						"state" => sanitize_text_field($_POST['state']),
						"zipCode" => sanitize_text_field($_POST['zipcode']),
						"country" => sanitize_text_field($_POST['country']),
						"email" => $useremail,
						"phoneNumber" => sanitize_text_field($_POST['phone'])
					)
				));

				if ($charge['response']['responseCode'] == 'APPROVED') {

					$txn_id = $charge['response']['transactionId'];
					$name = sanitize_text_field($_POST['name']);
					$payer_email = $charge['response']['billingAddr']['email'];
					$amount = sanitize_text_field($_POST['amount']);
					$status = 'success';
					$date = date('Y-m-d H:i:s');
					
					$expire_date = date("Y-m-d", strtotime("+$package->duration month", strtotime($date)));

					$wpdb->insert(
						$table,
						array(
							'date'  => $date,
							'package_id'   => $package->id,
							'user_id'   => $userid,
							'paid_amount'   => $amount,
							'transaction_id' => $txn_id,
							'payer_name'   => $name,
							'payer_email'   => $payer_email,
							'status'   => $status,
							'expire_date' => $expire_date,
						)
					);
					wp_reset_query();
					
					$dashboardurl = qc_sld_login_page()->sldcustom_login_get_translated_option_page('sld_dashboard_url');
					wp_redirect($dashboardurl.'?action=package&success='.urlencode('Your payment has beed successfull!'));
					exit;
					
				}
			} catch (Twocheckout_Error $e) {
				//print_r($e->getMessage());
				$dashboardurl = qc_sld_login_page()->sldcustom_login_get_translated_option_page('sld_dashboard_url');
				wp_redirect($dashboardurl.'?action=package&er='.$e->getMessage());
				exit;
			}
			
			
		}
		
		//mollie package payment
		if(isset($_GET['payment']) and $_GET['payment']=='mollie-save'){
			
			
			require_once(SLD_QCOPD_INC_DIR.'/mollie-api-php-master/examples/initialize.php');

			$mollie = new \Mollie\Api\MollieApiClient();
			$mollie->setApiKey(sld_get_option('sld_mollie_sectet_key'));

			$table          = $wpdb->prefix.'sld_package_purchased';
			$table1         = $wpdb->prefix.'sld_package';
			$package_id 	= isset($_GET['package']) ? sanitize_text_field($_GET['package']) : '';
			$package     	= $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table1 WHERE 1 and id=%d", $package_id ) );


		    if($package->duration=='1-year'){
			    $package->duration = 12;
		    }
		    if($package->duration=='2-years'){
			    $package->duration = 24;
		    }
		    if($package->duration=='3-years'){
			    $package->duration = 36;
		    }
		    if($package->duration=='4-years'){
			    $package->duration = 48;
		    }
		    if($package->duration=='5-years'){
			    $package->duration = 60;
		    }
		    if($package->duration=='lifetime'){
			    $package->duration = 120;
		    }
			
			$amount 	= number_format( (float) $package->Amount, 2, '.', '' );
			$currency 	= $package->currency;

			$current_user = wp_get_current_user();
			
			$pay_result = isset($_GET['pay_result']) ? sanitize_text_field($_GET['pay_result']) : '';


			if(!session_id()){
				session_start();
			}
			if(isset($_SESSION['package_id']) && empty($_SESSION['package_id'])){
				$_SESSION['package_id'] = $package_id;
			}



		    if( $pay_result !== 'success'){

			    $protocol = isset($_SERVER['HTTPS']) && strcasecmp('off', $_SERVER['HTTPS']) !== 0 ? "https" : "http";
			    $hostname = $_SERVER['HTTP_HOST'];
			    $path = dirname(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $_SERVER['PHP_SELF']);

				$payment = $mollie->payments->create([
			        "amount" 		=> [
			            "currency" 	=> $currency,
			            "value" 	=> $amount,
			        ],
			        "description" 	=> "Order #{$package_id}",
			        "redirectUrl" 	=> "{$protocol}://{$hostname}{$path}?action=package&pay_result=success",
			        "webhookUrl" 	=> "{$protocol}://{$hostname}{$path}/webhook.php",
			        "metadata" 		=> [
			            "order_id" 	=> $package_id,
			        ],
			    ]);

			    header("Location: " . $payment->getCheckoutUrl(), true, 303);

		    }
			
			
		    //if(!empty($_POST)){
		    if( $pay_result == 'success' ){

				$txn_id 		= 'mollie';
				$name 			= $current_user->user_email;
				$payer_email 	= $current_user->user_email;
		        $amount 		= $package->Amount;

		        $status 		= 'success';
			    $date 			= date('Y-m-d H:i:s');
				
			    $expire_date = date("Y-m-d", strtotime("+$package->duration month", strtotime($date)));

			    $wpdb->insert(
				    $table,
				    array(
					    'date'  			=> $date,
					    'package_id'   		=> $_SESSION['package_id'],
					    'user_id'   		=> $current_user->ID,
					    'paid_amount'   	=> $amount,
					    'transaction_id' 	=> $txn_id,
					    'payer_name'   		=> $name,
					    'payer_email'   	=> $payer_email,
					    'status'   			=> $status,
                        'expire_date' 		=> $expire_date,
						
				    )
			    );
				wp_reset_query();
				unset($_SESSION['package_id']);
					
				$dashboardurl = qc_sld_login_page()->sldcustom_login_get_translated_option_page('sld_dashboard_url');
				wp_redirect($dashboardurl.'?action=package&success='.urlencode('Your payment has beed successfull!'));
				exit;
            }
			
		}
		
		//mollie package renew payment
		if(isset($_GET['payment']) and $_GET['payment']=='mollie-renew'){
			
			
			require_once(SLD_QCOPD_INC_DIR.'/mollie-api-php-master/examples/initialize.php');

			$mollie = new \Mollie\Api\MollieApiClient();
			$mollie->setApiKey(sld_get_option('sld_mollie_sectet_key'));

			$table          = $wpdb->prefix.'sld_package_purchased';
			$table1         = $wpdb->prefix.'sld_package';
			$package_id 	= isset($_GET['package']) ? sanitize_text_field($_GET['package']) : '';
			//$package     	= $wpdb->get_row( "SELECT * FROM $table1 WHERE 1 and id=".$package_id );
			$package1     	= $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE 1 and id =%d", $package_id ) );
			$package     	= $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table1 WHERE 1 and id=%d", $package1->package_id ) );


		    if($package->duration=='1-year'){
			    $package->duration = 12;
		    }
		    if($package->duration=='2-years'){
			    $package->duration = 24;
		    }
		    if($package->duration=='3-years'){
			    $package->duration = 36;
		    }
		    if($package->duration=='4-years'){
			    $package->duration = 48;
		    }
		    if($package->duration=='5-years'){
			    $package->duration = 60;
		    }
		    if($package->duration=='lifetime'){
			    $package->duration = 120;
		    }
		    
			
			$amount 	= number_format((float) $package->Amount,2,'.', '');
			$currency 	= $package->currency;

			$current_user = wp_get_current_user();
			
			$pay_result = isset($_GET['pay_result']) ? sanitize_text_field($_GET['pay_result']) : '';


			if(!session_id()){
				session_start();
			}
			if(isset($_SESSION['package_id']) && empty($_SESSION['package_id'])){
				$_SESSION['package_id'] = $package_id;
				$_SESSION['package_expire_date'] = $package1->expire_date;
			}



		    if( $pay_result !== 'success'){

			    $protocol = isset($_SERVER['HTTPS']) && strcasecmp('off', $_SERVER['HTTPS']) !== 0 ? "https" : "http";
			    $hostname = $_SERVER['HTTP_HOST'];
			    $path = dirname(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $_SERVER['PHP_SELF']);

				$payment = $mollie->payments->create([
			        "amount" 		=> [
			            "currency" 	=> $currency,
			            "value" 	=> $amount,
			        ],
			        "description" 	=> "Order #{$package_id}",
			        "redirectUrl" 	=> "{$protocol}://{$hostname}{$path}?action=package&pay_result=success",
			        "webhookUrl" 	=> "{$protocol}://{$hostname}{$path}/webhook.php",
			        "metadata" 		=> [
			            "order_id" 	=> $package_id,
			        ],
			    ]);

			    header("Location: " . $payment->getCheckoutUrl(), true, 303);

		    }
			
			
		    //if(!empty($_POST)){
		    if( $pay_result == 'success' ){

				$txn_id 		= 'mollie';
				$name 			= $current_user->user_email;
				$payer_email 	= $current_user->user_email;
		        $amount 		= $package->Amount;

		        $status 		= 'success';
			    $date 			= date('Y-m-d H:i:s');
				
			    //$expire_date = date("Y-m-d", strtotime("+$package->duration month", strtotime($date)));
			    $expire_date = date("Y-m-d H:i:s", strtotime("+$package->duration month", strtotime( $_SESSION['package_expire_date'] )));

			    $wpdb->update(
			        $table,
			        array(
				        'renew'  			=> $date,
				        'expire_date' 		=>$expire_date,
				        'transaction_id' 	=> $txn_id,
			        ),
                    array('id'	=> $_SESSION['package_id'] ),
                    array(
                        '%s',
                        '%s',
                        '%s',
                    ),
                    array('%d')
		        );
				wp_reset_query();

				unset($_SESSION['package_id']);
				unset($_SESSION['package_expire_date']);
					
				$dashboardurl = qc_sld_login_page()->sldcustom_login_get_translated_option_page('sld_dashboard_url');
				wp_redirect($dashboardurl.'?action=package&success='.urlencode('Your payment has beed successfull!'));
				exit;
            }
			
		}
		
		if(isset($_GET['payment']) and $_GET['payment']=='offline-save'){

			$pid 			= sanitize_text_field($_GET['package']);
			$table          = $wpdb->prefix.'sld_package_purchased';
			$table1         = $wpdb->prefix.'sld_package';
			
			$package     	= $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table1 WHERE 1 and id=%d",$pid ));
			
			$currency 		= $package->currency;
		    $userid 		= $current_user->ID;
			$useremail 		= $current_user->user_email;

			$txn_id 		= 'offline';
			$name 			= $current_user->user_email;
			$payer_email 	= $current_user->user_email;
			$amount 		= sanitize_text_field($_POST['amount']);
			$status 		= 'pending';
			$date 			= date('Y-m-d H:i:s');
			
			$expire_date = date("Y-m-d", strtotime("+$package->duration month", strtotime($date)));

			$wpdb->insert(
				$table,
				array(
					'date'  		=> $date,
					'package_id'   	=> $package->id,
					'user_id'   	=> $userid,
					'paid_amount'   => $amount,
					'transaction_id'=> $txn_id,
					'payer_name'   	=> $name,
					'payer_email'   => $payer_email,
					'status'   		=> $status,
					'payment_method'=> 'offline',
					'expire_date' 	=> $expire_date,
				)
			);
			wp_reset_query();
			
			$dashboardurl = qc_sld_login_page()->sldcustom_login_get_translated_option_page('sld_dashboard_url');
			wp_redirect($dashboardurl.'?action=package&success='.urlencode('Your payment has beed successfull!'));
			exit;
			
			
		}
		
		if(isset($_GET['payment']) and $_GET['payment']=='stripe-renew'){
			require_once(SLD_QCOPD_INC_DIR.'/stripe-php-master/init.php');
			\Stripe\Stripe::setApiKey(sld_get_option('sld_stripe_sectet_key'));
			
	        $pkg 			= isset($_GET['pkg']) ? sanitize_text_field($_GET['pkg']) : '';
			
	        $package1     	= $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE 1 and id =%d", $pkg ) );
			$package     	= $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table1 WHERE 1 and id=%d", $package1->package_id ) );



		    if($package->duration=='1-year'){
			    $package->duration = 12;
		    }
		    if($package->duration=='2-years'){
			    $package->duration = 24;
		    }
		    if($package->duration=='3-years'){
			    $package->duration = 36;
		    }
		    if($package->duration=='4-years'){
			    $package->duration = 48;
		    }
		    if($package->duration=='5-years'){
			    $package->duration = 60;
		    }
	        if($package->duration=='lifetime'){
		        $package->duration = 120;
            }

	        $expire_date = date("Y-m-d H:i:s", strtotime("+$package->duration month", strtotime($package1->expire_date)));

			$amount = ($package->Amount*100);
			$currency = $package->currency;
		    $userid = sanitize_text_field($_GET['userid']);
			$token  = sanitize_text_field($_POST['stripeToken']);
			$email  = sanitize_text_field($_POST['stripeEmail']);
			$customer = \Stripe\Customer::create(array(
				  'email' => $email,
				  'source'  => $token
			 ));
			
			$charge = \Stripe\Charge::create(array(
			  'customer' => $customer->id,
			  'amount'   => $amount,
			  'currency' => $currency
			));

	        if(!empty($_POST)){
		        $txn_id = $token;
		        $name = $email;
		        $payer_email = $email;
		        $amount = $package->Amount;
		        $status = 'success';
		        $date = date('Y-m-d H:i:s');


		        $wpdb->update(
			        $table,
			        array(
				        'renew'  => $date,
				        'expire_date'=>$expire_date,
				        'transaction_id' => $txn_id,
			        ),
                    array('id'=>$pkg),
                    array(
                        '%s',
                        '%s',
                        '%s',
                    ),
                    array('%d')
		        );
				wp_reset_query();
	        }
        }
		
		if(isset($_GET['payment']) and $_GET['payment']=='2co-renew'){
			require_once(SLD_QCOPD_INC_DIR.'/2co-lib/Twocheckout.php');
			
			Twocheckout::privateKey(sld_get_option('sld_2checkout_sectet_key')); //Private Key
			Twocheckout::sellerId(sld_get_option('sld_2checkout_seller_id')); // 2Checkout Account Number
			if(sld_get_option('sld_enable_2checkout_sandbox')=="on"){
				Twocheckout::sandbox(true); // Set to false for production accounts.
			}else{
				Twocheckout::sandbox(false); // Set to false for production accounts.
			}
			
			$pkg 			= isset($_GET['pkg']) ? sanitize_text_field($_GET['pkg']) : '';
	        $package1     	= $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE 1 and id =%d", $pkg) );
			$package     	= $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table1 WHERE 1 and id=%d", $package1->package_id ) );
			
			$useremail 		= $current_user->user_email;
			

		    if($package->duration=='1-year'){
			    $package->duration = 12;
		    }
		    if($package->duration=='2-years'){
			    $package->duration = 24;
		    }
		    if($package->duration=='3-years'){
			    $package->duration = 36;
		    }
		    if($package->duration=='4-years'){
			    $package->duration = 48;
		    }
		    if($package->duration=='5-years'){
			    $package->duration = 60;
		    }
			if($package->duration=='lifetime'){
		        $package->duration = 120;
            }

	        $expire_date = date("Y-m-d H:i:s", strtotime("+$package->duration month", strtotime($package1->expire_date)));
			
			try {
				$charge = Twocheckout_Charge::auth(array(
					"merchantOrderId" => substr(md5(microtime()),rand(0,26),5),
					"token"      => sanitize_text_field($_POST['token']),
					"currency"   => $currency,
					"total"      => sanitize_text_field($_POST['amount']),
					"billingAddr" => array(
						"name" => sanitize_text_field($_POST['name']),
						"addrLine1" => sanitize_text_field($_POST['address']),
						"city" => sanitize_text_field($_POST['city']),
						"state" => sanitize_text_field($_POST['state']),
						"zipCode" => sanitize_text_field($_POST['zipcode']),
						"country" => sanitize_text_field($_POST['country']),
						"email" => $useremail,
						"phoneNumber" => sanitize_text_field($_POST['phone'])
					)
				));

				if ($charge['response']['responseCode'] == 'APPROVED') {
					
					
					$txn_id = $charge['response']['transactionId'];
					$name = sanitize_text_field($_POST['name']);
					$payer_email = $charge['response']['billingAddr']['email'];
					$amount = sanitize_text_field($_POST['amount']);
					$status = 'success';
					$date = date('Y-m-d H:i:s');

					$wpdb->update(
						$table,
						array(
							'renew'  => $date,
							'expire_date'=>$expire_date,
							'transaction_id' => $txn_id,
						),
						array('id'=>$pkg),
						array(
							'%s',
							'%s',
							'%s',
						),
						array('%d')
					);
					wp_reset_query();
					
					$dashboardurl = qc_sld_login_page()->sldcustom_login_get_translated_option_page('sld_dashboard_url');
					wp_redirect($dashboardurl.'?action=package&success='.urlencode('Your payment has beed successfull!'));
					exit;
					
				}
			} catch (Twocheckout_Error $e) {
				//print_r($e->getMessage());
				$dashboardurl = qc_sld_login_page()->sldcustom_login_get_translated_option_page('sld_dashboard_url');
				wp_redirect($dashboardurl.'?action=package&er='.$e->getMessage());
				exit;
			}
			
		}
		
		//Paypal Claim Payment Handle
		
		if(isset($_GET['payment']) and $_GET['payment']=='claim-paypal'){
			$ctable = $wpdb->prefix.'sld_claim_purchase';
			
	        $pkg = isset($_GET['pkg']) ? sanitize_text_field($_GET['pkg']) : '';

	        if(!empty($_POST)){
		        $txn_id = sanitize_text_field($_POST['txn_id']);
		        $name 	= sanitize_text_field($_POST['first_name']).' '.sanitize_text_field($_POST['last_name']);
		        $payer_email = sanitize_text_field($_POST['payer_email']);
		        $amount = sanitize_text_field($_POST['mc_gross']);
		        $date = date('Y-m-d H:i:s');

		        $wpdb->update(
			        $ctable,
			        array(
				        'transaction_id'=>$txn_id,
						'paid_amount'	=>$amount,
						'payer_name'	=>$name,
						'payment_method'=> 'paypal',
						'payer_email'	=>$payer_email
			        ),
                    array('id'=>$pkg),
                    array(
                        '%s',
                        '%d',
                        '%s',
                        '%s',
                    ),
                    array('%d')
		        );
				$this->sld_auto_approve_claim();
				wp_reset_query();
				$dashboardurl = qc_sld_login_page()->sldcustom_login_get_translated_option_page('sld_dashboard_url');
				wp_redirect($dashboardurl.'?action=claim&success='.urlencode('Your payment has beed successfull!'));
	        }
        }
		
		// Stripe claim payment handle
		
		if(isset($_GET['payment']) and $_GET['payment']=='claim-stripe'){
			
			$ctable = $wpdb->prefix.'sld_claim_purchase';
			$cptable = $wpdb->prefix.'sld_claim_configuration';
			
			require_once(SLD_QCOPD_INC_DIR.'/stripe-php-master/init.php');
			\Stripe\Stripe::setApiKey(sld_get_option('sld_stripe_sectet_key'));
			
	        $pkg 			= isset($_GET['pkg']) ? sanitize_text_field($_GET['pkg']) : '';
			
	        $claimpayment 	= $wpdb->get_row( $wpdb->prepare( "select * from $cptable where %d", 1 ) );

			$amount 	= ($claimpayment->Amount*100);
			$currency 	= $claimpayment->currency;
			$token  	= sanitize_text_field($_POST['stripeToken']);
			$email  	= sanitize_text_field($_POST['stripeEmail']);
			$customer 	= \Stripe\Customer::create(array(
				  'email' => $email,
				  'source'  => $token
			 ));
			$charge = \Stripe\Charge::create(array(
			  'customer' => $customer->id,
			  'amount'   => $amount,
			  'currency' => $currency
			));
	        if(!empty($_POST)){
		        $txn_id = $token;
		        $name = $email;
		        $payer_email = $email;
		        $amount = $claimpayment->Amount;
		        $wpdb->update(
			        $ctable,
			        array(
				        'transaction_id'=>$txn_id,
						'paid_amount'	=>$amount,
						'payer_name'	=>$name,
						'payment_method'=> 'stripe',
						'payer_email'	=>$payer_email
			        ),
                    array('id'=>$pkg),
                    array(
                        '%s',
                        '%d',
                        '%s',
                        '%s',
                    ),
                    array('%d')
		        );
				$this->sld_auto_approve_claim();
				wp_reset_query();
				$dashboardurl = qc_sld_login_page()->sldcustom_login_get_translated_option_page('sld_dashboard_url');
				wp_redirect($dashboardurl.'?action=claim&success='.urlencode('Your payment has beed successfull!'));
				exit;
	        }
        }
		
		// Mollie claim payment handle
		if(isset($_GET['payment']) and $_GET['payment']=='claim-mollie'){
			
			$ctable = $wpdb->prefix.'sld_claim_purchase';
			$cptable = $wpdb->prefix.'sld_claim_configuration';
			
			require_once(SLD_QCOPD_INC_DIR.'/mollie-api-php-master/examples/initialize.php');

			$mollie = new \Mollie\Api\MollieApiClient();
			$mollie->setApiKey(sld_get_option('sld_mollie_sectet_key'));

	        $pkg 			= isset($_GET['pkg']) ? sanitize_text_field($_GET['pkg']) : '';
			$orderId 		= $pkg;
	        $claimpayment 	= $wpdb->get_row( $wpdb->prepare(  "select * from $cptable where %d", 1 ) );

	        

			$amount 	=  number_format( (float)  $claimpayment->Amount,2,'.', '' );

			$currency 	= $claimpayment->currency;

			$pay_result = isset($_GET['pay_result']) ? $_GET['pay_result'] : '';


			if(!session_id()){
				session_start();
			}
			if(isset($_SESSION['pkg_id']) && empty($_SESSION['pkg_id'])){
				$_SESSION['pkg_id'] = $pkg;
			}


		    if( $pay_result !== 'success'){

			    $protocol = isset($_SERVER['HTTPS']) && strcasecmp('off', $_SERVER['HTTPS']) !== 0 ? "https" : "http";
			    $hostname = $_SERVER['HTTP_HOST'];
			    $path = dirname(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $_SERVER['PHP_SELF']);

				$payment = $mollie->payments->create([
			        "amount" 		=> [
			            "currency" 	=> $currency,
			            "value" 	=> $amount,
			        ],
			        "description" 	=> "Order #{$orderId}",
			        "redirectUrl" 	=> "{$protocol}://{$hostname}{$path}?payment=claim-mollie&pay_result=success",
			        "webhookUrl" 	=> "{$protocol}://{$hostname}{$path}/webhook.php",
			        "metadata" 		=> [
			            "order_id" 	=> $orderId,
			        ],
			    ]);

			    header("Location: " . $payment->getCheckoutUrl(), true, 303);

		    }


	        if( $pay_result == 'success' ){

				$txn_id 		= 'mollie';
				$name 			= $current_user->user_email;
				$payer_email 	= $current_user->user_email;
		        $amount 		= $claimpayment->Amount;
		        $wpdb->update(
			        $ctable,
			        array(
				        'transaction_id'=> $txn_id,
						'paid_amount'	=> $amount,
						'payer_name'	=> $name,
						'payment_method'=> 'mollie',
						'payer_email'	=> $payer_email,
						'status'  		=> 'approved'
			        ),
                    array('id'	=>	$_SESSION['pkg_id'] ),
                    array(
                        '%s',
                        '%d',
                        '%s',
                        '%s',
                    ),
                    array('%d')
		        );
				$this->sld_auto_approve_claim();
				wp_reset_query();
				unset($_SESSION['pkg_id']);
				$dashboardurl = qc_sld_login_page()->sldcustom_login_get_translated_option_page('sld_dashboard_url');
				wp_redirect($dashboardurl.'?action=claim&success='.urlencode('Your payment has beed successfull!'));
				exit;
	        }
        }
		
		if(isset($_GET['payment']) and $_GET['payment']=='claim-2co'){
			require_once(SLD_QCOPD_INC_DIR.'/2co-lib/Twocheckout.php');
			
			Twocheckout::privateKey(sld_get_option('sld_2checkout_sectet_key')); //Private Key
			Twocheckout::sellerId(sld_get_option('sld_2checkout_seller_id')); // 2Checkout Account Number
			if(sld_get_option('sld_enable_2checkout_sandbox')=="on"){
				Twocheckout::sandbox(true); // Set to false for production accounts.
			}else{
				Twocheckout::sandbox(false); // Set to false for production accounts.
			}
			
			$ctable = $wpdb->prefix.'sld_claim_purchase';
			$cptable = $wpdb->prefix.'sld_claim_configuration';
			$pkg = isset($_GET['pkg']) ? sanitize_text_field($_GET['pkg']) : '';
			$useremail = $current_user->user_email;
	        $claimpayment = $wpdb->get_row( $wpdb->prepare( "select * from $cptable where %d", 1) );
			

			try {
				$charge = Twocheckout_Charge::auth(array(
					"merchantOrderId" => substr(md5(microtime()),rand(0,26),5),
					"token"      => sanitize_text_field($_POST['token']),
					"currency"   => $currency,
					"total"      => sanitize_text_field($_POST['amount']),
					"billingAddr" => array(
						"name" => sanitize_text_field($_POST['name']),
						"addrLine1" => sanitize_text_field($_POST['address']),
						"city" => sanitize_text_field($_POST['city']),
						"state" => sanitize_text_field($_POST['state']),
						"zipCode" => sanitize_text_field($_POST['zipcode']),
						"country" => sanitize_text_field($_POST['country']),
						"email" => $useremail,
						"phoneNumber" => sanitize_text_field($_POST['phone'])
					)
				));

				if ($charge['response']['responseCode'] == 'APPROVED') {
					
					
					$txn_id = $charge['response']['transactionId'];
					$name = sanitize_text_field($_POST['name']);
					$payer_email = $charge['response']['billingAddr']['email'];
					$amount = sanitize_text_field($_POST['amount']);

					
					$wpdb->update(
						$ctable,
						array(
							'transaction_id'=>$txn_id,
							'paid_amount'	=>$amount,
							'payer_name'	=>$name,
							'payment_method'=> '2co',
							'payer_email'	=>$payer_email
						),
						array('id'=>$pkg),
						array(
							'%s',
							'%d',
							'%s',
							'%s',
						),
						array('%d')
					);
					$this->sld_auto_approve_claim();
					wp_reset_query();
					
					$dashboardurl = qc_sld_login_page()->sldcustom_login_get_translated_option_page('sld_dashboard_url');
					wp_redirect($dashboardurl.'?action=claim&success='.urlencode('Your payment has beed successfull!'));
					exit;
					
				}
			} catch (Twocheckout_Error $e) {
				//print_r($e->getMessage());
				$dashboardurl = qc_sld_login_page()->sldcustom_login_get_translated_option_page('sld_dashboard_url');
				wp_redirect($dashboardurl.'?action=claim&er='.$e->getMessage());
				exit;
			}
			
			
		}

		// Offline claim payment handle
		if(isset($_GET['payment']) && $_GET['payment'] == 'claim-offline' ){
			
			$ctable = $wpdb->prefix.'sld_claim_purchase';
			$cptable = $wpdb->prefix.'sld_claim_configuration';
			
	        $pkg = isset($_GET['pkg']) ? sanitize_text_field($_GET['pkg']) : '';
			
	        $claimpayment = $wpdb->get_row( $wpdb->prepare( "select * from $cptable where %d", 1) );




	        if(empty($_POST)){

	        	// var_dump($pkg);
	       		// wp_die();

		        //$txn_id = $token;
		        $name = $email;
		        $payer_email = $email;
		        $amount = $claimpayment->Amount;
		        $wpdb->update(
			        $ctable,
			        array(
				        'transaction_id'=>'offline',
						'paid_amount'	=>$amount,
						'payer_name'	=>$name,
						'payment_method'=> 'offline',
						'payer_email'	=>$payer_email
			        ),
                    array('id'=>$pkg),
                    array(
                        '%s',
                        '%d',
                        '%s',
                        '%s',
                    ),
                    array('%d')
		        );
				$this->sld_auto_approve_claim();
				wp_reset_query();
				$dashboardurl = qc_sld_login_page()->sldcustom_login_get_translated_option_page('sld_dashboard_url');
				wp_redirect($dashboardurl.'?action=claim&success='.urlencode('Your payment has beed successfull!'));
				exit;
	        }
        }
		

    }

    public function sld_auto_approve_claim(){
    	global $wpdb;
    	$current_user = wp_get_current_user();
    	$auto_approve_claim = sld_get_option('sld_auto_approve_claim_listing');
    	if( $auto_approve_claim == 'on' ){
			$user_id 	= $current_user->ID;
			$pkg 		= isset($_GET['pkg']) ? sanitize_text_field($_GET['pkg']) : '';
			$ctable 	= $wpdb->prefix.'sld_claim_purchase';
			$pdata 		= $wpdb->get_row( $wpdb->prepare( "Select * from $ctable where 1 and `id`=%d", $pkg ) );
			$listId 	= $pdata->listid;
			$listItem 	= $pdata->item;
			$lists 		= get_post_meta( $listId, 'qcopd_list_item01' );
					
			$userentry 	= $wpdb->prefix.'sld_user_entry';
			
			$category 	= get_the_terms($listId,'sld_cat');
			if(!empty($category)){
				$catslug = $category[0]->slug;
			}else{
				$catslug = '';
			}
			
			
			$datetime = date('Y-m-d H:i:s');
			
			foreach($lists as $item){
				if($item['qcopd_item_title']==$listItem){
					if( !isset($item['qcopd_item_img_link']) || empty($item['qcopd_item_img_link']) ){
						$item['qcopd_item_img_link'] = '';
					}
					if( !isset($item['qcopd_item_nofollow']) || empty($item['qcopd_item_nofollow']) ){
						$item['qcopd_item_nofollow'] = '';
					}
					$wpdb->insert(
						$userentry,
						array(
							'item_title'  => $item['qcopd_item_title'],
							'item_link'   => $item['qcopd_item_link'],
							'item_subtitle' => $item['qcopd_item_subtitle'],
							'category'   => $catslug,
							'sld_list'  => $listId,
							'user_id'=>  $user_id,
							'image_url'=> $item['qcopd_item_img_link'],
							'time'=> $datetime,
							'nofollow'=> $item['qcopd_item_nofollow'],
							'custom'=>$item['qcopd_timelaps'],
							'approval'=> (isset($pdata->payment_method) && $pdata->payment_method == 'offline') ? 0 : 1
							
						)
					);
					break;
					
				}
			}
			
			
			if($pdata->payment_method !== 'offline'){

				$wpdb->update(
					$ctable,
					array(
						'status'  => 'approved'
					),
					array( 'id' => $pkg),
					array(
						'%s',
					),
					array( '%d')
				);

			}
		}
    }

	public function sld_new_item_notification( $user_id, $item) {
        $user = new WP_User($user_id);

        $user_login = ($user->user_login);

        $sld_lan_email_new_link_text = sld_get_option('sld_lan_email_new_link_text') != '' ? sld_get_option('sld_lan_email_new_link_text') : __('A New link has been submitted to your list', 'qc-opd');
        $sld_lan_email_new_link_item_text = sld_get_option('sld_lan_email_new_link_item_text') != '' ? sld_get_option('sld_lan_email_new_link_item_text') : __('Please go to Simple Link Directory > Manage User Items to view this link', 'qc-opd');
        $sld_lan_email_item_name_text = sld_get_option('sld_lan_email_item_name_text') != '' ? sld_get_option('sld_lan_email_item_name_text') : __('Item Name', 'qc-opd');
        $sld_lan_email_item_submitted_by_text = sld_get_option('sld_lan_email_item_submitted_by_text') != '' ? sld_get_option('sld_lan_email_item_submitted_by_text') : __('Item Submitted By', 'qc-opd');


        $message  = sprintf(__('%s %s. %s.'), $sld_lan_email_new_link_text, get_option('blogname'), $sld_lan_email_new_link_item_text ) . "\r\n\r\n";
		$message .= sprintf(__('%s : %s'), $sld_lan_email_item_name_text, $item) . "\r\n\r\n";
        $message .= sprintf(__('%s : %s'), $sld_lan_email_item_submitted_by_text, $user_login) . "\r\n\r\n";


        @wp_mail(sld_get_option('sld_admin_email'), sprintf(__('[%s] %s!'), get_option('blogname'), $sld_lan_email_new_link_text ), $message);

    }

	public function sld_new_item_notification_offline_payments( $user_id, $item) {
        $user = new WP_User($user_id);

        $user_login = ($user->user_login);

        $sld_lan_email_new_offline_text = sld_get_option('sld_lan_email_new_offline_text') != '' ? sld_get_option('sld_lan_email_new_offline_text') : __('A New offline payments link has been submitted to your list', 'qc-opd');
        $sld_lan_email_new_offline_msg_text = sld_get_option('sld_lan_email_new_offline_msg_text') != '' ? sld_get_option('sld_lan_email_new_offline_msg_text') : __('Please go to Simple Link Directory > Manage User Links to view this link', 'qc-opd');
        $sld_lan_email_item_name_text = sld_get_option('sld_lan_email_item_name_text') != '' ? sld_get_option('sld_lan_email_item_name_text') : __('Item Name', 'qc-opd');
        $sld_lan_email_item_submitted_by_text = sld_get_option('sld_lan_email_item_submitted_by_text') != '' ? sld_get_option('sld_lan_email_item_submitted_by_text') : __('Item Submitted By', 'qc-opd');

        $message  = sprintf(__('%s %s. %s.'), $sld_lan_email_new_offline_text, get_option('blogname'), $sld_lan_email_new_offline_msg_text ) . "\r\n\r\n";
		$message .= sprintf(__('%s : %s'), $sld_lan_email_item_name_text, $item) . "\r\n\r\n";
        $message .= sprintf(__('%s : %s'), $sld_lan_email_item_submitted_by_text, $user_login) . "\r\n\r\n";

        @wp_mail(sld_get_option('sld_admin_email'), sprintf(__('[%s] %s!'), get_option('blogname'), $sld_lan_email_new_offline_text ), $message);

    }

	public function sld_new_item_notification_link_exchange( $user_id, $item) {
        $user = new WP_User($user_id);

        $user_login = ($user->user_login);

        $sld_lan_email_new_link_text = sld_get_option('sld_lan_email_new_link_text') != '' ? sld_get_option('sld_lan_email_new_link_text') : __('A New link has been submitted to your list', 'qc-opd');
        $sld_lan_email_new_link_exchange_text = sld_get_option('sld_lan_email_new_link_exchange_text') != '' ? sld_get_option('sld_lan_email_new_link_exchange_text') : __('Please go to Link Exchange > Manage User Links to view this link', 'qc-opd');
        $sld_lan_email_item_name_text = sld_get_option('sld_lan_email_item_name_text') != '' ? sld_get_option('sld_lan_email_item_name_text') : __('Item Name', 'qc-opd');
        $sld_lan_email_item_submitted_by_text = sld_get_option('sld_lan_email_item_submitted_by_text') != '' ? sld_get_option('sld_lan_email_item_submitted_by_text') : __('Item Submitted By', 'qc-opd');

        $message  = sprintf(__('%s %s. %s.'), $sld_lan_email_new_link_text, get_option('blogname'), $sld_lan_email_new_link_exchange_text) . "\r\n\r\n";
		$message .= sprintf(__('%s : %s'), $sld_lan_email_item_name_text, $item) . "\r\n\r\n";
        $message .= sprintf(__('%s : %s'), $sld_lan_email_item_submitted_by_text, $user_login) . "\r\n\r\n";

        @wp_mail(sld_get_option('sld_admin_email'), sprintf(__('[%s] %s!'), get_option('blogname'), $sld_lan_email_new_link_text ), $message);

    }
	
	public function sld_edit_item_notification( $user_id, $item) {
        $user = new WP_User($user_id);

        $user_login = ($user->user_login);

        $sld_lan_email_edit_link_text = sld_get_option('sld_lan_email_edit_link_text') != '' ? sld_get_option('sld_lan_email_edit_link_text') : __('A link has been edited to your list', 'qc-opd');
        $sld_lan_email_edit_msg_item_text = sld_get_option('sld_lan_email_edit_msg_item_text') != '' ? sld_get_option('sld_lan_email_edit_msg_item_text') : __('Please go to Simple Link Directory > Manage User Items to view this link', 'qc-opd');
        $sld_lan_email_item_edit_text = sld_get_option('sld_lan_email_item_edit_text') != '' ? sld_get_option('sld_lan_email_item_edit_text') : __('Item Edited', 'qc-opd');
        $sld_lan_email_item_edited_by_text = sld_get_option('sld_lan_email_item_edited_by_text') != '' ? sld_get_option('sld_lan_email_item_edited_by_text') : __('Item Edited By', 'qc-opd');

        $message  = sprintf(__('%s %s. %s.'), $sld_lan_email_edit_link_text, get_option('blogname'), $sld_lan_email_edit_msg_item_text ) . "\r\n\r\n";
		$message .= sprintf(__('%s : %s'), $sld_lan_email_item_edit_text, $item) . "\r\n\r\n";
        $message .= sprintf(__('%s : %s'), $sld_lan_email_item_edited_by_text, $user_login) . "\r\n\r\n";

        @wp_mail(sld_get_option('sld_admin_email'), sprintf(__('[%s] %s!'), get_option('blogname'), $sld_lan_email_edit_link_text ), $message);

    }
	
	public function sld_edit_item_notification_link_exchange( $user_id, $item) {
        $user = new WP_User($user_id);

        $user_login = ($user->user_login);

        $sld_lan_email_edit_link_text = sld_get_option('sld_lan_email_edit_link_text') != '' ? sld_get_option('sld_lan_email_edit_link_text') : __('A link has been edited to your list', 'qc-opd');
        $sld_lan_email_new_link_exchange_text = sld_get_option('sld_lan_email_new_link_exchange_text') != '' ? sld_get_option('sld_lan_email_new_link_exchange_text') : __('Please go to Link Exchange > Manage User Links to view this link', 'qc-opd');
        $sld_lan_email_item_edit_text = sld_get_option('sld_lan_email_item_edit_text') != '' ? sld_get_option('sld_lan_email_item_edit_text') : __('Item Edited', 'qc-opd');
        $sld_lan_email_item_edited_by_text = sld_get_option('sld_lan_email_item_edited_by_text') != '' ? sld_get_option('sld_lan_email_item_edited_by_text') : __('Item Edited By', 'qc-opd');

        $message  = sprintf(__('%s %s. %s.'), $sld_lan_email_edit_link_text, get_option('blogname'), $sld_lan_email_new_link_exchange_text ) . "\r\n\r\n";
		$message .= sprintf(__('%s : %s'), $sld_lan_email_item_edit_text, $item) . "\r\n\r\n";
        $message .= sprintf(__('%s : %s'), $sld_lan_email_item_edited_by_text, $user_login) . "\r\n\r\n";

        @wp_mail(sld_get_option('sld_admin_email'), sprintf(__('[%s] %s!'), get_option('blogname'), $sld_lan_email_edit_link_text ), $message);

    }
	
	public function sld_edit_item_notification_offline_payments( $user_id, $item) {
        $user = new WP_User($user_id);

        $user_login = ($user->user_login);

        $sld_lan_email_edit_offline_text = sld_get_option('sld_lan_email_edit_offline_text') != '' ? sld_get_option('sld_lan_email_edit_offline_text') : __('A offline payments link has been edited to your list', 'qc-opd');
        $sld_lan_email_edit_msg_item_text = sld_get_option('sld_lan_email_edit_msg_item_text') != '' ? sld_get_option('sld_lan_email_edit_msg_item_text') : __('Please go to Simple Link Directory > Manage User Items to view this link', 'qc-opd');
        $sld_lan_email_item_edit_text = sld_get_option('sld_lan_email_item_edit_text') != '' ? sld_get_option('sld_lan_email_item_edit_text') : __('Item Edited', 'qc-opd');
        $sld_lan_email_item_edited_by_text = sld_get_option('sld_lan_email_item_edited_by_text') != '' ? sld_get_option('sld_lan_email_item_edited_by_text') : __('Item Edited By', 'qc-opd');

        $message  = sprintf(__('A offline payments link has been edited to your list %s. %s.'), $sld_lan_email_edit_offline_text, get_option('blogname'), $sld_lan_email_edit_msg_item_text ) . "\r\n\r\n";
		$message .= sprintf(__('%s : %s'), $sld_lan_email_item_edit_text, $item) . "\r\n\r\n";
        $message .= sprintf(__('%s : %s'), $sld_lan_email_item_edited_by_text, $user_login) . "\r\n\r\n";

        @wp_mail(sld_get_option('sld_admin_email'), sprintf(__('[%s] %s!'), get_option('blogname'), $sld_lan_email_edit_offline_text ), $message);

    }

	public function sld_claim_notification($user_id, $item) {
		
        $user = new WP_User($user_id);

        $user_login = ($user->user_login);

        $sld_lan_email_claimed_lists_text = sld_get_option('sld_lan_email_claimed_lists_text') != '' ? sld_get_option('sld_lan_email_claimed_lists_text') : __('A item has been claimed from your list', 'qc-opd');
        $sld_lan_email_claimed_lists_msg_text = sld_get_option('sld_lan_email_claimed_lists_msg_text') != '' ? sld_get_option('sld_lan_email_claimed_lists_msg_text') : __('Please go to Simple Link Directory > Claimed Listing to view this claimed item.', 'qc-opd');
        $sld_lan_email_claimed_item = sld_get_option('sld_lan_email_claimed_item') != '' ? sld_get_option('sld_lan_email_claimed_item') : __('Item Claimed', 'qc-opd');
        $sld_lan_email_claimed_iem_by = sld_get_option('sld_lan_email_claimed_iem_by') != '' ? sld_get_option('sld_lan_email_claimed_iem_by') : __('Item Claimed By', 'qc-opd');

        $message  = sprintf(__('%s %s. %s.'), $sld_lan_email_claimed_lists_text, get_option('blogname'), $sld_lan_email_claimed_lists_msg_text) . "\r\n\r\n";
        $message .= sprintf(__('%s : %s'), $sld_lan_email_claimed_item, $item) . "\r\n\r\n";
        $message .= sprintf(__('%s : %s'), $sld_lan_email_claimed_iem_by, $user_login) . "\r\n\r\n";


        @wp_mail(sld_get_option('sld_admin_email'), sprintf(__('[%s] %s!'), get_option('blogname'), $sld_lan_email_claimed_lists_text), $message);

    }
	
	
	/*
	*
	* Admin Area integration
	*/
	public function qc_sld_admin_area(){
		return Sld_user_entry::get_instance();
	}
	
	public function sldcustom_user_permission_add(){
		$current_user = wp_get_current_user();

		if(is_user_logged_in() && in_array('slduser',$current_user->roles)){
			$current_user->add_cap('upload_files');
		}
		if(is_user_logged_in() && in_array('subscriber',$current_user->roles)){
			
			if(sld_get_option('sld_subscriber_image_upload')=='on'){
				$current_user->add_cap('upload_files');
			}else{
				$current_user->remove_cap('upload_files');
			}
			
		}
		
	}

    /**
     * Approve Subscriber profile.
     *
     * @return null
     */

    public function approve_subscriber_profile($id){
        global $wpdb;

        $sql = $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix."sld_user_entry where 1 and id =%d", $id );
        $identifier = time();
        $pdata = $wpdb->get_row($sql);
		
		$featured = 0;
		if($pdata->package_id > 0){
			$featured = 1;
		}
		
		if(sld_get_option('sld_paid_item_featured')!='on'){
			$featured = 0;
		}

		$qcopd_tags = isset($pdata->qcopd_tags) ? $pdata->qcopd_tags : '';
		
        if( $pdata->approval==0 || $pdata->approval==2){
            $prepare = array( //preparing Meta
                'qcopd_item_title' 			=> sanitize_text_field($pdata->item_title),
                'qcopd_item_link' 			=> trim($pdata->item_link),
                'qcopd_item_subtitle' 		=> sanitize_text_field($pdata->item_subtitle),
				'qcopd_description' 		=> sanitize_text_field($pdata->description),
                'qcopd_item_img_link' 		=> trim($pdata->image_url),
                'qcopd_fa_icon' 			=> '',
                'qcopd_item_img' 			=> '',
                'qcopd_item_nofollow' 		=> ($pdata->nofollow==1?1:0),
                'qcopd_item_newtab' 		=> 1,
                'qcopd_use_favicon' 		=> 1,
                'qcopd_upvote_count' 		=> 0,
                'qcopd_entry_time' 			=> date('Y-m-d H:i:s'),
                'qcopd_timelaps' 			=> $identifier,
				'qcopd_featured'			=> $featured,
				'qcopd_tags'				=> $qcopd_tags

            );

            add_post_meta( trim($pdata->sld_list), 'qcopd_list_item01', $prepare );

            $wpdb->update(
                $wpdb->prefix.'sld_user_entry',
                array(
                    'custom'  => $identifier,
                    'approval'=> 1
                ),
                array( 'id' => $id),
                array(
                    '%s',
                    '%d',
                ),
                array( '%d')
            );
			wp_reset_query();
        }elseif($pdata->approval==3){

            $sql = $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix."sld_user_entry where 1 and id =%d", $id );
            $pdata = $wpdb->get_row($sql);
            $identifier = time();
			
			
			
			$upvoteCount = 0;
            if($pdata->custom!=''){
				
				$searchQuery = $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix."postmeta WHERE 1 and `post_id` = %d and `meta_key` = 'qcopd_list_item01'", $pdata->sld_list );
				$results = @$wpdb->get_results($searchQuery);
				
				foreach($results as $result){
					$unserialize = unserialize($result->meta_value);
					if($pdata->custom == $unserialize['qcopd_timelaps']){
						$upvoteCount = $unserialize['qcopd_upvote_count'];
					}
				}
				
				
                $this->deny_subscriber_profile($id);
            }
			

            $prepare = array( //preparing Meta
                'qcopd_item_title' 			=> sanitize_text_field($pdata->item_title),
                'qcopd_item_link' 			=> trim($pdata->item_link),
                'qcopd_item_subtitle' 		=> sanitize_text_field($pdata->item_subtitle),
				'qcopd_description' 		=> sanitize_text_field($pdata->description),
                'qcopd_item_img_link' 		=> trim($pdata->image_url),
                'qcopd_fa_icon' 			=> '',
                'qcopd_item_img' 			=> '',
                'qcopd_item_nofollow' 		=> ($pdata->nofollow==1?1:0),
                'qcopd_item_newtab' 		=> 1,
                'qcopd_use_favicon' 		=> 1,
                'qcopd_upvote_count' 		=> $upvoteCount,
                'qcopd_entry_time' 			=> date('Y-m-d H:i:s'),
                'qcopd_timelaps' 			=> $identifier,
				'qcopd_featured'			=> $featured,
				'qcopd_tags'				=> $qcopd_tags

            );

            add_post_meta( trim($pdata->sld_list), 'qcopd_list_item01', $prepare );

            $wpdb->update(
                $wpdb->prefix.'sld_user_entry',
                array(
                    'custom'  => $identifier,
                    'approval'=> 1
                ),
                array( 'id' => $id),
                array(
                    '%s',
                    '%d',
                ),
                array( '%d')
            );
			wp_reset_query();
        }

    }

    /**
     * Delete User Entry.
     *
     * @return null
     */

    public function delete_subscriber_profile( $id ) {
        global $wpdb;

        $sql 	= $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix."sld_user_entry where 1 and id =%d", $id );
        $pdata 	= $wpdb->get_row($sql);
		
        if(@$pdata->approval==1){
            $this->deny_subscriber_profile($id);
        }
		
		
        $wpdb->delete(
            "{$wpdb->prefix}sld_user_entry",
            array( 'id' => $id ),
            array( '%d' )
        );


    }
	public function clean($string) {
		$string = str_replace(' ', '-', trim($string)); // Replaces all spaces with hyphens.

		return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
	}
    /**
     * Deny User Entry.
     *
     * @return null
     */

    public function deny_subscriber_profile($id){
        global $wpdb;

        $sql = $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix."sld_user_entry where 1 and id =%d", $id );
        $identifier = time();
        $pdata = $wpdb->get_row($sql);


		if( $pdata->approval==1 || $pdata->approval==3 ){

            $searchQuery = $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix."postmeta WHERE 1 and `post_id` = %d and `meta_key` = 'qcopd_list_item01'", $pdata->sld_list );
            $results = @$wpdb->get_results($searchQuery);
			
			foreach($results as $result){
				
				$unserialize = unserialize($result->meta_value);
				if($pdata->custom == $unserialize['qcopd_timelaps']){
					
					$meta_id = @$result->meta_id;

					@$wpdb->delete(
						"{$wpdb->prefix}postmeta",
						array( 'meta_id' => $meta_id ),
						array( '%d' )
					);

				}
				
				
			}
			
			$wpdb->update(
				$wpdb->prefix.'sld_user_entry',
				array(
					'custom'  => '',
					'approval'=> 2
				),
				array( 'id' => $id),
				array(
					'%s',
					'%d',
				),
				array( '%d')
			);

        }

    }

	/*
	*Wp enqueue Script
	* Load stylesheet.
	*/
	public function sldcustom_dashboard_enqueue_style(){
		wp_register_style( 'sldcustom_dashboard-css', SLD_QCOPD_ASSETS_URL.'/css/dashboardstyle.css', __FILE__ );
		wp_enqueue_style( 'sldcustom_dashboard-css' );

		$customCss = sld_get_option( 'sld_custom_style' );

		if( trim($customCss) != "" ) :

			wp_add_inline_style( 'sldcustom_dashboard-css', $customCss );
	

		endif;


	}

	/*
	*Wp ajax function
	*Category filter
	*/
	public function qcld_sld_category_filter_fnc(){
		
		$cateogy = isset($_POST['cat']) ? sanitize_text_field($_POST['cat']) : '';
		
		if($cateogy!=''){
			$sld = new WP_Query( array( 
				'post_type' => 'sld',
				'tax_query' => array(
					array (
						'taxonomy' 	=> 'sld_cat',
						'field' 	=> 'name',
						'terms' 	=> $cateogy,
					)
				),
				'posts_per_page' => -1,
				'order' => 'ASC',
				'orderby' => 'menu_order'
				) 
			);
		}else{
			$sld = new WP_Query( array( 
				'post_type' => 'sld',				
				'posts_per_page' => -1,
				'order' => 'ASC',
				'orderby' => 'menu_order'
				) 
			);
		}
		
		
		
		$excludel = sld_get_option('sld_exclude_list');
		
		while( $sld->have_posts() ) : $sld->the_post();
		?>
			<?php 
			if($excludel!=''){
				$exclude = explode(',',$excludel);
				if(!in_array(get_the_ID(),$exclude)){
					?>
					<option value="<?php echo get_the_ID(); ?>"><?php the_title(); ?></option>
					<?php
				}
			}else{
			?>
			<option value="<?php echo get_the_ID(); ?>"><?php the_title(); ?></option>
			<?php
			}
			?>
			
		<?php
		endwhile;
				
		die();
		
	}

    /*
    *
    * Load before header
    */
    function sldcustom_redirect_load_before_headers()
    {
        if( sld_get_option('sld_auto_redirect_login_page') == 'on' ){
        	if( !is_user_logged_in() ){
	        	global $post;
			    if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'sld_dashboard') ) {
		     		$url = qc_sld_login_page()->sldcustom_login_get_translated_option_page('sld_login_url');
		        	// echo $url;
		        	wp_safe_redirect($url);
		        	exit();
			    }
        	}
        }
        if (isset($_GET['sldact']) and sanitize_text_field($_GET['sldact']) == 'logout') {
            wp_logout();
            $url = get_home_url();
            wp_safe_redirect($url);
        }
    }

	/*
	*
	* GET Lists Status
	*/
	public function getStatus($args){

		$pending 	= (sld_get_option('sld_lan_frontend_pending')!=''?sld_get_option('sld_lan_frontend_pending'):esc_html('Pending', 'qc-opd'));
		$approved 	= (sld_get_option('sld_lan_frontend_approved')!=''?sld_get_option('sld_lan_frontend_approved'):esc_html('Approved', 'qc-opd'));
		$deny 		= (sld_get_option('sld_lan_frontend_deny')!=''?sld_get_option('sld_lan_frontend_deny'):esc_html('Deny', 'qc-opd'));
		$edited 	= (sld_get_option('sld_lan_frontend_edited')!=''?sld_get_option('sld_lan_frontend_edited'):esc_html('Edited', 'qc-opd'));

		if($args==0){
			return '<span style="color:#f4b042">'.__( $pending, 'qc-opd').'</span>';
		}elseif($args==1){
			return '<span style="color:green">'.__( $approved, 'qc-opd').'</span>';
		}elseif($args==2){
			return '<span style="color:red">'.__( $deny, 'qc-opd').'</span>';
		}else{
			return '<span style="color:#f4b042">'.__( $edited, 'qc-opd').'</span>';
		}
	}

	/*
	*
	* GET Image
	*/
	public function getImage($args){
		if($args!=''){
			echo '<img src="'.$args.'" width="50"/>';
		}else{
			echo '<img src="'.SLD_QCOPD_IMG_URL.'/no-image.png'.'" width="50"/>';
		}
	}	
	
	public function sld_dashboard_show(){
		wp_enqueue_script( 'jquery-ui-autocomplete' );
		wp_enqueue_style( 'sld-ui-autocomplete', SLD_QCOPD_ASSETS_URL . '/css/jquery_ui.css', array());
		wp_enqueue_script( 'qcopd-tag-input',  SLD_QCOPD_ASSETS_URL . '/js/tagInput.js', array('jquery')); //category tab
		wp_enqueue_script( 'qcopd-custom1-script'); //category tab
		wp_enqueue_script( 'qcopd-custom-script');
		wp_enqueue_script( 'qcopd-grid-packery');
		wp_enqueue_script( 'jq-slick.min-js');
		wp_enqueue_style('qcopd-admin-fa', SLD_QCOPD_ASSETS_URL . '/css/admin-fa-css.css', array() );
		wp_enqueue_style( 'jq-slick-theme-css');
		wp_enqueue_style( 'sldcustom_dashboard-css');
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
		global $wpdb;

		$current_user = wp_get_current_user();
		$table             = $wpdb->prefix.'sld_user_entry';
		$package_purchased_table = $wpdb->prefix.'sld_package_purchased';
        $package_table = $wpdb->prefix.'sld_package';
		
		if(is_user_logged_in() && (in_array('slduser',$current_user->roles) or in_array('administrator',$current_user->roles) or sld_get_option('sld_enable_anyusers')=='on')){
			
			$url = qc_sld_login_page()->sldcustom_login_get_translated_option_page('sld_dashboard_url');

			//check whether package enable or not
			
			$get_package = $wpdb->get_row( $wpdb->prepare( "select * from $package_table where 1 limit %d",1) );
	
			
            //$itempurchase = $wpdb->get_row( $wpdb->prepare( "select sum(p.item)as cnt from $package_table as p, $package_purchased_table as pd where pd.package_id = p.id and pd.user_id =%d", $current_user->ID ) );
            $itempurchase = $wpdb->get_row( $wpdb->prepare( "select sum(p.item)as cnt from $package_table as p, $package_purchased_table as pd where pd.package_id = p.id and pd.user_id =%d", $current_user->ID ) );

            if(!empty($get_package) and $get_package->enable==1){
	            if($itempurchase->cnt!='' and $itempurchase->cnt > 0){
		            $this->show_package = true;
		            $this->total_item = $itempurchase->cnt;
	            }else{
		            $this->show_package = true;
	            }
            }else{
                $this->show_package = false;
            }

			if(sld_get_option('sld_enable_free_submission')=='on'){
				if(sld_get_option('sld_free_item_limit')!=''){
					$this->total_item += sld_get_option('sld_free_item_limit');
                }
            }

            //find total submited item
           // $submited_item = $wpdb->get_row( $wpdb->prepare( "select count(*)as cnt from $table where 1 and user_id =%d", $current_user->ID) );
            $submited_item = $wpdb->get_row( "select count(*)as cnt from $table where 1 and user_id =" .$current_user->ID );
			if($submited_item->cnt==''){
				$this->submited_item = 0;
            }else{
			    $this->submited_item = $submited_item->cnt;
            }

			if($this->total_item > 0){
			    if($this->total_item > $submited_item->cnt){
				    $this->remain_item = ($this->total_item - $submited_item->cnt);
				    $this->allow_item_submit = true;
                }else{
			        $this->remain_item = 0;
				    $this->allow_item_submit = false;
                }
            }else{
			    $this->remain_item = 0;
				$this->allow_item_submit = false;
            }

			if(is_user_logged_in() && in_array('administrator',$current_user->roles)){
				$this->allow_item_submit = true;
			}

			if ( class_exists( 'Qcld_backlink_exchange_control' ) && ( get_option('qcld_link_exchange_enable') == 1) ){
				$this->allow_item_submit = true;
			}


?>
		<div class="sld_dashboard_main_area">
			<nav class="sldnav sldnav--red">
				<ul class="sldnav__list">
				
					<li class="sldnav__list__item <?php echo (!isset($_GET['action'])?'sldactive':''); ?>"><a href="<?php echo $url; ?>"><?php echo (sld_get_option('sld_lan_frontend_tab_dashboard')!=''?sld_get_option('sld_lan_frontend_tab_dashboard'):__('Dashboard', 'qc-opd')); ?></a></li>
                    

					<li class="sldnav__list__item <?php echo (isset($_GET['action'])&&$_GET['action']=='entry'?'sldactive':''); ?>"><a href="<?php echo esc_url( add_query_arg( 'action', 'entry', $url ) ) ?>"><?php echo (sld_get_option('sld_lan_frontend_tab_add_link')!=''?sld_get_option('sld_lan_frontend_tab_add_link'):__('Add Link', 'qc-opd')); ?></a></li>
					
					<li class="sldnav__list__item <?php echo (isset($_GET['action'])&&$_GET['action']=='entrylist'?'sldactive':''); ?>"><a href="<?php echo esc_url( add_query_arg( 'action', 'entrylist', $url ) ) ?>"><?php echo (sld_get_option('sld_lan_frontend_tab_your_links')!=''?sld_get_option('sld_lan_frontend_tab_your_links'):__('Your Links', 'qc-opd')); ?></a></li>
				<?php if(sld_get_option('sld_enable_claim_listing')=='on'): ?>
                   <li class="sldnav__list__item <?php echo (isset($_GET['action'])&&$_GET['action']=='claim'?'sldactive':''); ?>">
                   		<a href="<?php echo esc_url( add_query_arg( 'action', 'claim', $url ) ) ?>">
                   			<?php
                   				if( sld_get_option('sld_lan_claim_list') ){
                   					echo sld_get_option('sld_lan_claim_list');
                   				}else{
                   					echo __('Claim Listing', 'qc-opd');
                   				}
                   			?>
                   		</a>
                   </li>
				<?php endif; ?>
                <?php if($this->show_package==true): ?>
                <li class="sldnav__list__item <?php echo (isset($_GET['action'])&&$_GET['action']=='package'?'sldactive':''); ?>"><a href="<?php echo esc_url( add_query_arg( 'action', 'package', $url ) ) ?>"><?php echo (sld_get_option('sld_lan_frontend_tab_Package')!=''?sld_get_option('sld_lan_frontend_tab_Package'):__('Package', 'qc-opd')); ?></a></li>
                <?php endif; ?>

<!--					<li class="sldnav__list__item --><?php //echo (isset($_GET['action'])&&$_GET['action']=='payment'?'sldactive':''); ?><!--"><a href="--><?php //echo esc_url( add_query_arg( 'action', 'payment', $url ) ) ?><!--">Payment</a></li>-->
					
<!--					<li class="sldnav__list__item --><?php //echo (isset($_GET['action'])&&$_GET['action']=='help'?'sldactive':''); ?><!--"><a href="--><?php //echo esc_url( add_query_arg( 'action', 'help', $url ) ) ?><!--">Help</a></li>-->

                    <li class="sldnav__list__item <?php echo (isset($_GET['sldact'])&&$_GET['sldact']=='logout'?'sldactive':''); ?>"><a href="<?php echo esc_url( add_query_arg( 'sldact', 'logout', $url ) ) ?>"> <?php echo (sld_get_option('sld_lan_frontend_tab_logout')!=''?sld_get_option('sld_lan_frontend_tab_logout'):__('Logout', 'qc-opd')); ?> </a></li>

				</ul>
			</nav>
			
			<?php if(!isset($_GET['action']) and !isset($_GET['payment']) )://Dashboard ?>
			
			
				<?php 
				$userpkgs = $wpdb->get_results( $wpdb->prepare( "select * from $package_purchased_table where 1 and user_id = %d order by date DESC", $current_user->ID ) );
				if(!empty($userpkgs)):
					foreach($userpkgs as $userpkg):			
						if(strtotime(date('Y-m-d')) < strtotime($userpkg->expire_date)):
						?>
							<div class="sld_package_notification">Your package <b><?php echo $get_package->title; ?></b> will expire on <b><?php echo( date( "Y-m-d", strtotime( $userpkg->expire_date ) ) ) ?></b> </div>
						<?php
						else:
						?>
							<div class="sld_package_notification">Your package <b><?php echo $get_package->title; ?></b> is already expired on <b><?php echo( date( "Y-m-d", strtotime( $userpkg->expire_date ) ) ) ?></b> </div>
						<?php
						endif;

					endforeach;
				endif;
				?>

                <?php

                if(isset($_POST['first_name']) && $_POST['first_name']!=''){
	                $user_id = $current_user->ID;
	                $user_data = wp_update_user( array( 'ID' => $user_id, 'first_name' => $_POST['first_name'], 'last_name' =>$_POST['last_name'], 'user_email'=> $_POST['user_email'], 'user_login'=> $_POST['user_login'] ) );
	                if ( is_wp_error( $user_data ) ) {
		                echo '<p style="color:red;font-size: 20px;">'.(sld_get_option('sld_lan_frontend_something_wrong')!=''?sld_get_option('sld_lan_frontend_something_wrong'):__('Something Went Wrong.', 'qc-opd')).'</p>';
	                } else {
		                // Success!
		                echo '<p style="color:green;font-size: 20px;">'.(sld_get_option('sld_lan_frontend_update_profile_text')!=''?sld_get_option('sld_lan_frontend_update_profile_text'):__('User profile updated.', 'qc-opd')).'</p>';
	                }
                }
                ?>
                
				<h2>
					<?php 
					$fullName = $current_user->user_firstname.' '.$current_user->user_lastname;

					$dashboard_lan_text_to_hi = sld_get_option('dashboard_lan_text_to_hi')?sld_get_option('dashboard_lan_text_to_hi'): 'Hi';
					$dashboard_lan_text_to_welcome = sld_get_option('dashboard_lan_text_to_welcome')?sld_get_option('dashboard_lan_text_to_welcome'): ('Welcome to your Dashboard.');

					echo sprintf(__( '%s %s, %s', 'qc-opd' ),$dashboard_lan_text_to_hi, $fullName, $dashboard_lan_text_to_welcome );
					?>
				</h2>
				
                <table class="sld_total_count">
                    <?php
                    if(in_array('administrator',$current_user->roles)){
                        ?>
                        <tr>
                            <td><?php echo (sld_get_option('dashboard_lan_text_total_link')!=''?sld_get_option('dashboard_lan_text_total_link'):__('Total Link','qc-opd')) ?> : <?php echo (sld_get_option('dashboard_lan_text_unlimited')!=''?sld_get_option('dashboard_lan_text_unlimited'):__('Unlimited','qc-opd')) ?></td>
                            <td><?php echo (sld_get_option('dashboard_lan_text_submited_link')!=''?sld_get_option('dashboard_lan_text_submited_link'):__('Submited Link','qc-opd')) ?> : <?php echo $this->submited_item; ?></td>
                            <td><?php echo (sld_get_option('dashboard_lan_text_remaining_link')!=''?sld_get_option('dashboard_lan_text_remaining_link'):__('Remaining Link','qc-opd')) ?> : <?php echo (sld_get_option('dashboard_lan_text_unlimited')!=''?sld_get_option('dashboard_lan_text_unlimited'):__('Unlimited','qc-opd')) ?></td>
                        </tr>
                        <?php
                    }else{
                        ?>
                        <tr>
                            <td><?php echo (sld_get_option('dashboard_lan_text_total_link')!=''?sld_get_option('dashboard_lan_text_total_link'):__('Total Link','qc-opd')) ?> : <?php echo $this->total_item; ?></td>
                            <td><?php echo (sld_get_option('dashboard_lan_text_submited_link')!=''?sld_get_option('dashboard_lan_text_submited_link'):__('Submited Link','qc-opd')) ?> : <?php echo $this->submited_item; ?></td>
                            <td><?php echo (sld_get_option('dashboard_lan_text_remaining_link')!=''?sld_get_option('dashboard_lan_text_remaining_link'):__('Remaining Link','qc-opd')) ?> : <?php echo $this->remain_item; ?></td>
                        </tr>
                        <?php
                    }
                    ?>

                </table>

				<?php
				if(sld_get_option('sld_enable_paypal_test_mode')=='on'){
					$mainurl = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
				}else{
					$mainurl = 'https://www.paypal.com/cgi-bin/webscr';
				}			
				$packages = $wpdb->get_results( $wpdb->prepare( "select * from $package_table where %d ORDER BY menu_order ASC", 1) );
				?>
				<?php if(!empty($packages)): ?>
				<div class="sbd_price-table-wrapper">

					<h2><?php echo (sld_get_option('sld_lan_paid_pkg')!=''?sld_get_option('sld_lan_paid_pkg'):__('Available Packages','qc-opd')) ?></h2>
					
					<?php if(sld_get_option('sld_enable_free_submission')=='on'): ?>
					  <div class="sbd_pricing-table">
						<h2 class="sbd_pricing-table__header"><?php echo (sld_get_option('sld_lan_free')!=''?sld_get_option('sld_lan_free'):__('Free', 'qc-opd')) ?></h2>

						<ul class="sbd_pricing-table__list">
						  <li><?php echo (sld_get_option('sld_lan_free')!=''?sld_get_option('sld_lan_free'):__('Free', 'qc-opd')) ?></li>
						  <li><?php echo sld_get_option('sld_free_item_limit'); ?> <?php echo (sld_get_option('dashboard_lan_text_link')!=''?sld_get_option('dashboard_lan_text_link'):__('Link','qc-opd')) ?></li>
						  <li><?php echo (sld_get_option('sld_lan_free')!=''?sld_get_option('sld_lan_free'):__('Free', 'qc-opd')) ?></li>
						  <li>-</li>
						  
						  
						</ul>
					  </div>
					<?php endif; ?>
					
					<?php
					$pc = 0;
					foreach($packages as $package):
					$pc++;
					if( $package->enable ){
					?>
					
				  <div class="sbd_pricing-table" <?php echo ($pc==1?'style="margin-left: 0px;"':''); ?>>
					<h2 class="sbd_pricing-table__header"><?php echo (isset($package->title)&&$package->title!=''?$package->title:''); ?></h2>

					<ul class="sbd_pricing-table__list">
					  <li><?php echo (isset($package->description)&&$package->description!=''?$package->description:''); ?></li>
					  <li>
					  	<?php
					  		if( $package->item > 1 ){
					  			//$links_for_text = __('Links for','qc-opd');
					  			$links_for_text = sld_get_option('dashboard_lan_text_links_for')?sld_get_option('dashboard_lan_text_links_for'): ('Links for');
					  		}else{
					  			//$links_for_text = __('Link for','qc-opd');
					  			$links_for_text = sld_get_option('dashboard_lan_text_links_for')?sld_get_option('dashboard_lan_text_links_for'): ('Link for');
					  		}
					  		$text_package_duration = sld_get_option('dashboard_lan_text_package_duration')?sld_get_option('dashboard_lan_text_package_duration'): ('Month');
					  		$text_package_duration_lifetime = sld_get_option('text_package_duration_lifetime')?sld_get_option('text_package_duration_lifetime'): ('Lifetime');
					  		$text_package_duration_1year = sld_get_option('text_package_duration_1year')?sld_get_option('text_package_duration_1year'): ('1 Year');
					  		$text_package_duration_2years = sld_get_option('text_package_duration_2years')?sld_get_option('text_package_duration_2years'): ('2 Years');
					  		$text_package_duration_3years = sld_get_option('text_package_duration_3years')?sld_get_option('text_package_duration_3years'): ('3 Years');
					  		$text_package_duration_4years = sld_get_option('text_package_duration_4years')?sld_get_option('text_package_duration_4years'): ('4 Years');
					  		$text_package_duration_5years = sld_get_option('text_package_duration_5years')?sld_get_option('text_package_duration_5years'): ('5 Years');

					  		echo (isset($package->item)&&$package->item!=''?$package->item:'0'); ?> <?php echo (sld_get_option('sld_lan_listing_for')!=''?sld_get_option('sld_lan_listing_for'):$links_for_text) ?> 

					  		<?php 

					  			if($package->duration=='lifetime') { 
								 	echo  __( ucwords($text_package_duration_lifetime ? $text_package_duration_lifetime : $package->duration),'qc-opd'); 
								 }elseif($package->duration=='1-year') {
								 	echo __( ucwords($text_package_duration_1year ? $text_package_duration_1year : str_replace("-"," ",$package->duration)),'qc-opd');
								 }elseif($package->duration=='2-years') {
								 	echo __( ucwords($text_package_duration_2years ? $text_package_duration_2years : str_replace("-"," ",$package->duration)),'qc-opd');
								 }elseif($package->duration=='3-years') {
								 	echo __( ucwords($text_package_duration_3years ? $text_package_duration_3years : str_replace("-"," ",$package->duration)),'qc-opd');
								 }elseif($package->duration=='4-years') {
								 	echo __( ucwords($text_package_duration_4years ? $text_package_duration_4years : str_replace("-"," ",$package->duration)),'qc-opd');
								 }elseif($package->duration=='5-years') {
								 	echo __( ucwords($text_package_duration_5years ? $text_package_duration_5years : str_replace("-"," ",$package->duration)),'qc-opd');
								 }elseif($package->duration=='1') {
								 	echo $package->duration.' '.__( $text_package_duration,'qc-opd');
								 }else{
								 	echo $package->duration.' '.__( $text_package_duration,'qc-opd');
								 }


					  		?>
					  	</li>
					  	<li><?php echo (isset($package->Amount)&&$package->Amount!=''?$package->Amount:'0'); ?> <?php echo $package->currency; ?></li>
						<?php if(sld_get_option('sld_enable_paypal_recurring')=='on' && sld_get_option('sld_enable_paypal_payment')!='off' && $package->duration!='lifetime'): ?>
					  	<li>
								
							<span> <?php echo (sld_get_option('sld_lan_package_enable_recurring')!=''?sld_get_option('sld_lan_package_enable_recurring'):__('Enable Recurring', 'qc-opd')) ?></span><input type="checkbox" name="sld_enable_recurring" id="sld_enable_recurring" class="sld_enable_recurring_dashboard" value="1" />
								
						</li>
						<?php endif; ?>
					  	<li>
							<?php if(sld_get_option('sld_enable_paypal_payment')!='off'): ?>
						  <form action="<?php echo $mainurl; ?>" method="post" id="paypalProcessor">
							<input type="hidden" name="cmd" value="_xclick" />

							<input type="hidden" name="business" value="<?php echo sld_get_option('sld_paypal_email'); ?>">
							<input type="hidden" name="currency_code" value="<?php echo $package->currency; ?>" />
							<input type="hidden" name="no_note" value="1"/>
							<input type="hidden" name="no_shipping" value="1" />
							<input type="hidden" name="charset" value="utf-8" />

							<input type="hidden" name="notify_url" value="<?php echo esc_url( add_query_arg( array( 'user' => $current_user->ID, 'packagesave' => $package->id ), $url ) ) ?>" />

							<input type="hidden" name="return" value="<?php echo esc_url( add_query_arg( 'payment', 'success', $url ) ) ?>" />

							<input type="hidden" name="cancel_return" value="<?php echo esc_url( add_query_arg( 'payment', 'cancel', $url ) ) ?>">
							<input type="hidden" name="item_name" value="<?php echo $package->title; ?>">
							<input type="hidden" name="amount" value="<?php echo ( isset( $package->Amount ) && $package->Amount != '' ? $package->Amount : '0' ); ?>">

							<input type="hidden" name="quantity" value="1">
							<input type="hidden" name="receiver_email" value="<?php echo sld_get_option('sld_paypal_email'); ?>">
							<input type="image" name="submit" border="0"  src="<?php echo SLD_QCOPD_IMG_URL.'/btn_buynow_LG.gif'; ?>" alt="PayPal - The safer, easier way to pay online">
							<p style="margin: 0px 0px;padding: 0px;color: #000;font-size: 14px;margin-top: -6px;"><?php echo (sld_get_option('sld_lan_paypal')!=''?sld_get_option('sld_lan_paypal'):__('Paypal', 'qc-opd')); ?></p>
						  </form>

						<?php if(sld_get_option('sld_enable_paypal_recurring')=='on'): ?>
						<form action="<?php echo $mainurl; ?>" method="post" id="paypalProcessor_recurring" style="display:none">
			                <input type="hidden" name="cmd" value="_xclick-subscriptions" />

			                <input type="hidden" name="business" value="<?php echo sld_get_option('sld_paypal_email'); ?>">
			                <input type="hidden" name="currency_code" value="<?php echo $package->currency; ?>" />
			                <input type="hidden" name="no_note" value="1"/>
			                <input type="hidden" name="no_shipping" value="1" />
			                <input type="hidden" name="charset" value="utf-8" />

			                <input type="hidden" name="notify_url" value="<?php echo esc_url( add_query_arg( array( 'user' => $current_user->ID, 'packagesave' => $package->id ), $url ) ) ?>" />

			                <input type="hidden" name="return" value="<?php echo esc_url( add_query_arg( 'payment', 'success', $url ) ); ?>" />

			                <input type="hidden" name="cancel_return" value="<?php echo esc_url( add_query_arg( 'payment', 'cancel', $url ) ); ?>">
			                <input type="hidden" name="item_name" value="<?php echo $package->title; ?>">
							<input type="hidden" name="receiver_email" value="<?php echo sld_get_option('sld_paypal_email'); ?>">
							
							
							<input type="hidden" name="a3" value="<?php echo ( isset($package->Amount) && $package->Amount != '' ? $package->Amount : '0' ); ?>">
							<input type="hidden" name="p3" value="<?php echo ( isset($package->duration) && $package->duration != '' ? $package->duration : '0' ); ?>">
							<input type="hidden" name="t3" value="M">
							<input type="hidden" name="custom" value="recurring">

							<!-- Set recurring payments until canceled. -->
							<input type="hidden" name="src" value="1">
							
			                <input type="image" name="submit" border="0"  src="<?php echo SLD_QCOPD_IMG_URL.'/btn_buynow_LG.gif'; ?>" alt="PayPal - The safer, easier way to pay online">
							<p style="margin: 0px 0px;padding: 0px;color: #000;font-size: 14px;margin-top: -6px;"><?php echo (sld_get_option('sld_lan_paypal')!=''?sld_get_option('sld_lan_paypal'):__('Paypal', 'qc-opd')); ?></p>
			            </form>
						<?php endif; ?>



						  <?php endif; ?>
						  <?php if(sld_get_option('sld_enable_stripe_payment')=="on"): ?>
						  <form action="<?php echo esc_url( add_query_arg( array( 'payment' => 'stripe-save', 'userid' => $current_user->ID, 'package'=> $package->id ), $url ) ) ?>" method="post">
								<script src="https://checkout.stripe.com/checkout.js" class="stripe-button"
									  data-key="<?php echo sld_get_option('sld_stripe_public_key'); ?>"
									  data-description="<?php echo $package->title; ?>"
									  data-amount="<?php echo ( isset($package->Amount) && $package->Amount != '' ? ($package->Amount*100) : '0' ); ?>"
									  data-locale="auto"
									  data-currency="<?php echo $package->currency; ?>"	
									  ></script>
								<p style="margin: 0px 0px;padding: 0px;color: #000;font-size: 14px;"><?php echo (sld_get_option('sld_lan_stripe')!=''?sld_get_option('sld_lan_stripe'):__('Stripe', 'qc-opd')); ?></p>
							</form>
							<?php endif; ?>
			
							<?php if(sld_get_option('sld_enable_2checkout_payment')=="on"): ?>
							<button class="sld_2co_btn" data-mfp-src="#sld_package_2co"><?php echo (sld_get_option('sld_lan_pay_with_card')!=''?sld_get_option('sld_lan_pay_with_card'):__('Pay with Card', 'qc-opd')); ?></button>
							<p style="margin: 0px 0px;padding: 0px;color: #000;font-size: 14px;"><?php echo (sld_get_option('sld_lan_2checkout')!=''?sld_get_option('sld_lan_2checkout'):__('2Checkout', 'qc-opd')); ?></p>
							<div id="sld_package_2co" class="white-popup mfp-hide">
								<div class="sld_2co_form">
									<p class="sld_2co_head"><?php echo (sld_get_option('sld_lan_2checkout_payment')!=''?sld_get_option('sld_lan_2checkout_payment'):__('2Checkout Payment', 'qc-opd')); ?></p>
									<form id="sld_2co_package_form" action="<?php echo esc_url( add_query_arg( array('payment'=> '2co-save', 'package'=>$package->id), $url ) ) ?>" method="post">
										<input name="token" type="hidden" value="">
										<input name="amount" type="hidden" value="10">
										<input type="text" name="name" placeholder="Name" required />
										<input type="text" name="address" placeholder="Street Address" required />
										<input type="text" name="city" placeholder="City" required />
										<input type="text" name="state" placeholder="State" required />
										<input type="text" name="zipcode" placeholder="Zipcode" required />
										<input type="text" name="country" placeholder="Country" required />
										<input type="text" name="phone" placeholder="Phone" required />
										<input id="sld_package_ccNo" type="text" size="20" value="" placeholder="Card No" autocomplete="off" required />
										<div class="sld_2co_expire_date">
											<input type="text" size="2" id="sld_package_expMonth" maxlength="2" placeholder="MM" required /> / <input type="text" placeholder="YYYY" size="2" maxlength="4" id="sld_package_expYear" required />
										</div>
										<input id="sld_package_cvv" type="text" placeholder="CVV" value="" autocomplete="off" required />
										<input type="submit" class="sld_2co_submit" id="sld_2co_package_submit" value="Pay $<?php echo (isset($package->Amount)&&$package->Amount!=''?($package->Amount):'0'); ?>" />
									</form>
								<script>
									// Called when token created successfully.
									var successCallback = function(data) {
										var myForm = document.getElementById('sld_2co_package_form');
										
										// Set the token as the value for the token input
										myForm.token.value = data.response.token.token;

										// IMPORTANT: Here we call `submit()` on the form element directly instead of using jQuery to prevent and infinite token request loop.
										myForm.submit();
									};

									// Called when token creation fails.
									var errorCallback = function(data) {
										if (data.errorCode === 200) {
											tokenRequest();
										} else {
											alert(data.errorMsg);
										}
									};

									var tokenRequest = function() {
										// Setup token request arguments
										
										var args = {
											sellerId: "<?php echo sld_get_option('sld_2checkout_seller_id'); ?>",
											publishableKey: "<?php echo sld_get_option('sld_2checkout_public_key'); ?>",
											ccNo: document.getElementById('sld_package_ccNo').value,
											cvv: document.getElementById('sld_package_cvv').value,
											expMonth: document.getElementById('sld_package_expMonth').value,
											expYear: document.getElementById('sld_package_expYear').value
										};

										// Make the token request
										TCO.requestToken(successCallback, errorCallback, args);
									};

									jQuery(function() {
										// Pull in the public encryption key for our environment
										<?php if(sld_get_option('sld_enable_2checkout_sandbox')=="on"): ?>
										TCO.loadPubKey('sandbox');
										<?php endif; ?>

										jQuery("#sld_2co_package_form").submit(function(e) {
											// Call our token request function
											tokenRequest();

											// Prevent form from submitting
											return false;
										});
									});
								</script>
								</div>
							</div>
							<?php endif; ?>	
						
							<?php if(sld_get_option('sld_enable_mollie_payment')=="on"): ?>
								
								<form action="<?php echo esc_url( add_query_arg( array('payment'=> 'mollie-save', 'package'=> $package->id), $url ) ) ?>" method="post">
									<input type="hidden" class="amount" value="<?php echo (isset($package->Amount)&&$package->Amount!=''?($package->Amount):'0'); ?>" />
									<input type="submit" class="sld_mollie_submit" id="sld_mollie_package_submit" value="<?php esc_html_e('Mollie'); ?>" />
								</form>
							
							<?php endif; ?>
					  </li>
					</ul>
				  </div>
				  <?php 
				  	}
					endforeach; 
					?>
				  
				</div>
				<?php endif; ?>
				

                <?php
				if(sld_get_option('sld_profile_update')=='on'){

					$lan_text_profile_info = sld_get_option('dashboard_lan_text_profile_info')?sld_get_option('dashboard_lan_text_profile_info'): ('Update Profile Info');
					$lan_text_full_name = sld_get_option('dashboard_lan_text_full_name')?sld_get_option('dashboard_lan_text_full_name'): ('Full Name');
					$lan_text_first_name = sld_get_option('dashboard_lan_text_first_name')?sld_get_option('dashboard_lan_text_first_name'): ('First');
					$lan_text_last_name = sld_get_option('dashboard_lan_text_last_name')?sld_get_option('dashboard_lan_text_last_name'): ('Last');
					$lan_text_email = sld_get_option('dashboard_lan_text_email')?sld_get_option('dashboard_lan_text_email'): ('Email');
					$lan_text_usename = sld_get_option('dashboard_lan_text_usename')?sld_get_option('dashboard_lan_text_usename'): ('Username');
					$lan_text_submit_btn = sld_get_option('dashboard_lan_text_submit_btn')?sld_get_option('dashboard_lan_text_submit_btn'): ('Submit');
                ?>
                    <hr>
				<p class="updateprofile"><?php echo __($lan_text_profile_info, 'qc-opd'); ?></p>
				<form method="post">
				<ul class="sld_form-style-1">
					<li><label><?php echo __($lan_text_full_name, 'qc-opd'); ?> <span class="sld_required">*</span></label><input type="text" name="first_name" class="sld_field-divided" placeholder="<?php echo __($lan_text_first_name, 'qc-opd'); ?>" value="<?php echo $current_user->user_firstname; ?>" />&nbsp;<input type="text" name="last_name" class="sld_field-divided" placeholder="<?php echo __($lan_text_last_name, 'qc-opd'); ?>" value="<?php echo $current_user->user_lastname; ?>" /></li>
					<li>
						<label><?php echo __($lan_text_email, 'qc-opd'); ?> <span class="sld_required">*</span></label>
						<input type="email" name="user_email" class="field-long" placeholder="<?php echo __($lan_text_email, 'qc-opd'); ?>" value="<?php echo $current_user->user_email; ?>" />
					</li>
					<li>
						<label><?php echo __($lan_text_usename, 'qc-opd'); ?> <span class="sld_required">*</span></label>
						<input type="text" name="user_login" class="field-long" value="<?php echo $current_user->user_login; ?>" />
					</li>

					<li>
						<input class="sld_submit_style" type="submit" value="<?php echo __($lan_text_submit_btn, 'qc-opd'); ?>" />
					</li>
				</ul>
				</form>
                <?php } ?>

			<?php endif; ?>
			
			<?php if(isset($_GET['action']) && $_GET['action']=='entry' )://entry 
				require(SLD_QCOPD_DIR_MOD.'/dashboard/sld_entry.php');
			 endif; ?>
			
			<?php if(isset($_GET['action']) && $_GET['action']=='entrylist' )://entrylist ?>
				<?php require(SLD_QCOPD_DIR_MOD.'/dashboard/sld_entrylist.php'); ?>
			<?php endif; ?>
			
			<?php if(sld_get_option('sld_enable_claim_listing')=='on'): ?>
				<?php if(isset($_GET['action']) && $_GET['action']=='claim' )://entrylist ?>
					<?php require(SLD_QCOPD_DIR_MOD.'/dashboard/sld_claim.php'); ?>
				<?php endif; ?>
				<?php if(isset($_GET['action']) && $_GET['action']=='claim_edit' )://entrylist ?>
					<?php require(SLD_QCOPD_DIR_MOD.'/dashboard/sld_claim_edit.php'); ?>
				<?php endif; ?>
			<?php endif; ?>
			
			<?php if(isset($_GET['action']) && $_GET['action']=='package' )://Payment ?>
				<?php require(SLD_QCOPD_DIR_MOD.'/dashboard/sld_package.php'); ?>
			<?php endif; ?>

			<?php if(isset($_GET['action']) && $_GET['action']=='help' )://help ?>
				<h2>This is SLD Help page.</h2>
			<?php endif; ?>

			<?php if(isset($_GET['payment']) && $_GET['payment']=='success' )://help ?>
                <p style="color: #000;font-size: 16px;margin: 11px 0px;text-align:center;"><?php echo __('Payment has been done successfully. Thank you!
                    Now you can add links to our Lists.','qc-opd') ?></p>
			<?php endif; ?>
			
			<?php if(isset($_GET['payment']) && $_GET['payment']=='stripe-save' )://help ?>
                <p style="color: #000;font-size: 16px;margin: 11px 0px;text-align:center;"><?php echo __('Payment has been done successfully. Thank you!
                    Now you can add links to our Lists.','qc-opd') ?></p>
			<?php endif; ?>
			<?php if(isset($_GET['payment']) && $_GET['payment']=='2co-save' )://help ?>
                <p style="color: #000;font-size: 16px;margin: 11px 0px;text-align:center;"><?php echo __('Payment has been done successfully. Thank you! Now you can add links to our Lists.','qc-opd') ?></p>
			<?php endif; ?>
			<?php if(isset($_GET['payment']) && $_GET['payment']=='offline-save' )://help ?>
                <p style="color: #000;font-size: 16px;margin: 11px 0px;text-align:center;"><?php echo __('Payment has been done successfully. Thank you! Now you can add links to our Lists.','qc-opd') ?></p>
			<?php endif; ?>
			
			<?php if(isset($_GET['payment']) && $_GET['payment']=='stripe-renew' )://help ?>
                <p style="color: #000;font-size: 16px;margin: 11px 0px;text-align:center;"><?php echo __('Payment has been done successfully for package renewal. Thank you! ','qc-opd') ?></p>
			<?php endif; ?>
			
			<?php if(isset($_GET['payment']) && $_GET['payment']=='claim-stripe' )://help ?>
                <p style="color: #000;font-size: 16px;margin: 11px 0px;text-align:center;"><?php echo __('Payment has been done successfully for Claim Listing. Thank you! ','qc-opd') ?></p>
			<?php endif; ?>
			
			<?php if(isset($_GET['payment']) && $_GET['payment']=='claim-paypal' )://help ?>
                <p style="color: #000;font-size: 16px;margin: 11px 0px;text-align:center;"><?php echo __('Payment has been done successfully for Claim Listing. Thank you! ','qc-opd') ?></p>
			<?php endif; ?>


			<?php if(isset($_GET['action']) && $_GET['action']=='entryedit' )://help ?>
				<?php require(SLD_QCOPD_DIR_MOD.'/dashboard/sld_entryedit.php') ?>
			<?php endif; ?>
			
		</div>
<?php		
		}elseif(!is_user_logged_in()){
            $url = qc_sld_login_page()->sldcustom_login_get_translated_option_page('sld_login_url');
            $login_text = sld_get_option('login_text_to_view_content') ? sld_get_option('login_text_to_view_content') : sprintf(__('You have to login to view this content. <a href="%s">Click Here</a> to log in.','qc-opd'),$url);
            echo $login_text;
        }else{
        	$not_allowed_success_msg = sld_get_option('dashboard_lan_text_not_allowed_success_msg')!=''?sld_get_option('dashboard_lan_text_not_allowed_success_msg'):__('Sorry, You are not allowed to view the content of this page.', 'qc-opd');

			echo __( $not_allowed_success_msg,'qc-opd');
		}
		
			
		
		return ob_get_clean();
	}
}

function qc_sld_dashboard(){
	return qc_sld_dashboard::instance();
}
qc_sld_dashboard();