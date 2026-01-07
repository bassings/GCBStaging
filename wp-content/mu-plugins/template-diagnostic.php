<?php
/**
 * Template Diagnostic - Check what template is being used
 */

add_action('wp_footer', function() {
    if (current_user_can('administrator') && isset($_GET['debug_template'])) {
        echo '<div style="position: fixed; bottom: 0; right: 0; background: #000; color: #0f0; padding: 20px; z-index: 9999; font-family: monospace; font-size: 12px; max-width: 600px; border: 3px solid #0f0;">';
        echo '<h3 style="color: #0f0; margin: 0 0 10px 0;">🔍 TEMPLATE DEBUG INFO</h3>';

        // Template being used
        global $template;
        echo '<p><strong>Template File:</strong><br>' . esc_html($template) . '</p>';

        // Template from theme
        $theme_template = get_template_directory() . '/templates/index.html';
        echo '<p><strong>Theme Template Exists:</strong> ' . (file_exists($theme_template) ? '✅ YES' : '❌ NO') . '</p>';

        if (file_exists($theme_template)) {
            $content = file_get_contents($theme_template);
            echo '<p><strong>Template Size:</strong> ' . strlen($content) . ' bytes</p>';
            echo '<p><strong>Contains Query Block:</strong> ' . (strpos($content, 'wp:query') !== false ? '❌ YES (OLD)' : '✅ NO (CORRECT)') . '</p>';
            echo '<p><strong>Line Count:</strong> ' . substr_count($content, "\n") . ' lines</p>';
        }

        // Check for customized template in DB
        $custom_template = get_posts(array(
            'post_type' => 'wp_template',
            'name' => 'index',
            'posts_per_page' => 1
        ));

        echo '<p><strong>Customized Template in DB:</strong> ' . (!empty($custom_template) ? '⚠️ YES (overriding file)' : '✅ NO') . '</p>';

        // Active theme
        echo '<p><strong>Active Theme:</strong> ' . wp_get_theme()->get('Name') . '</p>';

        echo '<p style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #0f0;"><strong>💡 TIP:</strong> If "Contains Query Block" shows YES (OLD), the file hasn\'t been read yet. Try:<br>1. Hard refresh (Cmd+Shift+R)<br>2. Clear all caches<br>3. Restart WordPress Studio</p>';

        echo '</div>';
    }
});
