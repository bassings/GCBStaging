<?php
/**
 * Newsletter Preview Template
 * 
 * Usage: Add ?newsletter_preview=1&post_id=XXX to any URL
 * Example: http://localhost:8881/?newsletter_preview=1&post_id=12345
 * 
 * Shows how a post will render in email newsletters after our conversions.
 */

// Only allow when explicitly requested and in development/testing
if ( ! isset( $_GET['newsletter_preview'] ) || $_GET['newsletter_preview'] !== '1' ) {
	return;
}

// Security: Only allow for logged-in admins or local development
if ( ! current_user_can( 'manage_options' ) && $_SERVER['REMOTE_ADDR'] !== '127.0.0.1' && $_SERVER['REMOTE_ADDR'] !== '::1' ) {
	wp_die( 'Access denied. Must be admin or localhost.' );
}

$post_id = isset( $_GET['post_id'] ) ? intval( $_GET['post_id'] ) : 0;

if ( ! $post_id ) {
	// Show recent posts to choose from
	$recent = get_posts( array( 'numberposts' => 20, 'post_status' => 'publish' ) );
	echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Newsletter Preview - Select Post</title>';
	echo '<style>body{font-family:system-ui;max-width:800px;margin:2rem auto;padding:1rem;background:#1a1a1a;color:#fff}';
	echo 'a{color:#0084FF}li{margin:0.5rem 0}</style></head><body>';
	echo '<h1>📧 Newsletter Preview</h1><p>Select a post to preview:</p><ul>';
	foreach ( $recent as $post ) {
		$url = add_query_arg( array( 'newsletter_preview' => '1', 'post_id' => $post->ID ), home_url() );
		echo '<li><a href="' . esc_url( $url ) . '">' . esc_html( $post->post_title ) . '</a> (' . $post->post_date . ')</li>';
	}
	echo '</ul></body></html>';
	exit;
}

$post = get_post( $post_id );
if ( ! $post ) {
	wp_die( 'Post not found.' );
}

// Get the content and apply newsletter filters
$content = $post->post_content;

// Render blocks first (converts block markup to HTML)
$content = do_blocks( $content );

// Apply wpautop for paragraph formatting
$content = wpautop( $content );

// Apply our newsletter conversion filters (same order as actual newsletter)
$content = apply_filters( 'jetpack_newsletter_post_content', $content );

// Wrap in email-like container for preview
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Newsletter Preview: <?php echo esc_html( $post->post_title ); ?></title>
	<style>
		body {
			margin: 0;
			padding: 20px;
			background: #333;
			font-family: system-ui, sans-serif;
		}
		.preview-header {
			max-width: 600px;
			margin: 0 auto 20px;
			padding: 15px;
			background: #0084FF;
			color: #fff;
			border-radius: 4px;
		}
		.preview-header h1 {
			margin: 0 0 10px;
			font-size: 16px;
		}
		.preview-header p {
			margin: 0;
			font-size: 12px;
			opacity: 0.9;
		}
		.email-container {
			max-width: 600px;
			margin: 0 auto;
			background: #1a1a1a;
			padding: 20px;
			color: #fafafa;
			font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
			line-height: 1.6;
		}
		.email-container img {
			max-width: 100%;
			height: auto;
		}
		.email-container table {
			border-collapse: collapse;
		}
		.email-container a {
			color: #0084FF;
		}
	</style>
</head>
<body>
	<div class="preview-header">
		<h1>📧 Newsletter Preview</h1>
		<p>Post: <?php echo esc_html( $post->post_title ); ?> (ID: <?php echo $post_id; ?>)</p>
		<p>This shows how the post will appear in email newsletters.</p>
	</div>
	
	<div class="email-container">
		<h1 style="font-family: Georgia, serif; font-size: 28px; margin-bottom: 20px;">
			<?php echo esc_html( $post->post_title ); ?>
		</h1>
		
		<?php echo $content; ?>
	</div>
	
	<div class="preview-header" style="margin-top: 20px;">
		<p><a href="<?php echo esc_url( add_query_arg( 'newsletter_preview', '1', home_url() ) ); ?>" style="color:#fff">← Back to post list</a></p>
	</div>
</body>
</html>
<?php
exit;
