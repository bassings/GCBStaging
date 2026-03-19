<?php
/**
 * GCB Brutalist Theme Functions
 *
 * Editorial Brutalism meets automotive culture.
 * A bold FSE block theme featuring the "Neon Noir" design system.
 *
 * @package GCB_Brutalist
 */

// Security: Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SEO & GEO (Generative Engine Optimization) Filters
 *
 * Fix Yoast og:locale for Australian English.
 * WordPress site language is set to en_AU but Yoast falls back to en_US.
 */
add_filter( 'wpseo_locale', function () {
	return 'en_AU';
} );

// Also fix the OG locale meta tag (Yoast uses a separate filter).
add_filter( 'wpseo_og_locale', function () {
	return 'en_AU';
} );

/**
 * Enable Yoast SEO breadcrumbs for this theme.
 *
 * Required for the breadcrumb block to respect taxonomy settings
 * (e.g. showing categories in the breadcrumb trail for posts).
 */
add_action( 'after_setup_theme', function () {
	add_theme_support( 'yoast-seo-breadcrumbs' );

	// Bento card thumbnail: matches desktop display width (~400px) so srcset has a physical
	// file at the exact size the browser needs. Also ensures local dev (WP Studio, no Photon)
	// serves correctly-sized images.
	add_image_size( 'gcb-card', 400, 0, false );

	// CrUX-optimised thumbnail: covers hero + standard card at mobile 2.6x DPR (~1071px).
	// Physical file avoids Photon virtual resize cold-start on new article social shares.
	add_image_size( 'gcb-crux', 1024, 0, false );
}, 5 );

/**
 * Newsletter Preview Tool
 * 
 * Add ?newsletter_preview=1 to any URL to see post list
 * Add ?newsletter_preview=1&post_id=XXX to preview specific post
 * Only works for admins or localhost
 */
function gcb_newsletter_preview_init(): void {
	if ( isset( $_GET['newsletter_preview'] ) && $_GET['newsletter_preview'] === '1' ) {
		include get_template_directory() . '/newsletter-preview.php';
	}
}
add_action( 'template_redirect', 'gcb_newsletter_preview_init', 1 );

/**
 * Flush rewrite rules on theme activation
 *
 * Ensures custom rewrite rules (like /video/) are registered.
 */
function gcb_theme_activation() {
	// Register the video archive rewrite rule first
	gcb_register_video_archive();
	// Then flush rewrite rules
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'gcb_theme_activation' );

/**
 * Manual rewrite flush for development
 *
 * Access /?gcb_flush_rules=1 to flush rewrite rules.
 * Only works when logged in as admin or in test environment.
 */
function gcb_maybe_flush_rules() {
	if ( isset( $_GET['gcb_flush_rules'] ) && '1' === $_GET['gcb_flush_rules'] ) {
		// Only allow in admin or test environment
		if ( is_admin() || current_user_can( 'manage_options' ) || defined( 'GCB_TEST_KEY' ) ) {
			flush_rewrite_rules();
			if ( ! is_admin() ) {
				wp_safe_redirect( home_url( '/video/' ) );
				exit;
			}
		}
	}
}
add_action( 'init', 'gcb_maybe_flush_rules', 1 );

/**
 * Video Rail Orientation Configuration
 *
 * Controls aspect ratio for video rail pattern.
 *
 * Options:
 *   - 'landscape' (default): 16:9 aspect ratio (56.25% padding-bottom)
 *   - 'portrait': 9:16 aspect ratio (177.78% padding-bottom)
 *
 * @since 1.0.0
 */
if ( ! defined( 'GCB_VIDEO_RAIL_ORIENTATION' ) ) {
	define( 'GCB_VIDEO_RAIL_ORIENTATION', 'landscape' );
}

/**
 * Image Mode Configuration
 *
 * Controls whether images use color or brutalist grayscale aesthetic.
 *
 * Options:
 *   - 'color' (default): Full color images
 *   - 'grayscale': Brutalist grayscale with high contrast
 *
 * To enable brutalist grayscale mode:
 *   define( 'GCB_IMAGE_MODE', 'grayscale' );
 *
 * @since 1.0.0
 */
if ( ! defined( 'GCB_IMAGE_MODE' ) ) {
	define( 'GCB_IMAGE_MODE', 'color' );
}

/**
 * Output conditional CSS for image mode in the footer.
 *
 * When color mode is enabled, this overrides the grayscale filters in style.css
 * and inline template styles. Uses wp_footer to ensure it loads AFTER all other
 * CSS including inline styles in block templates (like search.html).
 */
function gcb_image_mode_css(): void {
	if ( is_admin() ) {
		return;
	}

	// Only output CSS if color mode is enabled (overrides grayscale everywhere).
	if ( defined( 'GCB_IMAGE_MODE' ) && 'color' === GCB_IMAGE_MODE ) {
		?>
		<style id="gcb-image-mode-override">
/* Color mode: Remove brutalist grayscale filters */
.wp-block-post-featured-image img,
.wp-block-image img,
.entry-content img,
.search-results-grid .wp-block-post-featured-image img,
.search-result-thumbnail img,
.wp-block-post-content img,
.wp-block-post-content .wp-block-image img,
.search-results-page .bento-item.gcb-bento-card .wp-block-post-featured-image img,
.gcb-bento-card__image,
.gcb-video-card img {
	filter: none !important;
}
		</style>
		<?php
	}
}
add_action( 'wp_footer', 'gcb_image_mode_css' );

/**
 * Add responsive video CSS for embedded videos
 *
 * Makes video embeds responsive on all screen sizes using 16:9 aspect ratio.
 */
function gcb_responsive_video_css(): void {
	// Only on frontend
	if ( is_admin() ) {
		return;
	}

	// Inline CSS to make videos responsive
	$css = '
		/* Responsive video embeds */
		.video-shortcode {
			position: relative;
			padding-bottom: 56.25%; /* 16:9 aspect ratio */
			height: 0;
			overflow: hidden;
			max-width: 100%;
		}
		.video-shortcode iframe {
			position: absolute;
			top: 0;
			left: 0;
			width: 100% !important;
			height: 100% !important;
			max-width: 100%;
		}
	';

	wp_add_inline_style( 'wp-block-library', $css );
}
add_action( 'wp_enqueue_scripts', 'gcb_responsive_video_css' );

/**
 * Enqueue theme stylesheet
 *
 * FSE block themes don't automatically enqueue style.css.
 * This ensures our custom pagination and other global styles are loaded.
 */
function gcb_enqueue_theme_styles(): void {
	wp_enqueue_style(
		'gcb-brutalist-style',
		get_stylesheet_uri(),
		array(),
		wp_get_theme()->get( 'Version' )
	);
}
add_action( 'wp_enqueue_scripts', 'gcb_enqueue_theme_styles' );

/**
 * Enqueue Lite YouTube Embed script
 *
 * Loads the custom element that provides lightweight YouTube embeds.
 * Only loads on pages with YouTube embeds to minimize HTTP requests.
 *
 * Expected LCP impact: 500-1500ms reduction by avoiding heavy iframe loading.
 */
function gcb_enqueue_lite_youtube(): void {
	// Only load on frontend
	if ( is_admin() ) {
		return;
	}

	wp_enqueue_script(
		'lite-youtube-embed',
		get_template_directory_uri() . '/assets/js/lite-youtube-embed.js',
		array(),
		'1.0.1',
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);
}
add_action( 'wp_enqueue_scripts', 'gcb_enqueue_lite_youtube' );

/**
 * Enqueue Lazy Spectra Gallery script
 *
 * Uses Intersection Observer to delay Spectra gallery initialization
 * until user scrolls near the gallery. Reduces TBT on initial load.
 */
function gcb_enqueue_lazy_spectra(): void {
	// Only load on frontend single posts (where galleries appear)
	if ( is_admin() || ! is_singular( 'post' ) ) {
		return;
	}

	wp_enqueue_script(
		'gcb-lazy-spectra',
		get_template_directory_uri() . '/assets/js/lazy-spectra.js',
		array(),
		wp_get_theme()->get( 'Version' ),
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);

	// Add inline CSS to hide galleries until loaded (prevents flash)
	wp_add_inline_style( 'gcb-brutalist-style', '
		/* Hide Spectra galleries until lazy-loaded */
		.wp-block-uagb-image-gallery:not(.spectra-lazy-loaded) .spectra-image-gallery__layout--carousel {
			opacity: 0;
		}
		.wp-block-uagb-image-gallery.spectra-lazy-loaded .spectra-image-gallery__layout--carousel {
			opacity: 1;
			transition: opacity 0.3s ease;
		}
	' );
}
add_action( 'wp_enqueue_scripts', 'gcb_enqueue_lazy_spectra' );

/**
 * Add resource hints for critical external resources
 *
 * Pre-establishes connections to reduce latency for:
 * - Google Fonts (typography)
 * - YouTube CDN (video thumbnails and embeds)
 * - WP.com Image CDN (production image delivery)
 *
 * Expected LCP impact: 200-500ms reduction.
 *
 * Uses WordPress native wp_resource_hints filter.
 *
 * @param array  $urls          URLs for resource hints.
 * @param string $relation_type The relation type.
 * @return array Modified URLs.
 */
function gcb_add_resource_hints( array $urls, string $relation_type ): array {
	if ( 'preconnect' === $relation_type ) {
		// Google Fonts
		$urls[] = array(
			'href'        => 'https://fonts.gstatic.com',
			'crossorigin' => 'anonymous',
		);

		// YouTube thumbnail CDN — no longer needed since thumbnails are cached
		// locally in the media library via GCB_YouTube_Thumbnail_Cache.
		// Re-enable if local caching is disabled.

		// Note: youtube-nocookie.com preconnect removed 2026-02-06
		// Lighthouse flagged it as unused — lite-youtube only loads iframe on click

		// WP.com Photon image CDN (critical for LCP)
		// Always preconnect — Jetpack Photon serves all images via i0/i1/i2.wp.com
		$urls[] = array(
			'href'        => 'https://i0.wp.com',
			'crossorigin' => 'anonymous',
		);
		$urls[] = array(
			'href'        => 'https://i1.wp.com',
			'crossorigin' => 'anonymous',
		);
		$urls[] = array(
			'href'        => 'https://i2.wp.com',
			'crossorigin' => 'anonymous',
		);
	}
	return $urls;
}
add_filter( 'wp_resource_hints', 'gcb_add_resource_hints', 10, 2 );

/**
 * Preload critical font files to prevent FOUT-induced CLS
 *
 * On WP.com production, fonts are served from fonts.wp.com (not fonts.gstatic.com).
 * We preload the exact Playfair Display woff2 files that WP.com serves to eliminate
 * the font loading delay from the critical render path.
 *
 * Updated: 2026-02-05 — removed gstatic Playfair preload (404 on production)
 * Updated: 2026-02-06 — added fonts.wp.com Playfair preloads (exact URLs from Lighthouse)
 */
function gcb_preload_critical_fonts(): void {
	if ( is_admin() ) {
		return;
	}
	// Preconnect to fonts.wp.com (serves Playfair Display on WP.com production)
	echo '<link rel="preconnect" href="https://fonts.wp.com" crossorigin="anonymous">' . "\n";
	
	// Playfair Display 400 (Regular) — critical for headlines/hero text
	// NOTE: URL must match the @font-face src that WP.com generates in wp-fonts-local
	echo '<link rel="preload" as="font" type="font/woff2" href="https://fonts.wp.com/s/playfairdisplay/v36/nuFvD-vYSZviVYUb_rj3ij__anPXJzDwcbmjWBN2PKdFvUDVZNLo_U2r.woff2" crossorigin="anonymous">' . "\n";
	
	// Playfair Display 700 (Bold) — used for logo and emphasis
	echo '<link rel="preload" as="font" type="font/woff2" href="https://fonts.wp.com/s/playfairdisplay/v36/nuFvD-vYSZviVYUb_rj3ij__anPXJzDwcbmjWBN2PKeiukDVZNLo_U2r.woff2" crossorigin="anonymous">' . "\n";
	
	// Space Mono 400 (nav, metadata) — works from gstatic
	echo '<link rel="preload" as="font" type="font/woff2" href="https://fonts.gstatic.com/s/spacemono/v13/i7dPIFZifjKcF5UAWdDRYEF8RQ.woff2" crossorigin="anonymous">' . "\n";
}
add_action( 'wp_head', 'gcb_preload_critical_fonts', 0 );

/**
 * Inline critical CSS for instant initial render
 *
 * Contains CSS variables, body base, skip-link, header, logo, and mobile menu toggle.
 * This eliminates render-blocking CSS for above-the-fold content.
 */
function gcb_inline_critical_css(): void {
	if ( is_admin() ) {
		return;
	}
	?>
	<style id="gcb-critical-css">
	/* CSS Custom Properties (Design Tokens) */
	:root {
		--wp--preset--color--void-black: #050505;
		--wp--preset--color--off-white: #FAFAFA;
		--wp--preset--color--highlight: #0084FF;
		--wp--preset--color--brutal-border: #333333;
		--wp--preset--color--brutal-grey: #AAAAAA;
		--wp--preset--font-family--playfair: 'Playfair Display', Georgia, serif;
		--wp--preset--font-family--mono: 'Space Mono', 'Courier New', monospace;
		--wp--preset--font-family--system-sans: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
	}
	body {
		margin: 0;
		background: var(--wp--preset--color--void-black);
		color: var(--wp--preset--color--off-white);
		font-family: var(--wp--preset--font-family--system-sans);
		-webkit-font-smoothing: antialiased;
	}
	.skip-link {
		position: absolute;
		top: -40px;
		left: 0;
		z-index: 1000;
		padding: 0.75rem 1.5rem;
		background-color: var(--wp--preset--color--highlight);
		color: var(--wp--preset--color--void-black);
		font-family: var(--wp--preset--font-family--mono);
		font-weight: 700;
		text-transform: uppercase;
		text-decoration: none;
	}
	.skip-link:focus { top: 0; }
	.site-header {
		position: sticky;
		top: 0;
		z-index: 100;
		background-color: var(--wp--preset--color--void-black);
		border-bottom: 2px solid var(--wp--preset--color--brutal-border);
	}
	.header-wrapper {
		max-width: 1200px;
		margin: 0 auto;
		padding: 1rem 1.5rem;
		display: flex;
		align-items: center;
		justify-content: space-between;
	}
	.site-logo a {
		display: inline-block;
		min-height: 44px;
		padding: 0.5rem 0;
		text-decoration: none;
		color: var(--wp--preset--color--off-white);
	}
	.logo-text {
		font-family: var(--wp--preset--font-family--playfair);
		font-size: 2rem;
		font-weight: 700;
		margin: 0;
	}
	.menu-toggle {
		display: flex;
		flex-direction: column;
		justify-content: center;
		align-items: center;
		background: transparent;
		border: none;
		padding: 0.75rem;
		cursor: pointer;
		min-height: 44px;
		min-width: 44px;
	}
	.hamburger-icon {
		display: flex;
		flex-direction: column;
		gap: 4px;
		width: 24px;
	}
	.hamburger-icon .line {
		display: block;
		width: 100%;
		height: 2px;
		background-color: var(--wp--preset--color--off-white);
	}
	.main-nav { display: none; }
	@media (min-width: 768px) {
		.main-nav { display: flex; gap: 2rem; }
		.menu-toggle { display: none; }
	}

	/* Bento Grid Fallback Layout */
	.gcb-bento-grid__container {
		display: grid !important;
		grid-template-columns: repeat(3, 1fr) !important;
		gap: 2rem !important;
		grid-auto-rows: 400px !important;
	}
	@media (max-width: 1024px) {
		.gcb-bento-grid__container {
			grid-template-columns: repeat(2, 1fr) !important;
		}
	}
	@media (max-width: 768px) {
		.gcb-bento-grid__container {
			grid-template-columns: 1fr !important;
			grid-auto-rows: minmax(300px, auto) !important;
		}
	}
	</style>
	<?php
}
add_action( 'wp_head', 'gcb_inline_critical_css', 1 );

/**
 * Defer non-critical CSS loading — DISABLED
 *
 * Jetpack Boost now handles CSS deferral on WP.com production using its own
 * media="not all" + onload technique. Having both the theme AND Jetpack Boost
 * defer the same stylesheet created broken nested noscript tags and messy HTML.
 *
 * Removed: 2026-02-09 — Jetpack Boost handles this; theme defer was conflicting.
 */

/**
 * Preload LCP hero image on homepage — v2 (Photon-aware)
 *
 * v1 was disabled (2026-02-05) because it preloaded the WordPress-native srcset
 * which didn't match Photon's rewritten URLs, causing double downloads.
 *
 * v2 fix: Preload ONLY the src URL (no imagesrcset), using the exact same
 * function (get_the_post_thumbnail_url) that bento-grid.php uses. Photon
 * rewrites both identically, so preload URL = img src URL. No double download.
 *
 * Uses the same transient cache as the bento grid pattern for zero extra DB queries.
 *
 * Added: 2026-03-05
 */
/**
/**
 * Preload the hero image with imagesrcset/imagesizes to match Jetpack's LCP
 * optimizer output. This avoids the duplicate download that occurred when we
 * preloaded a fixed size (1000x667) that didn't match what Jetpack's srcset
 * resolved to (900x506 on desktop).
 *
 * By using imagesrcset + imagesizes on the preload, the browser picks the
 * same URL from the preload as it would from the rendered img tag.
 */
function gcb_preload_hero_image(): void {
	if ( ! is_front_page() ) {
		return;
	}

	// Use the same cache key as bento-grid.php
	$cache_key  = 'gcb_bento_grid_' . date( 'Y-m-d-H' );
	$grid_posts = get_transient( $cache_key );

	if ( false === $grid_posts ) {
		$grid_posts = new WP_Query(
			array(
				'post_type'      => 'post',
				'posts_per_page' => 1,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);
	}

	if ( ! $grid_posts->have_posts() ) {
		return;
	}

	$grid_posts->the_post();
	$post_id      = get_the_ID();
	$thumbnail_id = get_post_thumbnail_id( $post_id );
	$hero_url     = get_the_post_thumbnail_url( $post_id, 'large' );
	wp_reset_postdata();

	if ( ! $hero_url || ! $thumbnail_id ) {
		return;
	}

	// Build a srcset that covers common desktop viewports.
	// Jetpack's LCP optimizer picks ~900px on desktop (≥1025px viewport).
	// We include a range so the preload matches regardless of viewport.
	$base_url = wp_get_attachment_url( $thumbnail_id );
	if ( ! $base_url ) {
		return;
	}

	// Use Photon-style resize URLs matching Jetpack's pattern.
	// IMPORTANT: Jetpack's LCP optimizer calculates resize dimensions based on
	// the CSS-rendered aspect ratio (16:9 from our aspect-ratio rule), NOT the
	// intrinsic image dimensions. We must use the same 16:9 ratio here.
	$photon_base = $hero_url; // Already a Photon URL on WP.com
	$srcset_entries = array();
	$widths = array( 354, 391, 419, 684, 708, 752, 782, 809, 838, 900, 1017, 1062, 1116, 1173, 1257 );
	$ratio  = 9 / 16; // Match CSS aspect-ratio: 16/9 on bento cards

	foreach ( $widths as $w ) {
		$h = (int) round( $w * $ratio );
		$srcset_entries[] = esc_url( $photon_base . '&resize=' . $w . '%2C' . $h . '&quality=75' ) . ' ' . $w . 'w';
	}

	// Match Jetpack's sizes attribute for the hero.
	$sizes = '(min-width: 1025px) 900px, (min-width: 835px) 752px, (min-width: 769px) 809px, (min-width: 441px) 747px, (min-width: 413px) 419px, (min-width: 394px) 391px, (min-width: 376px) 372px, (min-width: 361px) 354px, 339px';

	printf(
		'<link rel="preload" as="image" imagesrcset="%s" imagesizes="%s" fetchpriority="high">' . "\n",
		esc_attr( implode( ', ', $srcset_entries ) ),
		esc_attr( $sizes )
	);
}
add_action( 'wp_head', 'gcb_preload_hero_image', 2 );

/**
 * Preload LCP candidate image on single post pages — DISABLED
 *
 * Same issue as homepage preload v1: Jetpack Photon CDN rewrites srcset on
 * WP.com production, causing preload/img mismatch and double downloads.
 * Could be re-enabled with the same Photon-aware approach as the homepage.
 *
 * The featured image already gets fetchpriority="high" via
 * gcb_optimize_first_content_image(), which is sufficient.
 *
 * Removed: 2026-02-05 — see LCP-OPTIMIZATION-PLAN.md
 */

/**
 * Add fetchpriority="high" to first image in post content
 *
 * Optimizes LCP by telling the browser to prioritize loading the first content image.
 * Works in conjunction with preload to maximize LCP performance.
 *
 * Uses a static variable to ensure only the first image gets the attribute.
 */
function gcb_optimize_first_content_image( string $block_content, array $block ): string {
	static $first_image_processed = false;

	// Only process on single post pages
	if ( ! is_singular( 'post' ) ) {
		return $block_content;
	}

	// Only process image blocks
	if ( 'core/image' !== $block['blockName'] ) {
		return $block_content;
	}

	// Only process the first image
	if ( $first_image_processed ) {
		return $block_content;
	}

	// Mark as processed
	$first_image_processed = true;

	// Add fetchpriority="high" and loading="eager" to the img tag
	$block_content = preg_replace(
		'/<img\s/',
		'<img fetchpriority="high" loading="eager" ',
		$block_content,
		1
	);

	return $block_content;
}
add_filter( 'render_block', 'gcb_optimize_first_content_image', 10, 2 );

/**
 * Convert YouTube embeds to lite-youtube facade
 *
 * Transforms heavy YouTube iframe embeds into lightweight facades.
 * The iframe loads only when the user clicks the play button.
 *
 * Expected LCP impact: 500-1500ms reduction by deferring ~800KB+ per video.
 *
 * @param string $block_content Rendered block content.
 * @param array  $block         Block data.
 * @return string Modified block content.
 */
function gcb_convert_youtube_to_lite_embed( string $block_content, array $block ): string {
	// Only process YouTube embeds
	if ( 'core/embed' !== $block['blockName'] ) {
		return $block_content;
	}

	// Check if it's a YouTube embed
	if ( empty( $block['attrs']['providerNameSlug'] ) || 'youtube' !== $block['attrs']['providerNameSlug'] ) {
		return $block_content;
	}

	// Extract video ID from URL
	$url = $block['attrs']['url'] ?? '';
	if ( empty( $url ) ) {
		return $block_content;
	}

	$video_id = gcb_extract_youtube_id( $url );
	if ( ! $video_id ) {
		return $block_content;
	}

	// Get video title if available
	$title = '';
	if ( preg_match( '/<iframe[^>]+title="([^"]*)"/', $block_content, $matches ) ) {
		$title = esc_attr( $matches[1] );
	}

	// Build lite-youtube element
	$lite_youtube = sprintf(
		'<div class="wp-block-embed__wrapper"><lite-youtube videoid="%s" title="%s" params="rel=0&modestbranding=1"></lite-youtube></div>',
		esc_attr( $video_id ),
		$title ?: 'YouTube video'
	);

	// Replace the iframe with lite-youtube
	// Keep the outer wrapper structure for WordPress styling
	$block_content = preg_replace(
		'/<div class="wp-block-embed__wrapper">.*?<\/div>/s',
		$lite_youtube,
		$block_content
	);

	return $block_content;
}
add_filter( 'render_block', 'gcb_convert_youtube_to_lite_embed', 10, 2 );

/**
 * Extract YouTube video ID from URL
 *
 * Supports various YouTube URL formats:
 * - https://www.youtube.com/watch?v=VIDEO_ID
 * - https://youtu.be/VIDEO_ID
 * - https://www.youtube.com/embed/VIDEO_ID
 *
 * @param string $url YouTube URL.
 * @return string|null Video ID or null if not found.
 */
function gcb_extract_youtube_id( string $url ): ?string {
	// Parse URL
	$parsed = wp_parse_url( $url );
	if ( ! $parsed ) {
		return null;
	}

	// youtube.com/watch?v=VIDEO_ID
	if ( isset( $parsed['query'] ) ) {
		parse_str( $parsed['query'], $query_params );
		if ( isset( $query_params['v'] ) ) {
			return sanitize_text_field( $query_params['v'] );
		}
	}

	// youtu.be/VIDEO_ID or youtube.com/embed/VIDEO_ID
	if ( isset( $parsed['path'] ) ) {
		$path_parts = explode( '/', trim( $parsed['path'], '/' ) );
		$last_part  = end( $path_parts );
		if ( preg_match( '/^[a-zA-Z0-9_-]{11}$/', $last_part ) ) {
			return sanitize_text_field( $last_part );
		}
	}

	return null;
}

/**
 * Legacy Fusion YouTube shortcode handler
 *
 * Now that Fusion Builder is deactivated for performance, this provides a
 * lightweight replacement that converts legacy [fusion_youtube] shortcodes
 * to lite-youtube facades.
 *
 * @param array $atts Shortcode attributes.
 * @return string HTML output (lite-youtube element).
 */
function gcb_legacy_fusion_youtube_handler( array $atts ): string {
	$atts = shortcode_atts(
		array(
			'id'     => '',
			'width'  => '1200',
			'height' => '675',
		),
		$atts,
		'fusion_youtube'
	);

	$video_id = sanitize_text_field( $atts['id'] );

	if ( empty( $video_id ) ) {
		return '<p style="color: #CCFF00; border: 2px solid #CCFF00; padding: 1rem; background: #050505; font-family: monospace;">⚠️ YouTube video ID missing</p>';
	}

	// Render as lite-youtube facade (lightweight, loads iframe only on click)
	return sprintf(
		'<div class="legacy-youtube-wrapper" style="max-width: %spx; margin: 2rem auto;"><lite-youtube videoid="%s" title="YouTube video" params="rel=0&modestbranding=1"></lite-youtube></div>',
		absint( $atts['width'] ),
		esc_attr( $video_id )
	);
}
add_shortcode( 'fusion_youtube', 'gcb_legacy_fusion_youtube_handler' );

/**
 * Disable WordPress emoji detection script
 *
 * Removes wp-emoji-release.min.js (~5.6KB) which detects and replaces emoji
 * with images. Unnecessary for an automotive magazine — modern browsers
 * render emoji natively.
 *
 * Added: 2026-02-05 — see LCP-OPTIMIZATION-PLAN.md
 */
function gcb_disable_emojis(): void {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );

	// Remove the DNS prefetch for the emoji CDN
	add_filter(
		'wp_resource_hints',
		function ( $urls, $relation_type ) {
			if ( 'dns-prefetch' === $relation_type ) {
				$urls = array_filter(
					$urls,
					function ( $url ) {
						return ! is_string( $url ) || false === strpos( $url, 'https://s.w.org/images/core/emoji/' );
					}
				);
			}
			return $urls;
		},
		10,
		2
	);
}
add_action( 'init', 'gcb_disable_emojis' );

/**
 * Remove WP.com platform scripts (selective)
 *
 * devicepx-jetpack.js handles retina image swapping but is redundant when
 * using responsive images with srcset (which Jetpack Photon provides).
 *
 * NOTE: bilmur.min.js (RUM) is kept enabled for WP.com Stats functionality.
 * It adds ~200ms overhead but provides visitor analytics in WP Admin.
 *
 * Added: 2026-02-06 — based on Gemini deep research on WP.com LCP optimization
 * Updated: 2026-02-06 — re-enabled bilmur for WP.com Stats
 */
function gcb_remove_wpcom_rum_scripts(): void {
	// bilmur kept enabled for WP.com Stats
	
	// Remove devicepx-jetpack.js (retina image swapping - redundant with srcset)
	add_action( 'wp_enqueue_scripts', function() {
		wp_dequeue_script( 'devicepx' );
	}, 20 );
}
add_action( 'init', 'gcb_remove_wpcom_rum_scripts' );

/**
 * Conditional asset loading — remove single-post assets from homepage
 *
 * Jetpack loads Likes, Sharing, and Related Posts CSS/JS on every page,
 * but these features only appear on single post pages. Removing them
 * from the homepage reduces payload and main-thread work.
 *
 * Added: 2026-02-06 — granular per-page asset loading for LCP optimization
 */
function gcb_conditional_asset_loading_styles(): void {
	// Only remove on homepage/front page/archives (not single posts where these are needed)
	if ( is_front_page() || is_home() || is_archive() || is_search() ) {
		// Use wp_deregister_style (stronger than dequeue)
		wp_deregister_style( 'jetpack_likes' );
		wp_deregister_style( 'sharedaddy' );
		wp_deregister_style( 'sharing' );
		wp_deregister_style( 'jetpack_related-posts' );
		wp_deregister_style( 'social-logos' );
	}
}
add_action( 'wp_print_styles', 'gcb_conditional_asset_loading_styles', 100 );

function gcb_conditional_asset_loading_scripts(): void {
	// Only remove on homepage/front page/archives (not single posts where these are needed)
	if ( is_front_page() || is_home() || is_archive() || is_search() ) {
		// Remove Jetpack Likes queue handler JS
		// Try multiple approaches for WP.com compatibility
		wp_dequeue_script( 'jetpack_likes_queuehandler' );
		wp_deregister_script( 'jetpack_likes_queuehandler' );
	}
}
// Use multiple hooks to catch it wherever Jetpack adds it
add_action( 'wp_enqueue_scripts', 'gcb_conditional_asset_loading_scripts', 999 );
add_action( 'wp_print_scripts', 'gcb_conditional_asset_loading_scripts', 999 );
add_action( 'wp_footer', 'gcb_conditional_asset_loading_scripts', 1 );

/**
 * Dequeue Open Sans font on frontend
 *
 * WP.com loads Open Sans by default, but the GCB Brutalist design system only
 * uses Playfair Display, Space Mono, and system sans-serif. Removing Open Sans
 * saves a CSS request + font download (~2.8KB CSS + font files).
 *
 * Added: 2026-02-05 — see LCP-OPTIMIZATION-PLAN.md
 */
function gcb_dequeue_open_sans(): void {
	if ( is_admin() ) {
		return;
	}
	wp_dequeue_style( 'open-sans-css' );
	wp_deregister_style( 'open-sans-css' );
}
add_action( 'wp_enqueue_scripts', 'gcb_dequeue_open_sans', 100 );

/**
 * Delay Google Analytics (gtag.js) until user interaction
 *
 * Site Kit injects gtag.js as an async script in <head> which competes with the
 * LCP image for bandwidth (~151KB transferred). This filter replaces the eager
 * <script src="...gtag/js..."> with an inline snippet that only loads it after
 * the first user interaction (scroll, click, touch, keydown) or after 5 seconds,
 * whichever comes first.
 *
 * Impact: ~0.5-1s LCP improvement on mobile.
 * Trade-off: Loses analytics data from users who bounce within 5s without interacting (<5%).
 *
 * Added: 2026-03-05
 */
function gcb_delay_gtag_script( string $tag, string $handle, string $src ): string {
	if ( 'google_gtagjs' !== $handle ) {
		return $tag;
	}

	// Replace the eager script tag with a delayed loader
	$delayed = <<<SCRIPT
<script id="google_gtagjs-delayed">
(function() {
	var loaded = false;
	function loadGtag() {
		if (loaded) return;
		loaded = true;
		var s = document.createElement('script');
		s.src = '{$src}';
		s.async = true;
		document.head.appendChild(s);
	}
	var events = ['scroll', 'click', 'touchstart', 'keydown'];
	events.forEach(function(e) {
		window.addEventListener(e, loadGtag, {once: true, passive: true});
	});
	setTimeout(loadGtag, 5000);
})();
</script>
SCRIPT;

	return $delayed;
}
add_filter( 'script_loader_tag', 'gcb_delay_gtag_script', 10, 3 );

/**
 * Override WP.com font-display:fallback → swap
 *
 * WordPress.com's wp-fonts-local system forces font-display:fallback on all
 * @font-face rules, ignoring theme.json fontDisplay:"swap" declarations.
 * fallback gives only a ~100ms swap window which can cause invisible text
 * and delay LCP (element render delay).
 *
 * swap ensures text is immediately visible with fallback fonts, then swaps
 * to the web font once loaded — better for LCP and perceived performance.
 *
 * Added: 2026-03-08
 */
function gcb_force_font_display_swap( string $html ): string {
	return str_replace( 'font-display:fallback', 'font-display:swap', $html );
}

// Hook into the existing WebP output buffer (runs on template_redirect at priority 1)
// We add our font-display fix as a second output buffer callback that runs after WebP
add_action( 'template_redirect', function() {
	if ( is_admin() ) return;
	ob_start( 'gcb_force_font_display_swap' );
}, 2 );

/**
 * Google Fonts are now loaded via theme.json fontFace declarations.
 *
 * This provides better compatibility with WordPress.com hosting and avoids
 * issues with the fonts-api.wp.com proxy. Font files are loaded directly
 * from fonts.gstatic.com with font-display: swap for optimal performance.
 *
 * @see theme.json fontFamilies section for font definitions.
 */

/**
 * Add content_format taxonomy classes to body
 *
 * Enables CSS targeting of video/standard/gallery posts via body classes.
 * Integrates with GCB Content Intelligence plugin.
 *
 * @param array $classes Existing body classes.
 * @return array Modified body classes.
 */
function gcb_add_content_format_body_classes( array $classes ): array {
	if ( ! is_singular( 'post' ) ) {
		return $classes;
	}

	$post_id = get_the_ID();
	$format  = get_post_meta( $post_id, '_gcb_content_format', true );

	if ( ! empty( $format ) ) {
		$classes[] = 'content-format-' . sanitize_html_class( $format );
	}

	// Also add taxonomy term classes
	$terms = wp_get_object_terms( $post_id, 'content_format', array( 'fields' => 'slugs' ) );
	if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
		foreach ( $terms as $term_slug ) {
			$classes[] = 'tax-content_format-' . sanitize_html_class( $term_slug );
		}
	}

	return $classes;
}
add_filter( 'body_class', 'gcb_add_content_format_body_classes' );

/**
 * Convert Spectra Image Galleries to email-safe HTML for newsletters
 *
 * Jetpack Newsletter doesn't support JavaScript-based galleries (Slick carousel, etc.).
 * This filter converts rendered Spectra gallery HTML to simple tables for email.
 *
 * Works on RENDERED HTML (not block structure) because by the time content reaches
 * the newsletter filter, blocks have already been converted to HTML.
 *
 * @param string $content Post content (rendered HTML) being sent to email subscribers.
 * @return string Modified content with email-safe galleries.
 */
function gcb_convert_spectra_gallery_for_email( string $content ): string {
	// Check if content has Spectra gallery HTML (rendered class names)
	if ( strpos( $content, 'wp-block-uagb-image-gallery' ) === false &&
	     strpos( $content, 'spectra-image-gallery' ) === false ) {
		return $content;
	}

	// Match the Spectra gallery container - greedy match to the closing div
	// The structure is: <div class="wp-block-uagb-image-gallery...">..nested content..</div>
	// OR for carousel: <div class="spectra-image-gallery spectra-image-gallery__layout--carousel">
	// Use a more permissive pattern that captures everything between opening and final closing
	$pattern = '/<div[^>]*class="[^"]*wp-block-uagb-image-gallery[^"]*"[^>]*>(?:[^<]|<(?!\/div>))*(?:<div[^>]*>(?:[^<]|<(?!\/div>))*<\/div>)*[^<]*<\/div>/is';

	// Simpler approach: match from gallery container to the next major section
	// Since the gallery has deep nesting, extract all spectra images from content first
	// Match either wp-block-uagb-image-gallery OR spectra-image-gallery (carousel layout uses latter)
	if ( preg_match_all( '/<div[^>]*class="[^"]*(wp-block-uagb-image-gallery|spectra-image-gallery\s+spectra-image-gallery__layout)[^"]*"[^>]*>/i', $content, $gallery_starts, PREG_OFFSET_CAPTURE ) ) {
		// Process each gallery found
		foreach ( array_reverse( $gallery_starts[0] ) as $match ) {
			$start_pos = $match[1];
			
			// Find the end of this gallery block by counting div nesting
			$depth      = 1;
			$pos        = $start_pos + strlen( $match[0] );
			$len        = strlen( $content );
			$gallery_end = $len;
			
			while ( $pos < $len && $depth > 0 ) {
				$next_open  = strpos( $content, '<div', $pos );
				$next_close = strpos( $content, '</div>', $pos );
				
				if ( $next_close === false ) {
					break;
				}
				
				if ( $next_open !== false && $next_open < $next_close ) {
					$depth++;
					$pos = $next_open + 4;
				} else {
					$depth--;
					$pos = $next_close + 6;
					if ( $depth === 0 ) {
						$gallery_end = $next_close + 6;
					}
				}
			}
			
			// Extract gallery HTML
			$gallery_html = substr( $content, $start_pos, $gallery_end - $start_pos );
			
			// Convert to email-safe table
			$replacement = gcb_spectra_gallery_to_email_table( array( $gallery_html ) );
			
			// Replace in content
			$content = substr( $content, 0, $start_pos ) . $replacement . substr( $content, $gallery_end );
		}
	}

	// Strip out Spectra lightbox controls using div counting (same technique as gallery)
	// The lightbox has class 'spectra-image-gallery__control-lightbox' (often single quotes)
	if ( preg_match_all( '/<div[^>]*spectra-image-gallery__control-lightbox[^>]*>/i', $content, $lightbox_starts, PREG_OFFSET_CAPTURE ) ) {
		foreach ( array_reverse( $lightbox_starts[0] ) as $match ) {
			$start_pos = $match[1];
			$depth = 1;
			$pos = $start_pos + strlen( $match[0] );
			$len = strlen( $content );
			$end_pos = $len;
			
			while ( $pos < $len && $depth > 0 ) {
				$next_open  = strpos( $content, '<div', $pos );
				$next_close = strpos( $content, '</div>', $pos );
				
				if ( $next_close === false ) break;
				
				if ( $next_open !== false && $next_open < $next_close ) {
					$depth++;
					$pos = $next_open + 4;
				} else {
					$depth--;
					$pos = $next_close + 6;
					if ( $depth === 0 ) {
						$end_pos = $next_close + 6;
					}
				}
			}
			
			// Remove this lightbox div entirely
			$content = substr( $content, 0, $start_pos ) . substr( $content, $end_pos );
		}
	}
	
	// Also strip the close button that may appear outside the lightbox div
	$content = preg_replace(
		'/<button[^>]*spectra-image-gallery__control-lightbox--close[^>]*>.*?<\/button>/is',
		'',
		$content
	);

	return $content;
}

// Hook into multiple filters to catch newsletter content
add_filter( 'the_content_feed', 'gcb_convert_spectra_gallery_for_email', 5 );
add_filter( 'jetpack_newsletter_post_content', 'gcb_convert_spectra_gallery_for_email', 5 );
add_filter( 'render_block', 'gcb_maybe_convert_spectra_for_email', 10, 2 );

/**
 * Maybe convert Spectra gallery during block render (for Jetpack context)
 *
 * @param string $block_content The block content.
 * @param array  $block         The block data.
 * @return string Modified content.
 */
function gcb_maybe_convert_spectra_for_email( string $block_content, array $block ): string {
	// Only process in feed/email context
	if ( ! is_feed() && ! doing_filter( 'jetpack_newsletter_post_content' ) ) {
		return $block_content;
	}

	// Only process Spectra image gallery blocks
	if ( 'uagb/image-gallery' !== $block['blockName'] ) {
		return $block_content;
	}

	// Convert to email-safe HTML
	return gcb_spectra_gallery_to_email_table( array( $block_content ) );
}

/**
 * Convert Spectra gallery HTML to email-safe table
 *
 * Extracts images from the rendered gallery HTML and creates a simple table.
 *
 * @param array $matches Regex matches (full match at index 0).
 * @return string Email-safe HTML table.
 */
function gcb_spectra_gallery_to_email_table( array $matches ): string {
	$gallery_html = $matches[0];

	// Extract all img tags from the gallery (prioritize spectra thumbnail class)
	preg_match_all( '/<img[^>]*class="[^"]*spectra-image-gallery__media-thumbnail[^"]*"[^>]*>/i', $gallery_html, $img_matches );

	// Fallback: try any img tag if no spectra-specific images found
	if ( empty( $img_matches[0] ) ) {
		preg_match_all( '/<img[^>]+>/i', $gallery_html, $img_matches );
	}

	if ( empty( $img_matches[0] ) ) {
		// No images found, return empty or placeholder
		return '<p style="color: #AAAAAA; font-family: sans-serif; text-align: center;">[Gallery images - view on website]</p>';
	}

	$images = $img_matches[0];

	// Build email-safe HTML table
	$html = '<table role="presentation" cellspacing="0" cellpadding="8" border="0" width="100%" style="border-collapse: collapse; max-width: 600px; margin: 16px auto;">';

	// 2-column layout for email
	$columns     = 2;
	$count       = 0;
	$total       = count( $images );
	$is_odd      = ( $total % 2 !== 0 );

	foreach ( $images as $index => $img_tag ) {
		// Check if this is the last image and total is odd (needs centering)
		$is_last_odd = $is_odd && ( $index === $total - 1 );
		
		// Start new row
		if ( $count % $columns === 0 ) {
			if ( $count > 0 ) {
				$html .= '</tr>';
			}
			$html .= '<tr>';
		}

		// Extract src and alt from img tag
		preg_match( '/src=["\']([^"\']+)["\']/', $img_tag, $src_match );
		preg_match( '/alt=["\']([^"\']*)["\']/', $img_tag, $alt_match );

		$src = $src_match[1] ?? '';
		$alt = $alt_match[1] ?? '';

		// Skip if no src
		if ( empty( $src ) ) {
			$count++;
			continue;
		}

		// Clean up WP.com CDN URLs - get a reasonably sized image for email (280px wide, or 560px if centered)
		// Handle both encoded (%2C) and regular (,) resize params
		$img_width = $is_last_odd ? 560 : 280;
		$src = preg_replace( '/\?resize=\d+%2C\d+/', '?resize=' . $img_width . '%2C' . round( $img_width * 0.667 ), $src );
		$src = preg_replace( '/\?resize=\d+,\d+/', '?resize=' . $img_width . ',' . round( $img_width * 0.667 ), $src );
		// Ensure ssl=1 is present for HTTPS
		if ( strpos( $src, 'ssl=1' ) === false && strpos( $src, 'i0.wp.com' ) !== false ) {
			$src .= ( strpos( $src, '?' ) !== false ) ? '&ssl=1' : '?ssl=1';
		}

		// Last odd image spans both columns and is centered
		if ( $is_last_odd ) {
			$html .= '<td colspan="2" align="center" valign="top" style="padding: 8px;">';
			$html .= '<img src="' . esc_url( $src ) . '" alt="' . esc_attr( $alt ) . '" width="' . $img_width . '" style="max-width: 100%; height: auto; display: block; border: 1px solid #333333; margin: 0 auto;">';
		} else {
			$html .= '<td align="center" valign="top" style="padding: 8px; width: 50%;">';
			$html .= '<img src="' . esc_url( $src ) . '" alt="' . esc_attr( $alt ) . '" width="' . $img_width . '" style="max-width: 100%; height: auto; display: block; border: 1px solid #333333;">';
		}
		$html .= '</td>';

		$count++;
	}

	$html .= '</tr></table>';

	return $html;
}

/**
 * Convert lite-youtube elements to email-safe HTML for newsletters
 *
 * The lite-youtube custom element works great on websites but email clients
 * don't support custom HTML elements or JavaScript. This converts them to
 * simple clickable thumbnail images for email compatibility.
 *
 * @param string $content Post content being sent to email subscribers.
 * @return string Modified content with email-safe video thumbnails.
 */
function gcb_convert_lite_youtube_for_email( string $content ): string {
	// Check if content has lite-youtube elements
	if ( strpos( $content, '<lite-youtube' ) === false ) {
		return $content;
	}

	// Match lite-youtube elements and convert to email-safe thumbnails
	$pattern = '/<lite-youtube\s+videoid=["\']([^"\']+)["\'][^>]*(?:title=["\']([^"\']*)["\'])?[^>]*><\/lite-youtube>/i';

	$content = preg_replace_callback( $pattern, function( $matches ) {
		$video_id = $matches[1];
		$title    = ! empty( $matches[2] ) ? $matches[2] : 'Watch video';

		// YouTube thumbnail URL (maxresdefault for high quality)
		$thumbnail_url = 'https://i.ytimg.com/vi/' . esc_attr( $video_id ) . '/maxresdefault.jpg';
		$video_url     = 'https://www.youtube.com/watch?v=' . esc_attr( $video_id );

		// Build email-safe HTML table with centered thumbnail
		$html  = '<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="border-collapse: collapse; max-width: 600px; margin: 16px auto;">';
		$html .= '<tr><td align="center" style="padding: 0;">';
		$html .= '<a href="' . esc_url( $video_url ) . '" target="_blank" style="display: block; text-decoration: none;">';
		$html .= '<img src="' . esc_url( $thumbnail_url ) . '" alt="' . esc_attr( $title ) . '" width="600" style="width: 100%; max-width: 600px; height: auto; display: block; border: 2px solid #333333;">';
		$html .= '</a>';
		$html .= '</td></tr>';
		// Add "Click to watch" text below thumbnail
		$html .= '<tr><td align="center" style="padding: 8px 0 0 0;">';
		$html .= '<a href="' . esc_url( $video_url ) . '" target="_blank" style="font-family: monospace; font-size: 12px; color: #0084FF; text-decoration: none; text-transform: uppercase; letter-spacing: 0.05em;">▶ CLICK TO WATCH VIDEO</a>';
		$html .= '</td></tr>';
		$html .= '</table>';

		return $html;
	}, $content );

	// Also handle any wrapper divs around lite-youtube
	$wrapper_pattern = '/<div[^>]*class="[^"]*wp-block-embed__wrapper[^"]*"[^>]*>\s*(<table[^>]*>.*?<\/table>)\s*<\/div>/is';
	$content = preg_replace( $wrapper_pattern, '$1', $content );

	return $content;
}

// Hook into newsletter content filters
add_filter( 'the_content_feed', 'gcb_convert_lite_youtube_for_email', 6 );
add_filter( 'jetpack_newsletter_post_content', 'gcb_convert_lite_youtube_for_email', 6 );

/**
 * Register Video Rail block pattern
 *
 * Registers the PHP-generated video rail pattern for use in templates.
 */
function gcb_register_video_rail_pattern(): void {
	// Only register on frontend
	if ( is_admin() ) {
		return;
	}

	register_block_pattern(
		'gcb-brutalist/video-rail',
		array(
			'title'       => __( 'Video Rail', 'gcb-brutalist' ),
			'description' => __( 'Horizontal scrolling rail of video posts with Editorial Brutalism styling', 'gcb-brutalist' ),
			'categories'  => array( 'featured', 'gcb-content' ),
			'keywords'    => array( 'video', 'rail', 'horizontal', 'scroll', 'brutalism' ),
			'content'     => gcb_get_video_rail_content(),
		)
	);
}
add_action( 'init', 'gcb_register_video_rail_pattern' );

/**
 * Get video rail pattern content
 *
 * Generates the HTML for the video rail pattern by querying video posts.
 *
 * @return string Video rail HTML
 */
function gcb_get_video_rail_content(): string {
	ob_start();
	include get_template_directory() . '/patterns/video-rail.php';
	return ob_get_clean();
}

/**
 * Register Bento Grid block pattern
 *
 * Registers the PHP-generated bento grid pattern for use in templates.
 */
function gcb_register_bento_grid_pattern(): void {
	// Only register on frontend
	if ( is_admin() ) {
		return;
	}

	register_block_pattern(
		'gcb-brutalist/bento-grid',
		array(
			'title'       => __( 'Bento Grid', 'gcb-brutalist' ),
			'description' => __( 'Mixed layout grid combining video and standard posts with Editorial Brutalism styling', 'gcb-brutalist' ),
			'categories'  => array( 'featured', 'gcb-content' ),
			'keywords'    => array( 'bento', 'grid', 'mixed', 'layout', 'brutalism' ),
			'content'     => gcb_get_bento_grid_content(),
		)
	);
}
add_action( 'init', 'gcb_register_bento_grid_pattern' );

/**
 * Get bento grid pattern content
 *
 * Generates the HTML for the bento grid pattern by querying mixed posts.
 *
 * @return string Bento grid HTML
 */
function gcb_get_bento_grid_content(): string {
	ob_start();
	include get_template_directory() . '/patterns/bento-grid.php';
	return ob_get_clean();
}

/**
 * Register Hero Section block pattern
 *
 * Registers the PHP-generated hero section pattern for use in templates.
 */
function gcb_register_hero_section_pattern(): void {
	// Only register on frontend
	if ( is_admin() ) {
		return;
	}

	register_block_pattern(
		'gcb-brutalist/hero-section',
		array(
			'title'       => __( 'Hero Section', 'gcb-brutalist' ),
			'description' => __( 'Two-column hero section with feature and opinion cards using Editorial Brutalism styling', 'gcb-brutalist' ),
			'categories'  => array( 'featured', 'gcb-content' ),
			'keywords'    => array( 'hero', 'featured', 'opinion', 'brutalism', 'two-column' ),
			'content'     => gcb_get_hero_section_content(),
		)
	);
}
add_action( 'init', 'gcb_register_hero_section_pattern' );

/**
 * Get hero section pattern content
 *
 * Generates the HTML for the hero section pattern by querying latest posts.
 *
 * @return string Hero section HTML
 */
function gcb_get_hero_section_content(): string {
	ob_start();
	include get_template_directory() . '/patterns/hero-section.php';
	return ob_get_clean();
}

/**
 * Register Culture Grid block pattern
 *
 * Registers the PHP-generated culture grid pattern for use in templates.
 */
function gcb_register_culture_grid_pattern(): void {
	// Only register on frontend
	if ( is_admin() ) {
		return;
	}

	register_block_pattern(
		'gcb-brutalist/culture-grid',
		array(
			'title'       => __( 'Culture Grid', 'gcb-brutalist' ),
			'description' => __( '4-column responsive grid displaying text-only editorial content cards with Editorial Brutalism styling', 'gcb-brutalist' ),
			'categories'  => array( 'featured', 'gcb-content' ),
			'keywords'    => array( 'culture', 'grid', 'editorial', 'text-only', 'brutalism' ),
			'content'     => gcb_get_culture_grid_content(),
		)
	);
}
add_action( 'init', 'gcb_register_culture_grid_pattern' );

/**
 * Get culture grid pattern content
 *
 * Generates the HTML for the culture grid pattern by querying standard posts.
 *
 * @return string Culture grid HTML
 */
function gcb_get_culture_grid_content(): string {
	ob_start();
	include get_template_directory() . '/patterns/culture-grid.php';
	return ob_get_clean();
}

/**
 * Register Header Navigation block pattern
 *
 * Registers the PHP-generated header navigation pattern for use in templates.
 */
function gcb_register_header_navigation_pattern(): void {
	// Only register on frontend
	if ( is_admin() ) {
		return;
	}

	register_block_pattern(
		'gcb-brutalist/header-navigation',
		array(
			'title'       => __( 'Header Navigation', 'gcb-brutalist' ),
			'description' => __( 'Sticky header with desktop navigation and mobile slide-out menu using Editorial Brutalism styling', 'gcb-brutalist' ),
			'categories'  => array( 'header', 'gcb-content' ),
			'keywords'    => array( 'header', 'navigation', 'mobile', 'menu', 'sticky', 'brutalism' ),
			'content'     => gcb_get_header_navigation_content(),
		)
	);
}
add_action( 'init', 'gcb_register_header_navigation_pattern' );

/**
 * Get header navigation pattern content
 *
 * Generates the HTML for the header navigation pattern.
 *
 * @return string Header navigation HTML
 */
function gcb_get_header_navigation_content(): string {
	ob_start();
	include get_template_directory() . '/patterns/header-navigation.php';
	return ob_get_clean();
}

/**
 * Register Terminal Search Modal block pattern
 *
 * Registers the PHP-generated search modal pattern for use in templates.
 */
function gcb_register_search_terminal_pattern(): void {
	// Only register on frontend
	if ( is_admin() ) {
		return;
	}

	register_block_pattern(
		'gcb-brutalist/search-terminal',
		array(
			'title'       => __( 'Terminal Search Modal', 'gcb-brutalist' ),
			'description' => __( 'Full-screen terminal-style search modal with Editorial Brutalism styling', 'gcb-brutalist' ),
			'categories'  => array( 'search', 'gcb-content' ),
			'keywords'    => array( 'search', 'terminal', 'modal', 'brutalism' ),
			'content'     => gcb_get_search_terminal_content(),
		)
	);
}
add_action( 'init', 'gcb_register_search_terminal_pattern' );

/**
 * Get terminal search modal pattern content
 *
 * Generates the HTML for the terminal search modal pattern.
 *
 * @return string Terminal search modal HTML
 */
function gcb_get_search_terminal_content(): string {
	ob_start();
	include get_template_directory() . '/patterns/search-terminal.php';
	return ob_get_clean();
}

/**
 * Register Search Results Grid block pattern
 *
 * Registers the PHP-generated search results pattern for use in templates.
 */
function gcb_register_search_results_pattern(): void {
	// Only register on frontend
	if ( is_admin() ) {
		return;
	}

	register_block_pattern(
		'gcb-brutalist/search-results',
		array(
			'title'       => __( 'Search Results Grid', 'gcb-brutalist' ),
			'description' => __( '3x3 bento-style grid displaying search results with Editorial Brutalism styling', 'gcb-brutalist' ),
			'categories'  => array( 'search', 'gcb-content' ),
			'keywords'    => array( 'search', 'results', 'bento', 'grid', 'brutalism' ),
			'content'     => gcb_get_search_results_content(),
		)
	);
}
add_action( 'init', 'gcb_register_search_results_pattern' );

/**
 * Get search results pattern content
 *
 * Generates the HTML for the search results pattern by querying search term.
 *
 * @return string Search results HTML
 */
function gcb_get_search_results_content(): string {
	ob_start();
	include get_template_directory() . '/patterns/search-results.php';
	return ob_get_clean();
}

/**
 * Search Results Grid Shortcode
 *
 * Displays filtered search results in a bento-style grid.
 * Usage: [gcb_search_results]
 *
 * Note: We use a placeholder marker system to prevent wpautop from corrupting
 * the HTML output. The shortcode returns a placeholder, wpautop runs, then we
 * replace the placeholder with our clean HTML.
 */
function gcb_search_results_shortcode() {
	// Store the HTML in a global and return a placeholder to bypass wpautop
	global $gcb_search_results_html;
	$gcb_search_results_html = gcb_build_search_results_html();
	return '<!--GCB_SEARCH_RESULTS_PLACEHOLDER-->';
}

/**
 * Build the actual search results HTML
 *
 * @return string Clean HTML for search results grid
 */
function gcb_build_search_results_html() {
	// Get the search query
	$search_query = get_search_query();
	if ( empty( $search_query ) && isset( $_GET['s'] ) ) {
		$search_query = sanitize_text_field( wp_unslash( $_GET['s'] ) );
	}

	// Get current page for pagination (use custom parameter to avoid WordPress search issues)
	$paged = isset( $_GET['search_page'] ) ? max( 1, intval( $_GET['search_page'] ) ) : 1;

	// Build query args - search only in titles for more relevant results
	$args = array(
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => 9,
		'paged'          => $paged,
		'orderby'        => 'date',
		'order'          => 'DESC',
	);

	// Use custom title search for better relevance
	if ( ! empty( $search_query ) ) {
		// Instead of 's' parameter, use a custom meta query approach
		// We'll filter by title using posts_where filter
		add_filter( 'posts_where', function( $where ) use ( $search_query ) {
			global $wpdb;
			// Search only in post_title for more relevant results
			$where .= $wpdb->prepare(
				" AND {$wpdb->posts}.post_title LIKE %s",
				'%' . $wpdb->esc_like( $search_query ) . '%'
			);
			return $where;
		}, 10, 1 );
	}

	// Execute search query
	$search_results = new WP_Query( $args );

	// Remove the filter after query executes
	if ( ! empty( $search_query ) ) {
		remove_all_filters( 'posts_where' );
	}

	// Build HTML as string to prevent wpautop from adding <p> and <br> tags
	$html = '';

	if ( $search_results->have_posts() ) :
		$html .= '<div class="wp-block-query alignwide gcb-search-grid">';
		$html .= '<ul class="wp-block-post-template">';

		while ( $search_results->have_posts() ) :
			$search_results->the_post();
			$permalink = esc_url( get_permalink() );
			$title     = esc_html( get_the_title() );
			$excerpt   = esc_html( wp_trim_words( get_the_excerpt(), 55 ) );
			$date      = esc_html( get_the_date( 'M j, Y' ) );
			$datetime  = esc_attr( get_the_date( 'c' ) );

			// Get thumbnail HTML without wpautop interference
			$thumbnail_html = '';
			if ( has_post_thumbnail() ) {
				$thumbnail_html = '<a href="' . $permalink . '" style="display:block;flex-shrink:0;">';
				$thumbnail_html .= get_the_post_thumbnail(
					get_the_ID(),
					'medium_large',
					array(
						'loading'  => 'lazy',
						'decoding' => 'async',
						'style'    => 'width:100%;height:220px;object-fit:cover;display:block;border-bottom:2px solid var(--wp--preset--color--brutal-border);',
					)
				);
				$thumbnail_html .= '</a>';
			}

			$html .= '<li class="wp-block-post" style="margin:0;padding:0;">';
			$html .= '<div class="gcb-post-card" style="border:2px solid var(--wp--preset--color--brutal-border);background:var(--wp--preset--color--void-black);overflow:hidden;display:flex;flex-direction:column;height:100%;">';
			$html .= $thumbnail_html;
			$html .= '<h3 class="wp-block-post-title" style="font-family:var(--wp--preset--font-family--playfair);font-size:1.25rem;line-height:1.3;margin:0 0 0.75rem 0;padding:1.5rem 1.5rem 0 1.5rem;color:var(--wp--preset--color--off-white);">';
			$html .= '<a href="' . $permalink . '" style="color:inherit;text-decoration:none;">' . $title . '</a>';
			$html .= '</h3>';
			$html .= '<div class="wp-block-post-excerpt" style="flex:1 1 auto;font-family:var(--wp--preset--font-family--system-sans);font-size:0.875rem;line-height:1.5;color:var(--wp--preset--color--brutal-grey);margin:0;padding:0 1.5rem 0.75rem 1.5rem;">';
			$html .= '<p style="margin:0;">' . $excerpt . '</p>';
			$html .= '</div>';
			$html .= '<time class="wp-block-post-date" datetime="' . $datetime . '" style="margin-top:auto;flex-shrink:0;padding:0 1.5rem 1.5rem 1.5rem;font-family:var(--wp--preset--font-family--mono);font-size:0.75rem;color:var(--wp--preset--color--brutal-grey);display:flex;align-items:center;gap:0.75rem;">';
			$html .= $date;
			$html .= '<span style="padding:2px 8px;border:1px solid var(--wp--preset--color--brutal-border);text-transform:uppercase;">Article</span>';
			$html .= '</time>';
			$html .= '</div>';
			$html .= '</li>';
		endwhile;
		wp_reset_postdata();

		$html .= '</ul>';

		// Pagination
		$total_pages = $search_results->max_num_pages;
		if ( $total_pages > 1 ) {
			$html .= '<nav class="search-pagination" aria-label="Search results pagination">';

			// Previous link
			if ( $paged > 1 ) {
				$html .= '<a href="' . esc_url( add_query_arg( 'search_page', $paged - 1 ) ) . '" class="prev">← Previous</a>';
			}

			// Page numbers with ellipsis
			$show_dots_start = false;
			$show_dots_end   = false;

			for ( $i = 1; $i <= $total_pages; $i++ ) {
				$show_page = ( $i === 1 || $i === $total_pages || ( $i >= $paged - 2 && $i <= $paged + 2 ) );

				if ( $show_page ) {
					if ( $i === $paged ) {
						$html .= '<span class="current" aria-current="page">' . esc_html( $i ) . '</span>';
					} else {
						$html .= '<a href="' . esc_url( add_query_arg( 'search_page', $i ) ) . '">' . esc_html( $i ) . '</a>';
					}
					$show_dots_start = false;
					$show_dots_end   = false;
				} else {
					if ( $i < $paged && ! $show_dots_start ) {
						$html .= '<span class="dots">…</span>';
						$show_dots_start = true;
					} elseif ( $i > $paged && ! $show_dots_end ) {
						$html .= '<span class="dots">…</span>';
						$show_dots_end = true;
					}
				}
			}

			// Next link
			if ( $paged < $total_pages ) {
				$html .= '<a href="' . esc_url( add_query_arg( 'search_page', $paged + 1 ) ) . '" class="next">Next →</a>';
			}

			$html .= '</nav>';
		}

		$html .= '</div>';
	else :
		$html = '<div class="wp-block-query-no-results">';
		$html .= '<p class="search-no-results has-brutal-grey-color" style="padding:var(--wp--preset--spacing--50) 0;font-family:var(--wp--preset--font-family--mono);font-size:1.25rem;color:var(--wp--preset--color--brutal-grey);">No results found. Try a different search term.</p>';
		$html .= '</div>';
	endif;

	return $html;
}
add_shortcode( 'gcb_search_results', 'gcb_search_results_shortcode' );

/**
 * Replace search results placeholder with actual HTML in block output
 *
 * Block themes use render_block filter, not the_content, so we need to
 * intercept the shortcode block output to inject our clean HTML.
 *
 * @param string $block_content The block content.
 * @param array  $block         The block data.
 * @return string Content with placeholder replaced by search results HTML.
 */
function gcb_inject_search_results_html( $block_content, $block ) {
	global $gcb_search_results_html;

	if ( strpos( $block_content, '<!--GCB_SEARCH_RESULTS_PLACEHOLDER-->' ) !== false && ! empty( $gcb_search_results_html ) ) {
		// Clean up any <p> tags wpautop wrapped around our placeholder
		$block_content = preg_replace(
			'/<p>\s*<!--GCB_SEARCH_RESULTS_PLACEHOLDER-->\s*<\/p>/',
			$gcb_search_results_html,
			$block_content
		);
		// Also handle case without <p> tags
		$block_content = str_replace(
			'<!--GCB_SEARCH_RESULTS_PLACEHOLDER-->',
			$gcb_search_results_html,
			$block_content
		);
		// Clear the global to prevent reuse
		$gcb_search_results_html = '';
	}

	return $block_content;
}
add_filter( 'render_block', 'gcb_inject_search_results_html', 99, 2 );

/**
 * Shortcode: Category Children Grid
 * Displays child categories in a brutalist grid layout
 *
 * @return string HTML output for category children grid
 */
function gcb_category_children_shortcode() {
	// Get current category
	$current_category = get_queried_object();

	if ( ! $current_category || ! isset( $current_category->term_id ) ) {
		return '';
	}

	// Get child categories (only those with posts)
	$child_categories = get_terms(
		array(
			'taxonomy'   => 'category',
			'parent'     => $current_category->term_id,
			'hide_empty' => true,
			'orderby'    => 'name',
			'order'      => 'ASC',
		)
	);

	// Exit if no children
	if ( empty( $child_categories ) || is_wp_error( $child_categories ) ) {
		return '';
	}

	// Filter out invalid categories (empty names or zero count)
	$valid_categories = array_filter(
		$child_categories,
		function( $cat ) {
			return ! empty( $cat->name ) && $cat->count > 0;
		}
	);

	// Exit if no valid children after filtering
	if ( empty( $valid_categories ) ) {
		return '';
	}

	// Build HTML string without line breaks to prevent wpautop
	$html = '<div class="category-children-grid" style="margin-bottom: 4rem;">';

	// Section Header
	$html .= '<div style="border-bottom: 2px solid var(--wp--preset--color--highlight); padding-bottom: 1.5rem; margin-bottom: 2rem;">';
	$html .= '<h2 style="font-family: var(--wp--preset--font-family--playfair); font-size: 2rem; text-transform: uppercase; color: var(--wp--preset--color--off-white); margin: 0;">Browse by Brand</h2>';
	$html .= '</div>';

	// Brands Grid
	$html .= '<div class="brands-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem;">';

	foreach ( $valid_categories as $category ) {
		$category_link = get_term_link( $category );
		if ( is_wp_error( $category_link ) ) {
			continue;
		}

		$review_text = $category->count === 1 ? 'review' : 'reviews';

		$html .= '<a href="' . esc_url( $category_link ) . '" class="brand-card" style="display: block; padding: 1.5rem 1rem; border: 2px solid var(--wp--preset--color--brutal-border); text-decoration: none; transition: none; background: transparent;">';
		$html .= '<span style="display: block; font-family: var(--wp--preset--font-family--mono); font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--wp--preset--color--off-white); margin-bottom: 0.5rem;">' . esc_html( $category->name ) . '</span>';
		$html .= '<span style="display: block; font-family: var(--wp--preset--font-family--mono); font-size: 0.75rem; color: var(--wp--preset--color--brutal-grey);">' . esc_html( $category->count ) . ' ' . $review_text . '</span>';
		$html .= '</a>';
	}

	$html .= '</div>'; // .brands-grid

	// Inline styles
	$html .= '<style>';
	$html .= '.brand-card:hover, .brand-card:focus { border-color: var(--wp--preset--color--highlight) !important; background-color: rgba(204, 255, 0, 0.05) !important; outline: none; }';
	$html .= '.brand-card:focus-visible { outline: 2px solid var(--wp--preset--color--highlight); outline-offset: 2px; }';
	$html .= '@media (max-width: 768px) { .brands-grid { grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)) !important; } }';
	$html .= '@media (max-width: 480px) { .brands-grid { grid-template-columns: 1fr 1fr !important; } }';
	$html .= '</style>';

	$html .= '</div>'; // .category-children-grid

	return $html;
}
add_shortcode( 'gcb_category_children', 'gcb_category_children_shortcode' );

/**
 * Shortcode: Category Posts Display Control
 * Hides posts query on parent category pages that have children
 * Shows posts only on leaf category pages (brands with no subcategories)
 *
 * @return string CSS to hide posts or empty string
 */
function gcb_category_posts_shortcode() {
	// Get current category
	$current_category = get_queried_object();

	if ( ! $current_category || ! isset( $current_category->term_id ) ) {
		return '';
	}

	// Check if category has children
	$child_categories = get_terms(
		array(
			'taxonomy'   => 'category',
			'parent'     => $current_category->term_id,
			'hide_empty' => true,
			'fields'     => 'ids', // Only need count, not full objects
		)
	);

	// If category has children, hide the posts section
	if ( ! empty( $child_categories ) && ! is_wp_error( $child_categories ) ) {
		return '<style>.wp-block-query.alignwide { display: none !important; }</style>';
	}

	// No children, show posts (return empty string)
	return '';
}
add_shortcode( 'gcb_category_posts', 'gcb_category_posts_shortcode' );

/**
 * Year Selector Shortcode
 *
 * Displays a horizontal row of year buttons for archive navigation.
 * Used on date archive pages to allow users to browse posts by year.
 *
 * Features:
 * - Queries database for all years with published posts
 * - Highlights current year with highlight color background
 * - Links to WordPress year archive URLs (/YYYY/)
 * - WCAG 2.2 AA compliant (44px touch targets, focus indicators)
 *
 * Usage: [gcb_year_selector]
 *
 * @since 1.0.0
 * @return string HTML output for year selector
 */
function gcb_year_selector_shortcode(): string {
	global $wpdb;

	// Query all years with published posts
	$years = $wpdb->get_col(
		"SELECT DISTINCT YEAR(post_date) AS year
		FROM {$wpdb->posts}
		WHERE post_status = 'publish'
		AND post_type = 'post'
		ORDER BY year DESC"
	);

	if ( empty( $years ) ) {
		return '';
	}

	// Determine current year from archive context
	$current_year = is_year() ? get_query_var( 'year' ) : (int) date( 'Y' );

	$output = '<nav class="gcb-year-selector" aria-label="Browse posts by year">';
	$output .= '<ul class="gcb-year-selector__list">';

	foreach ( $years as $year ) {
		$year      = (int) $year;
		$is_active = ( $year === $current_year );
		$class     = 'gcb-year-selector__item' . ( $is_active ? ' gcb-year-selector__item--active' : '' );
		$aria      = $is_active ? ' aria-current="page"' : '';
		$url       = get_year_link( $year );

		$output .= sprintf(
			'<li class="%s"><a href="%s"%s>%d</a></li>',
			esc_attr( $class ),
			esc_url( $url ),
			$aria,
			$year
		);
	}

	$output .= '</ul>';
	$output .= '</nav>';

	return $output;
}
add_shortcode( 'gcb_year_selector', 'gcb_year_selector_shortcode' );

/**
 * Browse All Posts Link Shortcode
 *
 * Outputs a styled link to the current year's archive.
 * Used on the home page to provide navigation to older posts.
 *
 * Usage: [gcb_browse_all_link]
 *
 * @since 1.0.0
 * @return string HTML output for browse all posts link
 */
function gcb_browse_all_link_shortcode(): string {
	global $wpdb;

	// Get the most recent year with published posts
	$latest_year = $wpdb->get_var(
		"SELECT YEAR(post_date) AS year
		FROM {$wpdb->posts}
		WHERE post_status = 'publish'
		AND post_type = 'post'
		ORDER BY post_date DESC
		LIMIT 1"
	);

	// Fallback to current year if no posts found
	$target_year = $latest_year ? (int) $latest_year : (int) date( 'Y' );
	$archive_url = get_year_link( $target_year );

	return sprintf(
		'<a href="%s" class="gcb-browse-all-link" style="display: inline-flex; align-items: center; justify-content: center; min-height: 44px; padding: 0.75rem 2rem; font-family: var(--wp--preset--font-family--mono); font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.1em; text-decoration: none; border: 2px solid var(--wp--preset--color--brutal-border); background: var(--wp--preset--color--void-black); color: var(--wp--preset--color--off-white); transition: none;">BROWSE ALL POSTS<span style="margin-left: 0.5rem; color: var(--wp--preset--color--highlight);">&rarr;</span></a>
		<style>.gcb-browse-all-link:hover,.gcb-browse-all-link:focus{border-color: var(--wp--preset--color--highlight) !important;outline: 2px solid var(--wp--preset--color--highlight);outline-offset: 2px;}</style>',
		esc_url( $archive_url )
	);
}
add_shortcode( 'gcb_browse_all_link', 'gcb_browse_all_link_shortcode' );

/**
 * Set posts per page for year/date archives to 9
 *
 * Ensures a clean 3x3 grid layout on desktop.
 * Applies to both main query and Query Loop blocks with inherit:true.
 *
 * @param WP_Query $query The WordPress query object.
 */
function gcb_date_archive_posts_per_page( $query ): void {
	if ( is_admin() ) {
		return;
	}

	// Set 9 posts per page for year, month, and day archives
	if ( $query->is_date() ) {
		$query->set( 'posts_per_page', 9 );
	}
}
add_action( 'pre_get_posts', 'gcb_date_archive_posts_per_page' );

/**
 * Filter Query Loop block to use 9 posts per page on date archives
 *
 * The Query Loop block with inherit:true creates its own query.
 * This filter modifies the query vars before the block renders.
 *
 * @param array    $query_args  The query arguments.
 * @param WP_Block $block       The block instance.
 * @return array Modified query arguments.
 */
function gcb_query_loop_date_archive_posts( array $query_args, WP_Block $block ): array {
	// Only modify if we're on a date archive and the block inherits from query
	if ( is_date() && ! empty( $block->context['query']['inherit'] ) ) {
		$query_args['posts_per_page'] = 9;
	}
	return $query_args;
}
add_filter( 'query_loop_block_query_vars', 'gcb_query_loop_date_archive_posts', 10, 2 );

/**
 * Set 9 posts per page for category archives (3x3 grid)
 *
 * @param WP_Query $query The WordPress query object.
 */
function gcb_category_archive_posts_per_page( $query ): void {
	if ( is_admin() ) {
		return;
	}

	// Set 9 posts per page for category archives
	if ( $query->is_category() && $query->is_main_query() ) {
		$query->set( 'posts_per_page', 9 );
	}
}
add_action( 'pre_get_posts', 'gcb_category_archive_posts_per_page' );

/**
 * Filter Query Loop block to use 9 posts per page on category archives
 *
 * The Query Loop block with inherit:true creates its own query.
 * This filter modifies the query vars before the block renders.
 *
 * @param array    $query_args  The query arguments.
 * @param WP_Block $block       The block instance.
 * @return array Modified query arguments.
 */
function gcb_query_loop_category_archive_posts( array $query_args, WP_Block $block ): array {
	// Only modify if we're on a category archive and the block inherits from query
	if ( is_category() && ! empty( $block->context['query']['inherit'] ) ) {
		$query_args['posts_per_page'] = 9;
	}
	return $query_args;
}
add_filter( 'query_loop_block_query_vars', 'gcb_query_loop_category_archive_posts', 10, 2 );

/**
 * Force search results to order by date descending instead of relevance
 * Ensures most recent articles appear first on search results page
 * Applies to both main query and query blocks on search pages
 */
function gcb_force_search_order_by_date( $query ) {
	// Skip admin queries
	if ( is_admin() ) {
		return $query;
	}

	// Apply to main search query
	if ( $query->is_search() && $query->is_main_query() ) {
		$query->set( 'orderby', 'date' );
		$query->set( 'order', 'DESC' );
	}

	// Also apply to query blocks on search pages (not main query)
	// Query blocks have is_search() false, so check if we're on a search page
	if ( $query->is_search() && ! $query->is_main_query() ) {
		// This is a query block on a search page - ensure it uses search term
		$query->set( 'orderby', 'date' );
		$query->set( 'order', 'DESC' );
	}

	return $query;
}
add_action( 'pre_get_posts', 'gcb_force_search_order_by_date' );

/**
 * Register navigation menus
 *
 * Registers theme menu locations for use with WordPress nav menu system.
 * Primary menu displays in header (both desktop and mobile).
 */
function gcb_register_nav_menus(): void {
	register_nav_menus(
		array(
			'primary' => __( 'Primary Navigation', 'gcb-brutalist' ),
		)
	);
}
add_action( 'after_setup_theme', 'gcb_register_nav_menus' );

/**
 * Custom Navigation Walker for GCB Brutalist Theme
 *
 * Outputs flat <a> tags without <ul>/<li> wrappers to maintain
 * existing Editorial Brutalism styling and E2E test compatibility.
 *
 * Based on WordPress Walker_Nav_Menu with minimal output.
 */
class GCB_Nav_Walker extends Walker_Nav_Menu {

	/**
	 * Starts the list before the elements are added.
	 *
	 * @param string   $output Used to append additional content (passed by reference).
	 * @param int      $depth  Depth of menu item. Used for padding.
	 * @param stdClass $args   An object of wp_nav_menu() arguments.
	 */
	public function start_lvl( &$output, $depth = 0, $args = null ) {
		// Add submenu wrapper with proper classes
		$indent = str_repeat( "\t", $depth );
		$output .= "\n{$indent}<ul class=\"sub-menu\" aria-label=\"Submenu\">\n";
	}

	/**
	 * Ends the list of after the elements are added.
	 *
	 * @param string   $output Used to append additional content (passed by reference).
	 * @param int      $depth  Depth of menu item. Used for padding.
	 * @param stdClass $args   An object of wp_nav_menu() arguments.
	 */
	public function end_lvl( &$output, $depth = 0, $args = null ) {
		// Close submenu wrapper
		$indent = str_repeat( "\t", $depth );
		$output .= "{$indent}</ul>\n";
	}

	/**
	 * Starts the element output.
	 *
	 * Outputs <li> wrapper with <a> tag inside. Adds dropdown classes for items with children.
	 *
	 * @param string   $output Used to append additional content (passed by reference).
	 * @param WP_Post  $item   Menu item data object.
	 * @param int      $depth  Depth of menu item. Used for padding.
	 * @param stdClass $args   An object of wp_nav_menu() arguments.
	 * @param int      $id     Current item ID.
	 */
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

		// Build <li> classes
		$li_classes = array( 'menu-item' );

		// Add class for items with children
		if ( in_array( 'menu-item-has-children', $item->classes, true ) ) {
			$li_classes[] = 'has-dropdown';
		}

		// Add current page class if this is the active page
		if ( in_array( 'current-menu-item', $item->classes, true ) ) {
			$li_classes[] = 'current-menu-item';
		}

		// Add current parent class if this is a parent of the active page
		if ( in_array( 'current-menu-parent', $item->classes, true ) || in_array( 'current-menu-ancestor', $item->classes, true ) ) {
			$li_classes[] = 'current-menu-parent';
		}

		$li_class_attr = ' class="' . esc_attr( implode( ' ', $li_classes ) ) . '"';

		// Start <li>
		$output .= $indent . '<li' . $li_class_attr . '>';

		// Build link classes
		$link_classes = array();

		// Add link class based on context (desktop vs mobile)
		if ( isset( $args->link_class ) && ! empty( $args->link_class ) ) {
			$link_classes[] = $args->link_class;
		}

		// Add submenu class for depth
		if ( $depth > 0 ) {
			$link_classes[] = 'submenu-link';
		}

		$link_class_attr = ! empty( $link_classes ) ? ' class="' . esc_attr( implode( ' ', $link_classes ) ) . '"' : '';

		// Build link attributes
		$attributes  = '';
		$attributes .= ! empty( $item->attr_title ) ? ' title="' . esc_attr( $item->attr_title ) . '"' : '';
		$attributes .= ! empty( $item->target ) ? ' target="' . esc_attr( $item->target ) . '"' : '';
		$attributes .= ! empty( $item->xfn ) ? ' rel="' . esc_attr( $item->xfn ) . '"' : '';
		$attributes .= ! empty( $item->url ) ? ' href="' . esc_url( $item->url ) . '"' : '';

		// Add ARIA for dropdown toggle
		if ( in_array( 'menu-item-has-children', $item->classes, true ) ) {
			$attributes .= ' aria-haspopup="true" aria-expanded="false"';
		}

		// Output link
		$output .= '<a' . $link_class_attr . $attributes . '>';
		$output .= esc_html( $item->title );

		// Add dropdown indicator for items with children
		if ( in_array( 'menu-item-has-children', $item->classes, true ) ) {
			$output .= '<span class="dropdown-indicator" aria-hidden="true">▼</span>';
		}

		$output .= '</a>';
	}

	/**
	 * Ends the element output.
	 *
	 * @param string   $output Used to append additional content (passed by reference).
	 * @param WP_Post  $item   Menu item data object.
	 * @param int      $depth  Depth of menu item. Used for padding.
	 * @param stdClass $args   An object of wp_nav_menu() arguments.
	 */
	public function end_el( &$output, $item, $depth = 0, $args = null ) {
		// Close <li>
		$output .= "</li>\n";
	}
}

/**
 * Shortcode: Video Archive Grid
 *
 * Displays all videos from the GCB YouTube channel in a grid layout.
 * Used on the /video/ archive page.
 *
 * @return string HTML output for video archive grid.
 */
function gcb_video_archive_shortcode() {
	// Load YouTube fetcher
	$fetcher_path = get_template_directory() . '/../../plugins/gcb-content-intelligence/includes/class-gcb-youtube-channel-fetcher.php';

	if ( ! file_exists( $fetcher_path ) ) {
		return '<p style="color: var(--wp--preset--color--brutal-grey); font-family: var(--wp--preset--font-family--mono);">Video archive is currently unavailable.</p>';
	}

	require_once $fetcher_path;

	// Get videos from YouTube API (limited to 50 to avoid memory issues)
	$videos = GCB_YouTube_Channel_Fetcher::get_all_videos( 50 );

	if ( empty( $videos ) ) {
		return '<p style="color: var(--wp--preset--color--brutal-grey); font-family: var(--wp--preset--font-family--mono);">No videos available at this time.</p>';
	}

	ob_start();
	?>
	<style>
		.gcb-video-archive {
			display: grid;
			grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
			gap: 1.5rem;
		}

		@media (min-width: 768px) {
			.gcb-video-archive {
				grid-template-columns: repeat(2, 1fr);
			}
		}

		@media (min-width: 1024px) {
			.gcb-video-archive {
				grid-template-columns: repeat(3, 1fr);
			}
		}

		@media (min-width: 1440px) {
			.gcb-video-archive {
				grid-template-columns: repeat(4, 1fr);
			}
		}

		.gcb-video-archive__card {
			border: 1px solid var(--wp--preset--color--brutal-border);
			background: var(--wp--preset--color--void-black);
			transition: none;
		}

		.gcb-video-archive__card:hover,
		.gcb-video-archive__card:focus-within {
			border-color: var(--wp--preset--color--highlight) !important;
		}

		.gcb-video-archive__card a {
			display: block;
			text-decoration: none;
		}

		.gcb-video-archive__card a:focus {
			outline: 2px solid var(--wp--preset--color--highlight);
			outline-offset: 2px;
		}

		.gcb-video-archive__aspect {
			position: relative;
			width: 100%;
			padding-bottom: 56.25%; /* 16:9 aspect ratio */
			overflow: hidden;
		}

		.gcb-video-archive__aspect img {
			position: absolute;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			object-fit: cover;
		}

		.gcb-video-archive__overlay {
			position: absolute;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			background-color: var(--wp--preset--color--void-black);
			opacity: 0.3;
		}

		.gcb-video-archive__play {
			position: absolute;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			display: flex;
			align-items: center;
			justify-content: center;
		}

		.gcb-video-archive__play svg {
			width: 64px;
			height: 64px;
			color: var(--wp--preset--color--highlight);
			filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.5));
		}

		.gcb-video-archive__content {
			padding: 1rem;
		}

		.gcb-video-archive__title {
			font-family: var(--wp--preset--font-family--playfair);
			font-size: 1.125rem;
			font-weight: bold;
			line-height: 1.3;
			color: var(--wp--preset--color--off-white);
			margin: 0 0 0.5rem 0;
		}

		.gcb-video-archive__meta {
			font-family: var(--wp--preset--font-family--mono);
			font-size: 0.75rem;
			color: var(--wp--preset--color--brutal-grey);
			text-transform: uppercase;
			letter-spacing: 0.05em;
			margin: 0;
		}
	</style>

	<div class="gcb-video-archive">
		<?php foreach ( $videos as $video ) :
			$video_id    = $video['video_id'];
			$title       = $video['title'];
			$duration    = $video['duration'];
			$view_count  = $video['view_count'];
			$thumbnail   = $video['thumbnail'];
			$youtube_url = "https://www.youtube.com/watch?v={$video_id}";

			// Format duration (using the same helper from video-rail.php)
			$formatted_duration = '';
			if ( ! empty( $duration ) ) {
				preg_match( '/PT(?:(\d+)H)?(?:(\d+)M)?(?:(\d+)S)?/', $duration, $matches );
				$hours   = isset( $matches[1] ) ? intval( $matches[1] ) : 0;
				$minutes = isset( $matches[2] ) ? intval( $matches[2] ) : 0;
				$seconds = isset( $matches[3] ) ? intval( $matches[3] ) : 0;
				if ( $hours > 0 ) {
					$formatted_duration = sprintf( '%d:%02d:%02d', $hours, $minutes, $seconds );
				} else {
					$formatted_duration = sprintf( '%d:%02d', $minutes, $seconds );
				}
			}

			// Format view count
			$formatted_views = '';
			if ( ! empty( $view_count ) ) {
				$count = intval( $view_count );
				if ( $count >= 1000000 ) {
					$formatted_views = round( $count / 1000000, 1 ) . 'M';
				} elseif ( $count >= 1000 ) {
					$formatted_views = round( $count / 1000 ) . 'K';
				} else {
					$formatted_views = number_format( $count );
				}
			}
			?>
			<article class="gcb-video-archive__card video-archive-item">
				<a href="<?php echo esc_url( $youtube_url ); ?>"
				   target="_blank"
				   rel="noopener noreferrer"
				   aria-label="Watch <?php echo esc_attr( $title ); ?> on YouTube">

					<div class="gcb-video-archive__aspect">
						<?php if ( $thumbnail ) : ?>
							<img src="<?php echo esc_url( $thumbnail ); ?>"
							     alt="<?php echo esc_attr( $title ); ?>"
							     loading="lazy"
							     width="480"
							     height="360" />
						<?php endif; ?>
						<div class="gcb-video-archive__overlay"></div>
						<div class="gcb-video-archive__play">
							<svg class="video-play-button" viewBox="0 0 100 100" fill="currentColor" role="img" aria-hidden="true">
								<polygon points="30,20 30,80 80,50" />
							</svg>
						</div>
					</div>

					<div class="gcb-video-archive__content">
						<h3 class="gcb-video-archive__title">
							<?php echo esc_html( $title ); ?>
						</h3>
						<p class="gcb-video-archive__meta">
							<?php if ( $formatted_duration ) : ?>
								<?php echo esc_html( $formatted_duration ); ?>
							<?php endif; ?>
							<?php if ( $formatted_duration && $formatted_views ) : ?>
								<span> • </span>
							<?php endif; ?>
							<?php if ( $formatted_views ) : ?>
								<?php echo esc_html( $formatted_views ); ?> Views
							<?php endif; ?>
						</p>
					</div>
				</a>
			</article>
		<?php endforeach; ?>
	</div>
	<?php

	return ob_get_clean();
}
add_shortcode( 'gcb_video_archive', 'gcb_video_archive_shortcode' );

/**
 * Register video archive page and rewrite rules
 *
 * Creates /video/ endpoint for the video archive page.
 */
function gcb_register_video_archive() {
	// Add rewrite rule for /video/
	add_rewrite_rule(
		'^video/?$',
		'index.php?gcb_video_archive=1',
		'top'
	);

	// Register query var
	add_filter( 'query_vars', function( $vars ) {
		$vars[] = 'gcb_video_archive';
		return $vars;
	} );
}
add_action( 'init', 'gcb_register_video_archive' );

/**
 * Prevent canonical redirect for video archive
 *
 * WordPress tries to redirect /video/ to similar post slugs.
 * This prevents that redirect for our custom endpoint.
 */
function gcb_prevent_video_archive_redirect( $redirect_url, $requested_url ) {
	// Check if this is our video archive URL
	$path = trim( wp_parse_url( $requested_url, PHP_URL_PATH ), '/' );
	if ( 'video' === $path ) {
		return false; // Prevent redirect
	}
	return $redirect_url;
}
add_filter( 'redirect_canonical', 'gcb_prevent_video_archive_redirect', 10, 2 );

/**
 * Redirect old permalink structure to new structure
 *
 * OLD: /%postname%/%category%/ (e.g., /my-post/car-reviews/)
 * NEW: /%postname%/ (e.g., /my-post/)
 *
 * This handles the permalink structure migration without breaking existing links.
 * Uses 301 (permanent redirect) for SEO preservation.
 *
 * IMPORTANT: This runs on 'parse_request' hook (before WordPress routing) to
 * intercept old URLs before WordPress treats them as 404s.
 *
 * @since 1.0.0
 */
function gcb_redirect_old_permalink_structure( $wp ): void {
	// Only run on frontend for non-admin requests
	if ( is_admin() || defined( 'DOING_AJAX' ) || defined( 'DOING_CRON' ) ) {
		return;
	}

	// Get the current request path
	$request_path = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
	if ( empty( $request_path ) ) {
		return;
	}

	// Parse the URL path (remove query string and sanitize)
	$path = wp_parse_url( $request_path, PHP_URL_PATH );
	$path = trim( $path, '/' );

	// Skip if empty or core WordPress path
	if ( empty( $path ) ||
	     strpos( $path, 'wp-admin' ) === 0 ||
	     strpos( $path, 'wp-json' ) === 0 ||
	     strpos( $path, 'wp-login.php' ) === 0 ||
	     strpos( $path, 'wp-content' ) === 0 ) {
		return;
	}

	// Split path into segments
	$segments = explode( '/', $path );

	// Old structure has 2+ segments: [post-slug, category-slug, ...] or [post-slug, parent-cat, child-cat]
	// New structure has 1 segment: [post-slug]
	if ( count( $segments ) < 2 ) {
		return; // Not the old permalink pattern
	}

	// First segment is the post slug
	$post_slug = sanitize_title( $segments[0] );

	// Remaining segments are category hierarchy (parent/child categories)
	$category_segments = array_slice( $segments, 1 );

	// Direct database query to find post by slug (works regardless of permalink structure)
	global $wpdb;
	$post_id = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT ID FROM {$wpdb->posts} WHERE post_name = %s AND post_type = 'post' AND post_status = 'publish' LIMIT 1",
			$post_slug
		)
	);

	// If post doesn't exist, return (let WordPress handle 404)
	if ( ! $post_id ) {
		return;
	}

	// Get all categories for this post
	$post_categories = wp_get_post_categories( $post_id, array( 'fields' => 'all' ) );
	if ( empty( $post_categories ) ) {
		return;
	}

	// Check if ANY of the category segments match the post's categories
	// This handles both flat and hierarchical category structures
	$is_valid_old_url = false;

	foreach ( $category_segments as $category_slug ) {
		$category_slug = sanitize_title( $category_slug );

		foreach ( $post_categories as $cat ) {
			if ( $cat->slug === $category_slug ) {
				$is_valid_old_url = true;
				break 2; // Break out of both loops
			}
		}
	}

	// If this matches the old structure, redirect to new structure
	if ( $is_valid_old_url ) {
		$new_url = home_url( '/' . $post_slug . '/' );

		// Preserve query string if present
		$query_string = isset( $_SERVER['QUERY_STRING'] ) ? wp_unslash( $_SERVER['QUERY_STRING'] ) : '';
		if ( ! empty( $query_string ) ) {
			$new_url .= '?' . sanitize_text_field( $query_string );
		}

		// 301 permanent redirect for SEO
		wp_safe_redirect( $new_url, 301 );
		exit;
	}
}
add_action( 'parse_request', 'gcb_redirect_old_permalink_structure', 1 );

/**
 * Render video archive page
 *
 * When /video/ is accessed, render the video archive page.
 * Uses template_redirect to output directly for FSE compatibility.
 */
function gcb_render_video_archive() {
	// Check both query var and direct URL match
	$is_video_archive = get_query_var( 'gcb_video_archive' );

	// Fallback: Direct URL check if rewrite rules aren't flushed
	if ( ! $is_video_archive ) {
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '';
		$path = trim( wp_parse_url( $request_uri, PHP_URL_PATH ), '/' );
		if ( 'video' === $path ) {
			$is_video_archive = true;
		}
	}

	if ( ! $is_video_archive ) {
		return;
	}

	// Set proper headers
	status_header( 200 );
	header( 'Content-Type: text/html; charset=utf-8' );

	// Get header and footer template parts
	$header = '';
	$footer = '';

	// Use block_template_part() if available (WordPress 5.9+)
	if ( function_exists( 'block_template_part' ) ) {
		ob_start();
		block_template_part( 'header' );
		$header = ob_get_clean();

		ob_start();
		block_template_part( 'footer' );
		$footer = ob_get_clean();
	}

	// Get the video archive content
	$video_content = gcb_video_archive_shortcode();

	// Output the full page
	?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'video-archive-page' ); ?>>
<?php wp_body_open(); ?>

<?php echo $header; ?>

<main id="main-content" class="wp-block-group" style="margin-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--30);padding-left:var(--wp--preset--spacing--30);padding-bottom:var(--wp--preset--spacing--60)">

	<!-- Archive Header -->
	<div class="wp-block-group" style="border-bottom-color:var(--wp--preset--color--highlight);border-bottom-width:2px;border-bottom-style:solid;margin-bottom:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--30)">
		<h1 class="wp-block-heading has-off-white-color has-text-color" style="font-family:var(--wp--preset--font-family--playfair);font-size:3rem;text-transform:uppercase;color:var(--wp--preset--color--off-white);">ALL VIDEOS</h1>
		<p class="has-brutal-grey-color has-text-color" style="font-family:var(--wp--preset--font-family--system);font-size:1.125rem;margin-top:var(--wp--preset--spacing--20);color:var(--wp--preset--color--brutal-grey);">Watch the latest automotive content from Gay Car Boys on YouTube.</p>
	</div>

	<!-- Video Grid -->
	<?php echo $video_content; ?>

</main>

<?php echo $footer; ?>

<?php wp_footer(); ?>
</body>
</html>
	<?php
	exit;
}
add_action( 'template_redirect', 'gcb_render_video_archive' );

/**
 * Set page title for video archive
 */
function gcb_video_archive_title( $title ) {
	if ( get_query_var( 'gcb_video_archive' ) ) {
		return 'All Videos - Gay Car Boys';
	}
	return $title;
}
add_filter( 'pre_get_document_title', 'gcb_video_archive_title' );

/**
 * Fallback function for primary menu
 *
 * Displays default menu items if no menu is assigned.
 * Maintains backward compatibility with static menu.
 *
 * @param array $args Menu arguments.
 */
function gcb_primary_menu_fallback( $args ) {
	// Extract link class from args
	$link_class = isset( $args['link_class'] ) ? $args['link_class'] : 'nav-link';

	// Default menu items
	$menu_items = array(
		array(
			'url'   => home_url( '/car-reviews/' ),
			'title' => 'Car Reviews',
		),
		array(
			'url'   => home_url( '/car-news/' ),
			'title' => 'Car News',
		),
		array(
			'url'   => home_url( '/electric-cars/' ),
			'title' => 'Electric Cars',
		),
		array(
			'url'   => home_url( '/brands/' ),
			'title' => 'Brands',
		),
	);

	// Output links
	foreach ( $menu_items as $item ) {
		printf(
			'<a href="%s" class="%s">%s</a>',
			esc_url( $item['url'] ),
			esc_attr( $link_class ),
			esc_html( $item['title'] )
		);
	}
}

/**
 * Inject author box into the_content before Jetpack sharing/related posts.
 *
 * Jetpack sharing runs at priority 19, related posts at 40.
 * We inject at priority 9 so the author box appears right after article text
 * but before sharing buttons, related stories, and subscribe form.
 */
add_filter( 'the_content', function ( $content ) {
	if ( ! is_single() || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}

	$author_id   = get_the_author_meta( 'ID' );
	$avatar      = get_avatar( $author_id, 80, '', '', array( 'class' => 'gcb-author-avatar' ) );
	$name        = get_the_author();
	$author_url  = get_author_posts_url( $author_id );
	$bio         = get_the_author_meta( 'description' );

	$author_box = '
	<div class="gcb-author-box-injected" style="border:2px solid var(--wp--preset--color--brutal-border, #333);padding:1.5rem;margin-top:3rem;display:flex;gap:1.5rem;align-items:flex-start;">
		<div style="flex-shrink:0;">' . $avatar . '</div>
		<div style="display:flex;flex-direction:column;gap:0.5rem;">
			<span style="font-family:var(--wp--preset--font-family--mono, monospace);font-size:0.625rem;text-transform:uppercase;letter-spacing:0.1em;color:var(--wp--preset--color--highlight, #35c5f4);">Written by</span>
			<a href="' . esc_url( $author_url ) . '" style="font-family:var(--wp--preset--font-family--playfair, serif);font-size:1.25rem;font-weight:700;color:var(--wp--preset--color--off-white, #f5f5f5);text-decoration:none;">' . esc_html( $name ) . '</a>
			<p style="font-family:var(--wp--preset--font-family--system-sans, sans-serif);font-size:0.875rem;line-height:1.5;color:var(--wp--preset--color--brutal-grey, #888);margin:0;">' . esc_html( $bio ) . '</p>
		</div>
	</div>';

	return $content . $author_box;
}, 9 );

/**
 * Inject category breadcrumbs at the top of single post content.
 *
 * Runs at priority 1 on the_content — before the author box (9) and
 * Jetpack sharing (19) / related posts (40).
 */
add_filter( 'the_content', function ( $content ) {
	if ( ! is_single() || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}

	$sep = ' » ';
	$crumbs = '<a href="' . esc_url( home_url( '/' ) ) . '">Home</a>';

	// Try Yoast primary category first, fall back to first category.
	$primary_term_id = 0;
	if ( class_exists( 'WPSEO_Primary_Term' ) ) {
		$primary = new \WPSEO_Primary_Term( 'category', get_the_ID() );
		$primary_term_id = $primary->get_primary_term();
	}

	if ( ! $primary_term_id ) {
		$cats = get_the_category();
		if ( ! empty( $cats ) ) {
			// Pick the deepest (most specific) category — avoids showing
			// an unrelated parent like "Mercedes Benz News" on a Jaecoo post.
			$best = $cats[0];
			foreach ( $cats as $cat ) {
				if ( count( get_ancestors( $cat->term_id, 'category' ) ) >
				     count( get_ancestors( $best->term_id, 'category' ) ) ) {
					$best = $cat;
				}
			}
			$primary_term_id = $best->term_id;
		}
	}

	if ( $primary_term_id ) {
		$ancestors = array_reverse( get_ancestors( $primary_term_id, 'category' ) );
		foreach ( $ancestors as $anc_id ) {
			$anc = get_term( $anc_id, 'category' );
			if ( $anc && ! is_wp_error( $anc ) ) {
				$crumbs .= $sep . '<a href="' . esc_url( get_term_link( $anc ) ) . '">' . esc_html( $anc->name ) . '</a>';
			}
		}
		$term = get_term( $primary_term_id, 'category' );
		if ( $term && ! is_wp_error( $term ) ) {
			$crumbs .= $sep . '<a href="' . esc_url( get_term_link( $term ) ) . '">' . esc_html( $term->name ) . '</a>';
		}
	}

	$crumbs .= $sep . '<span class="breadcrumb_last" aria-current="page">' . esc_html( get_the_title() ) . '</span>';

	$breadcrumb_html = '<nav class="gcb-breadcrumbs" aria-label="Breadcrumb">' . $crumbs . '</nav>';

	return $breadcrumb_html . $content;
}, 1 );

/**
 * Add Review schema (JSON-LD) to car review articles.
 *
 * Automatically detects review posts by checking if any assigned category
 * slug contains "review". Outputs a Review schema with the car name parsed
 * from the title and a generic "recommended" rating.
 *
 * Note: This adds a basic Review schema without a numeric rating.
 * For star ratings in Google results, Alan would need to add explicit
 * ratings per article (future enhancement via custom field or block).
 */
add_action( 'wp_head', function () {
	if ( ! is_single() ) {
		return;
	}

	// Check if this post is in a review category.
	$categories = get_the_category();
	$is_review  = false;
	foreach ( $categories as $cat ) {
		if ( strpos( $cat->slug, 'review' ) !== false ) {
			$is_review = true;
			break;
		}
	}

	if ( ! $is_review ) {
		return;
	}

	$schema = array(
		'@context'      => 'https://schema.org',
		'@type'         => 'Review',
		'name'          => get_the_title(),
		'author'        => array(
			'@type' => 'Person',
			'name'  => get_the_author(),
		),
		'datePublished' => get_the_date( 'c' ),
		'publisher'     => array(
			'@type' => 'Organization',
			'name'  => 'Gay Car Boys',
			'url'   => home_url( '/' ),
		),
		'itemReviewed'  => array(
			'@type' => 'Car',
			'name'  => get_the_title(),
		),
		'url'           => get_permalink(),
	);

	// If post has a featured image, include it.
	$thumb = get_the_post_thumbnail_url( get_the_ID(), 'large' );
	if ( $thumb ) {
		$schema['image'] = $thumb;
	}

	echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}, 1 );

/**
 * Serve ai.txt at /ai.txt
 *
 * An emerging standard that tells AI crawlers about the site.
 * Served from the theme directory.
 */
add_action( 'init', function () {
	add_rewrite_rule( '^ai\.txt$', 'index.php?gcb_ai_txt=1', 'top' );
} );

add_filter( 'query_vars', function ( $vars ) {
	$vars[] = 'gcb_ai_txt';
	return $vars;
} );

add_action( 'template_redirect', function () {
	if ( get_query_var( 'gcb_ai_txt' ) ) {
		$file = get_template_directory() . '/ai.txt';
		if ( file_exists( $file ) ) {
			header( 'Content-Type: text/plain; charset=utf-8' );
			header( 'Cache-Control: public, max-age=86400' );
			readfile( $file );
			exit;
		}
	}
} );




/**
 * Downsize YouTube thumbnails to reduce payload
 *
 * Replaces hqdefault.jpg (480x360, ~30-45KB) with mqdefault.jpg (320x180, ~10-15KB)
 * for contexts where the image is displayed at ≤320px (e.g. video rail cards).
 * For full-width contexts (video archive), keeps hqdefault.
 *
 * Photon CDN was tested but doesn't re-encode third-party images to WebP or
 * improve cache headers — YouTube's 2h TTL passes through unchanged.
 * We already preconnect to i.ytimg.com so the connection cost is minimal.
 *
 * Added: 2026-03-08
 *
 * @param string $url YouTube thumbnail URL
 * @param string $context 'rail' for small cards, 'archive' for full cards
 * @return string Optimised thumbnail URL
 */
function gcb_optimize_youtube_thumbnail( string $url, string $context = 'rail' ): string {
	if ( 'rail' === $context && strpos( $url, 'hqdefault' ) !== false ) {
		return str_replace( 'hqdefault', 'mqdefault', $url );
	}
	return $url;
}

/**
 * Generate a CrUX-aligned srcset with Photon resize URLs
 *
 * WordPress's wp_get_attachment_image_srcset() only includes physical thumbnails
 * (300w, 768w, 1200w). The hero image gets Jetpack LCP enrichment (18+ entries)
 * but standard bento cards don't, causing a gap: sizes="400px" picks 768w because
 * there's no ~400w entry in the srcset.
 *
 * This function generates srcset entries at widths that match real-world browser
 * picks for CrUX devices, using Photon's on-the-fly resize capability.
 *
 * @param int    $attachment_id  The attachment post ID.
 * @param array  $target_widths  Array of pixel widths to include in srcset.
 * @return string srcset attribute value, or empty string on failure.
 */
function gcb_photon_srcset( int $attachment_id, array $target_widths = array( 300, 400, 768, 1024, 1200 ) ): string {
	$src = wp_get_attachment_image_url( $attachment_id, 'full' );
	if ( ! $src ) {
		return '';
	}

	$meta = wp_get_attachment_metadata( $attachment_id );
	if ( empty( $meta['width'] ) || empty( $meta['height'] ) ) {
		return '';
	}

	$orig_w = (int) $meta['width'];
	$orig_h = (int) $meta['height'];
	$ratio  = $orig_h / $orig_w;

	// Strip any existing Photon query params from the source URL
	$base_url = preg_replace( '/\?.*$/', '', $src );

	// If the URL is already Photon-wrapped, use it as-is; otherwise wrap it
	if ( strpos( $base_url, 'i0.wp.com' ) === false &&
	     strpos( $base_url, 'i1.wp.com' ) === false &&
	     strpos( $base_url, 'i2.wp.com' ) === false ) {
		// Wrap in Photon
		$clean = preg_replace( '#^https?://#', '', $base_url );
		$base_url = 'https://i0.wp.com/' . $clean;
	}

	$entries = array();
	foreach ( $target_widths as $w ) {
		if ( $w > $orig_w ) {
			// Don't upscale — include the original as the max entry
			$h = $orig_h;
			$w = $orig_w;
			$entries[ $w ] = sprintf( '%s?resize=%d%%2C%d&ssl=1 %dw', $base_url, $w, $h, $w );
			break;
		}
		$h = (int) round( $w * $ratio );
		$entries[ $w ] = sprintf( '%s?resize=%d%%2C%d&ssl=1 %dw', $base_url, $w, $h, $w );
	}

	return implode( ', ', $entries );
}

/**
 * GCB WebP Safety Net v2
 *
 * Serves .webp files when .jpg/.png is requested.
 * If the exact thumbnail .webp doesn't exist, falls back to the full-size .webp
 * via Photon resize parameters.
 *
 * Three layers:
 * 1. wp_get_attachment_url — catches WP-generated attachment URLs
 * 2. wp_calculate_image_srcset — catches srcset thumbnail URLs
 * 3. Output buffer — catches everything else (Spectra, Photon, inline HTML)
 */

// Helper: Given an upload-relative path like 2026/02/image-300x200.jpg,
// check if the .webp exists. If not, try the full-size .webp and return
// a Photon-resized URL. Returns [new_url, found] or [original, false].
function gcb_webp_resolve( $url ) {
	static $upload_dir = null;
	if ( $upload_dir === null ) {
		$upload_dir = wp_upload_dir();
	}

	// Only process URLs pointing to our uploads
	if ( strpos( $url, 'wp-content/uploads/' ) === false ) {
		return $url;
	}

	// Strip Photon wrapper if present (i0.wp.com/domain/path)
	$clean_url = $url;
	$is_photon = false;
	if ( preg_match( '#https?://i[012]\.wp\.com/(.+)#', $url, $pm ) ) {
		$clean_url = 'https://' . $pm[1];
		$is_photon = true;
	}

	// Strip query params for file checking
	$url_no_query = preg_replace( '/\?.*$/', '', $clean_url );

	// Not a jpg/jpeg/png? Skip.
	if ( ! preg_match( '/\.(jpe?g|png)$/i', $url_no_query ) ) {
		return $url;
	}

	// Build the webp path
	$webp_url  = preg_replace( '/\.(jpe?g|png)$/i', '.webp', $url_no_query );
	$webp_path = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $webp_url );

	// Case 1: Exact .webp exists (full-size or thumbnail)
	if ( file_exists( $webp_path ) ) {
		return preg_replace( '/\.(jpe?g|png)/i', '.webp', $url );
	}

	// Case 2: Thumbnail doesn't exist as .webp — try full-size .webp + Photon resize
	if ( preg_match( '/(.+)-(\d+)x(\d+)\.(jpe?g|png)$/i', $url_no_query, $tm ) ) {
		$full_url  = $tm[1] . '.webp';
		$width     = $tm[2];
		$height    = $tm[3];
		$full_path = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $full_url );

		if ( file_exists( $full_path ) ) {
			// Return Photon URL that resizes the full-size webp on the fly
			$site_url = preg_replace( '#^https?://#', '', $upload_dir['baseurl'] );
			return "https://i0.wp.com/{$site_url}/" . basename( dirname( $full_url ) ) . '/../' .
			       substr( $full_url, strlen( $upload_dir['baseurl'] ) + 1 ) .
			       "?resize={$width}%2C{$height}&ssl=1";
		}
	}

	return $url;
}

// Simpler version of the Photon fallback for the output buffer
function gcb_webp_rewrite_upload_url( $full_match, $path_before_ext, $ext, $after_ext = '' ) {
	static $upload_dir = null;
	if ( $upload_dir === null ) {
		$upload_dir = wp_upload_dir();
	}

	// Try exact .webp
	$rel_path = $path_before_ext . '.webp';
	// Extract the uploads-relative portion
	if ( preg_match( '#wp-content/uploads/(.+)$#', $rel_path, $m ) ) {
		$disk_path = $upload_dir['basedir'] . '/' . $m[1];
		if ( file_exists( $disk_path ) ) {
			return $path_before_ext . '.webp' . $after_ext;
		}

		// Try stripping thumbnail dimensions to find full-size
		if ( preg_match( '#^(.+)-\d+x\d+$#', $path_before_ext, $tm ) ) {
			$full_rel = $tm[1] . '.webp';
			if ( preg_match( '#wp-content/uploads/(.+)$#', $full_rel, $fm ) ) {
				$full_disk = $upload_dir['basedir'] . '/' . $fm[1];
				if ( file_exists( $full_disk ) ) {
					// Rewrite to full-size .webp (Photon will handle resizing via query params)
					return $tm[1] . '.webp' . $after_ext;
				}
			}
		}
	}

	return $full_match;
}

// Layer 1: Filter attachment URLs
add_filter( 'wp_get_attachment_url', 'gcb_webp_attachment_url', 99 );
function gcb_webp_attachment_url( $url ) {
	$new = gcb_webp_resolve( $url );
	return $new;
}

// Layer 2: Filter srcset
add_filter( 'wp_calculate_image_srcset', 'gcb_webp_srcset', 99 );
function gcb_webp_srcset( $sources ) {
	if ( ! is_array( $sources ) ) return $sources;
	foreach ( $sources as $width => &$source ) {
		if ( isset( $source['url'] ) ) {
			$source['url'] = gcb_webp_resolve( $source['url'] );
		}
	}
	return $sources;
}

// Layer 3: Output buffer

add_action( 'template_redirect', 'gcb_webp_output_buffer_start', 1 );
function gcb_webp_output_buffer_start() {
	if ( is_admin() ) return;
	ob_start( 'gcb_webp_rewrite_html' );
}

function gcb_webp_rewrite_html( $html ) {
	// Rewrite upload URLs: both direct and through Photon
	$html = preg_replace_callback(
		'#((?:i[012]\.wp\.com/[^/]+/)?wp-content/uploads/\d{4}/\d{2}/[^\s"\'<>?]+)\.(jpe?g|png)(\?[^\s"\'<>]*)?#i',
		function ( $matches ) {
			$path   = $matches[1];
			$ext    = $matches[2];
			$query  = isset( $matches[3] ) ? $matches[3] : '';
			return gcb_webp_rewrite_upload_url( $matches[0], $path, $ext, $query );
		},
		$html
	);
	return $html;
}

/**
 * GCB Image Upload Optimisation
 *
 * 1. Cap uploaded images at 1200px (WordPress handles the downscale)
 * 2. Convert JPG/PNG uploads to WebP automatically (quality 82)
 */

// Cap max image dimensions to 1200px on upload
add_filter( 'big_image_size_threshold', function() {
	return 1200;
});

// Convert uploaded JPG/PNG to WebP
add_filter( 'wp_handle_upload', function( $upload ) {
	// Only process images
	if ( ! in_array( $upload['type'], [ 'image/jpeg', 'image/png' ], true ) ) {
		return $upload;
	}

	$file = $upload['file'];
	$info = @getimagesize( $file );
	if ( ! $info ) return $upload;

	// Memory guard — skip extremely large images (shouldn't happen with 1200px cap, but just in case)
	$w = $info[0];
	$h = $info[1];
	if ( $w > 8000 || $h > 8000 ) return $upload;

	// Load image with GD
	switch ( $info[2] ) {
		case IMAGETYPE_JPEG: $img = @imagecreatefromjpeg( $file ); break;
		case IMAGETYPE_PNG:  $img = @imagecreatefrompng( $file );  break;
		default: return $upload;
	}
	if ( ! $img ) return $upload;

	// Preserve alpha for PNGs
	if ( $info[2] === IMAGETYPE_PNG ) {
		imagepalettetotruecolor( $img );
		imagealphablending( $img, true );
		imagesavealpha( $img, true );
	}

	// Write WebP
	$webp_path = preg_replace( '/\.(jpe?g|png)$/i', '.webp', $file );
	if ( imagewebp( $img, $webp_path, 82 ) ) {
		chmod( $webp_path, 0644 );
		// Remove original, update upload array to point to WebP
		@unlink( $file );
		$upload['file'] = $webp_path;
		$upload['url']  = preg_replace( '/\.(jpe?g|png)$/i', '.webp', $upload['url'] );
		$upload['type'] = 'image/webp';
	}
	imagedestroy( $img );

	return $upload;
});

/**
 * Generate physical thumbnail files at upload time.
 *
 * Photon/Jetpack overrides the WP image editor to make all thumbnails "virtual"
 * (generated on-demand by the CDN). This is slow on first request.
 * We unhook Photon's editor override during metadata generation so WordPress
 * creates real thumbnail files using GD, then re-hook it.
 *
 * Only generates the sizes that actually matter for page load performance.
 * Rarely-used sizes (portrait, square, tiny) stay virtual.
 */
/**
 * Set Photon image quality to 75 (default is 80).
 *
 * This reduces hero image from 108KB to 71KB (-34%) with no visible
 * quality loss on WebP. On a throttled mobile connection, saves ~1s
 * of LCP load time.
 */
add_filter( 'jetpack_photon_pre_args', function( $args ) {
	if ( ! isset( $args['quality'] ) ) {
		$args['quality'] = 75;
	}
	return $args;
} );

add_filter( 'wp_generate_attachment_metadata', 'gcb_generate_physical_thumbnails', 5, 2 );
function gcb_generate_physical_thumbnails( $metadata, $attachment_id ) {
	// Only process WebP images
	if ( empty( $metadata['file'] ) || ! preg_match( '/\.webp$/i', $metadata['file'] ) ) {
		return $metadata;
	}

	$upload_dir = wp_upload_dir();
	$source     = trailingslashit( $upload_dir['basedir'] ) . $metadata['file'];

	if ( ! file_exists( $source ) ) {
		return $metadata;
	}

	$info = @getimagesize( $source );
	if ( ! $info ) return $metadata;

	$orig_w = $info[0];
	$orig_h = $info[1];
	$dir    = dirname( $source );

	// Sizes to generate physically — the ones that matter for page load
	$physical_sizes = array(
		'thumbnail'    => array( 'width' => 150, 'height' => 150, 'crop' => true ),
		'medium'       => array( 'width' => 300, 'height' => 300, 'crop' => false ),
		'gcb-card'     => array( 'width' => 400, 'height' => 0,   'crop' => false ), // Bento card desktop display size (~400px)
		'medium_large' => array( 'width' => 768, 'height' => 0,   'crop' => false ),
		'gcb-crux'     => array( 'width' => 1024, 'height' => 0,  'crop' => false ), // CrUX: covers hero + card at mobile 2.6x DPR (~1071px)
		'large'        => array( 'width' => 1200, 'height' => 1200, 'crop' => false ),
		'newspack-article-block-landscape-large' => array( 'width' => 1200, 'height' => 900, 'crop' => true ),
		'newspack-article-block-uncropped'       => array( 'width' => 1200, 'height' => 9999, 'crop' => false ),
	);

	$img = @imagecreatefromwebp( $source );
	if ( ! $img ) return $metadata;

	$base_name = pathinfo( $metadata['file'], PATHINFO_FILENAME );

	foreach ( $physical_sizes as $size_name => $size ) {
		$max_w = $size['width'];
		$max_h = $size['height'] ?: 9999;
		$crop  = $size['crop'];

		// Skip if original is smaller than this size
		if ( $orig_w <= $max_w && $orig_h <= $max_h && ! $crop ) {
			continue;
		}

		if ( $crop ) {
			// Crop to exact dimensions
			$ratio_w = $max_w / $orig_w;
			$ratio_h = $max_h / $orig_h;
			$ratio   = max( $ratio_w, $ratio_h );
			$crop_w  = (int) round( $max_w / $ratio );
			$crop_h  = (int) round( $max_h / $ratio );
			$src_x   = (int) round( ( $orig_w - $crop_w ) / 2 );
			$src_y   = (int) round( ( $orig_h - $crop_h ) / 2 );

			$thumb = imagecreatetruecolor( $max_w, $max_h );
			imagecopyresampled( $thumb, $img, 0, 0, $src_x, $src_y, $max_w, $max_h, $crop_w, $crop_h );
			$new_w = $max_w;
			$new_h = $max_h;
		} else {
			// Scale proportionally
			$ratio = min( $max_w / $orig_w, $max_h / $orig_h );
			if ( $ratio >= 1 ) continue; // Don't upscale
			$new_w = (int) round( $orig_w * $ratio );
			$new_h = (int) round( $orig_h * $ratio );

			$thumb = imagecreatetruecolor( $new_w, $new_h );
			imagecopyresampled( $thumb, $img, 0, 0, 0, 0, $new_w, $new_h, $orig_w, $orig_h );
		}

		$thumb_filename = $base_name . '-' . $new_w . 'x' . $new_h . '.webp';
		$thumb_path     = $dir . '/' . $thumb_filename;

		if ( imagewebp( $thumb, $thumb_path, 82 ) ) {
			chmod( $thumb_path, 0644 );
			$metadata['sizes'][ $size_name ] = array(
				'file'      => $thumb_filename,
				'width'     => $new_w,
				'height'    => $new_h,
				'mime-type' => 'image/webp',
			);
		}
		imagedestroy( $thumb );
	}

	imagedestroy( $img );
	return $metadata;
}
