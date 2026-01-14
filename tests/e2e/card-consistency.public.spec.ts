import { test, expect } from '@playwright/test';

/**
 * Card Consistency E2E Tests
 *
 * Tests that card styling is consistent across all pages:
 * - Homepage Bento Grid
 * - Category archive pages
 * - Search results pages
 * - General archive pages
 *
 * All cards should match the homepage Bento Grid card style:
 * - Image at top (edge-to-edge with border-bottom)
 * - Title below (Playfair Display font)
 * - Excerpt text (brutal-grey color)
 * - Metadata row: Date + "Article" badge
 * - 2px border, hover shows highlight (acid-lime)
 */

test.describe('Card Consistency - Homepage Bento Grid', () => {
	test.beforeEach(async ({ request }) => {
		// Reset database
		await request.delete('/wp-json/gcb-testing/v1/reset', {
			headers: { 'GCB-Test-Key': 'test-secret-key-local' },
		});

		// Create posts for the bento grid
		for (let i = 0; i < 4; i++) {
			await request.post('/wp-json/gcb-testing/v1/create-post', {
				data: {
					title: `Bento Card Test ${i + 1}`,
					content: '<p>Test content for bento grid card styling verification</p>',
					status: 'publish',
				},
				headers: {
					'Content-Type': 'application/json',
					'GCB-Test-Key': 'test-secret-key-local',
				},
			});
		}
	});

	test('Homepage bento cards have 2px border', async ({ page }) => {
		await page.goto('/', { waitUntil: 'domcontentloaded' });

		const card = page.locator('.gcb-bento-card').first();
		await expect(card).toBeVisible();

		const borderStyle = await card.evaluate((el) => {
			const styles = window.getComputedStyle(el);
			return {
				width: styles.borderWidth,
				style: styles.borderStyle,
			};
		});

		expect(borderStyle.width).toContain('2px');
		expect(borderStyle.style).toBe('solid');
	});

	test('Homepage bento cards show acid-lime border on hover', async ({ page }) => {
		await page.goto('/', { waitUntil: 'domcontentloaded' });

		const card = page.locator('.gcb-bento-card').first();
		await expect(card).toBeVisible();

		// Hover over the card
		await card.hover();
		await page.waitForTimeout(100);

		const hoverBorderColor = await card.evaluate((el) => {
			return window.getComputedStyle(el).borderColor;
		});

		// Acid-lime/highlight is #CCFF00 = rgb(204, 255, 0)
		expect(hoverBorderColor).toBe('rgb(204, 255, 0)');
	});

	test('Homepage bento cards have Playfair Display title', async ({ page }) => {
		await page.goto('/', { waitUntil: 'domcontentloaded' });

		const title = page.locator('.gcb-bento-card__title').first();
		await expect(title).toBeVisible();

		const fontFamily = await title.evaluate((el) => {
			return window.getComputedStyle(el).fontFamily;
		});

		expect(fontFamily).toMatch(/Playfair/i);
	});

	test('Homepage bento cards have Article badge', async ({ page }) => {
		await page.goto('/', { waitUntil: 'domcontentloaded' });

		// The badge is in the metadata area
		const badge = page.locator('.gcb-bento-card__meta span').first();
		await expect(badge).toBeVisible();

		const badgeText = await badge.textContent();
		expect(badgeText?.toLowerCase()).toContain('article');
	});

	test('Homepage bento cards have excerpt', async ({ page }) => {
		await page.goto('/', { waitUntil: 'domcontentloaded' });

		const excerpt = page.locator('.gcb-bento-card__excerpt').first();
		await expect(excerpt).toBeVisible();

		const excerptText = await excerpt.textContent();
		expect(excerptText?.length).toBeGreaterThan(10);
	});

	test('Homepage bento cards metadata uses brutal-grey color', async ({ page }) => {
		await page.goto('/', { waitUntil: 'domcontentloaded' });

		const meta = page.locator('.gcb-bento-card__meta').first();
		await expect(meta).toBeVisible();

		const metaColor = await meta.evaluate((el) => {
			return window.getComputedStyle(el).color;
		});

		// Brutal grey #AAAAAA = rgb(170, 170, 170)
		expect(metaColor).toBe('rgb(170, 170, 170)');
	});
});

test.describe('Card Consistency - Search Results', () => {
	test.beforeEach(async ({ request }) => {
		// Reset database
		await request.delete('/wp-json/gcb-testing/v1/reset', {
			headers: { 'GCB-Test-Key': 'test-secret-key-local' },
		});
	});

	test('Search results cards have 2px border', async ({ page, request }) => {
		await request.post('/wp-json/gcb-testing/v1/create-post', {
			data: {
				title: 'Searchable Test Post',
				content: '<p>Searchable content here</p>',
				status: 'publish',
			},
			headers: {
				'Content-Type': 'application/json',
				'GCB-Test-Key': 'test-secret-key-local',
			},
		});

		await page.goto('/?s=Searchable', { waitUntil: 'domcontentloaded' });

		const card = page.locator('.bento-item.gcb-bento-card').first();
		await expect(card).toBeVisible();

		const borderStyle = await card.evaluate((el) => {
			const styles = window.getComputedStyle(el);
			return {
				width: styles.borderWidth,
				style: styles.borderStyle,
			};
		});

		expect(borderStyle.width).toContain('2px');
		expect(borderStyle.style).toBe('solid');
	});

	test('Search results cards have excerpt', async ({ page, request }) => {
		await request.post('/wp-json/gcb-testing/v1/create-post', {
			data: {
				title: 'Excerpt Test Post',
				content: '<p>This is the content that should appear as an excerpt on the search results page.</p>',
				status: 'publish',
			},
			headers: {
				'Content-Type': 'application/json',
				'GCB-Test-Key': 'test-secret-key-local',
			},
		});

		await page.goto('/?s=Excerpt', { waitUntil: 'domcontentloaded' });

		const excerpt = page.locator('.gcb-bento-card__excerpt').first();
		await expect(excerpt).toBeVisible();

		const excerptText = await excerpt.textContent();
		expect(excerptText?.length).toBeGreaterThan(10);
	});

	test('Search results cards have Article badge', async ({ page, request }) => {
		await request.post('/wp-json/gcb-testing/v1/create-post', {
			data: {
				title: 'Badge Search Post',
				content: '<p>Content for badge test</p>',
				status: 'publish',
			},
			headers: {
				'Content-Type': 'application/json',
				'GCB-Test-Key': 'test-secret-key-local',
			},
		});

		await page.goto('/?s=Badge', { waitUntil: 'domcontentloaded' });

		// The badge is in the metadata area
		const badge = page.locator('.gcb-bento-card__meta span').first();
		await expect(badge).toBeVisible();

		const badgeText = await badge.textContent();
		expect(badgeText?.toLowerCase()).toContain('article');
	});

	test('Search results cards show acid-lime border on hover', async ({ page, request }) => {
		await request.post('/wp-json/gcb-testing/v1/create-post', {
			data: {
				title: 'Hover Search Post',
				content: '<p>Hover test content</p>',
				status: 'publish',
			},
			headers: {
				'Content-Type': 'application/json',
				'GCB-Test-Key': 'test-secret-key-local',
			},
		});

		await page.goto('/?s=Hover', { waitUntil: 'domcontentloaded' });

		const card = page.locator('.bento-item.gcb-bento-card').first();
		await expect(card).toBeVisible();

		await card.hover();
		await page.waitForTimeout(100);

		const hoverBorderColor = await card.evaluate((el) => {
			return window.getComputedStyle(el).borderColor;
		});

		expect(hoverBorderColor).toBe('rgb(204, 255, 0)');
	});

	test('Search results cards have Playfair Display title', async ({ page, request }) => {
		await request.post('/wp-json/gcb-testing/v1/create-post', {
			data: {
				title: 'Font Search Post',
				content: '<p>Font test content</p>',
				status: 'publish',
			},
			headers: {
				'Content-Type': 'application/json',
				'GCB-Test-Key': 'test-secret-key-local',
			},
		});

		await page.goto('/?s=Font', { waitUntil: 'domcontentloaded' });

		const title = page.locator('.gcb-bento-card__title').first();
		await expect(title).toBeVisible();

		const fontFamily = await title.evaluate((el) => {
			return window.getComputedStyle(el).fontFamily;
		});

		expect(fontFamily).toMatch(/Playfair/i);
	});

	test('Search results responsive - 3 columns desktop, 2 tablet, 1 mobile', async ({ page, request }) => {
		// Create multiple posts
		for (let i = 0; i < 9; i++) {
			await request.post('/wp-json/gcb-testing/v1/create-post', {
				data: {
					title: `Responsive Search ${i + 1}`,
					content: '<p>Responsive test content</p>',
					status: 'publish',
				},
				headers: {
					'Content-Type': 'application/json',
					'GCB-Test-Key': 'test-secret-key-local',
				},
			});
		}

		// Desktop - 3 columns
		await page.setViewportSize({ width: 1920, height: 1080 });
		await page.goto('/?s=Responsive', { waitUntil: 'domcontentloaded' });

		const grid = page.locator('.search-results-grid.gcb-bento-grid__container');
		let gridCols = await grid.evaluate((el) => window.getComputedStyle(el).gridTemplateColumns);
		expect(gridCols.split(' ').length).toBe(3);

		// Tablet - 2 columns
		await page.setViewportSize({ width: 768, height: 1024 });
		await page.waitForTimeout(100);
		gridCols = await grid.evaluate((el) => window.getComputedStyle(el).gridTemplateColumns);
		expect(gridCols.split(' ').length).toBe(2);

		// Mobile - 1 column
		await page.setViewportSize({ width: 375, height: 667 });
		await page.waitForTimeout(100);
		gridCols = await grid.evaluate((el) => window.getComputedStyle(el).gridTemplateColumns);
		expect(gridCols.split(' ').length).toBe(1);
	});
});

test.describe('Card Consistency - Cross-Page Comparison', () => {
	test.beforeEach(async ({ request }) => {
		// Reset database
		await request.delete('/wp-json/gcb-testing/v1/reset', {
			headers: { 'GCB-Test-Key': 'test-secret-key-local' },
		});

		// Create test posts
		for (let i = 0; i < 3; i++) {
			await request.post('/wp-json/gcb-testing/v1/create-post', {
				data: {
					title: `Unified Card Test ${i + 1}`,
					content: '<p>Content for unified card testing across pages</p>',
					status: 'publish',
					categories: ['unified-test'],
				},
				headers: {
					'Content-Type': 'application/json',
					'GCB-Test-Key': 'test-secret-key-local',
				},
			});
		}
	});

	test('Homepage and category page cards have matching border width', async ({ page }) => {
		await page.setViewportSize({ width: 1920, height: 1080 });

		// Check homepage bento grid card
		await page.goto('/', { waitUntil: 'domcontentloaded' });
		const homepageCard = page.locator('.gcb-bento-card').first();
		const homepageBorder = await homepageCard.evaluate((el) => {
			return window.getComputedStyle(el).borderWidth;
		});

		// Check category page card
		await page.goto('/category/unified-test/', { waitUntil: 'domcontentloaded' });
		const categoryCard = page.locator('.gcb-post-card').first();
		const categoryBorder = await categoryCard.evaluate((el) => {
			return window.getComputedStyle(el).borderWidth;
		});

		expect(homepageBorder).toBe(categoryBorder);
	});

	test('Homepage and search results cards have matching title font', async ({ page }) => {
		await page.setViewportSize({ width: 1920, height: 1080 });

		// Check homepage bento grid title
		await page.goto('/', { waitUntil: 'domcontentloaded' });
		const homepageTitle = page.locator('.gcb-bento-card__title').first();
		const homepageFont = await homepageTitle.evaluate((el) => {
			return window.getComputedStyle(el).fontFamily;
		});

		// Check search results title
		await page.goto('/?s=Unified', { waitUntil: 'domcontentloaded' });
		const searchTitle = page.locator('.gcb-bento-card__title').first();
		const searchFont = await searchTitle.evaluate((el) => {
			return window.getComputedStyle(el).fontFamily;
		});

		expect(homepageFont).toBe(searchFont);
	});

	test('All pages use brutal-grey color for metadata', async ({ page }) => {
		await page.setViewportSize({ width: 1920, height: 1080 });

		// Homepage metadata
		await page.goto('/', { waitUntil: 'domcontentloaded' });
		const homepageMeta = page.locator('.gcb-bento-card__meta').first();
		const homepageMetaColor = await homepageMeta.evaluate((el) => {
			return window.getComputedStyle(el).color;
		});

		// Search results metadata
		await page.goto('/?s=Unified', { waitUntil: 'domcontentloaded' });
		const searchMeta = page.locator('.gcb-bento-card__meta').first();
		const searchMetaColor = await searchMeta.evaluate((el) => {
			return window.getComputedStyle(el).color;
		});

		// Both should be brutal-grey #AAAAAA = rgb(170, 170, 170)
		expect(homepageMetaColor).toBe('rgb(170, 170, 170)');
		expect(searchMetaColor).toBe('rgb(170, 170, 170)');
	});
});
