<?php
/**
 * Block Validation Script
 *
 * Validates that migrated posts have valid Gutenberg block structure
 * using WordPress's parse_blocks() function.
 */

require_once __DIR__ . '/wp-load.php';

// Test posts from MIGRATION-TEST-PLAN.md
$test_posts = [
    ['id' => 3049,  'pattern' => 'video|multi-col',      'coverage' => 1542],
    ['id' => 26909, 'pattern' => 'mixed|multi-col',      'coverage' => 1168],
    ['id' => 6,     'pattern' => 'text-only|columned',   'coverage' => 671],
    ['id' => 16188, 'pattern' => 'mixed|complex',        'coverage' => 377],
    ['id' => 729,   'pattern' => 'standard|multi-col',   'coverage' => 44],
    ['id' => 91845, 'pattern' => 'table|multi-col',      'coverage' => 28],
    ['id' => 27615, 'pattern' => 'text-only|multi-col',  'coverage' => 16],
    ['id' => 32659, 'pattern' => 'video-gallery|complex','coverage' => 13],
    ['id' => 17475, 'pattern' => 'video|columned',       'coverage' => 10],
    ['id' => 28667, 'pattern' => 'video|complex',        'coverage' => 8],
    ['id' => 30756, 'pattern' => 'multi-image|complex',  'coverage' => 4],
    ['id' => 31474, 'pattern' => 'mixed-heavy|complex',  'coverage' => 4],
    ['id' => 33420, 'pattern' => 'text-only|complex',    'coverage' => 3],
    ['id' => 28597, 'pattern' => 'text-only|single',     'coverage' => 1],
    ['id' => 28612, 'pattern' => 'multi-image|single',   'coverage' => 1],
    ['id' => 28629, 'pattern' => 'table|single',         'coverage' => 1],
    ['id' => 28632, 'pattern' => 'standard|single',      'coverage' => 1],
    ['id' => 31092, 'pattern' => 'multi-image|multi-col','coverage' => 1],
    ['id' => 33073, 'pattern' => 'standard|complex',     'coverage' => 1],
    ['id' => 94615, 'pattern' => 'table|complex',        'coverage' => 1],
];

/**
 * Validate block structure recursively
 */
function validate_block($block, &$issues, $path = '') {
    $block_name = $block['blockName'] ?? 'unknown';
    $current_path = $path . '/' . $block_name;

    // Check for null blocks (invalid)
    if ($block_name === null && !empty(trim($block['innerHTML'] ?? ''))) {
        // This is freeform HTML - not necessarily invalid
        return;
    }

    // Check for specific block types
    switch ($block_name) {
        case 'core/column':
            // Column blocks should have width attribute matching flex-basis style
            $attrs = $block['attrs'] ?? [];
            $inner_html = $block['innerHTML'] ?? '';

            if (isset($attrs['width'])) {
                $width = $attrs['width'];
                if (strpos($inner_html, "flex-basis:$width") === false &&
                    strpos($inner_html, "flex-basis: $width") === false) {
                    $issues[] = "$current_path: Missing flex-basis style matching width attribute ($width)";
                }
            }
            break;

        case 'core/image':
            $attrs = $block['attrs'] ?? [];
            $inner_html = $block['innerHTML'] ?? '';

            // Check for wp-image-{id} class
            if (isset($attrs['id'])) {
                $expected_class = 'wp-image-' . $attrs['id'];
                if (strpos($inner_html, $expected_class) === false) {
                    $issues[] = "$current_path: Missing $expected_class class on img element";
                }
            }

            // Check for sizeSlug and corresponding class
            if (isset($attrs['sizeSlug'])) {
                $expected_class = 'size-' . $attrs['sizeSlug'];
                if (strpos($inner_html, $expected_class) === false) {
                    $issues[] = "$current_path: Missing $expected_class class on figure element";
                }
            }
            break;

        case 'core/list':
            // List blocks should have inner blocks for list items
            $inner_blocks = $block['innerBlocks'] ?? [];
            if (empty($inner_blocks)) {
                // Check if innerHTML contains list items directly (old style)
                $inner_html = $block['innerHTML'] ?? '';
                if (strpos($inner_html, '<li>') !== false) {
                    $issues[] = "$current_path: List items should be nested wp:list-item blocks";
                }
            }
            break;

        case 'core/columns':
            // Columns blocks should have column children
            $inner_blocks = $block['innerBlocks'] ?? [];
            if (empty($inner_blocks)) {
                $issues[] = "$current_path: Columns block has no inner column blocks";
            }
            break;

        case 'core/embed':
            // Check for valid URL
            $attrs = $block['attrs'] ?? [];
            $url = $attrs['url'] ?? '';
            if (empty($url)) {
                $issues[] = "$current_path: Embed block missing URL attribute";
            } elseif (strpos($url, 'http://http') !== false || strpos($url, 'https://https') !== false) {
                $issues[] = "$current_path: Embed block has malformed URL: $url";
            }
            break;
    }

    // Recursively validate inner blocks
    foreach ($block['innerBlocks'] ?? [] as $inner_block) {
        validate_block($inner_block, $issues, $current_path);
    }
}

/**
 * Count blocks by type
 */
function count_blocks($blocks, &$counts, $depth = 0) {
    foreach ($blocks as $block) {
        $name = $block['blockName'] ?? 'freeform';
        if (!isset($counts[$name])) {
            $counts[$name] = 0;
        }
        $counts[$name]++;

        if (!empty($block['innerBlocks'])) {
            count_blocks($block['innerBlocks'], $counts, $depth + 1);
        }
    }
}

echo "========================================\n";
echo "  BLOCK VALIDATION REPORT\n";
echo "========================================\n\n";

$results = [];
$total_issues = 0;
$total_posts = 0;
$passed_posts = 0;

foreach ($test_posts as $test) {
    $post_id = $test['id'];
    $pattern = $test['pattern'];
    $coverage = $test['coverage'];

    $post = get_post($post_id);

    if (!$post) {
        echo "❌ Post $post_id ($pattern): NOT FOUND\n";
        $results[] = ['id' => $post_id, 'pattern' => $pattern, 'status' => 'NOT_FOUND', 'issues' => []];
        continue;
    }

    $total_posts++;

    // Check if post has blocks
    if (strpos($post->post_content, '<!-- wp:') === false) {
        echo "⚠️  Post $post_id ($pattern): No Gutenberg blocks found\n";
        $results[] = ['id' => $post_id, 'pattern' => $pattern, 'status' => 'NO_BLOCKS', 'issues' => []];
        continue;
    }

    // Parse blocks
    $blocks = parse_blocks($post->post_content);

    // Validate each block
    $issues = [];
    foreach ($blocks as $block) {
        validate_block($block, $issues);
    }

    // Count blocks
    $counts = [];
    count_blocks($blocks, $counts);

    if (empty($issues)) {
        $block_count = array_sum($counts);
        echo "✅ Post $post_id ($pattern): VALID - " . $block_count . " blocks\n";
        $results[] = ['id' => $post_id, 'pattern' => $pattern, 'status' => 'VALID', 'issues' => [], 'blocks' => $counts];
        $passed_posts++;
    } else {
        echo "❌ Post $post_id ($pattern): " . count($issues) . " ISSUES\n";
        foreach ($issues as $issue) {
            echo "   - $issue\n";
            $total_issues++;
        }
        $results[] = ['id' => $post_id, 'pattern' => $pattern, 'status' => 'INVALID', 'issues' => $issues, 'blocks' => $counts];
    }
}

echo "\n========================================\n";
echo "  SUMMARY\n";
echo "========================================\n";
echo "Total posts validated: $total_posts\n";
echo "Passed: $passed_posts\n";
echo "Failed: " . ($total_posts - $passed_posts) . "\n";
echo "Total issues: $total_issues\n";

// Calculate coverage
$passing_coverage = 0;
$total_coverage = 0;
foreach ($results as $result) {
    $test_info = array_filter($test_posts, fn($t) => $t['id'] == $result['id']);
    $test_info = reset($test_info);
    if ($test_info) {
        $total_coverage += $test_info['coverage'];
        if ($result['status'] === 'VALID') {
            $passing_coverage += $test_info['coverage'];
        }
    }
}

echo "\n========================================\n";
echo "  COVERAGE\n";
echo "========================================\n";
echo "Passing patterns cover: $passing_coverage / $total_coverage posts\n";
echo "Coverage percentage: " . round($passing_coverage / $total_coverage * 100, 1) . "%\n";

// Print detailed results table
echo "\n========================================\n";
echo "  DETAILED RESULTS\n";
echo "========================================\n";
printf("%-8s %-25s %-8s %s\n", "Post ID", "Pattern", "Coverage", "Status");
echo str_repeat("-", 60) . "\n";
foreach ($results as $result) {
    $test_info = array_filter($test_posts, fn($t) => $t['id'] == $result['id']);
    $test_info = reset($test_info);
    $coverage = $test_info ? $test_info['coverage'] : 0;
    $status_icon = match($result['status']) {
        'VALID' => '✅',
        'INVALID' => '❌',
        'NO_BLOCKS' => '⚠️',
        default => '❓',
    };
    printf("%-8s %-25s %-8s %s %s\n",
        $result['id'],
        $result['pattern'],
        $coverage,
        $status_icon,
        $result['status']
    );
}
