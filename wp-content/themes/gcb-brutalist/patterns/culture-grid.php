<?php
/**
 * Title: Culture Grid
 * Slug: gcb-brutalist/culture-grid
 * Categories: featured
 * Description: 4-column responsive grid displaying text-only editorial content cards
 *
 * Editorial Brutalism Pattern:
 * - Text-only cards for high information density (NO images)
 * - Category labels with acid lime color
 * - Playfair Display headlines (serif, large)
 * - Space Mono excerpts with Brutal Grey color
 * - Date only (no author on cards)
 * - Brutal Border with Acid Lime hover effect
 * - Responsive: 1 col mobile, 2 col tablet, 4 col desktop
 */

// Query for standard posts (exclude videos by using content_format taxonomy)
$culture_grid_args = array(
    'post_type'      => 'post',
    'posts_per_page' => 8,
    'post_status'    => 'publish',
    'orderby'        => 'date',
    'order'          => 'DESC',
    'tax_query'      => array(
        array(
            'taxonomy' => 'content_format',
            'field'    => 'slug',
            'terms'    => 'standard', // Only get standard (non-video) posts
        ),
    ),
);

$culture_grid_query = new WP_Query($culture_grid_args);

if (!$culture_grid_query->have_posts()) {
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

                // Limit excerpt to ~15 words for brutalist aesthetic
                $excerpt_words = explode(' ', $post_excerpt);
                $short_excerpt = implode(' ', array_slice($excerpt_words, 0, 15));
                if (count($excerpt_words) > 15) {
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
        background-color: #050505; /* Void Black */
        padding: 4rem 1rem;
    }

    .culture-grid-wrapper {
        max-width: 1280px;
        margin: 0 auto;
    }

    /* Section Header */
    .culture-grid-header {
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #333333; /* Brutal Border */
    }

    .culture-grid-title {
        font-family: 'Playfair Display', serif;
        font-size: 2.5rem;
        font-weight: 700;
        color: #FAFAFA; /* Off-White */
        margin: 0;
        letter-spacing: -0.02em;
    }

    /* 4-Column Responsive Grid */
    .culture-grid-container {
        display: grid;
        gap: 1.5rem;
        /* Desktop: 4 columns */
        grid-template-columns: repeat(4, 1fr);
    }

    /* Tablet: 2 columns (768px) */
    @media (max-width: 1024px) and (min-width: 768px) {
        .culture-grid-container {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    /* Mobile: 1 column (< 768px) */
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
        background-color: #050505; /* Void Black */
        border: 1px solid #333333; /* Brutal Border */
        padding: 1.5rem;
        min-height: 200px;
        display: flex;
        flex-direction: column;
    }

    .culture-card:hover {
        border-color: #CCFF00; /* Acid Lime hover */
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
        font-family: 'Space Mono', 'JetBrains Mono', monospace;
        font-size: 0.75rem;
        font-weight: 700;
        color: #CCFF00; /* Acid Lime */
        letter-spacing: 0.1em;
        margin-bottom: 1rem;
        text-transform: uppercase;
    }

    /* Headline (Playfair Display, Large) */
    .culture-card-title {
        font-family: 'Playfair Display', serif;
        font-size: 1.5rem; /* text-2xl equivalent (24px) */
        font-weight: 700;
        color: #FAFAFA; /* Off-White */
        line-height: 1.3;
        margin: 0 0 1rem 0;
        letter-spacing: -0.01em;
    }

    /* Excerpt (Mono Font, Brutal Grey) */
    .culture-card-excerpt {
        font-family: 'Space Mono', 'JetBrains Mono', monospace;
        font-size: 0.875rem;
        color: #999999; /* Brutal Grey */
        line-height: 1.6;
        margin: 0 0 auto 0; /* Push date to bottom */
        flex-grow: 1;
    }

    /* Date Only (No Author) */
    .culture-card-meta {
        font-family: 'Space Mono', 'JetBrains Mono', monospace;
        font-size: 0.75rem;
        color: #999999; /* Brutal Grey */
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid #333333; /* Subtle divider */
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
        outline: 2px solid #CCFF00; /* Acid Lime */
        outline-offset: 2px;
    }

    .culture-card-link:focus {
        outline: none; /* Remove default, handled by parent */
    }
</style>

<?php wp_reset_postdata(); ?>
