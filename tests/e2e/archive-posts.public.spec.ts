import { test, expect } from '@playwright/test';

/**
 * Archive Posts - Year-Based Navigation Tests
 *
 * Tests the "Older Posts" feature that allows users to browse
 * all posts organized by year with pagination.
 */

test.describe('Archive Posts - Home Page Link', () => {
	test.beforeEach(async ({ page }) => {
		// Navigate to home page - no post creation needed for link visibility tests
		await page.goto('/', { waitUntil: 'domcontentloaded' });
	});

	test('Desktop - "Browse All Posts" link visible at bottom of home page', async ({ page }) => {
		await page.setViewportSize({ width: 1920, height: 1080 });

		// Scroll to bottom of page
		await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight));

		// Verify link exists
		const browseLink = page.locator('a:has-text("BROWSE ALL POSTS"), a:has-text("Browse All Posts")');
		await expect(browseLink).toBeVisible();
	});

	test('Mobile (375px) - "Browse All Posts" link visible and accessible', async ({ page }) => {
		await page.setViewportSize({ width: 375, height: 667 });

		// Scroll to bottom
		await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight));

		// Verify link exists
		const browseLink = page.locator('a:has-text("BROWSE ALL POSTS"), a:has-text("Browse All Posts")');
		await expect(browseLink).toBeVisible();

		// Verify touch target is at least 44px
		const linkBox = await browseLink.boundingBox();
		expect(linkBox?.height).toBeGreaterThanOrEqual(44);
	});

	test('Clicking "Browse All Posts" navigates to year archive', async ({ page }) => {
		await page.setViewportSize({ width: 1920, height: 1080 });

		// Scroll to bottom and click link
		await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight));
		const browseLink = page.locator('a:has-text("BROWSE ALL POSTS"), a:has-text("Browse All Posts")');
		await browseLink.click();

		// Verify navigation to year archive (e.g., /2026/)
		await page.waitForURL(/\/\d{4}\//);
		expect(page.url()).toMatch(/\/\d{4}\//);
	});
});

test.describe('Archive Posts - Year Selector', () => {
	test.beforeEach(async ({ page, request }) => {
		// Reset database
		await request.delete('/wp-json/gcb-testing/v1/reset', {
			headers: { 'GCB-Test-Key': 'test-secret-key-local' },
		});

		// Create posts for multiple years
		const years = [2026, 2025, 2024, 2023];
		for (const year of years) {
			await request.post('/wp-json/gcb-testing/v1/create-post', {
				data: {
					title: `Archive Post ${year}`,
					content: `<p>Content from ${year}</p>`,
					status: 'publish',
					date: `${year}-06-15 12:00:00`,
				},
				headers: {
					'Content-Type': 'application/json',
					'GCB-Test-Key': 'test-secret-key-local',
				},
			});
		}
	});

	test('Year selector displays all years with posts', async ({ page }) => {
		await page.setViewportSize({ width: 1920, height: 1080 });
		await page.goto('/2026/', { waitUntil: 'domcontentloaded' });

		// Verify year selector exists
		const yearSelector = page.locator('.gcb-year-selector');
		await expect(yearSelector).toBeVisible();

		// Verify all years with posts are shown
		await expect(page.locator('.gcb-year-selector a:has-text("2026")')).toBeVisible();
		await expect(page.locator('.gcb-year-selector a:has-text("2025")')).toBeVisible();
		await expect(page.locator('.gcb-year-selector a:has-text("2024")')).toBeVisible();
		await expect(page.locator('.gcb-year-selector a:has-text("2023")')).toBeVisible();
	});

	test('Current year is highlighted with acid-lime', async ({ page }) => {
		await page.setViewportSize({ width: 1920, height: 1080 });
		await page.goto('/2025/', { waitUntil: 'domcontentloaded' });

		// Get the 2025 year button/link
		const currentYearLink = page.locator('.gcb-year-selector a:has-text("2025")');
		await expect(currentYearLink).toBeVisible();

		// Verify it has active/current styling (acid-lime background)
		const bgColor = await currentYearLink.evaluate((el) => {
			return window.getComputedStyle(el).backgroundColor;
		});

		// Acid Lime #CCFF00 = rgb(204, 255, 0)
		expect(bgColor).toBe('rgb(204, 255, 0)');
	});

	test('Clicking year navigates to that year archive', async ({ page }) => {
		await page.setViewportSize({ width: 1920, height: 1080 });
		await page.goto('/2026/', { waitUntil: 'domcontentloaded' });

		// Click 2024 year link
		const year2024Link = page.locator('.gcb-year-selector a:has-text("2024")');
		await year2024Link.click();

		// Verify navigation
		await page.waitForURL(/\/2024\//);
		expect(page.url()).toContain('/2024/');
	});

	test('Year selector has 44px touch targets', async ({ page }) => {
		await page.setViewportSize({ width: 375, height: 667 });
		await page.goto('/2026/', { waitUntil: 'domcontentloaded' });

		// Check touch target size
		const yearLinks = page.locator('.gcb-year-selector a');
		const count = await yearLinks.count();
		expect(count).toBeGreaterThan(0);

		for (let i = 0; i < count; i++) {
			const link = yearLinks.nth(i);
			const box = await link.boundingBox();
			expect(box?.height).toBeGreaterThanOrEqual(44);
		}
	});
});

test.describe('Archive Posts - Posts Grid', () => {
	test.setTimeout(60000);

	test.beforeEach(async ({ page, request }) => {
		// Reset database
		await request.delete('/wp-json/gcb-testing/v1/reset', {
			headers: { 'GCB-Test-Key': 'test-secret-key-local' },
		});

		// Create 15 posts for 2026 to test pagination
		for (let i = 1; i <= 15; i++) {
			await request.post('/wp-json/gcb-testing/v1/create-post', {
				data: {
					title: `Archive Test Post ${i}`,
					content: `<p>Test content for post ${i}</p>`,
					status: 'publish',
					date: `2026-${String(i).padStart(2, '0')}-15 12:00:00`,
				},
				headers: {
					'Content-Type': 'application/json',
					'GCB-Test-Key': 'test-secret-key-local',
				},
			});
		}
	});

	test('Desktop (1920px) - Grid displays 3 columns', async ({ page }) => {
		await page.setViewportSize({ width: 1920, height: 1080 });
		await page.goto('/2026/', { waitUntil: 'domcontentloaded' });

		// Verify grid container
		const grid = page.locator('.wp-block-post-template');
		await expect(grid).toBeVisible();

		// Check grid columns
		const gridTemplateColumns = await grid.evaluate((el) => {
			return window.getComputedStyle(el).gridTemplateColumns;
		});

		// Should have 3 columns
		const columnCount = gridTemplateColumns.split(' ').length;
		expect(columnCount).toBe(3);
	});

	test('Tablet (768px) - Grid displays 2 columns', async ({ page }) => {
		await page.setViewportSize({ width: 768, height: 1024 });
		await page.goto('/2026/', { waitUntil: 'domcontentloaded' });

		const grid = page.locator('.wp-block-post-template');
		await expect(grid).toBeVisible();

		const gridTemplateColumns = await grid.evaluate((el) => {
			return window.getComputedStyle(el).gridTemplateColumns;
		});

		const columnCount = gridTemplateColumns.split(' ').length;
		expect(columnCount).toBe(2);
	});

	test('Mobile (375px) - Grid displays 1 column', async ({ page }) => {
		await page.setViewportSize({ width: 375, height: 667 });
		await page.goto('/2026/', { waitUntil: 'domcontentloaded' });

		const grid = page.locator('.wp-block-post-template');
		await expect(grid).toBeVisible();

		const gridTemplateColumns = await grid.evaluate((el) => {
			return window.getComputedStyle(el).gridTemplateColumns;
		});

		const columnCount = gridTemplateColumns.split(' ').length;
		expect(columnCount).toBe(1);

		// Verify no horizontal scroll
		const bodyScrollWidth = await page.evaluate(() => document.body.scrollWidth);
		expect(bodyScrollWidth).toBeLessThanOrEqual(376);
	});

	test('Post cards display full color images (no grayscale)', async ({ page }) => {
		await page.setViewportSize({ width: 1920, height: 1080 });
		await page.goto('/2026/', { waitUntil: 'domcontentloaded' });

		// Get featured image and check filter
		const featuredImage = page.locator('.wp-block-post-featured-image img').first();

		// If there are images, verify no grayscale filter
		if (await featuredImage.count() > 0) {
			const filter = await featuredImage.evaluate((el) => {
				return window.getComputedStyle(el).filter;
			});

			// Should NOT have grayscale filter
			expect(filter).not.toContain('grayscale');
		}
	});

	test('Posts only show content from selected year', async ({ page, request }) => {
		// Add a post from different year
		await request.post('/wp-json/gcb-testing/v1/create-post', {
			data: {
				title: 'Post from 2025',
				content: '<p>Content from 2025</p>',
				status: 'publish',
				date: '2025-06-15 12:00:00',
			},
			headers: {
				'Content-Type': 'application/json',
				'GCB-Test-Key': 'test-secret-key-local',
			},
		});

		await page.setViewportSize({ width: 1920, height: 1080 });
		await page.goto('/2026/', { waitUntil: 'domcontentloaded' });

		// Verify 2025 post is NOT visible on 2026 archive
		const post2025 = page.locator('article:has-text("Post from 2025")');
		await expect(post2025).not.toBeVisible();

		// Verify 2026 posts ARE visible
		const post2026 = page.locator('article:has-text("Archive Test Post")').first();
		await expect(post2026).toBeVisible();
	});
});

test.describe('Archive Posts - Pagination', () => {
	test.setTimeout(60000);

	test.beforeEach(async ({ page, request }) => {
		// Reset database
		await request.delete('/wp-json/gcb-testing/v1/reset', {
			headers: { 'GCB-Test-Key': 'test-secret-key-local' },
		});

		// Create 20 posts to ensure pagination (assuming 12 per page)
		for (let i = 1; i <= 20; i++) {
			await request.post('/wp-json/gcb-testing/v1/create-post', {
				data: {
					title: `Pagination Test Post ${i}`,
					content: `<p>Pagination test content ${i}</p>`,
					status: 'publish',
					date: `2026-${String(Math.min(i, 12)).padStart(2, '0')}-${String(i).padStart(2, '0')} 12:00:00`,
				},
				headers: {
					'Content-Type': 'application/json',
					'GCB-Test-Key': 'test-secret-key-local',
				},
			});
		}
	});

	test('Pagination controls visible when posts exceed per-page limit', async ({ page }) => {
		await page.setViewportSize({ width: 1920, height: 1080 });
		await page.goto('/2026/', { waitUntil: 'domcontentloaded' });

		// Verify pagination exists
		const pagination = page.locator('.wp-block-query-pagination');
		await expect(pagination).toBeVisible();
	});

	test('Page numbers are displayed', async ({ page }) => {
		await page.setViewportSize({ width: 1920, height: 1080 });
		await page.goto('/2026/', { waitUntil: 'domcontentloaded' });

		// Verify page numbers
		const pageNumbers = page.locator('.wp-block-query-pagination-numbers');
		await expect(pageNumbers).toBeVisible();
	});

	test('Next page button navigates to page 2', async ({ page }) => {
		await page.setViewportSize({ width: 1920, height: 1080 });
		await page.goto('/2026/', { waitUntil: 'domcontentloaded' });

		// Click next
		const nextButton = page.locator('.wp-block-query-pagination-next');
		if (await nextButton.isVisible()) {
			await nextButton.click();
			await page.waitForURL(/\/page\/2/);
			expect(page.url()).toContain('/page/2');
		}
	});

	test('Previous page button appears on page 2', async ({ page }) => {
		await page.setViewportSize({ width: 1920, height: 1080 });
		await page.goto('/2026/page/2/', { waitUntil: 'domcontentloaded' });

		// Verify previous button exists
		const prevButton = page.locator('.wp-block-query-pagination-previous');
		await expect(prevButton).toBeVisible();
	});

	test('Clicking page number navigates directly to that page', async ({ page }) => {
		await page.setViewportSize({ width: 1920, height: 1080 });
		await page.goto('/2026/', { waitUntil: 'domcontentloaded' });

		// Click page 2 link
		const page2Link = page.locator('.wp-block-query-pagination-numbers a:has-text("2")');
		if (await page2Link.isVisible()) {
			await page2Link.click();
			await page.waitForURL(/\/page\/2/);
			expect(page.url()).toContain('/page/2');
		}
	});
});

test.describe('Archive Posts - Accessibility WCAG 2.2 AA', () => {
	test.beforeEach(async ({ page, request }) => {
		// Reset database
		await request.delete('/wp-json/gcb-testing/v1/reset', {
			headers: { 'GCB-Test-Key': 'test-secret-key-local' },
		});

		// Create test posts
		for (let i = 1; i <= 5; i++) {
			await request.post('/wp-json/gcb-testing/v1/create-post', {
				data: {
					title: `Accessibility Test Post ${i}`,
					content: `<p>Content for accessibility testing</p>`,
					status: 'publish',
					date: `2026-0${i}-15 12:00:00`,
				},
				headers: {
					'Content-Type': 'application/json',
					'GCB-Test-Key': 'test-secret-key-local',
				},
			});
		}
	});

	test('Archive page has proper heading hierarchy', async ({ page }) => {
		await page.setViewportSize({ width: 1920, height: 1080 });
		await page.goto('/2026/', { waitUntil: 'domcontentloaded' });

		// Verify H1 exists for archive title
		const h1 = page.locator('h1');
		await expect(h1).toBeVisible();
	});

	test('Year selector links have visible focus indicators', async ({ page }) => {
		await page.setViewportSize({ width: 1920, height: 1080 });
		await page.goto('/2026/', { waitUntil: 'domcontentloaded' });

		// Focus a year link
		const yearLink = page.locator('.gcb-year-selector a').first();
		await yearLink.focus();

		// Check for focus indicator
		const outlineStyle = await yearLink.evaluate((el) => {
			const styles = window.getComputedStyle(el);
			return {
				outline: styles.outline,
				outlineColor: styles.outlineColor,
				outlineWidth: styles.outlineWidth,
			};
		});

		// Verify outline is visible
		expect(outlineStyle.outline).not.toBe('none');
		expect(parseInt(outlineStyle.outlineWidth)).toBeGreaterThanOrEqual(2);
	});

	test('All post cards are keyboard navigable', async ({ page }) => {
		await page.setViewportSize({ width: 1920, height: 1080 });
		await page.goto('/2026/', { waitUntil: 'domcontentloaded' });

		// Tab through page
		await page.keyboard.press('Tab');
		await page.keyboard.press('Tab');
		await page.keyboard.press('Tab');

		// Verify some element is focused (post link or navigation)
		const focusedElement = await page.evaluate(() => document.activeElement?.tagName);
		expect(focusedElement).toBeDefined();
	});

	test('Pagination buttons have 44px touch targets', async ({ page }) => {
		await page.setViewportSize({ width: 375, height: 667 });

		// Create more posts for pagination
		const request = page.request;
		for (let i = 6; i <= 20; i++) {
			await request.post('/wp-json/gcb-testing/v1/create-post', {
				data: {
					title: `Extra Post ${i}`,
					content: '<p>Extra content</p>',
					status: 'publish',
					date: `2026-06-${String(i).padStart(2, '0')} 12:00:00`,
				},
				headers: {
					'Content-Type': 'application/json',
					'GCB-Test-Key': 'test-secret-key-local',
				},
			});
		}

		await page.goto('/2026/', { waitUntil: 'domcontentloaded' });

		// Check pagination button sizes
		const paginationLinks = page.locator('.wp-block-query-pagination a');
		const count = await paginationLinks.count();

		for (let i = 0; i < count; i++) {
			const link = paginationLinks.nth(i);
			const box = await link.boundingBox();
			if (box) {
				expect(box.height).toBeGreaterThanOrEqual(44);
			}
		}
	});
});

test.describe('Archive Posts - Design Tokens', () => {
	test.beforeEach(async ({ page, request }) => {
		// Reset database
		await request.delete('/wp-json/gcb-testing/v1/reset', {
			headers: { 'GCB-Test-Key': 'test-secret-key-local' },
		});

		// Create test post
		await request.post('/wp-json/gcb-testing/v1/create-post', {
			data: {
				title: 'Design Token Test Post',
				content: '<p>Test content</p>',
				status: 'publish',
				date: '2026-06-15 12:00:00',
			},
			headers: {
				'Content-Type': 'application/json',
				'GCB-Test-Key': 'test-secret-key-local',
			},
		});
	});

	test('Page background is Void Black (#050505)', async ({ page }) => {
		await page.setViewportSize({ width: 1920, height: 1080 });
		await page.goto('/2026/', { waitUntil: 'domcontentloaded' });

		const bgColor = await page.evaluate(() => {
			return window.getComputedStyle(document.body).backgroundColor;
		});

		// Void Black #050505 = rgb(5, 5, 5)
		expect(bgColor).toBe('rgb(5, 5, 5)');
	});

	test('Archive title is uppercase Off-White (#FAFAFA)', async ({ page }) => {
		await page.setViewportSize({ width: 1920, height: 1080 });
		await page.goto('/2026/', { waitUntil: 'domcontentloaded' });

		const title = page.locator('h1');
		await expect(title).toBeVisible();

		const styles = await title.evaluate((el) => {
			const computed = window.getComputedStyle(el);
			return {
				color: computed.color,
				textTransform: computed.textTransform,
			};
		});

		// Off-White #FAFAFA = rgb(250, 250, 250)
		expect(styles.color).toBe('rgb(250, 250, 250)');
		expect(styles.textTransform).toBe('uppercase');
	});

	test('Post cards have Brutal Border (#333333) with acid-lime hover', async ({ page }) => {
		await page.setViewportSize({ width: 1920, height: 1080 });
		await page.goto('/2026/', { waitUntil: 'domcontentloaded' });

		const card = page.locator('.gcb-post-card').first();

		if (await card.isVisible()) {
			// Check default border
			const borderColor = await card.evaluate((el) => {
				return window.getComputedStyle(el).borderColor;
			});

			// Brutal Border #333333 = rgb(51, 51, 51)
			expect(borderColor).toBe('rgb(51, 51, 51)');

			// Hover and check acid-lime border
			await card.hover();
			const hoverBorderColor = await card.evaluate((el) => {
				return window.getComputedStyle(el).borderColor;
			});

			// Should change to Acid Lime #CCFF00 = rgb(204, 255, 0)
			expect(hoverBorderColor).toBe('rgb(204, 255, 0)');
		}
	});

	test('Year selector inactive links have brutal-border styling', async ({ page }) => {
		await page.setViewportSize({ width: 1920, height: 1080 });

		// Add post from another year
		const request = page.request;
		await request.post('/wp-json/gcb-testing/v1/create-post', {
			data: {
				title: 'Post from 2025',
				content: '<p>Content</p>',
				status: 'publish',
				date: '2025-06-15 12:00:00',
			},
			headers: {
				'Content-Type': 'application/json',
				'GCB-Test-Key': 'test-secret-key-local',
			},
		});

		await page.goto('/2026/', { waitUntil: 'domcontentloaded' });

		// Check inactive year link styling
		const inactiveLink = page.locator('.gcb-year-selector a:has-text("2025")');

		if (await inactiveLink.isVisible()) {
			const styles = await inactiveLink.evaluate((el) => {
				const computed = window.getComputedStyle(el);
				return {
					borderColor: computed.borderColor,
					color: computed.color,
				};
			});

			// Border should be brutal-border #333333
			expect(styles.borderColor).toBe('rgb(51, 51, 51)');
			// Text should be off-white #FAFAFA
			expect(styles.color).toBe('rgb(250, 250, 250)');
		}
	});
});
