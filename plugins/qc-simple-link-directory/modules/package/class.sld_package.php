<?php
if ( ! defined( 'ABSPATH' ) ) exit;
class Sld_package {
	// class instance
	static $instance;

	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	// class constructor
	public function __construct() {
		add_filter( 'set-screen-option', array( __CLASS__, 'set_screen' ), 10, 3 );
		add_action( 'admin_menu', array( $this, 'pd_custom_plugin_admin_menu' ) );

	}

	public static function set_screen( $status, $option, $value ) {
		return $value;
	}

	public function pd_custom_plugin_admin_menu() {

		$hook = add_submenu_page(
			'edit.php?post_type=sld',
			__('Paid Packages', 'qc-opd'),
			__('Paid Packages', 'qc-opd'),
			'manage_options',
			'qcsld_package',
			array(
				$this,
				'qc_pd_plugin_settings_page'
			)
		);




	}
	public function qc_pd_plugin_settings_page(){
		global $wpdb;
		if(!function_exists('wp_get_current_user')) {
			include(ABSPATH . "wp-includes/pluggable.php"); 
		}
		$table             = $wpdb->prefix.'sld_package';
		$current_user = wp_get_current_user();
		$msg = '';

		$menu_order_check_field = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table") );
		//Add column if not present.
		if(!isset($menu_order_check_field->menu_order) && empty($menu_order_check_field->menu_order)){
		    $wpdb->query("ALTER TABLE $table ADD COLUMN IF NOT EXISTS menu_order INT(11) NOT NULL DEFAULT 0");
		}

		//get form data
		if(isset($_POST['qc_sld_item_duration']) and $_POST['qc_sld_item_duration']!='' and isset($_POST['qc_sld_save'])){

			$title = sanitize_text_field($_POST['qc_sld_package_title']);
			$description = sanitize_text_field($_POST['qc_sld_package_desc']);
			$duration = sanitize_text_field($_POST['qc_sld_item_duration']);
			$sandbox = isset($_POST['qc_sld_test_mode'])?$_POST['qc_sld_test_mode']:0;
			$enable = isset($_POST['qc_sld_package_enable'])?$_POST['qc_sld_package_enable']:0;
			$menu_order = isset($_POST['qc_sld_package_menu_order'])?$_POST['qc_sld_package_menu_order']:0;
			$currency = sanitize_text_field($_POST['qc_sld_currency']);
			$item = sanitize_text_field($_POST['qc_sld_item']);
			$amount = ($_POST['qc_sld_amount']);
			$email = isset($_POST['qc_sld_paypal']) ? sanitize_email($_POST['qc_sld_paypal']) : '';
			$date = date('Y-m-d H:i:s');
			$recurring = isset($_POST['qc_sld_recurring']) ? $_POST['qc_sld_recurring'] : '';
			if($duration=='lifetime'){
				$recurring = 0;
			}

			if(isset($_POST['qc_sld_update']) and $_POST['qc_sld_update']!=''){
				$uid = $_POST['qc_sld_update'];
				$wpdb->update(
					$table,
					array(
						'date'  		=> $date,
						'title'   		=> $title,
						'description'   => $description,
						'duration'   	=> $duration,
						'currency'   	=> $currency,
						'item'   		=> $item,
						'Amount' 		=> $amount,
						'enable'   		=> $enable,
						'menu_order'   	=> $menu_order,
						
					),

					array( 'id' => $uid),
					array(
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%d',
						'%d',
						

					),
					array( '%d')
				);
				$msg = '<div id="message" class="updated notice notice-success is-dismissible"><p>Package has been Updated Successfully. </p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
			}else{

				$wpdb->insert(
					$table,
					array(
						'date'  		=> $date,
						'title'   		=> $title,
						'description'   => $description,
						'duration'   	=> $duration,
						'currency'   	=> $currency,
						'item'   		=> $item,
						'Amount' 		=> $amount,
						'enable'   		=> $enable,
						'menu_order'   	=> $menu_order,
						
					)
				);

				$msg = '<div id="message" class="updated notice notice-success is-dismissible"><p>Package has been Created Successfully. </p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
			}


		}
		if(isset($_GET['act']) && $_GET['act']=='delete'){
			$id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
			$wpdb->delete(
				$table,
				array( 'id' => $id ),
				array( '%d' )
			);
			
			$msg = '<div id="message" class="updated notice notice-success is-dismissible"><p>Package has been Deleted Successfully. </p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
		}
		
		
		
		
		if(isset($_GET['act']) && $_GET['act']=='addnew'){
?>
		<div class="wrap">

			<div id="poststuff">
				<div id="post-body" class="metabox-holder">
					<!-- <div id="post-body-content" style="padding: 50px;box-sizing: border-box;box-shadow: 0 8px 25px 3px rgba(0,0,0,.2);background: #fff;"> -->
					<div id="post-body-content" >
					
					<?php
						if($msg!=''){
							echo $msg;
						}
						?>
						<h1 class="wp-heading-inline" ><?php echo __('Add Your Package', 'qc-opd') ?></h1>
						<a class="page-title-action" href="<?php echo admin_url( 'edit.php?post_type=sld&page=qcsld_package&act=addnew'); ?>">
							<?php echo __('Add New Package', 'qc-opd') ?>
						</a>
						<hr class="wp-header-end">
						
						<div class="qcld-sld-square-section-block">
							<form method="post" action="">
								<table class="form-table">

	                                <tr>
	                                    <th><label for="qc_pd_package_title"><?php _e( 'Enable Package', 'qc-opd' ); ?></label>
	                                    </th>

	                                    <td>
	                                        <input type="checkbox" id="qc_sld_package_enable" name="qc_sld_package_enable" value="1" checked="checked"/>

	                                    </td>
	                                </tr>
									<tr>
	                                    <th><label for="qc_pd_package_title"><?php _e( 'Package Title', 'qc-opd' ); ?></label>
	                                    </th>

	                                    <td>
	                                        <input type="text" id="qc_sld_package_title" name="qc_sld_package_title" value="" required/>

	                                    </td>
	                                </tr>
	                                <tr>
	                                    <th><label for="qc_pd_package_desc"><?php _e( 'Package Description', 'qc-opd' ); ?></label>
	                                    </th>

	                                    <td>
	                                        <textarea id="qc_sld_package_desc" name="qc_sld_package_desc" rows="5" cols="50"></textarea>

	                                    </td>
	                                </tr>

									<tr>
										<th><label for="qc_pd_item_duration"><?php _e( 'Duration', 'qc-opd' ); ?></label>
										</th>

										<td>
											<select id="qc_sld_item_duration" name="qc_sld_item_duration" required>
												<option value=""><?php esc_html_e( 'None', 'qc-opd' ); ?></option>
												<?php
												for($i=1;$i<25;$i++){

													echo '<option value="'.$i.'">'.$i.' Month</option>';
													

												}
												?>
												<option value="1-year"><?php _e( '1 year', 'qc-opd' ); ?></option>
												<option value="2-years"><?php _e( '2 years', 'qc-opd' ); ?></option>
												<option value="3-years"><?php _e( '3 years', 'qc-opd' ); ?></option>
												<option value="4-years"><?php _e( '4 years', 'qc-opd' ); ?></option>
												<option value="5-years"><?php _e( '5 years', 'qc-opd' ); ?></option>
												<option value="lifetime"><?php _e( 'Lifetime', 'qc-opd' ); ?></option>
											</select>
											<span class="description"><?php _e( 'Select duration for how long the listing will remain visible.', 'qc-opd' ); ?></span>
										</td>
									</tr>

	                                <tr>
	                                    <th><label for="qc_pd_currency"><?php _e( 'Currency', 'qc-opd' ); ?></label>
	                                    </th>
	                                    <td>
	                                        <select name="qc_sld_currency" id="qc_sld_currency" required>

	                                            <option value="USD"><?php esc_html_e( 'US Dollars ($)', 'qc-opd' ); ?></option>
	                                            <option value="EUR" ><?php esc_html_e( 'Euros (€)', 'qc-opd' ); ?></option>
	                                            <option value="GBP" ><?php esc_html_e( 'Pounds Sterling (£)', 'qc-opd' ); ?></option>
	                                            <option value="ARS" ><?php esc_html_e( 'Argentine Peso ($)', 'qc-opd' ); ?></option>
	                                            <option value="AUD" ><?php esc_html_e( 'Australian Dollars ($)', 'qc-opd' ); ?></option>
	                                            <option value="BRL" ><?php esc_html_e( 'Brazilian Real (R$)', 'qc-opd' ); ?></option>
	                                            <option value="CAD" ><?php esc_html_e( 'Canadian Dollars ($)', 'qc-opd' ); ?></option>
	                                            <option value="CNY" ><?php esc_html_e( 'Chinese Yuan', 'qc-opd' ); ?></option>
	                                            <option value="CZK" ><?php esc_html_e( 'Czech Koruna', 'qc-opd' ); ?></option>
	                                            <option value="DKK" ><?php esc_html_e( 'Danish Krone', 'qc-opd' ); ?></option>
	                                            <option value="HKD" ><?php esc_html_e( 'Hong Kong Dollar ($)', 'qc-opd' ); ?></option>
	                                            <option value="HUF" ><?php esc_html_e( 'Hungarian Forint', 'qc-opd' ); ?></option>
	                                            <option value="INR" ><?php esc_html_e( 'Indian Rupee', 'qc-opd' ); ?></option>
	                                            <option value="IDR" ><?php esc_html_e( 'Indonesia Rupiah', 'qc-opd' ); ?></option>
	                                            <option value="ILS" ><?php esc_html_e( 'Israeli Shekel', 'qc-opd' ); ?></option>
	                                            <option value="JPY" ><?php esc_html_e( 'Japanese Yen (¥)', 'qc-opd' ); ?></option>
	                                            <option value="MYR" ><?php esc_html_e( 'Malaysian Ringgits', 'qc-opd' ); ?></option>
	                                            <option value="MXN" ><?php esc_html_e( 'Mexican Peso ($)', 'qc-opd' ); ?></option>
	                                            <option value="NGN" ><?php esc_html_e( 'Nigerian Naira (₦)', 'qc-opd' ); ?></option>
	                                            <option value="NZD" ><?php esc_html_e( 'New Zealand Dollar ($)', 'qc-opd' ); ?></option>
	                                            <option value="NOK" ><?php esc_html_e( 'Norwegian Krone', 'qc-opd' ); ?></option>
	                                            <option value="PHP" ><?php esc_html_e( 'Philippine Pesos', 'qc-opd' ); ?></option>
	                                            <option value="PLN" ><?php esc_html_e( 'Polish Zloty', 'qc-opd' ); ?></option>
	                                            <option value="SGD" ><?php esc_html_e( 'Singapore Dollar ($)', 'qc-opd' ); ?></option>
	                                            <option value="ZAR" ><?php esc_html_e( 'South African Rand (R)', 'qc-opd' ); ?></option>
	                                            <option value="KRW" ><?php esc_html_e( 'South Korean Won', 'qc-opd' ); ?></option>
	                                            <option value="SEK" ><?php esc_html_e( 'Swedish Krona', 'qc-opd' ); ?></option>
	                                            <option value="CHF" ><?php esc_html_e( 'Swiss Franc', 'qc-opd' ); ?></option>
	                                            <option value="TWD" ><?php esc_html_e( 'Taiwan New Dollars', 'qc-opd' ); ?></option>
	                                            <option value="THB" ><?php esc_html_e( 'Thai Baht', 'qc-opd' ); ?></option>
	                                            <option value="TRY" ><?php esc_html_e( 'Turkish Lira', 'qc-opd' ); ?></option>
	                                            <option value="VND" ><?php esc_html_e( 'Vietnamese Dong', 'qc-opd' ); ?></option>

	                                        </select>
	                                    </td>
	                                </tr>

	                                <tr>
	                                    <th><label for="qc_pd_item"><?php _e( 'Total Item', 'qc-opd' ); ?></label>
	                                    </th>

	                                    <td>
	                                        <input type="text" id="qc_sld_item" name="qc_sld_item" value="" required/>
	                                        <span class="description"><?php _e( 'How many listing user can add?', 'qc-opd' ); ?></span>
	                                    </td>
	                                </tr>

	                                <tr>
										<th><label for="qc_pd_amount"><?php _e( 'Package Price', 'qc-opd' ); ?></label>
										</th>

										<td>
											<input type="text" id="qc_sld_amount" name="qc_sld_amount" value="" required/>
											<span class="description"><?php _e( 'Enter price for the package.', 'qc-opd' ); ?></span>
										</td>
									</tr>

									

									<tr>
										<th><label for="qc_pd_save"><?php _e( '', 'qc-opd' ); ?></label>
										</th>

										<td>
											
											<input type="submit" name="qc_sld_save" class="button button-primary button-large" id="qc_sld_save" value="Save" />
											
										</td>
									</tr>

								</table>
							</form>
							<hr>
							<a class="button button-normal" href="<?php echo admin_url( 'edit.php?post_type=sld&page=qcsld_package'); ?>">
								<?php echo __('Go Back', 'qc-opd') ?>
							</a>
						</div>
					</div>
				</div>
			</div>
		</div>
			
<?php		
		}elseif( isset($_GET['act']) && $_GET['act']=='edit'){
		$id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
		$row     = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE 1 and id=%d", $id ) );
		
?>		
		<div class="wrap">

			<div id="poststuff">
				<div id="post-body" class="metabox-holder">
					<!-- <div id="post-body-content" style="padding: 50px;box-sizing: border-box;box-shadow: 0 8px 25px 3px rgba(0,0,0,.2);background: #fff;"> -->
					<div id="post-body-content" >
			<?php
			if($msg!=''){
				echo $msg;
			}
			?>
			<h1 class="wp-heading-inline" ><?php echo __('Manage Your Package', 'qc-opd') ?></h1>
			<a class="page-title-action" href="<?php echo admin_url( 'edit.php?post_type=sld&page=qcsld_package&act=addnew'); ?>">
				<?php echo __('Add New Package', 'qc-opd') ?>
			</a>
			<hr class="wp-header-end">

			<?php
			if(empty($row)){
			?>
				<?php echo __('<p>You have no package created! Please submit the following information and press save to Create a Package and charge your client to submit there list item.</p>', 'qc-opd') ?>
				

			<?php } ?>
			<div class="qcld-sld-square-section-block">
				<form method="post" action="">
					<table class="form-table">

						<tr>
							<th><label for="qc_pd_package_title"><?php _e( 'Enable Package', 'qc-opd' ); ?></label>
							</th>

							<td>
								<input type="checkbox" id="qc_sld_package_enable" name="qc_sld_package_enable" <?php echo (isset($row->enable) && $row->enable==1)?'checked="checked"':''; ?> value="1"/>

							</td>
						</tr>
						<tr>
							<th><label for="qc_pd_package_title"><?php _e( 'Package Title', 'qc-opd' ); ?></label>
							</th>

							<td>
								<input type="text" id="qc_sld_package_title" name="qc_sld_package_title" value="<?php echo (isset($row->title)&&$row->title!=''?$row->title:''); ?>" required/>

							</td>
						</tr>
						<tr>
							<th><label for="qc_pd_package_desc"><?php _e( 'Package Description', 'qc-opd' ); ?></label>
							</th>

							<td>
								<textarea id="qc_sld_package_desc" name="qc_sld_package_desc" rows="5" cols="50"><?php echo (isset($row->description)&&$row->description!=''?$row->description:''); ?></textarea>

							</td>
						</tr>

						<tr>
							<th><label for="qc_pd_item_duration"><?php _e( 'Duration', 'qc-opd' ); ?></label>
							</th>

							<td>
								<select id="qc_sld_item_duration" name="qc_sld_item_duration" required>
									<option value=""><?php esc_html_e( 'None', 'qc-opd' ); ?></option>
									<?php
									for($i=1;$i<25;$i++){
										if(isset($row->duration) and $row->duration==$i){
											echo '<option value="'.$i.'" selected="selected">'.$i.' Month</option>';
										}else{
											echo '<option value="'.$i.'">'.$i.' Month</option>';
										}

									}
									?>
									<option value="1-year" <?php echo ($row->duration=='1-year'?'selected="selected"':''); ?>><?php _e( '1 year', 'qc-opd' ); ?></option>
									<option value="2-years" <?php echo ($row->duration=='2-years'?'selected="selected"':''); ?>><?php _e( '2 years', 'qc-opd' ); ?></option>
									<option value="3-years" <?php echo ($row->duration=='3-years'?'selected="selected"':''); ?>><?php _e( '3 years', 'qc-opd' ); ?></option>
									<option value="4-years" <?php echo ($row->duration=='4-years'?'selected="selected"':''); ?>><?php _e( '4 years', 'qc-opd' ); ?></option>
									<option value="5-years" <?php echo ($row->duration=='5-years'?'selected="selected"':''); ?>><?php _e( '5 years', 'qc-opd' ); ?></option>
									<option value="lifetime" <?php echo ($row->duration=='lifetime'?'selected="selected"':''); ?>><?php _e( 'Lifetime', 'qc-opd' ); ?></option>
								</select>
								<span class="description"><?php _e( 'Select duration for how long the listing will remain visible.', 'qc-opd' ); ?></span>
							</td>
						</tr>

						<tr>
							<th><label for="qc_pd_currency"><?php _e( 'Currency', 'qc-opd' ); ?></label>
							</th>
							<td>
								<select name="qc_sld_currency" id="qc_sld_currency" required>

									<option value="USD" <?php echo (isset($row->currency)&&$row->currency=='USD'?'selected="selected"':''); ?>><?php esc_html_e( 'US Dollars ($)', 'qc-opd' ); ?></option>
									<option value="EUR" <?php echo (isset($row->currency)&&$row->currency=='EUR'?'selected="selected"':''); ?>><?php esc_html_e( 'Euros (€)', 'qc-opd' ); ?></option>
									<option value="GBP" <?php echo (isset($row->currency)&&$row->currency=='GBP'?'selected="selected"':''); ?>><?php esc_html_e( 'Pounds Sterling (£)', 'qc-opd' ); ?></option>
									<option value="ARS" <?php echo (isset($row->currency)&&$row->currency=='ARS'?'selected="selected"':''); ?>><?php esc_html_e( 'Argentine Peso ($)', 'qc-opd' ); ?></option>
									<option value="AUD" <?php echo (isset($row->currency)&&$row->currency=='AUD'?'selected="selected"':''); ?>><?php esc_html_e( 'Australian Dollars ($)', 'qc-opd' ); ?></option>
									<option value="BRL" <?php echo (isset($row->currency)&&$row->currency=='BRL'?'selected="selected"':''); ?>><?php esc_html_e( 'Brazilian Real (R$)', 'qc-opd' ); ?></option>
									<option value="CAD" <?php echo (isset($row->currency)&&$row->currency=='CAD'?'selected="selected"':''); ?>><?php esc_html_e( 'Canadian Dollars ($)', 'qc-opd' ); ?></option>
									<option value="CNY" <?php echo (isset($row->currency)&&$row->currency=='CNY'?'selected="selected"':''); ?>><?php esc_html_e( 'Chinese Yuan', 'qc-opd' ); ?></option>
									<option value="CZK" <?php echo (isset($row->currency)&&$row->currency=='CZK'?'selected="selected"':''); ?>><?php esc_html_e( 'Czech Koruna', 'qc-opd' ); ?></option>
									<option value="DKK" <?php echo (isset($row->currency)&&$row->currency=='DKK'?'selected="selected"':''); ?>><?php esc_html_e( 'Danish Krone', 'qc-opd' ); ?></option>
									<option value="HKD" <?php echo (isset($row->currency)&&$row->currency=='HKD'?'selected="selected"':''); ?>><?php esc_html_e( 'Hong Kong Dollar ($)', 'qc-opd' ); ?></option>
									<option value="HUF" <?php echo (isset($row->currency)&&$row->currency=='HUF'?'selected="selected"':''); ?>><?php esc_html_e( 'Hungarian Forint', 'qc-opd' ); ?></option>
									<option value="INR" <?php echo (isset($row->currency)&&$row->currency=='INR'?'selected="selected"':''); ?>><?php esc_html_e( 'Indian Rupee', 'qc-opd' ); ?></option>
									<option value="IDR" <?php echo (isset($row->currency)&&$row->currency=='IDR'?'selected="selected"':''); ?>><?php esc_html_e( 'Indonesia Rupiah', 'qc-opd' ); ?></option>
									<option value="ILS" <?php echo (isset($row->currency)&&$row->currency=='ILS'?'selected="selected"':''); ?>><?php esc_html_e( 'Israeli Shekel', 'qc-opd' ); ?></option>
									<option value="JPY" <?php echo (isset($row->currency)&&$row->currency=='JPY'?'selected="selected"':''); ?>><?php esc_html_e( 'Japanese Yen (¥)', 'qc-opd' ); ?></option>
									<option value="MYR" <?php echo (isset($row->currency)&&$row->currency=='MYR'?'selected="selected"':''); ?>><?php esc_html_e( 'Malaysian Ringgits', 'qc-opd' ); ?></option>
									<option value="MXN" <?php echo (isset($row->currency)&&$row->currency=='MXN'?'selected="selected"':''); ?>><?php esc_html_e( 'Mexican Peso ($)', 'qc-opd' ); ?></option>
									<option value="NGN" <?php echo (isset($row->currency)&&$row->currency=='NGN'?'selected="selected"':''); ?>><?php esc_html_e( 'Nigerian Naira (₦)', 'qc-opd' ); ?></option>
									<option value="NZD" <?php echo (isset($row->currency)&&$row->currency=='NZD'?'selected="selected"':''); ?>><?php esc_html_e( 'New Zealand Dollar ($)', 'qc-opd' ); ?></option>
									<option value="NOK" <?php echo (isset($row->currency)&&$row->currency=='NOK'?'selected="selected"':''); ?>><?php esc_html_e( 'Norwegian Krone', 'qc-opd' ); ?></option>
									<option value="PHP" <?php echo (isset($row->currency)&&$row->currency=='PHP'?'selected="selected"':''); ?>><?php esc_html_e( 'Philippine Pesos', 'qc-opd' ); ?></option>
									<option value="PLN" <?php echo (isset($row->currency)&&$row->currency=='PLN'?'selected="selected"':''); ?>><?php esc_html_e( 'Polish Zloty', 'qc-opd' ); ?></option>
									<option value="SGD" <?php echo (isset($row->currency)&&$row->currency=='SGD'?'selected="selected"':''); ?>><?php esc_html_e( 'Singapore Dollar ($)', 'qc-opd' ); ?></option>
									<option value="ZAR" <?php echo (isset($row->currency)&&$row->currency=='ZAR'?'selected="selected"':''); ?>><?php esc_html_e( 'South African Rand (R)', 'qc-opd' ); ?></option>
									<option value="KRW" <?php echo (isset($row->currency)&&$row->currency=='KRW'?'selected="selected"':''); ?>><?php esc_html_e( 'South Korean Won', 'qc-opd' ); ?></option>
									<option value="SEK" <?php echo (isset($row->currency)&&$row->currency=='SEK'?'selected="selected"':''); ?>><?php esc_html_e( 'Swedish Krona', 'qc-opd' ); ?></option>
									<option value="CHF" <?php echo (isset($row->currency)&&$row->currency=='CHF'?'selected="selected"':''); ?>><?php esc_html_e( 'Swiss Franc', 'qc-opd' ); ?></option>
									<option value="TWD" <?php echo (isset($row->currency)&&$row->currency=='TWD'?'selected="selected"':''); ?>><?php esc_html_e( 'Taiwan New Dollars', 'qc-opd' ); ?></option>
									<option value="THB" <?php echo (isset($row->currency)&&$row->currency=='THB'?'selected="selected"':''); ?>><?php esc_html_e( 'Thai Baht', 'qc-opd' ); ?></option>
									<option value="TRY" <?php echo (isset($row->currency)&&$row->currency=='TRY'?'selected="selected"':''); ?>><?php esc_html_e( 'Turkish Lira', 'qc-opd' ); ?></option>
									<option value="VND" <?php echo (isset($row->currency)&&$row->currency=='VND'?'selected="selected"':''); ?>><?php esc_html_e( 'Vietnamese Dong', 'qc-opd' ); ?></option>

								</select>
							</td>
						</tr>

						<tr>
							<th><label for="qc_pd_item"><?php _e( 'Total Item', 'qc-opd' ); ?></label>
							</th>

							<td>
								<input type="text" id="qc_sld_item" name="qc_sld_item" value="<?php echo (isset($row->item)&&$row->item!=''?$row->item:'10'); ?>" required/>
								<span class="description"><?php _e( 'How many listing user can add?', 'qc-opd' ); ?></span>
							</td>
						</tr>

						<tr>
							<th><label for="qc_pd_amount"><?php _e( 'Package Price', 'qc-opd' ); ?></label>
							</th>

							<td>
								<input type="text" id="qc_sld_amount" name="qc_sld_amount" value="<?php echo (isset($row->Amount)&&$row->Amount!=''?$row->Amount:''); ?>" required/>
								<span class="description"><?php _e( 'Enter price for the package.', 'qc-opd' ); ?></span>
							</td>
						</tr>

						

						<tr>
							<th><label for="qc_sld_save"><?php _e( '', 'qc-opd' ); ?></label>
							</th>

							<td>
								<?php
								if(isset($row->id) and $row->id!=''){?>
									<input type="hidden" name="qc_sld_update" id="qc_sld_update" value="<?php echo $row->id; ?>" />
								<?php } ?>
								<input type="submit" class="button button-primary button-large" name="qc_sld_save" id="qc_sld_save" value="<?php esc_html_e( 'Save', 'qc-opd' ); ?>" />

							</td>
						</tr>

					</table>
				</form>
				<hr>
				<a class="button button-normal" href="<?php echo admin_url( 'edit.php?post_type=sld&page=qcsld_package'); ?>">
					<?php echo __('Go Back', 'qc-opd') ?>
				</a>
			</div>
			</div>
			</div>
			</div>
			</div>

<?php	
		}else{


		$rows     = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table WHERE %d ORDER BY menu_order ASC", 1 ) ); ?>
		<div class="wrap">

			<div id="poststuff">
				<div id="post-body" class="metabox-holder">
					<!-- <div id="post-body-content" style="padding: 50px;box-sizing: border-box;box-shadow: 0 8px 25px 3px rgba(0,0,0,.2);background: #fff;"> -->
					<div id="post-body-content" >

					<?php
						if($msg!=''){
							echo $msg;
						}
					?>
					
					<h1 class="wp-heading-inline" ><?php _e('Package List', 'qc-opd'); ?></h1>
					<a class="page-title-action" href="<?php echo admin_url( 'edit.php?post_type=sld&page=qcsld_package&act=addnew'); ?>">
						<?php echo __('Add New Package', 'qc-opd') ?>
					</a>
					<hr class="wp-header-end">
					<ul class="subsubsub">
						<li class="all"><a href="edit.php?post_type=sld&page=qcsld_package" class="current" aria-current="page"><?php esc_html_e( 'All', 'qc-opd' ); ?> <span class="count">(<?php echo count($rows); ?>)</span></a></li>
					</ul>
					<div>
						<table class="wp-list-table widefat fixed striped posts package-listing-sorting">
							<thead>
								<tr class="">
									<th class="sld_payment_cell">
										<?php _e( 'Title', 'qc-opd' ) ?>
									</th>
									<th class="sld_payment_cell">
										<?php _e( 'Description', 'qc-opd' ) ?>
									</th>
									<th class="sld_payment_cell">
										<?php _e( 'Duration', 'qc-opd' ) ?>
									</th>
									<th class="sld_payment_cell">
										<?php _e( 'Amount', 'qc-opd' ); ?>
									</th>
									<th class="sld_payment_cell">
										<?php _e( 'Currency', 'qc-opd' ); ?>
									</th>
									<th class="sld_payment_cell">
										<?php _e( 'Total Items', 'qc-opd' ); ?>
									</th>
									<th class="sld_payment_cell">
										<?php _e( 'Status', 'qc-opd' ); ?>
									</th>
									
									<th class="sld_payment_cell">
										<?php _e( 'Action', 'qc-opd' ); ?>
									</th>
									<th class="sld_payment_cell">
										<?php _e( 'Order', 'qc-opd' ) ?>
									</th>
								</tr>
							</thead>

					<tbody>
					<?php
					foreach($rows as $row){
					?>
						<tr class="sld_payment_row" data-package-id="<?php echo $row->id; ?>">
							<td class="sld_payment_cell">
								<div class="sld_responsive_head"><?php echo __('Title', 'qc-opd') ?></div>
								<strong>
									<a class="row-title" href="<?php echo admin_url( 'edit.php?post_type=sld&page=qcsld_package&act=edit&id='.$row->id); ?>">
										<?php echo ($row->title); ?>
									</a>
								</strong>
							</td>
							<td class="sld_payment_cell">
								<div class="sld_responsive_head"><?php echo __('Description', 'qc-opd') ?></div>
								<?php echo $row->description; ?>
							</td>
							<td class="sld_payment_cell">
								<div class="sld_responsive_head"><?php echo __('Duration', 'qc-opd') ?></div>
								
								<?php

								 if($row->duration=='lifetime') { 
								 	echo $row->duration;
								 	echo ""; 
								 }elseif($row->duration=='1-year') {
								 	echo str_replace("-"," ",$row->duration);
								 }elseif($row->duration=='2-years') {
								 	echo str_replace("-"," ",$row->duration);
								 }elseif($row->duration=='3-years') {
								 	echo str_replace("-"," ",$row->duration);
								 }elseif($row->duration=='4-years') {
								 	echo str_replace("-"," ",$row->duration);
								 }elseif($row->duration=='5-years') {
								 	echo str_replace("-"," ",$row->duration);
								 }elseif($row->duration=='1') {
								 	echo $row->duration;
								 	echo __(' Month', 'qc-opd');
								 }else{
								 	echo $row->duration;
								 	echo __(' Months', 'qc-opd');
								 }

								?>
							</td>
							<td class="sld_payment_cell">
								<div class="sld_responsive_head"><?php echo __('Amount', 'qc-opd') ?></div>
								<?php
									
									echo $row->Amount;
								?>
							</td>
							<td class="sld_payment_cell">
								<div class="sld_responsive_head"><?php echo __('Currency', 'qc-opd') ?></div>
								<?php echo $row->currency ?>
							</td>
							<td class="sld_payment_cell">
								<div class="sld_responsive_head"><?php echo __('Total Items', 'qc-opd') ?></div>
								<?php echo ($row->item); ?>
							</td>
							<td class="sld_payment_cell">
								<div class="sld_responsive_head"><?php echo __('Status', 'qc-opd') ?></div>
								<?php echo ($row->enable==1?'Active':'Inactive'); ?>
							</td>
							
							<td class="sld_payment_cell">
								<div class="sld_responsive_head"><?php echo __('Action', 'qc-opd') ?></div>
								<a class="button button-primary" href="<?php echo admin_url( 'edit.php?post_type=sld&page=qcsld_package&act=edit&id='.$row->id); ?>">
									<?php echo __('Edit', 'qc-opd') ?>
								</a>
								<a class="button button-danger" href="<?php echo admin_url( 'edit.php?post_type=sld&page=qcsld_package&act=delete&id='.$row->id); ?>">
									<?php echo __('Delete', 'qc-opd') ?>
								</a>
								
							</td>
							<td class="sld_payment_cell">
								<div class="sld_responsive_head"><?php echo __('Order', 'qc-opd') ?></div>
								<strong>
									<img src="<?php echo SLD_QCOPD_IMG_URL . '/move_alt1.png'; ?>" title="" alt="Move Icon" width="24" height="24" class="" />
								</strong>
							</td>
						</tr>
					<?php
					}
					?>
					</tbody>

					</table>

				</div>
						
						
					</div>
				</div>
			</div>
		</div>
		
		
<div class="wrap">
			
<?php 
$table             = $wpdb->prefix.'sld_claim_configuration';
$current_user = wp_get_current_user();
$msg = '';
//echo $table;exit;

//get form data
if(isset($_POST['qc_sld_claim_save'])){

	
	$enable = isset($_POST['qc_sld_claim_listing_enable'])?$_POST['qc_sld_claim_listing_enable']:0;
	$currency = sanitize_text_field($_POST['qc_sld_currency']);
	
	$amount = ($_POST['qc_sld_amount']);
	
	$date = date('Y-m-d H:i:s');
	
	
	if(isset($_POST['qc_sld_update']) and $_POST['qc_sld_update']!=''){
		$uid = $_POST['qc_sld_update'];
		$wpdb->update(
			$table,
			array(
				'date'  => $date,
				'currency'   => $currency,
				'Amount' => $amount,
				
				'enable'   => $enable,
				
			),

			array( 'id' => $uid),
			array(
				'%s',
				'%s',
				'%s',

				'%d',
				

			),
			array( '%d')
		);
		$msg = '<div id="message" class="updated notice notice-success is-dismissible"><p>Claim Configuration Updated. </p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
	}else{

		$wpdb->insert(
			$table,
			array(
				'date'  => $date,
				
				'currency'   => $currency,
				'Amount' => $amount,
				
				'enable'   => $enable,
				
			)
		);
		
		

		$msg = '<div id="message" class="updated notice notice-success is-dismissible"><p>Claim Configuration Created. </p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
	}


}
$row     = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE %d", 1 ) ); ?>
			<div id="poststuff">
				<div id="post-body" class="metabox-holder">
					<div id="post-body-content" style="padding: 50px;box-sizing: border-box;box-shadow: 0 8px 25px 3px rgba(0,0,0,.2);background: #fff;">

						<?php
						if($msg!=''){
							echo $msg;
						}
						?>
						<h1><?php echo __('Manage Claim Listing Payment', 'qc-opd') ?></h1>
						<hr>
						

						<form method="post" action="">
							<table class="form-table">

                                <tr>
                                    <th><label for="qc_sld_package_title"><?php _e( 'Enable Payment for Claim Listing', 'qc-opd' ); ?></label>
                                    </th>

                                    <td>
                                        <input type="checkbox" id="qc_sld_claim_listing_enable" name="qc_sld_claim_listing_enable" <?php echo (isset($row->enable) && $row->enable==1)?'checked="checked"':''; ?> value="1"/>
										

                                    </td>
                                </tr>

								<tr>
									<th><label for="qc_sld_amount"><?php _e( 'Claim Listing Price', 'qc-opd' ); ?></label>
									</th>

									<td>
										<input type="text" id="qc_sld_amount" name="qc_sld_amount" value="<?php echo (isset($row->Amount)&&$row->Amount!=''?$row->Amount:''); ?>" required/>
										<span class="description"><?php _e( 'Enter price for the Claim Listing.', 'qc-opd' ); ?></span>
									</td>
								</tr>

                                <tr>
                                    <th><label for="qc_sld_currency"><?php _e( 'Currency', 'qc-opd' ); ?></label>
                                    </th>
                                    <td>
                                        <select name="qc_sld_currency" id="qc_sld_currency" required>

                                            <option value="USD" <?php echo (isset($row->currency)&&$row->currency=='USD'?'selected="selected"':''); ?>><?php esc_html_e( 'US Dollars ($)', 'qc-opd' ); ?></option>
                                            <option value="EUR" <?php echo (isset($row->currency)&&$row->currency=='EUR'?'selected="selected"':''); ?>><?php esc_html_e( 'Euros (€)', 'qc-opd' ); ?></option>
                                            <option value="GBP" <?php echo (isset($row->currency)&&$row->currency=='GBP'?'selected="selected"':''); ?>><?php esc_html_e( 'Pounds Sterling (£)', 'qc-opd' ); ?></option>
                                            <option value="ARS" <?php echo (isset($row->currency)&&$row->currency=='ARS'?'selected="selected"':''); ?>><?php esc_html_e( 'Argentine Peso ($)', 'qc-opd' ); ?></option>
                                            <option value="AUD" <?php echo (isset($row->currency)&&$row->currency=='AUD'?'selected="selected"':''); ?>><?php esc_html_e( 'Australian Dollars ($)', 'qc-opd' ); ?></option>
                                            <option value="BRL" <?php echo (isset($row->currency)&&$row->currency=='BRL'?'selected="selected"':''); ?>><?php esc_html_e( 'Brazilian Real (R$)', 'qc-opd' ); ?></option>
                                            <option value="CAD" <?php echo (isset($row->currency)&&$row->currency=='CAD'?'selected="selected"':''); ?>><?php esc_html_e( 'Canadian Dollars ($)', 'qc-opd' ); ?></option>
                                            <option value="CNY" <?php echo (isset($row->currency)&&$row->currency=='CNY'?'selected="selected"':''); ?>><?php esc_html_e( 'Chinese Yuan', 'qc-opd' ); ?></option>
                                            <option value="CZK" <?php echo (isset($row->currency)&&$row->currency=='CZK'?'selected="selected"':''); ?>><?php esc_html_e( 'Czech Koruna', 'qc-opd' ); ?></option>
                                            <option value="DKK" <?php echo (isset($row->currency)&&$row->currency=='DKK'?'selected="selected"':''); ?>><?php esc_html_e( 'Danish Krone', 'qc-opd' ); ?></option>
                                            <option value="HKD" <?php echo (isset($row->currency)&&$row->currency=='HKD'?'selected="selected"':''); ?>><?php esc_html_e( 'Hong Kong Dollar ($)', 'qc-opd' ); ?></option>
                                            <option value="HUF" <?php echo (isset($row->currency)&&$row->currency=='HUF'?'selected="selected"':''); ?>><?php esc_html_e( 'Hungarian Forint', 'qc-opd' ); ?></option>
                                            <option value="INR" <?php echo (isset($row->currency)&&$row->currency=='INR'?'selected="selected"':''); ?>><?php esc_html_e( 'Indian Rupee', 'qc-opd' ); ?></option>
                                            <option value="IDR" <?php echo (isset($row->currency)&&$row->currency=='IDR'?'selected="selected"':''); ?>><?php esc_html_e( 'Indonesia Rupiah', 'qc-opd' ); ?></option>
                                            <option value="ILS" <?php echo (isset($row->currency)&&$row->currency=='ILS'?'selected="selected"':''); ?>><?php esc_html_e( 'Israeli Shekel', 'qc-opd' ); ?></option>
                                            <option value="JPY" <?php echo (isset($row->currency)&&$row->currency=='JPY'?'selected="selected"':''); ?>><?php esc_html_e( 'Japanese Yen (¥)', 'qc-opd' ); ?></option>
                                            <option value="MYR" <?php echo (isset($row->currency)&&$row->currency=='MYR'?'selected="selected"':''); ?>><?php esc_html_e( 'Malaysian Ringgits', 'qc-opd' ); ?></option>
                                            <option value="MXN" <?php echo (isset($row->currency)&&$row->currency=='MXN'?'selected="selected"':''); ?>><?php esc_html_e( 'Mexican Peso ($)', 'qc-opd' ); ?></option>
                                            <option value="NGN" <?php echo (isset($row->currency)&&$row->currency=='NGN'?'selected="selected"':''); ?>><?php esc_html_e( 'Nigerian Naira (₦)', 'qc-opd' ); ?></option>
                                            <option value="NZD" <?php echo (isset($row->currency)&&$row->currency=='NZD'?'selected="selected"':''); ?>><?php esc_html_e( 'New Zealand Dollar ($)', 'qc-opd' ); ?></option>
                                            <option value="NOK" <?php echo (isset($row->currency)&&$row->currency=='NOK'?'selected="selected"':''); ?>><?php esc_html_e( 'Norwegian Krone', 'qc-opd' ); ?></option>
                                            <option value="PHP" <?php echo (isset($row->currency)&&$row->currency=='PHP'?'selected="selected"':''); ?>><?php esc_html_e( 'Philippine Pesos', 'qc-opd' ); ?></option>
                                            <option value="PLN" <?php echo (isset($row->currency)&&$row->currency=='PLN'?'selected="selected"':''); ?>><?php esc_html_e( 'Polish Zloty', 'qc-opd' ); ?></option>
                                            <option value="SGD" <?php echo (isset($row->currency)&&$row->currency=='SGD'?'selected="selected"':''); ?>><?php esc_html_e( 'Singapore Dollar ($)', 'qc-opd' ); ?></option>
                                            <option value="ZAR" <?php echo (isset($row->currency)&&$row->currency=='ZAR'?'selected="selected"':''); ?>><?php esc_html_e( 'South African Rand (R)', 'qc-opd' ); ?></option>
                                            <option value="KRW" <?php echo (isset($row->currency)&&$row->currency=='KRW'?'selected="selected"':''); ?>><?php esc_html_e( 'South Korean Won', 'qc-opd' ); ?></option>
                                            <option value="SEK" <?php echo (isset($row->currency)&&$row->currency=='SEK'?'selected="selected"':''); ?>><?php esc_html_e( 'Swedish Krona', 'qc-opd' ); ?></option>
                                            <option value="CHF" <?php echo (isset($row->currency)&&$row->currency=='CHF'?'selected="selected"':''); ?>><?php esc_html_e( 'Swiss Franc', 'qc-opd' ); ?></option>
                                            <option value="TWD" <?php echo (isset($row->currency)&&$row->currency=='TWD'?'selected="selected"':''); ?>><?php esc_html_e( 'Taiwan New Dollars', 'qc-opd' ); ?></option>
                                            <option value="THB" <?php echo (isset($row->currency)&&$row->currency=='THB'?'selected="selected"':''); ?>><?php esc_html_e( 'Thai Baht', 'qc-opd' ); ?></option>
                                            <option value="TRY" <?php echo (isset($row->currency)&&$row->currency=='TRY'?'selected="selected"':''); ?>><?php esc_html_e( 'Turkish Lira', 'qc-opd' ); ?></option>
                                            <option value="VND" <?php echo (isset($row->currency)&&$row->currency=='VND'?'selected="selected"':''); ?>><?php esc_html_e( 'Vietnamese Dong', 'qc-opd' ); ?></option>

                                        </select>
                                    </td>
                                </tr>

								<tr>
									<th><label for="qc_sld_save"><?php _e( '', 'qc-opd' ); ?></label>
									</th>

									<td>
										<?php
										if(isset($row->id) and $row->id!=''){?>
											<input type="hidden" name="qc_sld_update" id="qc_sld_update" value="<?php echo $row->id; ?>" />
										<?php } ?>
										<input class="button button-primary" type="submit" name="qc_sld_claim_save" id="qc_sld_save" value="<?php esc_html_e( 'Save', 'qc-opd' ); ?>" />

									</td>
								</tr>

							</table>
						</form>
						<hr>
						
					</div>
				</div>
			</div>
		</div>
		
		
<?php
		}
	}
}
function sld_package(){
	return Sld_package::get_instance();
}
sld_package();