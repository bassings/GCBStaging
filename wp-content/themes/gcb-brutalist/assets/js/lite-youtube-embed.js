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

		// Set up poster image (YouTube thumbnail)
		const posterUrl = `https://i.ytimg.com/vi/${videoId}/maxresdefault.jpg`;
		this.style.backgroundImage = `url("${posterUrl}")`;

		// Create play button
		const playBtn = document.createElement('button');
		playBtn.type = 'button';
		playBtn.classList.add('lyt-playbtn');
		playBtn.setAttribute('aria-label', `Play video: ${this.getAttribute('title') || 'YouTube video'}`);
		this.appendChild(playBtn);

		// Set up click handler
		this.addEventListener('click', this.addIframe.bind(this));

		// Keyboard support
		this.setAttribute('tabindex', '0');
		this.addEventListener('keydown', (e) => {
			if (e.key === 'Enter' || e.key === ' ') {
				e.preventDefault();
				this.addIframe();
			}
		});
	}

	addIframe() {
		if (this.iframeLoaded) return;

		const videoId = this.getAttribute('videoid');
		const params = new URLSearchParams(this.getAttribute('params') || 'autoplay=1');

		// Ensure autoplay is enabled when user clicks
		if (!params.has('autoplay')) {
			params.set('autoplay', '1');
		}

		const iframe = document.createElement('iframe');
		iframe.width = '560';
		iframe.height = '315';
		iframe.src = `https://www.youtube-nocookie.com/embed/${videoId}?${params.toString()}`;
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
