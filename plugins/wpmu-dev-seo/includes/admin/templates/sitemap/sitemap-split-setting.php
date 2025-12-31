<?php
$option_name = empty( $_view['option_name'] ) ? '' : $_view['option_name'];
$split_sitemaps = Smartcrawl_Sitemap_Utils::split_sitemaps_enabled();
$items_per_sitemap = Smartcrawl_Sitemap_Utils::get_items_per_sitemap();
$automatically_switched = empty( $automatically_switched ) ? false : $automatically_switched;
$total_post_count = empty( $total_post_count ) ? 0 : $total_post_count;
?>
<div class="sui-box-settings-row">
	<div class="sui-box-settings-col-1">
		<label class="sui-settings-label"><?php esc_html_e( 'Generation Method', 'wds' ); ?></label>
		<p class="sui-description">
			<?php esc_html_e( 'Sitemaps can be generated as a single file, or split into multiple linking files. Single sitemaps are generally fine for small sites, but large sites will need split sitemaps.', 'wds' ); ?>
		</p>
	</div>
	<div class="sui-box-settings-col-2">
		<div class="wds-toggleable-inside wds-conditional sui-toggle-content">
			<label style="display: none">
				<select name="<?php echo esc_attr( $option_name ); ?>[split-sitemap]"
				        id="split-sitemap"
				        class="none-sui">
					<option <?php selected( ! $split_sitemaps ); ?>
							value="0">
						<?php esc_html_e( 'Default', 'wds' ); ?>
					</option>

					<option <?php selected( $split_sitemaps ); ?>
							value="1">
						<?php esc_html_e( 'Split', 'wds' ); ?>
					</option>
				</select>
			</label>

			<div id="wds-sitemap-type-tabs" class="sui-side-tabs sui-tabs">
				<label class="sui-label"><?php esc_html_e( 'Sitemap Structure', 'wds' ); ?></label>

				<div data-tabs>
					<div class="<?php echo ! $split_sitemaps ? 'active' : ''; ?> <?php echo $automatically_switched && $total_post_count ? 'sui-tooltip' : ''; ?>"
					     data-tooltip="<?php esc_attr_e( 'You have too many posts to generate a single sitemap', 'wds' ); ?>"
					     data-split="0">
						<?php esc_html_e( 'Default', 'wds' ); ?>
					</div>
					<div class="<?php echo $split_sitemaps ? 'active' : ''; ?>"
					     data-split="1">
						<?php esc_html_e( 'Split', 'wds' ); ?>
					</div>
				</div>

				<div data-panes>
					<div class="wds-single-pane">
						<div class="wds-conditional-inside hidden"
						     data-conditional-val="0">
							<p class="sui-description">
								<?php echo smartcrawl_format_link(
									esc_html__( 'This option generates a single sitemap.xml with all your links in it. Your index file can be found at %s', 'wds' ),
									smartcrawl_get_sitemap_url(),
									smartcrawl_get_sitemap_url()
								); ?>
							</p>

							<div class="sui-notice">
								<p><?php printf(
										esc_html__( 'Single sitemaps can only contain %d posts. If you reach this limit, SmartCrawl will automatically switch to split sitemaps to avoid high resource usage which may crash your website.', 'wds' ),
										SMARTCRAWL_SITEMAP_POST_LIMIT
									); ?>
								</p>
							</div>
						</div>

						<div class="wds-conditional-inside hidden"
						     data-conditional-val="1">

							<p class="sui-description">
								<?php echo smartcrawl_format_link(
									esc_html__( 'This option generates and links multiple paginated sitemaps. Your index file can be found at %s', 'wds' ),
									smartcrawl_get_sitemap_url(),
									smartcrawl_get_sitemap_url()
								); ?>
							</p>

							<div class="sui-row">
								<div class="sui-form-field sui-col">
									<label for="items-per-sitemap" class="sui-label">
										<?php echo esc_html__( 'Number of links per sitemap', 'wds' ); ?>
									</label>
									<input type="number"
									       id="items-per-sitemap"
									       class="sui-form-control sui-input-sm"
									       value="<?php echo esc_attr( $items_per_sitemap ); ?>"
									       name="<?php echo esc_attr( $option_name ); ?>[items-per-sitemap]"/>
								</div>
							</div>
							<p class="sui-description">
								<?php printf(
									esc_html__( 'Choose how many URLs each sitemap has, up to %s. A higher number will use more server resources to generate.', 'wds' ),
									Smartcrawl_Sitemap_Utils::get_max_items_per_sitemap()
								); ?>
							</p>

							<?php if ( $automatically_switched && $total_post_count ): ?>
								<div class="sui-notice sui-notice-warning">
									<p><?php printf(
											esc_html__( 'Note: We have automatically switched you to split sitemaps as you have approximately %s posts - this would easily crash your server if you were running a single sitemap.', 'wds' ),
											$total_post_count
										); ?>
									</p>
								</div>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>