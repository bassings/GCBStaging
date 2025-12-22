<?php

class Smartcrawl_Controller_Hub { // phpcs:ignore -- We have two versions of this class


	private static $_instance;

	private $_is_running = false;

	private function __construct() {
	}

	/**
	 * Boot controller listeners
	 *
	 * Do it only once, if they're already up do nothing
	 *
	 * @return bool Status
	 */
	public static function serve() {
		$me = self::get();
		if ( $me->is_running() ) {
			return false;
		}

		$me->_add_hooks();

		return true;
	}

	/**
	 * Obtain instance without booting up
	 *
	 * @return Smartcrawl_Controller_Hub instance
	 */
	public static function get() {
		if ( empty( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Check if we already have the actions bound
	 *
	 * @return bool Status
	 */
	public function is_running() {
		return $this->_is_running;
	}

	/**
	 * Bind listening actions
	 */
	private function _add_hooks() {
		add_filter( 'wdp_register_hub_action', array( $this, 'register_hub_actions' ) );

		$this->_is_running = true;
	}

	/**
	 * Registers Hub action listeners
	 *
	 * @param array $actions All the Hub actions registered this far
	 *
	 * @return array Augmented actions
	 */
	public function register_hub_actions( $actions ) {
		if ( ! is_array( $actions ) ) {
			return $actions;
		}

		$actions['wds-sync-ignores'] = array( $this, 'json_sync_ignores_list' );
		$actions['wds-purge-ignores'] = array( $this, 'json_purge_ignores_list' );

		$actions['wds-sync-extras'] = array( $this, 'json_sync_extras_list' );
		$actions['wds-purge-extras'] = array( $this, 'json_purge_extras_list' );

		$actions['wds-audit-data'] = array(
			$this,
			'json_receive_audit_data'
		);

		$actions['wds-seo-summary'] = array( $this, 'json_seo_summary' );
		$actions['wds-run-checkup'] = array( $this, 'json_run_checkup' );

		return $actions;
	}

	public function obj_to_array( $data ) {
		return json_decode(
			json_encode( $data ),
			true
		);
	}

	/**
	 * Receives the SEO Audit data pushes from the Hub
	 *
	 * Updates the crawl state.
	 *
	 * @param object $params Hub-provided parameters
	 * @param string $action Action called
	 */
	public function json_receive_audit_data( $params = array(), $action = '' ) {
		$status = true;

		$service = Smartcrawl_Service::get(
			Smartcrawl_Service::SERVICE_SEO
		);
		$data = $this->obj_to_array( $params );
		$service->set_result( $data );
		$service->set_progress_flag( empty( $data['end'] ) );
		$service->set_last_run_timestamp();
		Smartcrawl_Logger::debug('Received sitemap crawl data from remote');

		return ! empty( $status )
			? wp_send_json_success()
			: wp_send_json_error();
	}

	/**
	 * Fresh ignores from the Hub action handler
	 *
	 * Updates local ignores list when the Hub storage is updated.
	 *
	 * @param object $params Hub-provided parameters
	 * @param string $action Action called
	 */
	public function json_sync_ignores_list( $params = array(), $action = '' ) {
		Smartcrawl_Logger::info( 'Received ignores syncing request' );
		$status = $this->sync_ignores_list( $params, $action );

		return ! empty( $status )
			? wp_send_json_success()
			: wp_send_json_error();
	}

	/**
	 * Fresh ignores from the Hub action handler
	 *
	 * Updates local ignores list when the Hub storage is updated.
	 *
	 * @param object $params Hub-provided parameters
	 * @param string $action Action called
	 *
	 * @return bool Status
	 */
	public function sync_ignores_list( $params = array(), $action = '' ) {
		$ignores = new Smartcrawl_Model_Ignores();

		$data = stripslashes_deep( (array) $params );
		if ( empty( $data['issue_ids'] ) || ! is_array( $data['issue_ids'] ) ) {
			return false;
		}

		$status = true;
		foreach ( $data['issue_ids'] as $issue_id ) {
			$tmp = $ignores->set_ignore( $issue_id );
			if ( ! $tmp ) {
				$status = false;
			}
		}

		return $status;
	}

	/**
	 * Purge ignores from the Hub action handler
	 *
	 * Purges local ignores list when the Hub storage is purged.
	 *
	 * @param object $params Hub-provided parameters
	 * @param string $action Action called
	 */
	public function json_purge_ignores_list( $params = array(), $action = '' ) {
		Smartcrawl_Logger::info( 'Received ignores purging request' );
		$status = $this->purge_ignores_list( $params, $action );

		return ! empty( $status )
			? wp_send_json_success()
			: wp_send_json_error();
	}

	/**
	 * Purge ignores from the Hub action handler
	 *
	 * Purges local ignores list when the Hub storage is purged.
	 *
	 * @param object $params Hub-provided parameters
	 * @param string $action Action called
	 *
	 * @return bool Status
	 */
	public function purge_ignores_list( $params = array(), $action = '' ) {
		$ignores = new Smartcrawl_Model_Ignores();

		return $ignores->clear();
	}

	/**
	 * Fresh extras from the Hub action handler
	 *
	 * Updates local extra URLs list when the Hub storage is updated.
	 *
	 * @param object $params Hub-provided parameters
	 * @param string $action Action called
	 */
	public function json_sync_extras_list( $params = array(), $action = '' ) {
		Smartcrawl_Logger::info( 'Received extras syncing request' );
		$status = $this->sync_extras_list( $params, $action );

		return ! empty( $status )
			? wp_send_json_success()
			: wp_send_json_error();
	}

	/**
	 * Fresh extras from the Hub action handler
	 *
	 * Updates local extra URLs list when the Hub storage is updated.
	 *
	 * @param object $params Hub-provided parameters
	 * @param string $action Action called
	 *
	 * @return bool Status
	 */
	public function sync_extras_list( $params = array(), $action = '' ) {
		$data = stripslashes_deep( (array) $params );
		if ( empty( $data['urls'] ) || ! is_array( $data['urls'] ) ) {
			return false;
		}

		$existing = Smartcrawl_Sitemap_Utils::get_extra_urls();
		foreach ( $data['urls'] as $url ) {
			$existing[] = esc_url( $url );
		}

		return Smartcrawl_Sitemap_Utils::set_extra_urls( $existing );
	}

	/**
	 * Purge extras from the Hub action handler
	 *
	 * Purges local extra URLs list when the Hub storage is updated.
	 *
	 * @param object $params Hub-provided parameters
	 * @param string $action Action called
	 */
	public function json_purge_extras_list( $params = array(), $action = '' ) {
		$status = $this->purge_extras_list( $params, $action );

		return ! empty( $status )
			? wp_send_json_success()
			: wp_send_json_error();
	}

	/**
	 * Purge extras from the Hub action handler
	 *
	 * Purges local extra URLs list when the Hub storage is updated.
	 *
	 * @param object $params Hub-provided parameters
	 * @param string $action Action called
	 *
	 * @return bool Status
	 */
	public function purge_extras_list( $params = array(), $action = '' ) {
		return Smartcrawl_Sitemap_Utils::set_extra_urls( array() );
	}

	public function json_seo_summary() {
		$options = Smartcrawl_Settings::get_options();

		// Twitter cards
		$twitter_enabled = (bool) smartcrawl_get_array_value( $options, 'twitter-card-enable' );
		$card_type = smartcrawl_get_array_value( $options, 'twitter-card-type' );
		$twitter = $twitter_enabled
			? array( 'card_type' => $card_type )
			: array();

		// Pinterest
		$pinterest_status = smartcrawl_get_array_value( $options, 'pinterest-verification-status' );
		$pinterest_value = smartcrawl_get_array_value( $options, 'pinterest-verify' );
		$pinterest = empty( $pinterest_value )
			? array()
			: array(
				'status' => 'fail' === $pinterest_status ? 'unverified' : 'verified',
			);

		// URL redirects
		$redirection = new Smartcrawl_Model_Redirection();
		$redirects_count = count( $redirection->get_all_redirections() );

		// Moz
		$moz_access_id = smartcrawl_get_array_value( $options, 'access-id' );
		$moz_secret_key = smartcrawl_get_array_value( $options, 'secret-key' );
		$moz_active = $moz_access_id && $moz_secret_key;

		// Robots file
		$robots_controller = Smartcrawl_Controller_Robots::get();

		// The whole advanced page can be disabled in the network settings
		$advanced_active = smartcrawl_is_allowed_tab( Smartcrawl_Settings::TAB_AUTOLINKS );
		$advanced = $advanced_active
			? array(
				'url_redirects'      => $redirects_count,
				'moz_active'         => $moz_active,
				'robots_txt_active'  => $robots_controller->robots_active(),
				'autolinking_active' => Smartcrawl_Settings::get_setting( 'autolinks' ),
			)
			: array();

		// Checkup reporting schedule
		$checkup_service = Smartcrawl_Service::get( Smartcrawl_Service::SERVICE_CHECKUP );
		if ( $checkup_service->in_progress() ) {
			// Call status once so that the last updated timestamp gets updated
			$checkup_service->status();
		}
		$checkup_reporting_enabled = smartcrawl_get_array_value( $options, 'checkup-cron-enable' );
		$checkup_reporting = $checkup_reporting_enabled
			? array(
				'frequency'  => smartcrawl_get_array_value( $options, 'checkup-frequency' ),
				'day'        => smartcrawl_get_array_value( $options, 'checkup-dow' ),
				'time'       => smartcrawl_get_array_value( $options, 'checkup-tod' ),
				'recipients' => count( smartcrawl_get_array_value( $options, 'checkup-email-recipients' ) ),
			)
			: array();
		$checkup = $this->is_active( 'checkup' )
			? array(
				'in_progress'        => $checkup_service->in_progress(),
				'last_run_timestamp' => $checkup_service->get_last_checked_timestamp(),
				'reporting'          => (object) $checkup_reporting,
			)
			: array();

		// Crawler reporting schedule
		$seo_service = Smartcrawl_Service::get( Smartcrawl_Service::SERVICE_SEO );
		$seo_report = $seo_service->get_report();
		$crawler_reporting_enabled = smartcrawl_get_array_value( $options, 'crawler-cron-enable' );
		$crawler_reporting = $crawler_reporting_enabled
			? array(
				'frequency' => smartcrawl_get_array_value( $options, 'crawler-frequency' ),
				'day'       => smartcrawl_get_array_value( $options, 'crawler-dow' ),
				'time'      => smartcrawl_get_array_value( $options, 'crawler-tod' ),
			)
			: array();
		$sitemap = $this->is_active( 'sitemap' )
			? array(
				'crawler' => array(
					'in_progress'        => $seo_report->is_in_progress(),
					'last_run_timestamp' => $seo_service->get_last_run_timestamp(),
					'reporting'          => (object) $crawler_reporting,
				),
			)
			: array();

		// Third-party import
		$import_plugins = array();
		$yoast_importer = new Smartcrawl_Yoast_Importer();
		if ( $yoast_importer->data_exists() ) {
			$import_plugins[] = 'yoast';
		}
		$aioseo = new Smartcrawl_AIOSEOP_Importer();
		if ( $aioseo->data_exists() ) {
			$import_plugins[] = 'aioseo';
		}

		$onpage_active = $this->is_active( 'onpage' );
		$onpage = $onpage_active
			? array(
				'static_homepage'   => get_option( 'show_on_front' ) === 'page',
				'public_post_types' => count( get_post_types( array( 'public' => true ) ) ),
			)
			: array();

		$social_active = $this->is_active( 'social' );
		$social = $social_active
			? array(
				'opengraph_active' => (bool) smartcrawl_get_array_value( $options, 'og-enable' ),
				'twitter'          => (object) $twitter,
				'pinterest'        => (object) $pinterest,
			)
			: array();

		wp_send_json_success( array(
			'sitewide'           => is_multisite() && smartcrawl_is_switch_active( 'SMARTCRAWL_SITEWIDE' ),
			'onpage'             => (object) $onpage,
			'social'             => (object) $social,
			'advanced' => (object) $advanced,
			'checkup'  => (object) $checkup,
			'sitemap'  => (object) $sitemap,
			'import'             => array( 'plugins' => $import_plugins ),
		) );
	}

	public function is_active( $module ) {
		return Smartcrawl_Settings::get_setting( $module )
		       && smartcrawl_is_allowed_tab( 'wds_' . $module );
	}

	public function json_run_checkup() {
		$service = Smartcrawl_Service::get( Smartcrawl_Service::SERVICE_CHECKUP );
		$started = $service->start();

		if ( $started ) {
			wp_send_json_success();
		} else {
			wp_send_json_error();
		}
	}
}