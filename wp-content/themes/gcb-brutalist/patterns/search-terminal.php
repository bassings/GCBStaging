<?php
/**
 * Title: Terminal Search Modal
 * Slug: gcb-brutalist/search-terminal
 * Categories: search, gcb-content
 * Description: Full-screen terminal-style search modal with Editorial Brutalism styling
 */
?>

<!-- Search Modal Overlay (hidden by default) -->
<div class="search-overlay" data-overlay="search-modal" aria-hidden="true"></div>

<!-- Search Modal -->
<div class="search-modal"
     data-modal="search"
     role="dialog"
     aria-label="Search"
     aria-hidden="true">

	<div class="search-modal__content">
		<!-- Close Button -->
		<button class="search-modal__close" aria-label="Close search">
			<span class="close-icon">&times;</span>
		</button>

		<!-- Terminal Search Form -->
		<form class="terminal-search-form"
		      role="search"
		      method="get"
		      action="<?php echo esc_url( home_url( '/' ) ); ?>">

			<!-- Terminal Input Container -->
			<div class="terminal-input-wrapper">
				<!-- CSS ::before adds ">_" prompt -->
				<input type="search"
				       name="s"
				       class="terminal-search-input"
				       placeholder="Enter search query..."
				       aria-label="Search input"
				       autocomplete="off"
				       value="<?php echo esc_attr( get_search_query() ); ?>" />

				<!-- Submit Button (Acid Lime Arrow) -->
				<button type="submit"
				        class="terminal-submit"
				        aria-label="Submit search">
					<span class="submit-icon">→</span>
				</button>
			</div>
		</form>
	</div>
</div>

<style>
/* Search Overlay (reuse mobile menu pattern) */
.search-overlay {
	position: fixed;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	background-color: rgba(5, 5, 5, 0.95); /* Near-opaque Void Black */
	z-index: 200;
	opacity: 0;
	visibility: hidden;
	transition: opacity 0.3s ease, visibility 0.3s ease;
}

.search-overlay.active {
	opacity: 1;
	visibility: visible;
}

/* Search Modal */
.search-modal {
	position: fixed;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	z-index: 205;
	display: flex;
	align-items: center;
	justify-content: center;
	opacity: 0;
	visibility: hidden;
	transition: opacity 0.3s ease, visibility 0.3s ease;
	pointer-events: none; /* Allow clicks to pass through to overlay */
}

.search-modal.active {
	opacity: 1;
	visibility: visible;
	pointer-events: auto; /* Enable clicks when active */
}

.search-modal__content {
	position: relative;
	max-width: 800px;
	width: 90%;
	padding: 2rem;
	pointer-events: auto; /* Content blocks clicks */
}

/* Close Button */
.search-modal__close {
	position: absolute;
	top: 0;
	right: 0;
	background: transparent;
	border: none;
	color: var(--wp--preset--color--off-white, #FAFAFA);
	font-size: 2.5rem;
	line-height: 1;
	cursor: pointer;
	padding: 0.5rem;
	min-height: 44px;
	min-width: 44px;
}

.search-modal__close:hover {
	color: var(--wp--preset--color--acid-lime, #CCFF00);
}

.search-modal__close:focus {
	outline: 2px solid var(--wp--preset--color--acid-lime, #CCFF00);
	outline-offset: 2px;
}

/* Terminal Search Form */
.terminal-search-form {
	width: 100%;
}

.terminal-input-wrapper {
	position: relative;
	display: flex;
	align-items: center;
	background: var(--wp--preset--color--void-black, #050505);
	border: 1px solid var(--wp--preset--color--brutal-border, #333333);
	padding: 1rem;
	gap: 1rem;
}

/* Terminal Prompt (CSS ::before) */
.terminal-input-wrapper::before {
	content: ">_";
	font-family: var(--wp--preset--font-family--mono, 'Space Mono', monospace);
	font-size: 1.5rem;
	font-weight: 700;
	color: var(--wp--preset--color--acid-lime, #CCFF00);
	flex-shrink: 0;
}

/* Search Input */
.terminal-search-input {
	flex: 1;
	background: transparent;
	border: none;
	color: var(--wp--preset--color--acid-lime, #CCFF00);
	font-family: var(--wp--preset--font-family--mono, 'Space Mono', monospace);
	font-size: 1.5rem;
	outline: none;
	padding: 0;
}

.terminal-search-input::placeholder {
	color: rgba(204, 255, 0, 0.5); /* 50% Acid Lime */
}

.terminal-search-input:focus {
	outline: 2px solid var(--wp--preset--color--acid-lime, #CCFF00);
	outline-offset: 4px;
}

/* Submit Button */
.terminal-submit {
	background: transparent;
	border: 1px solid var(--wp--preset--color--brutal-border, #333333);
	color: var(--wp--preset--color--acid-lime, #CCFF00);
	font-size: 1.5rem;
	padding: 0.5rem 1rem;
	cursor: pointer;
	min-height: 44px;
	min-width: 44px;
	transition: none;
}

.terminal-submit:hover {
	background: rgba(204, 255, 0, 0.1);
	border-color: var(--wp--preset--color--acid-lime, #CCFF00);
}

.terminal-submit:focus {
	outline: 2px solid var(--wp--preset--color--acid-lime, #CCFF00);
	outline-offset: 2px;
}

/* Responsive */
@media (max-width: 768px) {
	.search-modal__content {
		width: 95%;
		padding: 1rem;
	}

	.terminal-input-wrapper::before,
	.terminal-search-input {
		font-size: 1.25rem;
	}

	.search-modal {
		align-items: flex-start;
		padding-top: 20vh; /* Keep input visible above keyboard */
	}
}
</style>

<script>
(function() {
	'use strict';

	const searchToggles = document.querySelectorAll('.search-toggle, .mobile-search-toggle');
	const searchModal = document.querySelector('.search-modal');
	const searchOverlay = document.querySelector('.search-overlay');
	const searchClose = document.querySelector('.search-modal__close');
	const searchInput = document.querySelector('.terminal-search-input');
	const body = document.body;

	function openSearch() {
		// Close mobile menu if open (mutual exclusion)
		const mobileMenu = document.querySelector('.mobile-menu');
		if (mobileMenu && mobileMenu.classList.contains('active')) {
			const menuOverlay = document.querySelector('.menu-overlay');
			mobileMenu.classList.remove('active');
			if (menuOverlay) menuOverlay.classList.remove('active');
			mobileMenu.setAttribute('aria-hidden', 'true');
		}

		// Open search modal
		searchModal.classList.add('active');
		searchOverlay.classList.add('active');
		searchModal.setAttribute('aria-hidden', 'false');
		searchOverlay.setAttribute('aria-hidden', 'false');
		body.classList.add('menu-open'); // Reuse scroll lock

		// Focus input
		if (searchInput) {
			searchInput.focus();
		}
	}

	function closeSearch() {
		searchModal.classList.remove('active');
		searchOverlay.classList.remove('active');
		searchModal.setAttribute('aria-hidden', 'true');
		searchOverlay.setAttribute('aria-hidden', 'true');
		body.classList.remove('menu-open');
	}

	// Focus trap to prevent Tab escape
	function trapFocus(element) {
		const focusableElements = element.querySelectorAll(
			'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
		);
		const firstElement = focusableElements[0];
		const lastElement = focusableElements[focusableElements.length - 1];

		element.addEventListener('keydown', function(e) {
			if (e.key !== 'Tab') return;

			if (e.shiftKey) {
				if (document.activeElement === firstElement) {
					lastElement.focus();
					e.preventDefault();
				}
			} else {
				if (document.activeElement === lastElement) {
					firstElement.focus();
					e.preventDefault();
				}
			}
		});
	}

	// Wire up toggle buttons
	searchToggles.forEach(function(toggle) {
		toggle.addEventListener('click', openSearch);
	});

	// Close button
	if (searchClose) {
		searchClose.addEventListener('click', closeSearch);
	}

	// Overlay click
	if (searchOverlay) {
		searchOverlay.addEventListener('click', closeSearch);
	}

	// Modal background click (when clicking outside content)
	if (searchModal) {
		searchModal.addEventListener('click', function(e) {
			// Only close if clicking the modal itself, not its children
			if (e.target === searchModal) {
				closeSearch();
			}
		});
	}

	// ESC key
	document.addEventListener('keydown', function(e) {
		if (e.key === 'Escape' && searchModal.classList.contains('active')) {
			closeSearch();
		}
	});

	// Search query persistence (for back button navigation)
	const urlParams = new URLSearchParams(window.location.search);
	const existingQuery = urlParams.get('s');
	if (existingQuery && searchInput) {
		searchInput.value = existingQuery;
	}

	// Set up focus trap once
	if (searchModal) {
		trapFocus(searchModal);
	}
})();
</script>
