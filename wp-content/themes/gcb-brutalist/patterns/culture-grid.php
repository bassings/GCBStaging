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

        <!-- 4-Column Responsive Grid (carousel on mobile) -->
        <div class="culture-carousel-wrapper">
            <button class="gcb-carousel-arrow gcb-carousel-arrow--prev hidden" aria-label="Previous article">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg>
            </button>
            <div class="culture-grid-container gcb-mobile-carousel-culture" role="region" aria-label="Latest reviews and news">
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
            <button class="gcb-carousel-arrow gcb-carousel-arrow--next" aria-label="Next article">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M8.59 16.59L10 18l6-6-6-6-1.41 1.41L13.17 12z"/></svg>
            </button>
        </div><!-- .culture-carousel-wrapper -->
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

    /* Mobile: Horizontal carousel with peek */
    @media (max-width: 767px) {
        .culture-grid-container {
            /* Override grid with flex for horizontal scroll */
            display: flex !important;
            flex-wrap: nowrap !important;
            overflow-x: auto !important;
            overflow-y: hidden !important;
            scroll-snap-type: x mandatory !important;
            -webkit-overflow-scrolling: touch !important;
            gap: 1rem !important;
            /* Extend to edges for full-bleed scroll */
            margin-left: calc(-1 * var(--wp--preset--spacing--30)) !important;
            margin-right: calc(-1 * var(--wp--preset--spacing--30)) !important;
            padding-left: var(--wp--preset--spacing--30) !important;
            padding-right: var(--wp--preset--spacing--30) !important;
            padding-bottom: 0.5rem !important;
            /* Hide scrollbar but keep functionality */
            scrollbar-width: none !important;
            -ms-overflow-style: none !important;
        }
        .culture-grid-container::-webkit-scrollbar {
            display: none !important;
        }
        
        /* Carousel cards - 70% width to leave room for arrow buttons */
        .culture-grid-container > .culture-card {
            flex: 0 0 70% !important;
            min-width: 70% !important;
            max-width: 70% !important;
            scroll-snap-align: center !important;
        }
        
        /* Ensure last card has room to snap */
        .culture-grid-container::after {
            content: '';
            flex: 0 0 15%;
        }

        .culture-grid-title {
            font-size: 2rem;
        }
        
        /* Carousel wrapper with arrow button space */
        .culture-carousel-wrapper {
            position: relative;
            padding: 0 44px;
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
    
    /* Arrow buttons for culture carousel */
    @media (max-width: 767px) {
        .culture-carousel-wrapper .gcb-carousel-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 36px;
            height: 36px;
            background: var(--wp--preset--color--highlight);
            border: none;
            border-radius: 50%;
            z-index: 10;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0.9;
            -webkit-tap-highlight-color: transparent;
        }
        .culture-carousel-wrapper .gcb-carousel-arrow:active {
            opacity: 1;
            transform: translateY(-50%) scale(0.95);
        }
        .culture-carousel-wrapper .gcb-carousel-arrow svg {
            width: 20px;
            height: 20px;
            fill: var(--wp--preset--color--void-black);
        }
        .culture-carousel-wrapper .gcb-carousel-arrow--prev { left: 0; }
        .culture-carousel-wrapper .gcb-carousel-arrow--next { right: 0; }
        .culture-carousel-wrapper .gcb-carousel-arrow.hidden {
            opacity: 0.3;
            pointer-events: none;
        }
    }
    
    /* Hide arrows on tablet and desktop */
    @media (min-width: 768px) {
        .culture-carousel-wrapper {
            padding: 0 !important;
        }
        .culture-carousel-wrapper .gcb-carousel-arrow {
            display: none !important;
        }
    }
</style>

<!-- Carousel arrow functionality -->
<script>
(function() {
    'use strict';
    function initCultureCarouselArrows() {
        document.querySelectorAll('.culture-carousel-wrapper').forEach(function(wrapper) {
            var carousel = wrapper.querySelector('.gcb-mobile-carousel-culture');
            var prevBtn = wrapper.querySelector('.gcb-carousel-arrow--prev');
            var nextBtn = wrapper.querySelector('.gcb-carousel-arrow--next');
            
            if (!carousel || !prevBtn || !nextBtn) return;
            
            var cards = carousel.querySelectorAll('.culture-card');
            
            function updateArrowState() {
                var scrollLeft = carousel.scrollLeft;
                var maxScroll = carousel.scrollWidth - carousel.clientWidth;
                
                if (scrollLeft <= 10) {
                    prevBtn.classList.add('hidden');
                } else {
                    prevBtn.classList.remove('hidden');
                }
                
                if (scrollLeft >= maxScroll - 10) {
                    nextBtn.classList.add('hidden');
                } else {
                    nextBtn.classList.remove('hidden');
                }
            }
            
            function getCurrentIndex() {
                var scrollCenter = carousel.scrollLeft + carousel.clientWidth / 2;
                for (var i = 0; i < cards.length; i++) {
                    var card = cards[i];
                    var cardLeft = card.offsetLeft;
                    var cardRight = cardLeft + card.offsetWidth;
                    if (scrollCenter >= cardLeft && scrollCenter <= cardRight) {
                        return i;
                    }
                }
                return 0;
            }
            
            function scrollToCard(index) {
                if (index < 0) index = 0;
                if (index >= cards.length) index = cards.length - 1;
                var card = cards[index];
                if (card) {
                    card.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
                }
            }
            
            prevBtn.addEventListener('click', function() {
                scrollToCard(getCurrentIndex() - 1);
            });
            
            nextBtn.addEventListener('click', function() {
                scrollToCard(getCurrentIndex() + 1);
            });
            
            carousel.addEventListener('scroll', updateArrowState, { passive: true });
            updateArrowState();
        });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCultureCarouselArrows);
    } else {
        initCultureCarouselArrows();
    }
})();
</script>

<?php wp_reset_postdata(); ?>
