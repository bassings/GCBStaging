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
 * Add resource hints for critical external resources
 *
 * Pre-establishes connection to Google Fonts to reduce font loading latency.
 * Uses WordPress native wp_resource_hints filter.
 *
 * @param array  $urls          URLs for resource hints.
 * @param string $relation_type The relation type.
 * @return array Modified URLs.
 */
function gcb_add_resource_hints( array $urls, string $relation_type ): array {
	if ( 'preconnect' === $relation_type ) {
		$urls[] = array(
			'href'        => 'https://fonts.gstatic.com',
			'crossorigin' => 'anonymous',
		);
	}
	return $urls;
}
add_filter( 'wp_resource_hints', 'gcb_add_resource_hints', 10, 2 );

/**
 * Preload critical font files to prevent FOUT-induced CLS
 *
 * Preloads Playfair Display 700 (logo, headings) and Space Mono 400 (nav, metadata)
 * which are critical for above-the-fold rendering.
 */
function gcb_preload_critical_fonts(): void {
	if ( is_admin() ) {
		return;
	}
	// Playfair Display 700 (logo, headings)
	echo '<link rel="preload" as="font" type="font/woff2" href="https://fonts.gstatic.com/s/playfairdisplay/v37/nuFvD-vYSZviVYUb_rj3ij__anPXJzDwcbmjWBN2PKd6unDXbtM.woff2" crossorigin="anonymous">' . "\n";
	// Space Mono 400 (nav, metadata)
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
	</style>
	<?php
}
add_action( 'wp_head', 'gcb_inline_critical_css', 1 );

/**
 * Defer non-critical CSS loading
 *
 * Uses media="print" + onload swap technique to defer the main stylesheet.
 * Critical CSS is already inlined via gcb_inline_critical_css().
 *
 * @param string $tag    The stylesheet tag.
 * @param string $handle The stylesheet handle.
 * @param string $href   The stylesheet URL.
 * @return string Modified tag.
 */
function gcb_defer_non_critical_css( string $tag, string $handle, string $href ): string {
	if ( 'gcb-brutalist-style' !== $handle || is_admin() ) {
		return $tag;
	}
	// Use media="print" + onload swap
	$tag = str_replace( "media='all'", "media='print' onload=\"this.media='all'\"", $tag );
	// Noscript fallback for browsers without JavaScript
	return $tag . '<noscript><link rel="stylesheet" href="' . esc_url( $href ) . '"></noscript>';
}
add_filter( 'style_loader_tag', 'gcb_defer_non_critical_css', 10, 3 );

/**
 * Preload LCP candidate image on homepage
 *
 * Preloads the hero post's featured image to improve LCP score.
 * Only active on front page/home to avoid unnecessary preloads.
 */
function gcb_preload_lcp_image(): void {
	if ( ! is_front_page() && ! is_home() ) {
		return;
	}
	$hero_post = get_posts(
		array(
			'post_type'      => 'post',
			'posts_per_page' => 1,
			'orderby'        => 'date',
			'order'          => 'DESC',
		)
	);
	if ( empty( $hero_post ) ) {
		return;
	}
	$thumbnail_id = get_post_thumbnail_id( $hero_post[0]->ID );
	if ( ! $thumbnail_id ) {
		return;
	}
	$image_src = wp_get_attachment_image_src( $thumbnail_id, 'large' );
	if ( ! $image_src ) {
		return;
	}
	$srcset = wp_get_attachment_image_srcset( $thumbnail_id, 'large' );
	echo '<link rel="preload" as="image" href="' . esc_url( $image_src[0] ) . '"';
	if ( $srcset ) {
		echo ' imagesrcset="' . esc_attr( $srcset ) . '" imagesizes="(max-width: 768px) 100vw, 66vw"';
	}
	echo ' fetchpriority="high">' . "\n";
}
add_action( 'wp_head', 'gcb_preload_lcp_image', 2 );

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
 * Jetpack Newsletter doesn't support JavaScript-based galleries (Isotope, Swiper, etc.).
 * This filter converts Spectra galleries to simple HTML tables for email compatibility.
 * Only runs when content is being sent to email subscribers.
 *
 * @param string $content Post content being sent to email subscribers.
 * @return string Modified content with email-safe galleries.
 */
function gcb_convert_spectra_gallery_for_email( string $content ): string {
	// Only run this for email/RSS context
	if ( ! is_feed() && ! ( defined( 'JETPACK_NEWSLETTER_EMAIL' ) && JETPACK_NEWSLETTER_EMAIL ) ) {
		return $content;
	}

	// Check if content has Spectra Image Gallery blocks
	if ( ! has_block( 'uagb/image-gallery', $content ) ) {
		return $content;
	}

	// Parse blocks from content
	$blocks = parse_blocks( $content );

	// Process blocks recursively to convert galleries
	$processed_blocks = gcb_process_blocks_for_email( $blocks );

	// Serialize blocks back to content
	return serialize_blocks( $processed_blocks );
}
add_filter( 'the_content_feed', 'gcb_convert_spectra_gallery_for_email', 10, 1 );
add_filter( 'jetpack_newsletter_post_content', 'gcb_convert_spectra_gallery_for_email', 10, 1 );

/**
 * Recursively process blocks to convert Spectra galleries
 *
 * Walks through block tree and converts uagb/image-gallery blocks to email-safe HTML.
 *
 * @param array $blocks Array of parsed blocks.
 * @return array Processed blocks.
 */
function gcb_process_blocks_for_email( array $blocks ): array {
	$processed = array();

	foreach ( $blocks as $block ) {
		// If this is a Spectra Image Gallery, convert it
		if ( 'uagb/image-gallery' === $block['blockName'] ) {
			$processed[] = gcb_convert_spectra_to_email_table( $block );
		} else {
			// Process inner blocks recursively
			if ( ! empty( $block['innerBlocks'] ) ) {
				$block['innerBlocks'] = gcb_process_blocks_for_email( $block['innerBlocks'] );
			}
			$processed[] = $block;
		}
	}

	return $processed;
}

/**
 * Convert Spectra gallery block to email-safe HTML table
 *
 * Email clients don't support JavaScript, CSS Grid, or Flexbox reliably.
 * This converts the gallery to a 2-column HTML table with inline styles.
 *
 * @param array $block Spectra gallery block data.
 * @return array HTML block with email-safe gallery.
 */
function gcb_convert_spectra_to_email_table( array $block ): array {
	$attrs         = $block['attrs'] ?? array();
	$media_gallery = $attrs['mediaGallery'] ?? array();

	if ( empty( $media_gallery ) ) {
		return $block; // Return original if no images
	}

	// Build email-safe HTML table (works in all email clients including Outlook)
	$html = '<table role="presentation" cellspacing="0" cellpadding="10" border="0" width="100%" style="border-collapse: collapse; max-width: 600px; margin: 0 auto;">';
	$html .= '<tr>';

	// Use 2-column layout for email (mobile-friendly)
	$columns = 2;
	$count   = 0;

	foreach ( $media_gallery as $image ) {
		// Start new row after every $columns images
		if ( $count > 0 && 0 === $count % $columns ) {
			$html .= '</tr><tr>';
		}

		// Get image URL and alt text (use medium size for faster email loading)
		$image_url = '';
		if ( ! empty( $image['sizes']['medium']['url'] ) ) {
			$image_url = $image['sizes']['medium']['url'];
		} elseif ( ! empty( $image['url'] ) ) {
			$image_url = $image['url'];
		}

		$alt_text = $image['alt'] ?? '';
		$caption  = $image['caption'] ?? '';

		if ( empty( $image_url ) ) {
			$count++;
			continue;
		}

		// Email-safe table cell with inline styles
		$html .= '<td align="center" valign="top" style="padding: 10px;">';
		$html .= '<img src="' . esc_url( $image_url ) . '" alt="' . esc_attr( $alt_text ) . '" width="280" style="max-width: 100%; height: auto; display: block; border: 1px solid #333333;">';

		// Add caption if it exists (brutalist theme styling)
		if ( ! empty( $caption ) ) {
			$html .= '<p style="margin: 8px 0 0; font-size: 14px; color: #AAAAAA; font-family: \'Space Mono\', \'Courier New\', monospace;">' . wp_kses_post( $caption ) . '</p>';
		}

		$html .= '</td>';
		$count++;
	}

	// Fill remaining cells in the last row to prevent layout issues
	$remaining = $columns - ( $count % $columns );
	if ( $remaining < $columns ) {
		for ( $i = 0; $i < $remaining; $i++ ) {
			$html .= '<td style="padding: 10px;"></td>';
		}
	}

	$html .= '</tr>';
	$html .= '</table>';

	// Return as HTML block (core/html) which email clients handle well
	return array(
		'blockName'    => 'core/html',
		'attrs'        => array(),
		'innerBlocks'  => array(),
		'innerHTML'    => $html,
		'innerContent' => array( $html ),
	);
}

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
 * @since 1.0.0
 */
function gcb_redirect_old_permalink_structure(): void {
	// Only run on frontend for non-admin requests
	if ( is_admin() || is_feed() || is_robots() || is_trackback() ) {
		return;
	}

	// Get the current request URI
	$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
	if ( empty( $request_uri ) ) {
		return;
	}

	// Parse the URL path (remove query string)
	$path = wp_parse_url( $request_uri, PHP_URL_PATH );
	$path = trim( $path, '/' );

	// Skip if empty or already a WordPress core path
	if ( empty( $path ) || in_array( $path, array( 'wp-admin', 'wp-login.php', 'wp-json' ), true ) ) {
		return;
	}

	// Pattern to match old permalink structure: /post-slug/category-slug/
	// Split the path into segments
	$segments = explode( '/', $path );

	// Old structure has 2 segments: [post-slug, category-slug]
	// New structure has 1 segment: [post-slug]
	if ( count( $segments ) !== 2 ) {
		return; // Not the old permalink pattern
	}

	$post_slug     = $segments[0];
	$category_slug = $segments[1];

	// Try to find the post by slug
	$post = get_page_by_path( $post_slug, OBJECT, 'post' );

	// If post doesn't exist, let WordPress handle the 404
	if ( ! $post ) {
		return;
	}

	// Verify this URL actually matches the old permalink structure
	// by checking if the second segment is a category assigned to this post
	$post_categories = get_the_category( $post->ID );
	$is_old_structure = false;

	foreach ( $post_categories as $cat ) {
		if ( $cat->slug === $category_slug ) {
			$is_old_structure = true;
			break;
		}
	}

	// If this matches the old structure, redirect to new structure
	if ( $is_old_structure ) {
		$new_url = home_url( '/' . $post_slug . '/' );

		// Preserve query string if present
		$query_string = isset( $_SERVER['QUERY_STRING'] ) ? sanitize_text_field( wp_unslash( $_SERVER['QUERY_STRING'] ) ) : '';
		if ( ! empty( $query_string ) ) {
			$new_url .= '?' . $query_string;
		}

		// 301 permanent redirect for SEO
		wp_safe_redirect( $new_url, 301 );
		exit;
	}
}
add_action( 'template_redirect', 'gcb_redirect_old_permalink_structure', 1 );

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
