/**
 * Spectra Gallery Image Preloading
 * 
 * Improves carousel UX by preloading nearby images:
 * 1. First 3 images in each gallery load eagerly (no lazy)
 * 2. When gallery scrolls, preload upcoming images
 * 3. Reveals gallery smoothly when ready
 */

(function() {
	'use strict';

	function initGalleryPreloading() {
		document.querySelectorAll('.wp-block-uagb-image-gallery').forEach(function(gallery) {
			var images = gallery.querySelectorAll('img[loading="lazy"]');
			
			// Make first 3 images load eagerly
			for (var i = 0; i < Math.min(3, images.length); i++) {
				images[i].removeAttribute('loading');
				images[i].setAttribute('fetchpriority', i === 0 ? 'high' : 'auto');
			}
			
			// Mark gallery as initialized
			gallery.classList.add('spectra-lazy-loaded');
			
			// For carousel galleries, preload next images on scroll
			var carousel = gallery.querySelector('.spectra-image-gallery__layout--carousel');
			if (carousel) {
				var preloadedIndex = 3;
				
				carousel.addEventListener('scroll', function() {
					// Preload next 2 images when scrolling
					for (var j = preloadedIndex; j < Math.min(preloadedIndex + 2, images.length); j++) {
						if (images[j].getAttribute('loading') === 'lazy') {
							images[j].removeAttribute('loading');
						}
					}
					preloadedIndex = Math.min(preloadedIndex + 2, images.length);
				}, { passive: true });
			}
		});
	}

	// Run on DOM ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initGalleryPreloading);
	} else {
		initGalleryPreloading();
	}
})();
