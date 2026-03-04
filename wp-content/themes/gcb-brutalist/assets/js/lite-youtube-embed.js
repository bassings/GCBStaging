/**
 * Lite YouTube Embed
 *
 * A lightweight YouTube embed facade that loads the full iframe only when clicked.
 * Reduces initial page load by ~800KB+ per video.
 *
 * Based on: https://github.com/paulirish/lite-youtube-embed
 * Optimized for GCB Brutalist theme.
 *
 * @package GCB_Brutalist
 */

class LiteYouTubeEmbed extends HTMLElement {
	constructor() {
		super();
		this.iframeLoaded = false;
	}

	connectedCallback() {
		const videoId = this.getAttribute('videoid');
		if (!videoId) {
			console.warn('lite-youtube: missing videoid attribute');
			return;
		}

		// Set up poster image with fallback chain
		this.loadPosterWithFallback(videoId);

		// Create play button
		const playBtn = document.createElement('button');
		playBtn.type = 'button';
		playBtn.classList.add('lyt-playbtn');
		playBtn.setAttribute('aria-label', 'Play video: ' + (this.getAttribute('title') || 'YouTube video'));
		this.appendChild(playBtn);

		// Set up click handler
		this.addEventListener('click', this.addIframe.bind(this));

		// Keyboard support
		this.setAttribute('tabindex', '0');
		this.addEventListener('keydown', function(e) {
			if (e.key === 'Enter' || e.key === ' ') {
				e.preventDefault();
				this.addIframe();
			}
		}.bind(this));
	}

	/**
	 * Try thumbnail resolutions in order: maxresdefault > sddefault > hqdefault
	 * Not all videos have maxresdefault (1280x720), so we fall back gracefully.
	 */
	loadPosterWithFallback(videoId) {
		var self = this;
		var base = 'https://i.ytimg.com/vi/' + videoId + '/';
		var fallbacks = [
			base + 'maxresdefault.jpg',
			base + 'sddefault.jpg',
			base + 'hqdefault.jpg'
		];

		function tryLoad(index) {
			if (index >= fallbacks.length) return;
			var img = new Image();
			img.onload = function() {
				// YouTube returns a 120x90 gray placeholder for missing thumbs;
				// real maxresdefault is 1280x720, sddefault is 640x480, hqdefault is 480x360
				if (img.naturalWidth <= 120 && index < fallbacks.length - 1) {
					tryLoad(index + 1);
				} else {
					self.style.backgroundImage = 'url("' + fallbacks[index] + '")';
				}
			};
			img.onerror = function() { tryLoad(index + 1); };
			img.src = fallbacks[index];
		}

		// Set hqdefault immediately as a safe baseline, then upgrade if better exists
		this.style.backgroundImage = 'url("' + base + 'hqdefault.jpg")';
		tryLoad(0);
	}

	addIframe() {
		if (this.iframeLoaded) return;

		var videoId = this.getAttribute('videoid');
		var params = new URLSearchParams(this.getAttribute('params') || 'autoplay=1');

		// Ensure autoplay is enabled when user clicks
		if (!params.has('autoplay')) {
			params.set('autoplay', '1');
		}

		var iframe = document.createElement('iframe');
		iframe.width = '560';
		iframe.height = '315';
		iframe.src = 'https://www.youtube-nocookie.com/embed/' + videoId + '?' + params.toString();
		iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share';
		iframe.allowFullscreen = true;
		iframe.title = this.getAttribute('title') || 'YouTube video player';

		// Replace button with iframe
		this.innerHTML = '';
		this.appendChild(iframe);
		this.classList.add('lyt-activated');
		this.iframeLoaded = true;
	}
}

// Register custom element
if ('customElements' in window) {
	customElements.define('lite-youtube', LiteYouTubeEmbed);
} else {
	console.warn('lite-youtube: Custom Elements not supported in this browser');
}
