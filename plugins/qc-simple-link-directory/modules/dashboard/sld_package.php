<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$packages = $wpdb->get_results( $wpdb->prepare( "select * from $package_table where %d", 1 ) );
if(empty($packages)){
	echo __('<p>No Package Available</p>','qc-opd');
	return;
}
//$pkginfo = $wpdb->get_row("select * from $package_purchased_table where 1 and user_id = ".$current_user->ID." order by date DESC");


$customCss = "strong {
		font-weight: bold;
	}

	em {
		font-style: italic;
	}

	table {
		background: #f5f5f5;
		border: 1px solid #fff !important;
		box-shadow: inset 0 1px 0 #fff;
		font-size: 12px;
		line-height: 24px;
		margin: 30px auto;
		text-align: left;
		width: 800px;
	}


	td {
		border-right: 1px solid #fff;
		border-left: 1px solid #e8e8e8;
		border-top: 1px solid #fff;
		border-bottom: 1px solid #e8e8e8;
		padding: 10px 15px;
		position: relative;
		transition: all 300ms;
	}



	td:last-child {
		border-right: 1px solid #e8e8e8;
		box-shadow: inset -1px 0 0 #fff;
	}

	tr:last-of-type td {
		box-shadow: inset 0 -1px 0 #fff;
	}

	tr:last-of-type td:first-child {
		box-shadow: inset 1px -1px 0 #fff;
	}

	tr:last-of-type td:last-child {
		box-shadow: inset -1px -1px 0 #fff;
	}
.sld_table_package_head{
	background: #474343;
	color: #fff;
	text-align: center;
}
	.sld_table_package_content{
		font-size:16px;
 }";

wp_add_inline_style( 'sldcustom_dashboard-css', $customCss );

?>

<h2><?php echo (sld_get_option('sld_lan_package_details')!=''?sld_get_option('sld_lan_package_details'):__('Package Details', 'qc-opd')) ?></h2>



<?php if(isset($_GET['er']) && $_GET['er']!=''): ?>
<p style="color:#fff;background:red;padding:10px;text-align:center"><?php echo sanitize_text_field($_GET['er']); ?></p>
<?php endif; ?>
<?php if(isset($_GET['success']) && $_GET['success']!=''): ?>
<p style="color:#fff;background:green;padding:10px;text-align:center"><?php echo sanitize_text_field($_GET['success']); ?></p>
<?php endif; ?>

<?php 

foreach($packages as $package): 
if( $package->enable ){
?>
<table id="sld_package_table">
	<tbody>

	<tr>
		<td width="30%" class="sld_table_package_head"><strong> <?php echo (sld_get_option('sld_lan_package_title')!=''?sld_get_option('sld_lan_package_title'):__('Title', 'qc-opd')) ?></strong></td>
		<td width="70%" align="center" class="sld_table_package_content"><?php echo (isset($package->title)&&$package->title!=''?$package->title:''); ?></td>
	</tr>
    <?php
    if($package->description!=''){
    ?>
        <tr>
            <td width="30%" class="sld_table_package_head"><strong> <?php echo (sld_get_option('sld_lan_package_title')!=''?sld_get_option('sld_lan_package_description'):__('Description', 'qc-opd')) ?> </strong></td>
            <td width="70%" align="center" class="sld_table_package_content"><?php echo (isset($package->description)&&$package->description!=''?$package->description:''); ?></td>
        </tr>
    <?php
    }
    ?>

	<tr>
		<td width="30%" class="sld_table_package_head"><strong> <?php echo (sld_get_option('sld_lan_package_duration')!=''?sld_get_option('sld_lan_package_duration'):__('Duration', 'qc-opd')) ?> </strong></td>
		<td width="70%" align="center" class="sld_table_package_content">
		<?php
			echo (isset($package->duration)&&$package->duration!='lifetime'?$package->duration.__(' Month','qc-opd'):ucwords($package->duration));
		?>
		
		</td>
	</tr>
	

    <tr>
        <td width="30%" class="sld_table_package_head"><strong> <?php echo (sld_get_option('sld_lan_package_link')!=''?sld_get_option('sld_lan_package_link'):__('Link', 'qc-opd')) ?></strong></td>
        <td width="70%" align="center" class="sld_table_package_content"><?php echo (isset($package->item)&&$package->item!=''?$package->item:'0'); ?></td>
    </tr>

	<tr>
		<td width="30%" class="sld_table_package_head"><strong> <?php echo (sld_get_option('sld_lan_package_price')!=''?sld_get_option('sld_lan_package_price'):__('Price', 'qc-opd')) ?></strong></td>
		<td width="70%" align="center" class="sld_table_package_content"><?php echo (isset($package->Amount)&&$package->Amount!=''?$package->Amount:'0'); ?></td>
	</tr>

	<tr>
		<td width="30%" class="sld_table_package_head"><strong> <?php echo (sld_get_option('sld_lan_package_currency')!=''?sld_get_option('sld_lan_package_currency'):__('Currency', 'qc-opd')) ?></strong></td>
		<td width="70%" align="center" class="sld_table_package_content"><?php echo $package->currency; ?></td>
	</tr>
	<?php if(sld_get_option('sld_enable_paypal_recurring')=='on' && sld_get_option('sld_enable_paypal_payment')!='off' && $package->duration!='lifetime'): ?>
	<tr>
		<td width="30%" class="sld_table_package_head"></td>
		<td width="70%" align="center" class="sld_table_package_content">
				<div class="">
					<span> <?php echo (sld_get_option('sld_lan_package_enable_recurring')!=''?sld_get_option('sld_lan_package_enable_recurring'):__('Enable Recurring', 'qc-opd')) ?></span><input type="checkbox" name="sld_enable_recurring" id="sld_enable_recurring" class="sld_enable_recurring_package" value="1" />
				</div>
		</td>
	</tr>
	<?php endif; ?>
<?php

if(sld_get_option('sld_enable_paypal_test_mode')=='on'){
    $mainurl = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
}else{
    $mainurl = 'https://www.paypal.com/cgi-bin/webscr';
}



?>
    <tr>
        <td width="30%" bgcolor="#fff" style="border-left:none;" ></td>
        <td width="70%" align="center" class="sld_table_package_content">
			<?php if(sld_get_option('sld_enable_paypal_payment')!='off'): ?>
            <form action="<?php echo $mainurl; ?>" method="post" id="paypalProcessor">
                <input type="hidden" name="cmd" value="_xclick" />

                <input type="hidden" name="business" value="<?php echo sld_get_option('sld_paypal_email'); ?>">
                <input type="hidden" name="currency_code" value="<?php echo $package->currency; ?>" />
                <input type="hidden" name="no_note" value="1"/>
                <input type="hidden" name="no_shipping" value="1" />
                <input type="hidden" name="charset" value="utf-8" />

                <input type="hidden" name="notify_url" value="<?php echo esc_url( add_query_arg( array('user'=> $current_user->ID, 'packagesave'=>$package->id), $url ) ) ?>" />

                <input type="hidden" name="return" value="<?php echo esc_url( add_query_arg( 'payment', 'success', $url ) ) ?>" />

                <input type="hidden" name="cancel_return" value="<?php echo esc_url( add_query_arg( 'payment', 'cancel', $url ) ) ?>">
                <input type="hidden" name="item_name" value="<?php echo $package->title; ?>">
				<input type="hidden" name="receiver_email" value="<?php echo sld_get_option('sld_paypal_email'); ?>">
				
				
                <input type="hidden" name="amount" value="<?php echo (isset($package->Amount)&&$package->Amount!=''?$package->Amount:'0'); ?>">

                <input type="hidden" name="quantity" value="1">
                <input type="hidden" name="custom" value="normal">
				
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

                <input type="hidden" name="notify_url" value="<?php echo esc_url( add_query_arg( array('user'=> $current_user->ID, 'packagesave'=>$package->id), $url ) ) ?>" />

                <input type="hidden" name="return" value="<?php echo esc_url( add_query_arg( 'payment', 'success', $url ) ) ?>" />

                <input type="hidden" name="cancel_return" value="<?php echo esc_url( add_query_arg( 'payment', 'cancel', $url ) ) ?>">
                <input type="hidden" name="item_name" value="<?php echo $package->title; ?>">
				<input type="hidden" name="receiver_email" value="<?php echo sld_get_option('sld_paypal_email'); ?>">
				
				
				<input type="hidden" name="a3" value="<?php echo (isset($package->Amount)&&$package->Amount!=''?$package->Amount:'0'); ?>">
				<input type="hidden" name="p3" value="<?php echo (isset($package->duration)&&$package->duration!=''?$package->duration:'0'); ?>">
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
				<form action="<?php echo esc_url( add_query_arg( array('payment'=> 'stripe-save', 'userid'=>$current_user->ID, 'package'=> $package->id), $url ) ) ?>" method="post">
					<script src="https://checkout.stripe.com/checkout.js" class="stripe-button"
						  data-key="<?php echo sld_get_option('sld_stripe_public_key'); ?>"
						  data-description="<?php echo $package->title; ?>"
						  data-amount="<?php echo (isset($package->Amount)&&$package->Amount!=''?($package->Amount*100):'0'); ?>"
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

			
			
        </td>
    </tr>

	</tbody>
</table>
<?php 
}
endforeach; 

?>






<?php
$pkglist = $wpdb->get_results("select ppt.id as pid, ppt.package_id as id, ppt.`date` as purchase_date, ppt.renew as renew_date, ppt.expire_date as expiredate, ppt.recurring,ppt.status, pt.title, pt.Amount as cost, pt.currency as currency, pt.item as total_item from $package_purchased_table as ppt, $package_table as pt where 1 and ppt.user_id = ".$current_user->ID." and ppt.package_id = pt.id order by ppt.date DESC");



?>
<h2><?php echo (sld_get_option('sld_lan_package_package_list')!=''?sld_get_option('sld_lan_package_package_list'):__('Package List', 'qc-opd')) ?></h2>
<?php
if(!empty($pkglist)) {
	?>
    <div class="qc_sld_table_area">
        <div class="qc_sld_table">

            <div class="qc_sld_row header">

                <div class="qc_sld_cell qc_sld_table_head">
					<?php echo (sld_get_option('sld_lan_package_package_name')!=''?sld_get_option('sld_lan_package_package_name'):__('Package name', 'qc-opd')) ?>
                </div>

                <div class="qc_sld_cell qc_sld_table_head">
					<?php echo (sld_get_option('sld_lan_package_package_purchase_date')!=''?sld_get_option('sld_lan_package_package_purchase_date'):__('Purchase Date', 'qc-opd')) ?>
                </div>
                <div class="qc_sld_cell qc_sld_table_head">
					<?php echo (sld_get_option('sld_lan_package_package_expire_date')!=''?sld_get_option('sld_lan_package_package_expire_date'):__('Expire Date', 'qc-opd')) ?>
                </div>

                <div class="qc_sld_cell qc_sld_table_head">
					<?php echo (sld_get_option('sld_lan_package_package_total_link')!=''?sld_get_option('sld_lan_package_package_total_link'):__('Total Link', 'qc-opd')) ?>
                </div>

                <div class="qc_sld_cell qc_sld_table_head">
					<?php echo (sld_get_option('sld_lan_package_remaining_link')!=''?sld_get_option('sld_lan_package_remaining_link'):__('Remaining Link', 'qc-opd')) ?>
                </div>
                <div class="qc_sld_cell qc_sld_table_head">
					<?php echo (sld_get_option('sld_lan_package_package_cost')!=''?sld_get_option('sld_lan_package_package_cost'):__('Cost', 'qc-opd')) ?>
                </div>

                <div class="qc_sld_cell qc_sld_table_head">
					<?php echo (sld_get_option('sld_lan_package_status')!=''?sld_get_option('sld_lan_package_status'):__('Status', 'qc-opd')) ?>
                </div>

                <div class="qc_sld_cell qc_sld_table_head">
					<?php echo (sld_get_option('sld_lan_package_renew')!=''?sld_get_option('sld_lan_package_renew'):__('Renew', 'qc-opd')) ?>
                </div>
            </div>
			<?php
			$c = 0;
			foreach ( $pkglist as $row ):
				$c ++;
				?>

                <div class="qc_sld_row">



                    <div class="qc_sld_cell">
                        <div class="sld_responsive_head"><?php echo (sld_get_option('sld_lan_package_package_name')!=''?sld_get_option('sld_lan_package_package_name'):__('Package name', 'qc-opd')) ?></div>
						<?php echo $row->title; ?>
                    </div>


                    <div class="qc_sld_cell">
                        <div class="sld_responsive_head"><?php echo (sld_get_option('sld_lan_package_package_purchase_date')!=''?sld_get_option('sld_lan_package_package_purchase_date'):__('Purchase Date', 'qc-opd')) ?></div>
						<?php echo( date( "Y-m-d", strtotime( $row->purchase_date ) ) ); ?>
                    </div>

                    <div class="qc_sld_cell">
                        <div class="sld_responsive_head"><?php echo (sld_get_option('sld_lan_package_package_expire_date')!=''?sld_get_option('sld_lan_package_package_expire_date'):__('Expire Date', 'qc-opd')) ?></div>
						
						<?php 
							if(sld_get_option('sld_enable_stripe_payment')=="on"){
								echo esc_html('Recurring');
							}else{
								echo( date( "Y-m-d", strtotime( $row->expiredate ) ) ); 
							}
							
						
						?>
						
                    </div>

                    <div class="qc_sld_cell">
                        <div class="sld_responsive_head"><?php echo (sld_get_option('sld_lan_package_package_total_link')!=''?sld_get_option('sld_lan_package_package_total_link'):__('Total Link', 'qc-opd')) ?></div>
						<?php echo $row->total_item; ?>
                    </div>

                    <div class="qc_sld_cell">
                        <div class="sld_responsive_head"><?php echo (sld_get_option('sld_lan_package_package_remain_link')!=''?sld_get_option('sld_lan_package_package_remain_link'):__('Remain Link', 'qc-opd')); ?></div>
						<?php
						$submited_item = $wpdb->get_row("select count(*)as cnt from $table where 1 and package_id= ".$row->pid." and user_id =".$current_user->ID);
						echo ($row->total_item-$submited_item->cnt);
                        ?>
                    </div>

                    <div class="qc_sld_cell">
                        <div class="sld_responsive_head"><?php echo (sld_get_option('sld_lan_package_package_cost')!=''?sld_get_option('sld_lan_package_package_cost'):__('Cost', 'qc-opd')) ?></div>
						<?php echo $row->cost.' '.$row->currency; ?>
                    </div>

                    <div class="qc_sld_cell">
                        <div class="sld_responsive_head"><?php echo (sld_get_option('sld_lan_package_status')!=''?sld_get_option('sld_lan_package_status'):__('Status', 'qc-opd')) ?></div>
	                    <?php
						if(sld_get_option('sld_enable_stripe_payment')=="on"){
							if(trim($row->status)!='cancel'){
								//echo 'Active';
								echo (sld_get_option('sld_lan_package_package_active')!=''?sld_get_option('sld_lan_package_package_active'):__('Active', 'qc-opd'));
							}else{
								//echo 'Cancel';
								echo (sld_get_option('sld_lan_package_package_cancel')!=''?sld_get_option('sld_lan_package_package_cancel'):__('Cancel', 'qc-opd'));
							}
						}else{
							if(strtotime(date('Y-m-d')) < strtotime($row->expiredate)){
								// echo 'Active';
								echo (sld_get_option('sld_lan_package_package_active')!=''?sld_get_option('sld_lan_package_package_active'):__('Active', 'qc-opd'));
							}else{
								// echo 'Expired';
								echo (sld_get_option('sld_lan_package_package_expired')!=''?sld_get_option('sld_lan_package_package_expired'):__('Expired', 'qc-opd'));
							}
						}
	                    

	                    ?>
                    </div>

                    <div class="qc_sld_cell">
                        <div class="sld_responsive_head"><?php echo (sld_get_option('sld_lan_package_renew')!=''?sld_get_option('sld_lan_package_renew'):__('Renew', 'qc-opd')) ?></div>
						<?php if(sld_get_option('sld_enable_paypal_payment')!='off'): ?>
						<?php if(sld_get_option('sld_enable_paypal_recurring')!='on'): ?>
                        <form action="<?php echo $mainurl; ?>" method="post" id="paypalProcessor">
                            <input type="hidden" name="cmd" value="_xclick" />

                            <input type="hidden" name="business" value="<?php echo sld_get_option('sld_paypal_email'); ?>">
                            <input type="hidden" name="currency_code" value="<?php echo $package->currency; ?>" />
                            <input type="hidden" name="no_note" value="1"/>
                            <input type="hidden" name="no_shipping" value="1" />
                            <input type="hidden" name="charset" value="utf-8" />

                            <input type="hidden" name="notify_url" value="<?php echo esc_url( add_query_arg( array('packagerenew'=>$package->id), $url ) ); ?>" />

                            <input type="hidden" name="return" value="<?php echo esc_url( add_query_arg( 'payment', 'success', $url ) ) ?>" />

                            <input type="hidden" name="cancel_return" value="<?php echo esc_url( add_query_arg( 'payment', 'cancel', $url ) ) ?>">
                            <input type="hidden" name="item_name" value="<?php echo $package->title; ?>">
                            <input type="hidden" name="amount" value="<?php echo (isset($package->Amount)&&$package->Amount!=''?$package->Amount:'0'); ?>">

                            <input type="hidden" name="quantity" value="1">
                            <input type="hidden" name="receiver_email" value="<?php echo sld_get_option('sld_paypal_email'); ?>">
                            <input type="image" name="submit" border="0"  src="<?php echo SLD_QCOPD_IMG_URL.'/btn_buynow_LG.gif'; ?>" alt="PayPal - The safer, easier way to pay online">
							<span style="display:block;margin:0;padding:0;"><?php echo (sld_get_option('sld_lan_paypal')!=''?sld_get_option('sld_lan_paypal'):__('Paypal', 'qc-opd')); ?></span>
                        </form>
						<?php endif; ?>
						<?php endif; ?>
						
						<?php if(sld_get_option('sld_enable_stripe_payment')=="on"): ?>
							
							<form action="<?php echo esc_url( add_query_arg( array('payment'=> 'stripe-renew', 'pkg'=> $row->pid), $url ) ) ?>" method="post">
								<script src="https://checkout.stripe.com/checkout.js" class="stripe-button"
									  data-key="<?php echo sld_get_option('sld_stripe_public_key'); ?>"
									  data-description="<?php echo $package->title; ?>"
									  data-amount="<?php echo (isset($package->Amount)&&$package->Amount!=''?($package->Amount*100):'0'); ?>"
									  data-locale="auto"	
									  data-currency="<?php echo $package->currency; ?>"									  
									  ></script>
									 
									 <span style="display:block;margin:0;padding:0;"><?php echo (sld_get_option('sld_lan_stripe')!=''?sld_get_option('sld_lan_stripe'):__('Stripe', 'qc-opd')); ?></span>
							</form>
						
						<?php endif; ?>
						
						<?php if(sld_get_option('sld_enable_2checkout_payment')=="on"): ?>
						<button class="sld_2co_btn" data-mfp-src="#sld_package_2co<?php echo $c; ?>" style="margin-top: 6px;"><?php echo (sld_get_option('sld_lan_2checkout')!=''?sld_get_option('sld_lan_2checkout'):__('2Checkout', 'qc-opd')); ?></button>
						
						<div id="sld_package_2co<?php echo $c; ?>" class="white-popup mfp-hide">
							<div class="sld_2co_form">
								<p class="sld_2co_head"><?php echo (sld_get_option('sld_lan_2checkout_payment')!=''?sld_get_option('sld_lan_2checkout_payment'):__('2Checkout Payment', 'qc-opd')); ?></p>
								<form id="sld_2co_package_form<?php echo $c; ?>" action="<?php echo esc_url( add_query_arg( array('payment'=> '2co-renew', 'pkg'=>$row->pid), $url ) ) ?>" method="post">
									<input name="token" type="hidden" value="">
									<input name="amount" type="hidden" value="<?php echo (isset($package->Amount)&&$package->Amount!=''?($package->Amount):'0'); ?>">
									<input type="text" name="name" placeholder="Name" required />
									<input type="text" name="address" placeholder="Street Address" required />
									<input type="text" name="city" placeholder="City" required />
									<input type="text" name="state" placeholder="State" required />
									<input type="text" name="zipcode" placeholder="Zipcode" required />
									<input type="text" name="country" placeholder="Country" required />
									<input type="text" name="phone" placeholder="Phone" required />
									<input id="sld_package_ccNo<?php echo $c; ?>" type="text" size="20" value="" placeholder="Card No" autocomplete="off" required />
									<div class="sld_2co_expire_date">
										<input type="text" size="2" id="sld_package_expMonth<?php echo $c; ?>" maxlength="2" placeholder="MM" required /> / <input type="text" placeholder="YYYY" size="2" maxlength="4" id="sld_package_expYear<?php echo $c; ?>" required />
									</div>
									<input id="sld_package_cvv<?php echo $c; ?>" type="text" placeholder="CVV" value="" autocomplete="off" required />
									<input type="submit" class="sld_2co_submit" id="sld_2co_package_submit<?php echo $c; ?>" value="Pay $<?php echo (isset($package->Amount)&&$package->Amount!=''?($package->Amount):'0'); ?>" />
								</form>
							<script>
								// Called when token created successfully.
								var successCallback<?php echo $c; ?> = function(data) {
									var myForm = document.getElementById('sld_2co_package_form<?php echo $c; ?>');
									
									// Set the token as the value for the token input
									myForm.token.value = data.response.token.token;

									// IMPORTANT: Here we call `submit()` on the form element directly instead of using jQuery to prevent and infinite token request loop.
									myForm.submit();
								};

								// Called when token creation fails.
								var errorCallback<?php echo $c; ?> = function(data) {
									if (data.errorCode === 200) {
										tokenRequest<?php echo $c; ?>();
									} else {
										alert(data.errorMsg);
									}
								};

								var tokenRequest<?php echo $c; ?> = function() {
									// Setup token request arguments
									
									var args = {
										sellerId: "<?php echo sld_get_option('sld_2checkout_seller_id'); ?>",
										publishableKey: "<?php echo sld_get_option('sld_2checkout_public_key'); ?>",
										ccNo: document.getElementById('sld_package_ccNo<?php echo $c; ?>').value,
										cvv: document.getElementById('sld_package_cvv<?php echo $c; ?>').value,
										expMonth: document.getElementById('sld_package_expMonth<?php echo $c; ?>').value,
										expYear: document.getElementById('sld_package_expYear<?php echo $c; ?>').value
									};

									// Make the token request
									TCO.requestToken(successCallback<?php echo $c; ?>, errorCallback<?php echo $c; ?>, args);
								};

								jQuery(function() {
									// Pull in the public encryption key for our environment
									<?php if(sld_get_option('sld_enable_2checkout_sandbox')=="on"): ?>
									TCO.loadPubKey('sandbox');
									<?php endif; ?>

									jQuery("#sld_2co_package_form<?php echo $c; ?>").submit(function(e) {
										// Call our token request function
										tokenRequest<?php echo $c; ?>();

										// Prevent form from submitting
										return false;
									});
								});
							</script>
							</div>
						</div>
						<?php endif; ?>	
						
						<?php if(sld_get_option('sld_enable_mollie_payment')=="on"): ?>
							
							<form action="<?php echo esc_url( add_query_arg( array('payment'=> 'mollie-renew', 'package'=> $package->id), $url ) ) ?>" method="post">
								<input type="hidden" class="amount" value="<?php echo (isset($package->Amount)&&$package->Amount!=''?($package->Amount):'0'); ?>" />
								<input type="submit" class="sld_mollie_submit" id="sld_mollie_package_submit" value="<?php esc_html_e('Mollie'); ?>" />
							</form>
						
						<?php endif; ?>

                    </div>

                </div>
				<?php
			endforeach;
			?>

        </div>

    </div>
	<?php
}else{
?>
    <p><?php echo (sld_get_option('sld_lan_package_package_no_purchased')!=''?sld_get_option('sld_lan_package_package_no_purchased'):__('You have no package purchased!', 'qc-opd')); ?></p>
<?php
}
?>
