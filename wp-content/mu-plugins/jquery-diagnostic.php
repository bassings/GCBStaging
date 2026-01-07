<?php
/**
 * jQuery Diagnostic - Show page type and jQuery load status
 */

add_action('wp_footer', function() {
    if (current_user_can('administrator') && isset($_GET['debug_jquery'])) {
        global $wp_query;

        echo '<div style="position: fixed; bottom: 20px; right: 20px; background: #000; color: #0f0; padding: 20px; z-index: 9999; font-family: monospace; font-size: 12px; max-width: 500px; border: 3px solid #0f0;">';
        echo '<h3 style="color: #0f0; margin: 0 0 10px 0;">🔍 JQUERY DEBUG INFO</h3>';

        // Page type detection
        echo '<p><strong>Page Type:</strong></p>';
        echo '<ul style="margin: 5px 0; padding-left: 20px;">';
        echo '<li>is_front_page(): ' . (is_front_page() ? '✅ TRUE' : '❌ FALSE') . '</li>';
        echo '<li>is_home(): ' . (is_home() ? '✅ TRUE' : '❌ FALSE') . '</li>';
        echo '<li>is_singular(): ' . (is_singular() ? '✅ TRUE' : '❌ FALSE') . '</li>';
        echo '<li>is_singular("post"): ' . (is_singular('post') ? '✅ TRUE' : '❌ FALSE') . '</li>';
        echo '<li>is_singular("page"): ' . (is_singular('page') ? '✅ TRUE' : '❌ FALSE') . '</li>';
        echo '<li>is_single(): ' . (is_single() ? '✅ TRUE' : '❌ FALSE') . '</li>';
        echo '<li>is_page(): ' . (is_page() ? '✅ TRUE' : '❌ FALSE') . '</li>';
        echo '<li>is_archive(): ' . (is_archive() ? '✅ TRUE' : '❌ FALSE') . '</li>';
        echo '<li>is_category(): ' . (is_category() ? '✅ TRUE' : '❌ FALSE') . '</li>';
        echo '</ul>';

        // jQuery status
        echo '<p style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #0f0;"><strong>jQuery Status:</strong></p>';
        echo '<p>wp_script_is("jquery", "enqueued"): ' . (wp_script_is('jquery', 'enqueued') ? '✅ LOADED' : '❌ NOT LOADED') . '</p>';

        // Expected behavior
        echo '<p style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #0f0;"><strong>Expected Behavior:</strong></p>';
        if (is_singular('post') || is_singular('page')) {
            echo '<p>✅ jQuery SHOULD be loaded (single post/page)</p>';
        } else {
            echo '<p>❌ jQuery should NOT be loaded (homepage/archive)</p>';
        }

        echo '</div>';
    }
});
