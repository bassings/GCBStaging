<?php
$sitemap_enabled = Smartcrawl_Sitemap_Utils::sitemap_enabled();
$option_name = empty( $_view['option_name'] ) ? '' : $_view['option_name'];
$options = Smartcrawl_Settings::get_specific_options( 'wds_robots_options' );
$sitemap_directive_disabled = smartcrawl_get_array_value( $options, 'sitemap_directive_disabled' );
$custom_sitemap_url = smartcrawl_get_array_value( $options, 'custom_sitemap_url' );
$custom_directives = Smartcrawl_Controller_Robots::get()->get_custom_directives();
$robots_output = Smartcrawl_Controller_Robots::get()->get_robot_file_contents();
?>

<form action="<?php echo esc_attr( $_view['action_url'] ); ?>" method="post" class="wds-form">
	<?php $this->settings_fields( $_view['option_name'] ); ?>
	<input type="hidden" value="1" name="<?php echo esc_attr( $option_name ); ?>[save_robots]"/>

	<p>
		<?php esc_html_e( 'Search engines use web crawlers (bots) to explore and index the internet. A robots.txt file is a critical text file that tells those bots what they can and can’t index, and where things are.', 'wds' ); ?>
	</p>

	<?php $this->_render( 'notice', array(
		'class'   => 'sui-notice-info',
		'message' => smartcrawl_format_link(
			esc_html__( 'Your robots.txt is active and visible to bots. You can view it at %s', 'wds' ),
			smartcrawl_get_robots_url(), smartcrawl_get_robots_url(), '_blank'
		),
	) ); ?>

	<div class="sui-box-settings-row">
		<div class="sui-box-settings-col-1">
			<label class="sui-settings-label"><?php esc_html_e( 'Output', 'wds' ); ?></label>
			<p class="sui-description"><?php esc_html_e( 'Here’s a preview of your current robots.txt output. Customize your robots.txt file below.', 'wds' ); ?></p>
		</div>

		<div class="sui-box-settings-col-2">
			<label for="robots-preview" class="sui-label"><?php esc_html_e( 'Robots.txt preview', 'wds' ); ?></label>
			<textarea id="robots-preview"
			          readonly="readonly"
			          class="sui-form-control"><?php echo esc_textarea( $robots_output ); ?></textarea>
		</div>
	</div>

	<div class="sui-box-settings-row">
		<div class="sui-box-settings-col-1">
			<label class="sui-settings-label"><?php esc_html_e( 'Include Sitemap', 'wds' ); ?></label>
			<p class="sui-description"><?php esc_html_e( 'It’s really good practice to instruct search engines where to find your sitemap. If enabled, we will automatically add the required code to your robots file.', 'wds' ); ?></p>
		</div>

		<div class="sui-box-settings-col-2">
			<div class="sui-form-field">
				<div class="wds-toggleable inverted <?php echo $sitemap_directive_disabled ? 'inactive' : ''; ?>">
					<?php $this->_render( 'toggle-item', array(
						'field_name' => sprintf( '%s[%s]', $option_name, 'sitemap_directive_disabled' ),
						'field_id'   => 'sitemap_directive_disabled',
						'checked'    => checked( $sitemap_directive_disabled, true, false ),
						'item_label' => esc_html__( 'Link to my Sitemap', 'wds' ),
						'inverted'   => true,
					) ); ?>

					<div class="wds-toggleable-inside sui-border-frame sui-toggle-content">
						<label for="custom_sitemap_url"
						       class="sui-label"><?php esc_html_e( 'Sitemap URL', 'wds' ); ?></label>
						<?php if ( $sitemap_enabled ): ?>
							<input id="custom_sitemap_url" type="hidden"
							       name="<?php echo esc_attr( $option_name ); ?>[custom_sitemap_url]"
							       value="<?php echo esc_attr( $custom_sitemap_url ); ?>"/>
							<label>
								<input type="text"
								       class="sui-form-control"
								       readonly
								       value="<?php echo esc_attr( smartcrawl_get_sitemap_url() ) ?>"/>
							</label>
							<?php $this->_render( 'notice', array(
								'class'   => 'grey',
								'message' => esc_html__( "We've detected you're using SmartCrawl's built in sitemap and will output this for you automatically.", 'wds' ),
							) ); ?>
						<?php else: ?>
							<input id="custom_sitemap_url" type="text"
							       class="sui-form-control"
							       name="<?php echo esc_attr( $option_name ); ?>[custom_sitemap_url]"
							       value="<?php echo esc_attr( $custom_sitemap_url ); ?>"/>

							<p class="sui-description">
								<?php printf(
									esc_html__( 'Copy and paste the URL to your sitemap. E.g %s or %s', 'wds' ),
									'<strong>/sitemap.xml</strong>',
									'<strong>https://example.com/sitemap.xml</strong>'
								) ?>
							</p>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="sui-box-settings-row">
		<div class="sui-box-settings-col-1">
			<label class="sui-settings-label"><?php esc_html_e( 'Customize', 'wds' ); ?></label>
			<p class="sui-description">
				<?php echo smartcrawl_format_link(
					esc_html__( 'Customize the robots.txt output here. We have %s on a range of examples and options for your robots.txt file.', 'wds' ),
					'https://premium.wpmudev.org/docs/wpmu-dev-plugins/smartcrawl/#Robots-txt',
					esc_html__( 'full documentation' )
				); ?>
			</p>
		</div>

		<div class="sui-box-settings-col-2">
			<label for="robots-file-contents"
			       class="sui-label"><?php esc_html_e( 'Edit your robots.txt file', 'wds' ); ?></label>
			<textarea id="robots-file-contents"
			          name="<?php echo esc_attr( $option_name ); ?>[custom_directives]"
			          class="sui-form-control"><?php echo esc_textarea( $custom_directives ); ?></textarea>
		</div>
	</div>

	<div class="sui-box-settings-row">
		<div class="sui-box-settings-col-1">
			<label class="sui-settings-label">
				<?php esc_html_e( 'Deactivate', 'wds' ); ?>
			</label>
			<p class="sui-description">
				<?php esc_html_e( 'No longer need a Robots.txt file? This will deactivate this feature and remove the file.', 'wds' ); ?>
			</p>
		</div>
		<div class="sui-box-settings-col-2">
			<button type="submit" name="deactivate-robots-component"
			        class="sui-button sui-button-ghost">
				<i class="sui-icon-power-on-off" aria-hidden="true"></i>
				<?php esc_html_e( 'Deactivate', 'wds' ); ?>
			</button>
		</div>
	</div>

	<footer class="sui-box-footer">
		<button name="submit" type="submit" class="sui-button sui-button-blue">
			<i class="sui-icon-save" aria-hidden="true"></i>
			<?php esc_html_e( 'Save Settings', 'wds' ); ?>
		</button>
	</footer>
</form>