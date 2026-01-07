<?php
/**
 * Template Content Diagnostic - Check what content is in the index template
 */

add_action('wp_footer', function() {
    if (current_user_can('administrator') && isset($_GET['debug_template_content'])) {
        echo '<div style="position: fixed; top: 0; left: 0; background: #000; color: #0f0; padding: 20px; z-index: 9999; font-family: monospace; font-size: 11px; max-width: 100%; max-height: 100vh; overflow: auto; border: 3px solid #0f0;">';
        echo '<h3 style="color: #0f0; margin: 0 0 10px 0;">🔍 TEMPLATE CONTENT DEBUG</h3>';

        // Check for customized template in database
        $custom_template = get_posts(array(
            'post_type' => 'wp_template',
            'name' => 'index',
            'posts_per_page' => 1,
            'post_status' => 'any'
        ));

        if (!empty($custom_template)) {
            echo '<p><strong>❌ CUSTOMIZED TEMPLATE FOUND IN DATABASE</strong></p>';
            echo '<p><strong>Post ID:</strong> ' . $custom_template[0]->ID . '</p>';
            echo '<p><strong>Post Status:</strong> ' . $custom_template[0]->post_status . '</p>';
            echo '<p><strong>Modified:</strong> ' . $custom_template[0]->post_modified . '</p>';
            echo '<p><strong>Content Length:</strong> ' . strlen($custom_template[0]->post_content) . ' chars</p>';

            // Show first 500 chars of content
            echo '<details><summary><strong>Template Content (first 1000 chars):</strong></summary>';
            echo '<pre style="color: #0f0; background: #000; padding: 10px; overflow-x: auto; font-size: 10px;">';
            echo esc_html(substr($custom_template[0]->post_content, 0, 1000));
            echo '</pre></details>';

            echo '<p style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #0f0;"><strong>💡 FIX:</strong> Delete this customized template via Site Editor or delete post ID ' . $custom_template[0]->ID . '</p>';
        } else {
            echo '<p><strong>✅ NO CUSTOMIZED TEMPLATE IN DATABASE</strong></p>';
            echo '<p>WordPress should be using the file: wp-content/themes/gcb-brutalist/templates/index.html</p>';
        }

        // Check file template
        $theme_template = get_template_directory() . '/templates/index.html';
        if (file_exists($theme_template)) {
            $content = file_get_contents($theme_template);
            echo '<p><strong>File Template Size:</strong> ' . strlen($content) . ' bytes</p>';
            echo '<p><strong>Contains wp:query:</strong> ' . (strpos($content, 'wp:query') !== false ? '❌ YES' : '✅ NO') . '</p>';
            echo '<p><strong>Contains wp:pattern video-rail:</strong> ' . (strpos($content, 'gcb-brutalist/video-rail') !== false ? '✅ YES' : '❌ NO') . '</p>';
            echo '<p><strong>Contains wp:pattern bento-grid:</strong> ' . (strpos($content, 'gcb-brutalist/bento-grid') !== false ? '✅ YES' : '❌ NO') . '</p>';
            echo '<p><strong>Contains wp:pattern culture-grid:</strong> ' . (strpos($content, 'gcb-brutalist/culture-grid') !== false ? '✅ YES' : '❌ NO') . '</p>';
        }

        echo '</div>';
    }
});
