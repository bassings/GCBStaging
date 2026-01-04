<?php
/**
 * Title: Header Navigation
 * Slug: gcb-brutalist/header-navigation
 * Categories: header
 * Description: Sticky header with desktop navigation and mobile slide-out menu
 *
 * Editorial Brutalism Navigation:
 * - Sticky header with scroll shadow
 * - Logo: "GCB" in Playfair Display
 * - Desktop: Horizontal nav links + terminal search button
 * - Mobile: 256px slide-out drawer from left with overlay
 * - Vanilla JavaScript (no jQuery)
 * - Full ARIA support and keyboard navigation
 */
?>

<!-- Skip Navigation Link (WCAG 2.4.1 - Bypass Blocks) -->
<a href="#main-content" class="skip-link">Skip to main content</a>

<!-- Sticky Header with Mobile Menu -->
<header class="site-header" role="banner">
	<div class="header-wrapper">
		<!-- Logo -->
		<div class="site-logo">
			<a href="<?php echo esc_url(home_url('/')); ?>" aria-label="GCB Home">
				<span class="logo-text">GCB</span>
			</a>
		</div>

		<!-- Desktop Navigation -->
		<nav class="main-nav" role="navigation" aria-label="Main Navigation">
			<?php
			wp_nav_menu(
				array(
					'theme_location'  => 'primary',
					'container'       => false,
					'items_wrap'      => '%3$s',
					'walker'          => new GCB_Nav_Walker(),
					'link_class'      => 'nav-link',
					'fallback_cb'     => 'gcb_primary_menu_fallback',
					'depth'           => 1,
				)
			);
			?>
		</nav>

		<!-- Terminal Search Button (Desktop) -->
		<button class="search-toggle" aria-label="Search">
			<span class="search-prompt">&gt;_</span>
			<span>Search</span>
		</button>

		<!-- Mobile Menu Toggle -->
		<button class="menu-toggle" aria-label="Open Menu" aria-expanded="false" aria-controls="mobile-menu">
			<span class="hamburger-icon">
				<span class="line"></span>
				<span class="line"></span>
				<span class="line"></span>
			</span>
			<span class="sr-only">Menu</span>
		</button>
	</div>
</header>

<!-- Mobile Menu Overlay -->
<div class="menu-overlay" data-overlay="mobile-menu" aria-hidden="true"></div>

<!-- Mobile Menu Drawer -->
<nav class="mobile-menu" data-menu="mobile" id="mobile-menu" aria-label="Mobile Navigation" aria-hidden="true">
	<div class="mobile-menu-content">
		<div class="mobile-menu-header">
			<span class="mobile-menu-title">GCB</span>
			<button class="menu-close" aria-label="Close Menu">
				<span class="close-icon">&times;</span>
			</button>
		</div>

		<div class="mobile-menu-links">
			<?php
			wp_nav_menu(
				array(
					'theme_location'  => 'primary',
					'container'       => false,
					'items_wrap'      => '%3$s',
					'walker'          => new GCB_Nav_Walker(),
					'link_class'      => 'mobile-nav-link',
					'fallback_cb'     => 'gcb_primary_menu_fallback',
					'depth'           => 1,
				)
			);
			?>

			<!-- Mobile Search Button (North Star) -->
			<button class="mobile-search-toggle" aria-label="Search">
				<span class="search-prompt">&gt;_</span>
				<span>Search</span>
			</button>
		</div>
	</div>
</nav>

<style>
	/* ===== SKIP NAVIGATION LINK (WCAG 2.4.1) ===== */
	.skip-link {
		position: absolute;
		top: -40px;
		left: 0;
		z-index: 1000;
		padding: 0.75rem 1.5rem;
		background-color: #CCFF00; /* Acid Lime */
		color: #050505; /* Void Black */
		font-family: 'Space Mono', monospace;
		font-size: 0.875rem;
		font-weight: 700;
		text-transform: uppercase;
		text-decoration: none;
		border: 2px solid #050505;
		
	}

	.skip-link:focus {
		top: 0;
		outline: 2px solid #CCFF00;
		outline-offset: 2px;
	}

	/* ===== STICKY HEADER ===== */
	.site-header {
		position: sticky;
		top: 0;
		z-index: 100;
		background-color: #050505; /* Void Black */
		border-bottom: 2px solid #333333; /* Brutal Border */
		
	}

	/* Shadow on scroll (applied via JS) */
	.site-header.scrolled {
		box-shadow: 0 2px 0 #333333; /* Brutal Border shadow */
	}

	.header-wrapper {
		max-width: 1280px;
		margin: 0 auto;
		padding: 1rem 1.5rem;
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 2rem;
	}

	/* ===== LOGO ===== */
	.site-logo {
		flex-shrink: 0;
	}

	.site-logo a {
		display: inline-block;
		min-height: 44px; /* WCAG 2.5.8 touch target */
		padding: 0.5rem 0; /* Ensure minimum touch target size */
		text-decoration: none;
		color: #FAFAFA; /* Off-White */
		line-height: 1.2;
	}

	.logo-text {
		font-family: 'Playfair Display', serif;
		font-size: 2rem;
		font-weight: 700;
		letter-spacing: 0.05em;
	}

	/* ===== DESKTOP NAVIGATION ===== */
	.main-nav {
		display: none; /* Hidden on mobile, shown on desktop */
		gap: 2rem;
	}

	.nav-link {
		font-family: 'Space Mono', monospace;
		font-size: 0.875rem;
		font-weight: 400;
		color: #FAFAFA; /* Off-White */
		text-decoration: none;
		text-transform: uppercase;
		letter-spacing: 0.05em;
		
	}

	.nav-link:hover,
	.nav-link:focus {
		color: #CCFF00; /* Acid Lime */
		outline: none;
	}

	.nav-link:focus-visible {
		outline: 2px solid #CCFF00; /* Acid Lime */
		outline-offset: 4px;
	}

	/* ===== TERMINAL SEARCH BUTTON ===== */
	.search-toggle {
		display: none; /* Hidden on mobile, shown on desktop */
		background: transparent;
		border: 1px solid #333333; /* Brutal Border */
		color: #FAFAFA; /* Off-White */
		font-family: 'Space Mono', monospace;
		font-size: 0.75rem;
		text-transform: uppercase;
		padding: 0.5rem 1rem;
		cursor: pointer;
		gap: 0.5rem;
		align-items: center;

		min-height: 44px;
		min-width: 44px;
	}

	.search-toggle:hover {
		border-color: #CCFF00; /* Acid Lime */
		background-color: rgba(204, 255, 0, 0.1);
	}

	.search-toggle:focus-visible {
		outline: 2px solid #CCFF00; /* Acid Lime */
		outline-offset: 2px;
	}

	.search-prompt {
		font-weight: 700;
	}

	/* ===== MOBILE MENU TOGGLE ===== */
	.menu-toggle {
		display: flex; /* Shown on mobile, hidden on desktop */
		flex-direction: column;
		justify-content: center;
		align-items: center;
		background: transparent;
		border: none;
		padding: 0.75rem;
		cursor: pointer;
		min-height: 44px;
		min-width: 44px;
	}

	.hamburger-icon {
		display: flex;
		flex-direction: column;
		gap: 4px;
		width: 24px;
	}

	.hamburger-icon .line {
		display: block;
		width: 100%;
		height: 2px;
		background-color: #FAFAFA; /* Off-White */
		transition: all 0.3s ease;
	}

	.menu-toggle[aria-expanded="true"] .hamburger-icon .line:nth-child(1) {
		transform: rotate(45deg) translate(6px, 6px);
	}

	.menu-toggle[aria-expanded="true"] .hamburger-icon .line:nth-child(2) {
		opacity: 0;
	}

	.menu-toggle[aria-expanded="true"] .hamburger-icon .line:nth-child(3) {
		transform: rotate(-45deg) translate(6px, -6px);
	}

	/* Screen reader only text */
	.sr-only {
		position: absolute;
		width: 1px;
		height: 1px;
		padding: 0;
		margin: -1px;
		overflow: hidden;
		clip: rect(0, 0, 0, 0);
		white-space: nowrap;
		border-width: 0;
	}

	/* ===== MOBILE MENU OVERLAY ===== */
	.menu-overlay {
		position: fixed;
		top: 0;
		left: 0;
		right: 0;
		bottom: 0;
		background-color: rgba(5, 5, 5, 0.8); /* Void Black with transparency */
		z-index: 90;
		opacity: 0;
		visibility: hidden;
		transition: opacity 0.3s ease, visibility 0.3s ease;
	}

	.menu-overlay.active {
		opacity: 1;
		visibility: visible;
	}

	/* ===== MOBILE MENU DRAWER ===== */
	.mobile-menu {
		position: fixed;
		top: 0;
		left: 0;
		bottom: 0;
		width: 256px;
		background-color: #050505; /* Void Black */
		border-right: 2px solid #333333; /* Brutal Border */
		z-index: 95;
		transform: translateX(-100%);
		visibility: hidden;
		transition: transform 0.3s ease, visibility 0.3s ease;
		overflow-y: auto;
	}

	.mobile-menu.active {
		transform: translateX(0);
		visibility: visible;
	}

	.mobile-menu-content {
		padding: 1.5rem;
	}

	.mobile-menu-header {
		display: flex;
		justify-content: space-between;
		align-items: center;
		margin-bottom: 2rem;
		padding-bottom: 1rem;
		border-bottom: 1px solid #333333; /* Brutal Border */
	}

	.mobile-menu-title {
		font-family: 'Playfair Display', serif;
		font-size: 1.5rem;
		font-weight: 700;
		color: #FAFAFA; /* Off-White */
	}

	.menu-close {
		background: transparent;
		border: none;
		color: #FAFAFA; /* Off-White */
		font-size: 2rem;
		line-height: 1;
		cursor: pointer;
		padding: 0.5rem;
		min-height: 44px;
		min-width: 44px;
		display: flex;
		align-items: center;
		justify-content: center;
	}

	.menu-close:hover {
		color: #CCFF00; /* Acid Lime */
	}

	.close-icon {
		display: block;
	}

	.mobile-menu-links {
		display: flex;
		flex-direction: column;
		gap: 0.5rem;
	}

	.mobile-nav-link {
		font-family: 'Space Mono', monospace;
		font-size: 1rem;
		font-weight: 400;
		color: #FAFAFA; /* Off-White */
		text-decoration: none;
		text-transform: uppercase;
		letter-spacing: 0.05em;
		padding: 1rem;
		padding-bottom: 1rem; /* North Star: border-bottom spacing */
		border: 1px solid transparent;
		border-bottom: 1px solid #333333; /* North Star: Brutal Border bottom on each link */

		min-height: 44px;
		display: flex;
		align-items: center;
	}

	.mobile-nav-link:hover,
	.mobile-nav-link:focus {
		color: #CCFF00; /* Acid Lime */
		border-color: #333333; /* Brutal Border */
		background-color: rgba(204, 255, 0, 0.05);
		outline: none;
	}

	.mobile-nav-link:focus-visible {
		outline: 2px solid #CCFF00; /* Acid Lime */
		outline-offset: 2px;
	}

	/* ===== MOBILE SEARCH TOGGLE (North Star) ===== */
	.mobile-search-toggle {
		font-family: 'Space Mono', monospace;
		font-size: 0.875rem;
		font-weight: 400;
		color: #FAFAFA; /* Off-White */
		text-transform: uppercase;
		letter-spacing: 0.05em;
		padding: 0.75rem 1rem;
		margin-top: 1.5rem; /* North Star: mt-6 spacing */
		border: 1px solid #333333; /* Brutal Border */
		background-color: transparent;
		cursor: pointer;
		display: flex;
		gap: 0.5rem;
		align-items: center;
		justify-content: center;
		min-height: 44px;
		width: 100%;
	}

	.mobile-search-toggle:hover {
		border-color: #CCFF00; /* Acid Lime */
		background-color: rgba(204, 255, 0, 0.1);
	}

	.mobile-search-toggle:focus-visible {
		outline: 2px solid #CCFF00; /* Acid Lime */
		outline-offset: 2px;
	}

	.mobile-search-toggle .search-prompt {
		color: #CCFF00; /* Acid Lime */
		font-weight: 700;
	}

	/* ===== BODY SCROLL LOCK ===== */
	body.menu-open {
		overflow: hidden;
	}

	/* ===== RESPONSIVE BREAKPOINTS ===== */

	/* Tablet: Show nav, hide menu toggle */
	@media (min-width: 768px) {
		.main-nav {
			display: flex;
		}

		.search-toggle {
			display: flex;
		}

		.menu-toggle {
			display: none;
		}

		/* Mobile menu should not be accessible on desktop */
		.mobile-menu,
		.menu-overlay {
			display: none;
		}
	}

	/* Desktop: Full layout */
	@media (min-width: 1024px) {
		.header-wrapper {
			padding: 1.25rem 2rem;
		}

		.logo-text {
			font-size: 2.5rem;
		}

		.nav-link {
			font-size: 1rem;
		}
	}
</style>

<script>
	// Navigation JavaScript - Vanilla JS (No jQuery)
	(function() {
		'use strict';

		// Elements
		const header = document.querySelector('.site-header');
		const menuToggle = document.querySelector('.menu-toggle');
		const menuClose = document.querySelector('.menu-close');
		const mobileMenu = document.querySelector('.mobile-menu');
		const menuOverlay = document.querySelector('.menu-overlay');
		const body = document.body;

		// Sticky header scroll shadow
		function handleScroll() {
			if (window.scrollY > 0) {
				header.classList.add('scrolled');
			} else {
				header.classList.remove('scrolled');
			}
		}

		// Open mobile menu
		function openMenu() {
			mobileMenu.classList.add('active');
			menuOverlay.classList.add('active');
			menuToggle.setAttribute('aria-expanded', 'true');
			mobileMenu.setAttribute('aria-hidden', 'false');
			menuOverlay.setAttribute('aria-hidden', 'false');
			body.classList.add('menu-open');

			// Trap focus in menu
			const firstFocusable = mobileMenu.querySelector('button, a');
			if (firstFocusable) {
				firstFocusable.focus();
			}
		}

		// Close mobile menu
		function closeMenu() {
			mobileMenu.classList.remove('active');
			menuOverlay.classList.remove('active');
			menuToggle.setAttribute('aria-expanded', 'false');
			mobileMenu.setAttribute('aria-hidden', 'true');
			menuOverlay.setAttribute('aria-hidden', 'true');
			body.classList.remove('menu-open');

			// Return focus to menu toggle
			menuToggle.focus();
		}

		// Toggle menu
		function toggleMenu() {
			const isOpen = menuToggle.getAttribute('aria-expanded') === 'true';
			if (isOpen) {
				closeMenu();
			} else {
				openMenu();
			}
		}

		// Event Listeners
		if (menuToggle) {
			menuToggle.addEventListener('click', toggleMenu);
		}

		if (menuClose) {
			menuClose.addEventListener('click', closeMenu);
		}

		if (menuOverlay) {
			menuOverlay.addEventListener('click', closeMenu);
		}

		// Close menu when clicking nav links (event delegation for dynamic content)
		if (mobileMenu) {
			mobileMenu.addEventListener('click', (e) => {
				// Check if clicked element is a mobile nav link
				if (e.target.classList.contains('mobile-nav-link')) {
					closeMenu();
				}
			});
		}

		// Close menu on ESC key
		document.addEventListener('keydown', (e) => {
			if (e.key === 'Escape' && mobileMenu.classList.contains('active')) {
				closeMenu();
			}
		});

		// Add scroll shadow to header
		window.addEventListener('scroll', handleScroll);

		// Initialize
		handleScroll();

	})();
</script>
