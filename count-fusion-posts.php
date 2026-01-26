<?php
/**
 * Count posts with Fusion shortcodes
 */

$args = array(
    'post_type' => 'post',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'fields' => 'ids'
);

$all_posts = get_posts($args);
$fusion_count = 0;
$fusion_youtube_count = 0;
$fusion_gallery_count = 0;
$fusion_text_count = 0;

foreach ($all_posts as $post_id) {
    $content = get_post_field('post_content', $post_id);

    if (strpos($content, '[fusion_') !== false) {
        $fusion_count++;

        // Count specific shortcode types
        if (strpos($content, '[fusion_youtube') !== false) {
            $fusion_youtube_count++;
        }
        if (strpos($content, '[fusion_gallery') !== false) {
            $fusion_gallery_count++;
        }
        if (strpos($content, '[fusion_text') !== false) {
            $fusion_text_count++;
        }
    }
}

echo "=== Fusion Shortcode Analysis ===" . PHP_EOL;
echo "Total published posts: " . count($all_posts) . PHP_EOL;
echo "Posts with Fusion shortcodes: " . $fusion_count . PHP_EOL;
echo "Posts without Fusion: " . (count($all_posts) - $fusion_count) . PHP_EOL;
echo "Percentage with Fusion: " . round(($fusion_count / count($all_posts)) * 100, 1) . "%" . PHP_EOL;
echo PHP_EOL;
echo "Breakdown by shortcode type:" . PHP_EOL;
echo "  - fusion_youtube: " . $fusion_youtube_count . " posts" . PHP_EOL;
echo "  - fusion_gallery: " . $fusion_gallery_count . " posts" . PHP_EOL;
echo "  - fusion_text: " . $fusion_text_count . " posts" . PHP_EOL;
