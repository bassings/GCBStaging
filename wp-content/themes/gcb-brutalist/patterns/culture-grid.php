<?php
/**
 * Title: Culture Grid
 * Slug: gcb-brutalist/culture-grid
 * Categories: featured
 * Description: 4-column responsive grid displaying text-only editorial content cards
 *
 * Editorial Brutalism Pattern:
 * - Text-only cards for high information density (NO images)
 * - Category labels with highlight color
 * - Playfair Display headlines (serif, large)
 * - Space Mono excerpts with Brutal Grey color
 * - Date only (no author on cards)
 * - Brutal Border with highlight color hover effect
 * - Responsive: 1 col mobile, 2 col tablet, 4 col desktop
 */

// Cache key for transient (invalidates hourly).
$cache_key          = 'gcb_culture_grid_offset_' . date( 'Y-m-d-H' );
$culture_grid_query = get_transient( $cache_key );

if ( false === $culture_grid_query ) {
	// Query for posts after the bento-grid (offset by 7 to avoid duplicates).
	$culture_grid_args = array(
		'post_type'      => 'post',
		'posts_per_page' => 8,
		'offset'         => 7, // Skip first 7 posts shown in bento-grid
		'post_status'    => 'publish',
		'orderby'        => 'date',
		'order'          => 'DESC',
	);

	$culture_grid_query = new WP_Query( $culture_grid_args );

	// Cache for 1 hour (reduce DB load).
	set_transient( $cache_key, $culture_grid_query, HOUR_IN_SECONDS );
}

if ( ! $culture_grid_query->have_posts() ) {
	return;
}
?>

<!-- Culture Grid Pattern -->
<section class="culture-grid-section" data-pattern="culture-grid">
    <div class="culture-grid-wrapper">
        <!-- Section Header -->
        <div class="culture-grid-header">
            <h2 class="culture-grid-title">LATEST REVIEWS & NEWS</h2>
        </div>

        <!-- 4-Column Responsive Grid -->
        <div class="culture-grid-container">
            <?php while ($culture_grid_query->have_posts()) : $culture_grid_query->the_post(); ?>
                <?php
                // Get post data
                $post_id = get_the_ID();
                $post_title = get_the_title();
                $post_permalink = get_permalink();
                $post_date = get_the_date('M j'); // North Star: Short format (Dec 27)
                $post_excerpt = get_the_excerpt();

                // Limit excerpt to 55 words (covers ~70% of posts without truncation)
                $excerpt_words = explode(' ', $post_excerpt);
                $short_excerpt = implode(' ', array_slice($excerpt_words, 0, 55));
                if (count($excerpt_words) > 55) {
                    $short_excerpt .= '...';
                }

                // Get primary category
                $categories = get_the_category();
                $primary_category = !empty($categories) ? $categories[0]->name : 'Editorial';
                ?>

                <!-- Text-Only Card -->
                <article class="culture-card">
                    <a href="<?php echo esc_url($post_permalink); ?>" class="culture-card-link">
                        <!-- Category Label (Acid Lime) -->
                        <div class="culture-card-category"><?php echo esc_html(strtoupper($primary_category)); ?></div>

                        <!-- Headline (Playfair Display) -->
                        <h3 class="culture-card-title"><?php echo esc_html($post_title); ?></h3>

                        <!-- Excerpt (Mono Font, Brutal Grey) -->
                        <p class="culture-card-excerpt"><?php echo esc_html($short_excerpt); ?></p>

                        <!-- Date Only (No Author) -->
                        <div class="culture-card-meta">
                            <time datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                                <?php echo esc_html($post_date); ?>
                            </time>
                        </div>
                    </a>
                </article>

            <?php endwhile; ?>
        </div>
    </div>
</section>

<style>
    /* Culture Grid Section */
    .culture-grid-section {
        background-color: var(--wp--preset--color--void-black);
        padding: var(--wp--preset--spacing--60) var(--wp--preset--spacing--30);
    }

    .culture-grid-wrapper {
        max-width: 1200px;
        margin: 0 auto;
    }

    /* Section Header */
    .culture-grid-header {
        margin-bottom: var(--wp--preset--spacing--40);
        padding-bottom: var(--wp--preset--spacing--30);
        border-bottom: 1px solid var(--wp--preset--color--brutal-border);
    }

    .culture-grid-title {
        font-family: var(--wp--preset--font-family--playfair);
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--wp--preset--color--off-white);
        margin: 0;
        letter-spacing: -0.02em;
    }

    /* 4-Column Responsive Grid */
    .culture-grid-container {
        display: grid;
        gap: var(--wp--preset--spacing--30);
        /* Desktop: 4 columns - minmax ensures equal widths */
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }

    /* Tablet: 2 columns */
    @media (min-width: 768px) and (max-width: 1024px) {
        .culture-grid-container {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    /* Mobile: 1 column */
    @media (max-width: 767px) {
        .culture-grid-container {
            grid-template-columns: 1fr;
        }

        .culture-grid-title {
            font-size: 2rem;
        }
    }

    /* Text-Only Card (NO Images) */
    .culture-card {
        background-color: var(--wp--preset--color--void-black);
        border: 1px solid var(--wp--preset--color--brutal-border);
        padding: var(--wp--preset--spacing--30);
        min-height: 200px;
        display: flex;
        flex-direction: column;
    }

    .culture-card:hover {
        border-color: var(--wp--preset--color--highlight);
    }

    .culture-card-link {
        text-decoration: none;
        color: inherit;
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    /* Category Label (Acid Lime) */
    .culture-card-category {
        font-family: var(--wp--preset--font-family--mono);
        font-size: 0.75rem;
        font-weight: 700;
        color: var(--wp--preset--color--highlight);
        letter-spacing: 0.1em;
        margin-bottom: 1rem;
        text-transform: uppercase;
    }

    /* Headline (Playfair Display, Large) */
    .culture-card-title {
        font-family: var(--wp--preset--font-family--playfair);
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--wp--preset--color--off-white);
        line-height: 1.3;
        margin: 0 0 1rem 0;
        letter-spacing: -0.01em;
    }

    /* Excerpt (Mono Font, Brutal Grey) */
    .culture-card-excerpt {
        font-family: var(--wp--preset--font-family--mono);
        font-size: 0.875rem;
        color: var(--wp--preset--color--brutal-grey);
        line-height: 1.6;
        margin: 0 0 auto 0;
        flex-grow: 1;
        word-break: break-word;
        overflow-wrap: break-word;
    }

    /* Date Only (No Author) */
    .culture-card-meta {
        font-family: var(--wp--preset--font-family--mono);
        font-size: 0.75rem;
        color: var(--wp--preset--color--brutal-grey);
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid var(--wp--preset--color--brutal-border);
    }

    .culture-card-meta time {
        font-weight: 400;
    }

    /* Ensure no images appear in cards */
    .culture-card img {
        display: none !important;
    }

    /* Accessibility: Focus States */
    .culture-card:focus-within {
        outline: 2px solid var(--wp--preset--color--highlight);
        outline-offset: 2px;
    }

    .culture-card-link:focus {
        outline: none;
    }
</style>

<?php wp_reset_postdata(); ?>
