<?php
/**
 * GCB Staging Diagnostic Tool
 *
 * Accessible at: /wp-admin/?page=gcb-diagnostic
 * Or via query parameter: /?gcb_diagnostic=1 (admin only)
 *
 * @package GCB_Brutalist
 */

// Security: Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add diagnostic page to WordPress admin menu
 */
function gcb_diagnostic_add_admin_page(): void {
	add_management_page(
		'GCB Diagnostic',
		'GCB Diagnostic',
		'manage_options',
		'gcb-diagnostic',
		'gcb_diagnostic_render_page'
	);
}
add_action( 'admin_menu', 'gcb_diagnostic_add_admin_page' );

/**
 * Handle query parameter access (?gcb_diagnostic=1)
 */
function gcb_diagnostic_query_param_handler(): void {
	if ( ! isset( $_GET['gcb_diagnostic'] ) || $_GET['gcb_diagnostic'] !== '1' ) {
		return;
	}

	// Security: Only admins
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Access denied. Please log in as administrator.' );
	}

	gcb_diagnostic_render_page();
	exit;
}
add_action( 'init', 'gcb_diagnostic_query_param_handler' );

/**
 * Render the diagnostic page
 */
function gcb_diagnostic_render_page(): void {
	?>
	<!DOCTYPE html>
	<html>
	<head>
		<title>GCB Staging Diagnostic Report</title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<style>
			body {
				font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, monospace;
				background: #000;
				color: #0f0;
				padding: 20px;
				line-height: 1.6;
				margin: 0;
			}
			.container {
				max-width: 1200px;
				margin: 0 auto;
			}
			h1, h2 {
				color: #0ff;
				border-bottom: 2px solid #0ff;
				padding-bottom: 10px;
			}
			h1 {
				font-size: 2rem;
			}
			h2 {
				font-size: 1.5rem;
				margin-top: 30px;
			}
			.ok {
				color: #0f0;
				font-weight: bold;
			}
			.warning {
				color: #ff0;
				font-weight: bold;
			}
			.error {
				color: #f00;
				font-weight: bold;
			}
			.section {
				margin: 20px 0;
				padding: 15px;
				border: 1px solid #333;
				background: #0a0a0a;
			}
			code {
				background: #111;
				padding: 2px 6px;
				border-radius: 3px;
				color: #0ff;
			}
			pre {
				background: #111;
				padding: 10px;
				overflow-x: auto;
				border-left: 3px solid #0ff;
				white-space: pre-wrap;
				word-wrap: break-word;
			}
			table {
				width: 100%;
				border-collapse: collapse;
				margin: 10px 0;
			}
			th, td {
				padding: 8px;
				text-align: left;
				border: 1px solid #333;
			}
			th {
				background: #0a0a0a;
				color: #0ff;
			}
			.alert {
				margin-top: 40px;
				padding: 20px;
				border: 2px solid #f00;
				color: #f00;
				background: #1a0000;
			}
			ul {
				margin: 10px 0;
				padding-left: 20px;
			}
		</style>
	</head>
	<body>
		<div class="container">
			<h1>🔍 GCB Staging Diagnostic Report</h1>
			<p><strong>Generated:</strong> <?php echo esc_html( current_time( 'Y-m-d H:i:s' ) ); ?></p>
			<p><strong>Environment:</strong> <?php echo esc_html( wp_get_environment_type() ); ?></p>

			<div class="section">
				<h2>1. Fusion Builder Status</h2>
				<table>
					<tr>
						<th>Check</th>
						<th>Status</th>
						<th>Details</th>
					</tr>
					<tr>
						<td>FusionBuilder class exists</td>
						<td><?php echo class_exists( 'FusionBuilder' ) ? '<span class="ok">✓ YES</span>' : '<span class="error">✗ NO</span>'; ?></td>
						<td><?php echo class_exists( 'FusionBuilder' ) ? 'Plugin code loaded' : 'Plugin not loaded'; ?></td>
					</tr>
					<tr>
						<td>[fusion_youtube] shortcode</td>
						<td><?php echo shortcode_exists( 'fusion_youtube' ) ? '<span class="ok">✓ YES</span>' : '<span class="error">✗ NO</span>'; ?></td>
						<td><?php echo shortcode_exists( 'fusion_youtube' ) ? 'Shortcode registered' : 'Shortcode NOT registered'; ?></td>
					</tr>
					<tr>
						<td>[fusion_code] shortcode</td>
						<td><?php echo shortcode_exists( 'fusion_code' ) ? '<span class="ok">✓ YES</span>' : '<span class="error">✗ NO</span>'; ?></td>
						<td><?php echo shortcode_exists( 'fusion_code' ) ? 'Shortcode registered' : 'Shortcode NOT registered'; ?></td>
					</tr>
					<tr>
						<td>Plugin active</td>
						<td><?php
						$active = is_plugin_active( 'fusion-builder/fusion-builder.php' );
						echo $active ? '<span class="ok">✓ YES</span>' : '<span class="error">✗ NO</span>';
						?></td>
						<td><?php echo $active ? 'In active_plugins list' : 'Not in active_plugins list'; ?></td>
					</tr>
				</table>
			</div>

			<div class="section">
				<h2>2. Fallback Functions Status</h2>
				<table>
					<tr>
						<th>Function</th>
						<th>Status</th>
						<th>Location</th>
					</tr>
					<tr>
						<td>gcb_process_fusion_video_fallback</td>
						<td><?php echo function_exists( 'gcb_process_fusion_video_fallback' ) ? '<span class="ok">✓ EXISTS</span>' : '<span class="error">✗ MISSING</span>'; ?></td>
						<td>functions.php (content filter)</td>
					</tr>
					<tr>
						<td>gcb_register_fusion_fallback_shortcodes</td>
						<td><?php echo function_exists( 'gcb_register_fusion_fallback_shortcodes' ) ? '<span class="ok">✓ EXISTS</span>' : '<span class="error">✗ MISSING</span>'; ?></td>
						<td>functions.php (shortcode registration)</td>
					</tr>
					<tr>
						<td>gcb_fusion_youtube_shortcode_fallback</td>
						<td><?php echo function_exists( 'gcb_fusion_youtube_shortcode_fallback' ) ? '<span class="ok">✓ EXISTS</span>' : '<span class="error">✗ MISSING</span>'; ?></td>
						<td>functions.php (fallback handler)</td>
					</tr>
					<tr>
						<td>gcb_fusion_code_shortcode_fallback</td>
						<td><?php echo function_exists( 'gcb_fusion_code_shortcode_fallback' ) ? '<span class="ok">✓ EXISTS</span>' : '<span class="error">✗ MISSING</span>'; ?></td>
						<td>functions.php (fallback handler)</td>
					</tr>
				</table>
			</div>

			<div class="section">
				<h2>3. Test YouTube Shortcode Processing</h2>
				<?php
				$test_shortcode = '[fusion_youtube id="0SCzvD2rODM" alignment="center" width="1200" height="675"]';
				$processed = do_shortcode( $test_shortcode );
				$has_iframe = strpos( $processed, '<iframe' ) !== false;
				$has_youtube_url = strpos( $processed, 'youtube.com/embed' ) !== false;
				?>
				<p><strong>Input:</strong><br><code><?php echo esc_html( $test_shortcode ); ?></code></p>
				<table>
					<tr>
						<th>Check</th>
						<th>Result</th>
					</tr>
					<tr>
						<td>Output length</td>
						<td><?php echo strlen( $processed ); ?> characters</td>
					</tr>
					<tr>
						<td>Contains &lt;iframe&gt;</td>
						<td><?php echo $has_iframe ? '<span class="ok">✓ YES</span>' : '<span class="error">✗ NO</span>'; ?></td>
					</tr>
					<tr>
						<td>Contains YouTube URL</td>
						<td><?php echo $has_youtube_url ? '<span class="ok">✓ YES</span>' : '<span class="error">✗ NO</span>'; ?></td>
					</tr>
					<tr>
						<td>Shortcode processed</td>
						<td><?php echo ( $processed !== $test_shortcode ) ? '<span class="ok">✓ YES</span>' : '<span class="error">✗ NO (unchanged)</span>'; ?></td>
					</tr>
				</table>
				<p><strong>Processed HTML:</strong></p>
				<pre><?php echo esc_html( $processed ); ?></pre>
			</div>

			<div class="section">
				<h2>4. Test fusion_code Shortcode (Base64 Table)</h2>
				<?php
				// Test base64 encoded content (like the table in the post)
				$test_code = '[fusion_code]PHRhYmxlPjx0cj48dGQ+VGVzdDwvdGQ+PC90cj48L3RhYmxlPg==[/fusion_code]';
				$processed_code = do_shortcode( $test_code );
				?>
				<p><strong>Input:</strong><br><code><?php echo esc_html( $test_code ); ?></code></p>
				<p><strong>Expected:</strong> <code>&lt;table&gt;&lt;tr&gt;&lt;td&gt;Test&lt;/td&gt;&lt;/tr&gt;&lt;/table&gt;</code></p>
				<p><strong>Output:</strong></p>
				<pre><?php echo esc_html( $processed_code ); ?></pre>
				<p><strong>Decoded correctly:</strong>
					<?php echo ( strpos( $processed_code, '<table>' ) !== false ) ? '<span class="ok">✓ YES</span>' : '<span class="error">✗ NO</span>'; ?>
				</p>
			</div>

			<div class="section">
				<h2>5. Actual Post Content Check (Polestar Post)</h2>
				<?php
				// Try to get the Polestar post
				$post = get_page_by_path( '2026-polestar-4-now-real-buttons-and-the-ride-is-fabulous', OBJECT, 'post' );
				if ( ! $post ) {
					// Try by ID if path doesn't work
					$post = get_post( 95298 );
				}

				if ( $post ) {
					echo '<p><strong>Post found:</strong> <span class="ok">✓ YES</span></p>';
					echo '<p><strong>Post ID:</strong> ' . esc_html( $post->ID ) . '</p>';
					echo '<p><strong>Post title:</strong> ' . esc_html( $post->post_title ) . '</p>';
					echo '<p><strong>Content length:</strong> ' . number_format( strlen( $post->post_content ) ) . ' characters</p>';

					$has_fusion_youtube = strpos( $post->post_content, '[fusion_youtube' ) !== false;
					echo '<p><strong>Contains [fusion_youtube]:</strong> ';
					echo $has_fusion_youtube ? '<span class="ok">✓ YES</span>' : '<span class="error">✗ NO</span>';
					echo '</p>';

					// Extract the YouTube shortcode
					if ( preg_match( '/\[fusion_youtube[^\]]+\]/', $post->post_content, $matches ) ) {
						echo '<p><strong>YouTube shortcode found:</strong></p>';
						echo '<pre>' . esc_html( $matches[0] ) . '</pre>';

						// Process it
						$processed_video = do_shortcode( $matches[0] );
						echo '<p><strong>Processed output length:</strong> ' . strlen( $processed_video ) . ' characters</p>';
						echo '<p><strong>Contains iframe:</strong> ';
						echo ( strpos( $processed_video, '<iframe' ) !== false ) ? '<span class="ok">✓ YES</span>' : '<span class="error">✗ NO</span>';
						echo '</p>';
						echo '<p><strong>Processed HTML preview (first 500 chars):</strong></p>';
						echo '<pre>' . esc_html( substr( $processed_video, 0, 500 ) ) . '...</pre>';
					} else {
						echo '<p><span class="warning">⚠ No [fusion_youtube] shortcode found in content</span></p>';
					}
				} else {
					echo '<p><strong>Post found:</strong> <span class="error">✗ NO</span></p>';
					echo '<p>Could not find Polestar post by path or ID 95298</p>';
				}
				?>
			</div>

			<div class="section">
				<h2>6. WordPress Environment</h2>
				<table>
					<tr>
						<th>Setting</th>
						<th>Value</th>
					</tr>
					<tr>
						<td>WordPress Version</td>
						<td><?php echo esc_html( get_bloginfo( 'version' ) ); ?></td>
					</tr>
					<tr>
						<td>PHP Version</td>
						<td><?php echo esc_html( PHP_VERSION ); ?></td>
					</tr>
					<tr>
						<td>Theme</td>
						<td><?php echo esc_html( wp_get_theme()->get( 'Name' ) ); ?> v<?php echo esc_html( wp_get_theme()->get( 'Version' ) ); ?></td>
					</tr>
					<tr>
						<td>Site URL</td>
						<td><?php echo esc_html( get_site_url() ); ?></td>
					</tr>
					<tr>
						<td>Home URL</td>
						<td><?php echo esc_html( get_home_url() ); ?></td>
					</tr>
					<tr>
						<td>Multisite</td>
						<td><?php echo is_multisite() ? 'Yes' : 'No'; ?></td>
					</tr>
				</table>

				<h3>Active Plugins (<?php echo count( get_option( 'active_plugins', array() ) ); ?>)</h3>
				<ul>
					<?php
					$active_plugins = get_option( 'active_plugins', array() );
					if ( empty( $active_plugins ) ) {
						echo '<li>No active plugins</li>';
					} else {
						foreach ( $active_plugins as $plugin ) {
							$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin, false, false );
							echo '<li>' . esc_html( $plugin ) . ' - ' . esc_html( $plugin_data['Name'] ?? 'Unknown' ) . '</li>';
						}
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
					echo '<p><span class="ok">✓ No common caching plugins detected</span></p>';
				}

				// Check for object cache
				if ( wp_using_ext_object_cache() ) {
					echo '<p><span class="warning">⚠ External object cache is active</span></p>';
					echo '<p><em>You may need to flush object cache separately</em></p>';
				} else {
					echo '<p><span class="ok">✓ No external object cache</span></p>';
				}
				?>
			</div>

			<div class="section">
				<h2>8. Diagnostic Summary</h2>
				<?php
				$fusion_active = class_exists( 'FusionBuilder' ) && shortcode_exists( 'fusion_youtube' );
				$fallbacks_exist = function_exists( 'gcb_process_fusion_video_fallback' ) &&
								   function_exists( 'gcb_fusion_youtube_shortcode_fallback' );

				echo '<p><strong>System Status:</strong></p>';
				if ( $fusion_active ) {
					echo '<p><span class="ok">✓ Fusion Builder is ACTIVE - using native shortcodes</span></p>';
				} elseif ( $fallbacks_exist ) {
					echo '<p><span class="warning">⚠ Fusion Builder is INACTIVE - using fallback system</span></p>';
					if ( shortcode_exists( 'fusion_youtube' ) ) {
						echo '<p><span class="ok">✓ Fallback shortcodes registered successfully</span></p>';
					} else {
						echo '<p><span class="error">✗ Fallback shortcodes NOT registered (check functions.php)</span></p>';
					}
				} else {
					echo '<p><span class="error">✗ PROBLEM: Neither Fusion Builder nor fallbacks are working</span></p>';
					echo '<p>Check that functions.php contains the fallback functions (lines 498-656)</p>';
				}

				// Check if test shortcode worked
				$test_shortcode = '[fusion_youtube id="0SCzvD2rODM"]';
				$test_result = do_shortcode( $test_shortcode );
				if ( strpos( $test_result, '<iframe' ) !== false ) {
					echo '<p><span class="ok">✓ YouTube embed test PASSED</span></p>';
				} else {
					echo '<p><span class="error">✗ YouTube embed test FAILED</span></p>';
					echo '<p>Shortcode output: <code>' . esc_html( substr( $test_result, 0, 100 ) ) . '...</code></p>';
				}
				?>
			</div>

			<div class="alert">
				<p><strong>⚠️ SECURITY NOTE:</strong></p>
				<p>This diagnostic page is only accessible to administrators.</p>
				<p>Consider disabling this mu-plugin after troubleshooting:</p>
				<p><code>wp-content/mu-plugins/gcb-staging-diagnostic.php</code></p>
			</div>
		</div>
	</body>
	</html>
	<?php
}
