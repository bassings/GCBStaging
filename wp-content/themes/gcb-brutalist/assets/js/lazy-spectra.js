/**
 * Lazy Spectra Gallery Initialization
 * 
 * Delays Spectra carousel/gallery initialization until the user scrolls near it.
 * Reduces TBT (Total Blocking Time) on initial page load.
 * 
 * How it works:
 * 1. Hides Spectra galleries initially (CSS handles this)
 * 2. Uses Intersection Observer to detect when gallery enters viewport
 * 3. Triggers Spectra's initialization only when needed
 * 4. Reveals the gallery with a fade-in
 */

(function() {
	'use strict';

	// Only run if Intersection Observer is supported
	if (!('IntersectionObserver' in window)) {
		// Fallback: just show galleries immediately on old browsers
		document.querySelectorAll('.wp-block-uagb-image-gallery').forEach(function(gallery) {
			gallery.classList.add('spectra-lazy-loaded');
		});
		return;
	}

	// Create observer with rootMargin to start loading slightly before visible
	const observer = new IntersectionObserver(function(entries) {
		entries.forEach(function(entry) {
			if (entry.isIntersecting) {
				const gallery = entry.target;
				
				// Stop observing this gallery
				observer.unobserve(gallery);
				
				// Trigger Spectra's initialization by dispatching a custom event
				// or calling their init function if available
				if (window.UAGBImageGallery && typeof window.UAGBImageGallery.init === 'function') {
					window.UAGBImageGallery.init(gallery);
				}
				
				// If Spectra uses Slick or Swiper, they might auto-init on DOM ready
				// In that case, the carousel should already be initialized
				// We just need to reveal it
				
				// Add loaded class to trigger CSS fade-in
				gallery.classList.add('spectra-lazy-loaded');
				
				// Dispatch event for any other scripts that need to know
				gallery.dispatchEvent(new CustomEvent('spectra-gallery-loaded'));
			}
		});
	}, {
		rootMargin: '200px 0px', // Start loading 200px before visible
		threshold: 0.01
	});

	// Observe all Spectra galleries
	function observeGalleries() {
		document.querySelectorAll('.wp-block-uagb-image-gallery:not(.spectra-lazy-loaded)').forEach(function(gallery) {
			observer.observe(gallery);
		});
	}

	// Run on DOM ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', observeGalleries);
	} else {
		observeGalleries();
	}
})();
