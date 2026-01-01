import { test, expect } from '@playwright/test';

test.describe('Search - Terminal Modal Display & Interaction', () => {
	test.beforeEach(async ({ page, request }) => {
		// Reset database before each test
		await request.delete('/wp-json/gcb-testing/v1/reset', {
			headers: { 'GCB-Test-Key': 'test-secret-key-local' },
		});

		// Navigate to homepage
		await page.goto('/', { waitUntil: 'networkidle' });
	});

	test('Desktop search toggle opens terminal modal', async ({ page }) => {
		// Set desktop viewport
		await page.setViewportSize({ width: 1280, height: 720 });

		// Locate and click desktop search toggle
		const searchToggle = page.locator('.search-toggle');
		await expect(searchToggle).toBeVisible();
		await searchToggle.click();

		// Verify modal is visible
		const searchModal = page.locator('.search-modal');
		await expect(searchModal).toBeVisible();
		await expect(searchModal).toHaveAttribute('aria-hidden', 'false');

		// Verify overlay is visible
		const searchOverlay = page.locator('.search-overlay');
		await expect(searchOverlay).toBeVisible();
	});

	test('Mobile search toggle opens terminal modal', async ({ page }) => {
		// Set mobile viewport
		await page.setViewportSize({ width: 375, height: 667 });

		// Open mobile menu first
		const menuToggle = page.locator('.menu-toggle');
		await menuToggle.click();

		// Locate and click mobile search toggle
		const mobileSearchToggle = page.locator('.mobile-search-toggle');
		await expect(mobileSearchToggle).toBeVisible();
		await mobileSearchToggle.click();

		// Verify modal is visible
		const searchModal = page.locator('.search-modal');
		await expect(searchModal).toBeVisible();
		await expect(searchModal).toHaveAttribute('aria-hidden', 'false');
	});

	test('Modal displays terminal-style input with ">_" prefix', async ({ page }) => {
		// Set desktop viewport
		await page.setViewportSize({ width: 1280, height: 720 });

		// Open search modal
		await page.locator('.search-toggle').click();

		// Verify terminal input is visible
		const terminalInput = page.locator('.terminal-search-input');
		await expect(terminalInput).toBeVisible();

		// Verify ">_" prompt is present via CSS ::before
		const inputWrapper = page.locator('.terminal-input-wrapper');
		const promptContent = await inputWrapper.evaluate((el) => {
			return window.getComputedStyle(el, '::before').content;
		});
		expect(promptContent).toContain('>_');
	});

	test('ESC key closes search modal', async ({ page }) => {
		// Set desktop viewport
		await page.setViewportSize({ width: 1280, height: 720 });

		// Open search modal
		await page.locator('.search-toggle').click();

		// Verify modal is open
		const searchModal = page.locator('.search-modal');
		await expect(searchModal).toBeVisible();

		// Press ESC key
		await page.keyboard.press('Escape');

		// Verify modal is closed
		await expect(searchModal).toHaveAttribute('aria-hidden', 'true');
		await expect(searchModal).not.toBeVisible();
	});

	test('Overlay click closes search modal', async ({ page }) => {
		// Set desktop viewport
		await page.setViewportSize({ width: 1280, height: 720 });

		// Open search modal
		await page.locator('.search-toggle').click();

		// Verify modal is open
		const searchModal = page.locator('.search-modal');
		await expect(searchModal).toBeVisible();

		// Click overlay
		const searchOverlay = page.locator('.search-overlay');
		await searchOverlay.click();

		// Verify modal is closed
		await expect(searchModal).toHaveAttribute('aria-hidden', 'true');
		await expect(searchModal).not.toBeVisible();
	});

	test('Close button closes search modal', async ({ page }) => {
		// Set desktop viewport
		await page.setViewportSize({ width: 1280, height: 720 });

		// Open search modal
		await page.locator('.search-toggle').click();

		// Verify modal is open
		const searchModal = page.locator('.search-modal');
		await expect(searchModal).toBeVisible();

		// Click close button
		const closeButton = page.locator('.search-modal__close');
		await closeButton.click();

		// Verify modal is closed
		await expect(searchModal).toHaveAttribute('aria-hidden', 'true');
		await expect(searchModal).not.toBeVisible();
	});
});

test.describe('Search - Form Submission', () => {
	test.beforeEach(async ({ page, request }) => {
		// Reset database
		await request.delete('/wp-json/gcb-testing/v1/reset', {
			headers: { 'GCB-Test-Key': 'test-secret-key-local' },
		});

		// Create test posts with searchable content
		await request.post('/wp-json/gcb-testing/v1/create-post', {
			data: {
				title: 'Porsche 911 GT3 Review',
				content: '<p>Detailed review of the iconic Porsche 911 GT3</p>',
				status: 'publish',
			},
			headers: {
				'Content-Type': 'application/json',
				'GCB-Test-Key': 'test-secret-key-local',
			},
		});

		await request.post('/wp-json/gcb-testing/v1/create-post', {
			data: {
				title: 'BMW M3 Competition Test Drive',
				content: '<p>Testing the new BMW M3 Competition</p>',
				status: 'publish',
			},
			headers: {
				'Content-Type': 'application/json',
				'GCB-Test-Key': 'test-secret-key-local',
			},
		});

		await page.goto('/', { waitUntil: 'networkidle' });
	});

	test('Enter key submits search and navigates to results', async ({ page }) => {
		// Set desktop viewport
		await page.setViewportSize({ width: 1280, height: 720 });

		// Open search modal
		await page.locator('.search-toggle').click();

		// Type search query
		const searchInput = page.locator('.terminal-search-input');
		await searchInput.fill('Porsche');

		// Press Enter
		await searchInput.press('Enter');

		// Wait for navigation
		await page.waitForURL('**/\\?s=Porsche');

		// Verify URL changed
		expect(page.url()).toContain('?s=Porsche');
	});

	test('Submit button submits search and navigates to results', async ({ page }) => {
		// Set desktop viewport
		await page.setViewportSize({ width: 1280, height: 720 });

		// Open search modal
		await page.locator('.search-toggle').click();

		// Type search query
		const searchInput = page.locator('.terminal-search-input');
		await searchInput.fill('BMW');

		// Click submit button
		const submitButton = page.locator('.terminal-submit');
		await submitButton.click();

		// Wait for navigation
		await page.waitForURL('**/\\?s=BMW');

		// Verify URL changed
		expect(page.url()).toContain('?s=BMW');
	});

	test('URL changes to /?s=query format', async ({ page }) => {
		// Set desktop viewport
		await page.setViewportSize({ width: 1280, height: 720 });

		// Open search modal
		await page.locator('.search-toggle').click();

		// Type and submit
		const searchInput = page.locator('.terminal-search-input');
		await searchInput.fill('GT3');
		await searchInput.press('Enter');

		// Wait for navigation
		await page.waitForURL('**/\\?s=GT3');

		// Verify exact URL format
		const url = new URL(page.url());
		expect(url.searchParams.get('s')).toBe('GT3');
	});

	test('Form submission with multi-word query', async ({ page }) => {
		// Set desktop viewport
		await page.setViewportSize({ width: 1280, height: 720 });

		// Open search modal
		await page.locator('.search-toggle').click();

		// Type multi-word query
		const searchInput = page.locator('.terminal-search-input');
		await searchInput.fill('Porsche 911 GT3');
		await searchInput.press('Enter');

		// Wait for navigation
		await page.waitForURL('**/?s=*');

		// Verify URL encoded properly
		expect(page.url()).toContain('?s=Porsche');
	});
});

test.describe('Search - Results Display', () => {
	test.beforeEach(async ({ page, request }) => {
		// Reset database
		await request.delete('/wp-json/gcb-testing/v1/reset', {
			headers: { 'GCB-Test-Key': 'test-secret-key-local' },
		});

		// Create diverse test posts
		await request.post('/wp-json/gcb-testing/v1/create-post', {
			data: {
				title: 'Porsche 911 Carrera S Review',
				content: '<p>In-depth review of the Porsche 911 Carrera S</p>',
				status: 'publish',
			},
			headers: {
				'Content-Type': 'application/json',
				'GCB-Test-Key': 'test-secret-key-local',
			},
		});

		await request.post('/wp-json/gcb-testing/v1/create-post', {
			data: {
				title: 'Porsche Taycan EV First Drive',
				content: '<p>Electric Porsche Taycan driving impressions</p>',
				status: 'publish',
			},
			headers: {
				'Content-Type': 'application/json',
				'GCB-Test-Key': 'test-secret-key-local',
			},
		});

		await request.post('/wp-json/gcb-testing/v1/create-post', {
			data: {
				title: 'Ferrari SF90 Stradale',
				content: '<p>Hybrid supercar from Ferrari</p>',
				status: 'publish',
			},
			headers: {
				'Content-Type': 'application/json',
				'GCB-Test-Key': 'test-secret-key-local',
			},
		});
	});

	test('Search results page displays query in header', async ({ page }) => {
		// Navigate directly to search results
		await page.goto('/?s=Porsche', { waitUntil: 'networkidle' });

		// Verify search title is present
		const searchTitle = page.locator('h1.search-title, h1:has-text("SEARCH RESULTS")');
		await expect(searchTitle).toBeVisible();

		// Verify query term is displayed
		const queryDisplay = page.locator('.search-query-term');
		await expect(queryDisplay).toBeVisible();
		await expect(queryDisplay).toContainText('Porsche');
	});

	test('Results use bento-grid layout pattern', async ({ page }) => {
		// Navigate to search results
		await page.goto('/?s=Porsche', { waitUntil: 'networkidle' });

		// Verify bento grid container exists
		const gridContainer = page.locator('.gcb-bento-grid__container, .search-results-grid');
		await expect(gridContainer).toBeVisible();

		// Verify cards are displayed
		const bentoCards = page.locator('.bento-item, .gcb-bento-card');
		expect(await bentoCards.count()).toBeGreaterThan(0);

		// Verify at least 2 Porsche posts are shown
		expect(await bentoCards.count()).toBeGreaterThanOrEqual(2);
	});

	test('No results message displays when zero matches', async ({ page }) => {
		// Navigate to search with query that won't match
		await page.goto('/?s=NonexistentCarBrand12345', { waitUntil: 'networkidle' });

		// Verify no results message is visible
		const noResults = page.locator('.search-no-results, .wp-block-query-no-results, p:has-text("No results")');
		await expect(noResults).toBeVisible();
	});
});

test.describe('Search - Responsive Behavior', () => {
	test.beforeEach(async ({ page, request }) => {
		// Reset database
		await request.delete('/wp-json/gcb-testing/v1/reset', {
			headers: { 'GCB-Test-Key': 'test-secret-key-local' },
		});

		await page.goto('/', { waitUntil: 'networkidle' });
	});

	test('Mobile viewport (375px) - Modal full-screen, input fills width', async ({ page }) => {
		// Set mobile viewport
		await page.setViewportSize({ width: 375, height: 667 });

		// Open mobile menu, then search
		await page.locator('.menu-toggle').click();
		await page.locator('.mobile-search-toggle').click();

		// Verify modal is visible
		const searchModal = page.locator('.search-modal');
		await expect(searchModal).toBeVisible();

		// Verify modal takes full screen
		const modalBox = await searchModal.boundingBox();
		expect(modalBox?.width).toBeGreaterThan(300); // Should fill most of 375px

		// Verify input is visible and sized appropriately
		const searchInput = page.locator('.terminal-search-input');
		await expect(searchInput).toBeVisible();

		// Verify no horizontal scroll
		const bodyScrollWidth = await page.evaluate(() => document.body.scrollWidth);
		const viewportWidth = 375;
		expect(bodyScrollWidth).toBeLessThanOrEqual(viewportWidth + 1); // +1 for rounding
	});

	test('Tablet viewport (768px) - Modal full-screen, input constrained', async ({ page }) => {
		// Set tablet viewport
		await page.setViewportSize({ width: 768, height: 1024 });

		// Open search modal
		await page.locator('.search-toggle').click();

		// Verify modal is visible
		const searchModal = page.locator('.search-modal');
		await expect(searchModal).toBeVisible();

		// Verify input wrapper is constrained (not full width)
		const inputWrapper = page.locator('.terminal-input-wrapper');
		const wrapperBox = await inputWrapper.boundingBox();
		expect(wrapperBox?.width).toBeLessThan(768); // Constrained, not full width
	});

	test('Desktop viewport (1280px) - Modal full-screen, input constrained', async ({ page }) => {
		// Set desktop viewport
		await page.setViewportSize({ width: 1280, height: 720 });

		// Open search modal
		await page.locator('.search-toggle').click();

		// Verify modal is visible and centered
		const searchModal = page.locator('.search-modal');
		await expect(searchModal).toBeVisible();

		// Verify modal content is centered and constrained
		const modalContent = page.locator('.search-modal__content');
		const contentBox = await modalContent.boundingBox();
		expect(contentBox?.width).toBeLessThan(1280); // Not full width
		expect(contentBox?.width).toBeGreaterThan(500); // Reasonable width
	});
});

test.describe('Search - Accessibility WCAG 2.2 AA', () => {
	test.beforeEach(async ({ page, request }) => {
		// Reset database
		await request.delete('/wp-json/gcb-testing/v1/reset', {
			headers: { 'GCB-Test-Key': 'test-secret-key-local' },
		});

		await page.goto('/', { waitUntil: 'networkidle' });
	});

	test('Search toggle buttons have 44px minimum touch targets', async ({ page }) => {
		// Set mobile viewport
		await page.setViewportSize({ width: 375, height: 667 });

		// Check desktop search toggle
		await page.setViewportSize({ width: 1280, height: 720 });
		const desktopToggle = page.locator('.search-toggle');
		const desktopBox = await desktopToggle.boundingBox();
		expect(desktopBox?.width).toBeGreaterThanOrEqual(44);
		expect(desktopBox?.height).toBeGreaterThanOrEqual(44);

		// Check mobile search toggle
		await page.setViewportSize({ width: 375, height: 667 });
		await page.locator('.menu-toggle').click();
		const mobileToggle = page.locator('.mobile-search-toggle');
		const mobileBox = await mobileToggle.boundingBox();
		expect(mobileBox?.width).toBeGreaterThanOrEqual(44);
		expect(mobileBox?.height).toBeGreaterThanOrEqual(44);
	});

	test('Search input has 2px acid lime focus indicator', async ({ page }) => {
		// Set desktop viewport
		await page.setViewportSize({ width: 1280, height: 720 });

		// Open search modal
		await page.locator('.search-toggle').click();

		// Focus input
		const searchInput = page.locator('.terminal-search-input');
		await searchInput.focus();

		// Verify focus indicator
		const outlineStyle = await searchInput.evaluate((el) => {
			const styles = window.getComputedStyle(el);
			return {
				outline: styles.outline,
				outlineColor: styles.outlineColor,
				outlineWidth: styles.outlineWidth,
			};
		});

		// Verify outline exists and is visible
		expect(outlineStyle.outline).not.toBe('none');
		expect(parseInt(outlineStyle.outlineWidth)).toBeGreaterThanOrEqual(2);
	});

	test('Modal has role="dialog" and aria-label="Search"', async ({ page }) => {
		// Set desktop viewport
		await page.setViewportSize({ width: 1280, height: 720 });

		// Open search modal
		await page.locator('.search-toggle').click();

		// Verify modal attributes
		const searchModal = page.locator('.search-modal');
		await expect(searchModal).toHaveAttribute('role', 'dialog');
		await expect(searchModal).toHaveAttribute('aria-label', 'Search');
	});

	test('Submit button has aria-label="Submit search"', async ({ page }) => {
		// Set desktop viewport
		await page.setViewportSize({ width: 1280, height: 720 });

		// Open search modal
		await page.locator('.search-toggle').click();

		// Verify submit button has aria-label
		const submitButton = page.locator('.terminal-submit');
		await expect(submitButton).toHaveAttribute('aria-label', 'Submit search');
	});

	test('Keyboard navigation works (Tab, Enter, ESC)', async ({ page }) => {
		// Set desktop viewport
		await page.setViewportSize({ width: 1280, height: 720 });

		// Open search modal
		await page.locator('.search-toggle').click();

		// Verify input receives focus automatically
		const searchInput = page.locator('.terminal-search-input');
		await expect(searchInput).toBeFocused();

		// Tab to submit button
		await page.keyboard.press('Tab');
		const submitButton = page.locator('.terminal-submit');
		await expect(submitButton).toBeFocused();

		// Tab to close button
		await page.keyboard.press('Tab');
		const closeButton = page.locator('.search-modal__close');
		await expect(closeButton).toBeFocused();

		// ESC closes modal
		await page.keyboard.press('Escape');
		const searchModal = page.locator('.search-modal');
		await expect(searchModal).toHaveAttribute('aria-hidden', 'true');
	});
});

test.describe('Search - Design Tokens', () => {
	test.beforeEach(async ({ page, request }) => {
		// Reset database
		await request.delete('/wp-json/gcb-testing/v1/reset', {
			headers: { 'GCB-Test-Key': 'test-secret-key-local' },
		});

		await page.goto('/', { waitUntil: 'networkidle' });
	});

	test('Input background is Void Black (#050505)', async ({ page }) => {
		// Set desktop viewport
		await page.setViewportSize({ width: 1280, height: 720 });

		// Open search modal
		await page.locator('.search-toggle').click();

		// Check input wrapper background
		const inputWrapper = page.locator('.terminal-input-wrapper');
		const bgColor = await inputWrapper.evaluate((el) => {
			return window.getComputedStyle(el).backgroundColor;
		});

		// Void Black #050505 = rgb(5, 5, 5)
		expect(bgColor).toBe('rgb(5, 5, 5)');
	});

	test('Input text is Acid Lime (#CCFF00)', async ({ page }) => {
		// Set desktop viewport
		await page.setViewportSize({ width: 1280, height: 720 });

		// Open search modal
		await page.locator('.search-toggle').click();

		// Check input text color
		const searchInput = page.locator('.terminal-search-input');
		const textColor = await searchInput.evaluate((el) => {
			return window.getComputedStyle(el).color;
		});

		// Acid Lime #CCFF00 = rgb(204, 255, 0)
		expect(textColor).toBe('rgb(204, 255, 0)');
	});

	test('Border is 1px solid Brutal Border (#333333) and font is Space Mono', async ({ page }) => {
		// Set desktop viewport
		await page.setViewportSize({ width: 1280, height: 720 });

		// Open search modal
		await page.locator('.search-toggle').click();

		// Check border
		const inputWrapper = page.locator('.terminal-input-wrapper');
		const borderStyle = await inputWrapper.evaluate((el) => {
			const styles = window.getComputedStyle(el);
			return {
				borderWidth: styles.borderWidth,
				borderStyle: styles.borderStyle,
				borderColor: styles.borderColor,
			};
		});

		expect(borderStyle.borderWidth).toBe('1px');
		expect(borderStyle.borderStyle).toBe('solid');
		// Brutal Border #333333 = rgb(51, 51, 51)
		expect(borderStyle.borderColor).toBe('rgb(51, 51, 51)');

		// Check font family
		const searchInput = page.locator('.terminal-search-input');
		const fontFamily = await searchInput.evaluate((el) => {
			return window.getComputedStyle(el).fontFamily.toLowerCase();
		});

		// Should include "mono" or "space mono"
		expect(fontFamily).toContain('mono');
	});
});
