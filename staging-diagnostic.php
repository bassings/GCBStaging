<?php
/**
 * STAGING DIAGNOSTIC TOOL
 *
 * Upload this file to your staging site root and visit it in browser:
 * https://staging-9ba2-gaycarboys.wpcomstaging.com/staging-diagnostic.php
 *
 * This will show you exactly what's different between local and staging.
 * DELETE THIS FILE after diagnosing - it contains sensitive information.
 */

// Load WordPress
require_once __DIR__ . '/wp-load.php';

// Security: Only allow logged-in administrators
if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
	die( 'Access denied. Please log in as administrator.' );
}

header( 'Content-Type: text/html; charset=utf-8' );
?>
<!DOCTYPE html>
<html>
<head>
	<title>Staging Diagnostic Report</title>
	<style>
		body {
			font-family: monospace;
			background: #000;
			color: #0f0;
			padding: 20px;
			line-height: 1.6;
		}
		h1, h2 {
			color: #0ff;
			border-bottom: 2px solid #0ff;
			padding-bottom: 10px;
		}
		.ok {
			color: #0f0;
		}
		.warning {
			color: #ff0;
		}
		.error {
			color: #f00;
		}
		.section {
			margin: 20px 0;
			padding: 15px;
			border: 1px solid #333;
		}
		code {
			background: #111;
			padding: 2px 6px;
			border-radius: 3px;
		}
		pre {
			background: #111;
			padding: 10px;
			overflow-x: auto;
			border-left: 3px solid #0ff;
		}
	</style>
</head>
<body>
	<h1>🔍 GCB Staging Diagnostic Report</h1>

	<div class="section">
		<h2>1. Fusion Builder Status</h2>
		<p><strong>Class exists:</strong>
			<?php echo class_exists( 'FusionBuilder' ) ? '<span class="ok">✓ YES</span>' : '<span class="error">✗ NO</span>'; ?>
		</p>
		<p><strong>Shortcode registered [fusion_youtube]:</strong>
			<?php echo shortcode_exists( 'fusion_youtube' ) ? '<span class="ok">✓ YES</span>' : '<span class="error">✗ NO</span>'; ?>
		</p>
		<p><strong>Shortcode registered [fusion_code]:</strong>
			<?php echo shortcode_exists( 'fusion_code' ) ? '<span class="ok">✓ YES</span>' : '<span class="error">✗ NO</span>'; ?>
		</p>
		<p><strong>Plugin active:</strong>
			<?php
			$active = is_plugin_active( 'fusion-builder/fusion-builder.php' );
			echo $active ? '<span class="ok">✓ YES</span>' : '<span class="error">✗ NO</span>';
			?>
		</p>
	</div>

	<div class="section">
		<h2>2. Fallback Functions Status</h2>
		<p><strong>Content filter registered:</strong>
			<?php echo function_exists( 'gcb_process_fusion_video_fallback' ) ? '<span class="ok">✓ YES</span>' : '<span class="error">✗ NO</span>'; ?>
		</p>
		<p><strong>Fallback shortcodes registered:</strong>
			<?php echo function_exists( 'gcb_register_fusion_fallback_shortcodes' ) ? '<span class="ok">✓ YES</span>' : '<span class="error">✗ NO</span>'; ?>
		</p>
	</div>

	<div class="section">
		<h2>3. Test YouTube Shortcode Processing</h2>
		<?php
		// Test shortcode
		$test_shortcode = '[fusion_youtube id="0SCzvD2rODM" alignment="center" width="1200" height="675"]';
		$processed = do_shortcode( $test_shortcode );

		echo '<p><strong>Input:</strong><br><code>' . esc_html( $test_shortcode ) . '</code></p>';
		echo '<p><strong>Output length:</strong> ' . strlen( $processed ) . ' characters</p>';
		echo '<p><strong>Contains iframe:</strong> ';
		echo strpos( $processed, '<iframe' ) !== false ? '<span class="ok">✓ YES</span>' : '<span class="error">✗ NO</span>';
		echo '</p>';
		echo '<p><strong>Processed HTML:</strong></p>';
		echo '<pre>' . esc_html( $processed ) . '</pre>';
		?>
	</div>

	<div class="section">
		<h2>4. Test fusion_code Shortcode</h2>
		<?php
		// Test base64 encoded content (like the table in the post)
		$test_code = '[fusion_code]PHRhYmxlPjx0cj48dGQ+VGVzdDwvdGQ+PC90cj48L3RhYmxlPg==[/fusion_code]';
		$processed_code = do_shortcode( $test_code );

		echo '<p><strong>Input:</strong><br><code>' . esc_html( $test_code ) . '</code></p>';
		echo '<p><strong>Output:</strong></p>';
		echo '<pre>' . esc_html( $processed_code ) . '</pre>';
		?>
	</div>

	<div class="section">
		<h2>5. Actual Post Content Check</h2>
		<?php
		// Get the Polestar post
		$post = get_post( 95298 );

		if ( $post ) {
			echo '<p><strong>Post found:</strong> <span class="ok">✓ YES</span></p>';
			echo '<p><strong>Post title:</strong> ' . esc_html( $post->post_title ) . '</p>';
			echo '<p><strong>Content length:</strong> ' . strlen( $post->post_content ) . ' characters</p>';
			echo '<p><strong>Contains [fusion_youtube]:</strong> ';
			echo strpos( $post->post_content, '[fusion_youtube' ) !== false ? '<span class="ok">✓ YES</span>' : '<span class="error">✗ NO</span>';
			echo '</p>';

			// Extract the YouTube shortcode
			if ( preg_match( '/\[fusion_youtube[^\]]+\]/', $post->post_content, $matches ) ) {
				echo '<p><strong>YouTube shortcode found:</strong></p>';
				echo '<pre>' . esc_html( $matches[0] ) . '</pre>';

				// Process it
				$processed_video = do_shortcode( $matches[0] );
				echo '<p><strong>Processed output:</strong></p>';
				echo '<pre>' . esc_html( $processed_video ) . '</pre>';
			}
		} else {
			echo '<p><strong>Post found:</strong> <span class="error">✗ NO (Post ID 95298 not found)</span></p>';
		}
		?>
	</div>

	<div class="section">
		<h2>6. WordPress Environment</h2>
		<p><strong>WP Version:</strong> <?php echo get_bloginfo( 'version' ); ?></p>
		<p><strong>PHP Version:</strong> <?php echo PHP_VERSION; ?></p>
		<p><strong>Theme:</strong> <?php echo wp_get_theme()->get( 'Name' ); ?></p>
		<p><strong>Active plugins:</strong></p>
		<ul>
			<?php
			$active_plugins = get_option( 'active_plugins' );
			foreach ( $active_plugins as $plugin ) {
				echo '<li>' . esc_html( $plugin ) . '</li>';
			}
			?>
		</ul>
	</div>

	<div class="section">
		<h2>7. Cache Status</h2>
		<?php
		// Check for common caching plugins/systems
		$cache_plugins = array(
			'wp-super-cache/wp-cache.php' => 'WP Super Cache',
			'w3-total-cache/w3-total-cache.php' => 'W3 Total Cache',
			'wp-rocket/wp-rocket.php' => 'WP Rocket',
			'litespeed-cache/litespeed-cache.php' => 'LiteSpeed Cache',
		);

		$found_cache = false;
		foreach ( $cache_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				echo '<p><span class="warning">⚠ ' . esc_html( $plugin_name ) . ' is active</span></p>';
				$found_cache = true;
			}
		}

		if ( ! $found_cache ) {
			echo '<p><span class="ok">✓ No caching plugins detected</span></p>';
		}

		// Check for object cache
		if ( wp_using_ext_object_cache() ) {
			echo '<p><span class="warning">⚠ External object cache is active</span></p>';
			echo '<p><em>You may need to flush object cache separately</em></p>';
		}
		?>
	</div>

	<p style="margin-top: 40px; padding: 20px; border: 2px solid #f00; color: #f00;">
		<strong>⚠️ IMPORTANT:</strong> Delete this file (staging-diagnostic.php) after reviewing the results.
		It contains sensitive information about your WordPress installation.
	</p>
</body>
</html>
<?php
