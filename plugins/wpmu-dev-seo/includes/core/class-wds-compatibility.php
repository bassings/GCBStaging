<?php

/**
 * Class Smartcrawl_Compatibility
 *
 * Fixes third-party compatibility issues
 */
class Smartcrawl_Compatibility extends Smartcrawl_Base_Controller {
	/**
	 * Singleton instance
	 *
	 * @var Smartcrawl_Compatibility
	 */
	private static $_instance;

	/**
	 * Obtain instance without booting up
	 *
	 * @return Smartcrawl_Compatibility instance
	 */
	public static function get() {
		if ( empty( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	protected function init() {
		add_action( 'init', array( $this, 'load_divi_in_ajax' ), - 10 );
		add_action( 'init', array( $this, 'load_wp_bakery_shortcodes' ), - 10 );
		add_filter( 'wds-omitted-shortcodes', array( $this, 'avada_omitted_shortcodes' ) );
		add_filter( 'wds-omitted-shortcodes', array( $this, 'divi_omitted_shortcodes' ) );
		add_filter( 'wds-omitted-shortcodes', array( $this, 'wpbakery_omitted_shortcodes' ) );
		add_filter( 'wds-omitted-shortcodes', array( $this, 'swift_omitted_shortcodes' ) );
		add_filter( 'wds_before_sitemap_rebuild', array( $this, 'prevent_wpml_url_translation' ) );
		add_filter( 'wds_full_sitemap_items', array( $this, 'add_wpml_homepage_versions' ) );
		add_filter( 'wds_partial_sitemap_items', array( $this, 'add_wpml_homepage_versions_to_full' ), 10, 3 );
		add_filter( 'wds-report-admin-url', array( $this, 'ensure_defender_login_redirect' ) );
		// Disable defender login redirect because we are not entirely sure about its security implications
		//add_filter( 'wds-report-admin-url', array( $this, 'ensure_defender_login_redirect' ) );

		return true;
	}

	public function avada_omitted_shortcodes( $omitted ) {
		return array_merge( $omitted, array(
			'fusion_code',
			'fusion_imageframe',
			'fusion_slide',
			'fusion_syntax_highlighter',
		) );
	}

	public function divi_omitted_shortcodes( $omitted ) {
		return array_merge( $omitted, array(
			'et_pb_code',
			'et_pb_fullwidth_code',
		) );
	}

	public function wpbakery_omitted_shortcodes( $omitted ) {
		return array_merge( $omitted, array(
			'vc_raw_js',
			'vc_raw_html',
		) );
	}

	public function swift_omitted_shortcodes( $omitted ) {
		return array_merge( $omitted, array(
			'spb_raw_js',
			'spb_raw_html',
		) );
	}

	/**
	 * Divi doesn't usually load its shortcodes during ajax requests but we need these shortcodes in order to
	 * render an accurate preview.
	 *
	 * Force Divi to load during our requests.
	 */
	public function load_divi_in_ajax() {
		if ( $this->is_preview_request() ) {
			$_POST['et_load_builder_modules'] = '1';
		}
	}

	/**
	 * Force WPBakery to load its shortcodes so we can render an accurate preview
	 */
	public function load_wp_bakery_shortcodes() {
		if ( $this->is_preview_request() ) {
			$load_shortcodes_callback = array(
				'WPBMap',
				'addAllMappedShortcodes',
			);

			if ( is_callable( $load_shortcodes_callback ) ) {
				add_action( 'init', $load_shortcodes_callback );
			}
		}
	}

	public function add_wpml_homepage_versions_to_full( $items, $type, $page_number ) {
		$is_first_post_sitemap = $type === 'post' && $page_number === 1;
		if ( ! $is_first_post_sitemap ) {
			return $items;
		}

		return $this->add_wpml_homepage_versions( $items );
	}

	public function add_wpml_homepage_versions( $items ) {
		global $sitepress;
		$sitepress_available = ! empty( $sitepress )
		                       && method_exists( $sitepress, 'get_active_languages' )
		                       && method_exists( $sitepress, 'get_default_language' )
		                       && method_exists( $sitepress, 'convert_url' );

		if ( ! $sitepress_available ) {
			return $items;
		}

		// Remove the original home url
		array_shift( $items );

		// Add all homepage versions
		$languages = $sitepress->get_active_languages( false, true );
		foreach ( $languages as $language_code => $language ) {
			if ( $sitepress->get_default_language() === $language_code ) {
				continue;
			}

			$item_url = $sitepress->convert_url( home_url(), $language_code );
			array_unshift(
				$items,
				$this->get_sitemap_homepage_item( $item_url )
			);
		}

		array_unshift(
			$items,
			$this->get_sitemap_homepage_item( home_url( '/' ) )
		);

		return $items;
	}

	private function get_sitemap_homepage_item( $url ) {
		$item = new Smartcrawl_Sitemap_Item();
		return $item->set_location( $url )
		            ->set_priority( 1 )
		            ->set_change_frequency( Smartcrawl_Sitemap_Item::FREQ_DAILY );
	}

	/**
	 * WPML tries to 'translate' urls but in our context it leads to every URL getting converted to the default language.
	 *
	 * If the post ID of an Urdu post is passed to get_permalink, we expect to get the Urdu url in return but the conversion changes it to default language URL.
	 */
	public function prevent_wpml_url_translation() {
		global $sitepress;
		if ( empty( $sitepress ) ) {
			return;
		}

		// Get rid of all permalink modifications when we are building the sitemap.
		remove_all_filters( 'post_link' );
	}

	private function is_preview_request() {
		return is_admin()
		       && smartcrawl_is_switch_active( 'DOING_AJAX' )
		       && isset( $_POST['_wds_nonce'] )
		       && (
			       wp_verify_nonce( $_POST['_wds_nonce'], 'wds-metabox-nonce' )
			       || wp_verify_nonce( $_POST['_wds_nonce'], 'wds-onpage-nonce' )
		       );
	}

	public function ensure_defender_login_redirect( $url ) {
		if (
			is_user_logged_in()
			|| ! method_exists( '\WP_Defender\Module\Advanced_Tools\Component\Mask_Api', 'maybeAppendTicketToUrl' )
		) {
			return $url;
		}

		return \WP_Defender\Module\Advanced_Tools\Component\Mask_Api::maybeAppendTicketToUrl( $url );
	}
}