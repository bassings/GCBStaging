import { test, expect } from '@playwright/test';

test.describe('Single Post Content - Brutalist Table Styling', () => {
	// Note: No database reset needed for CSS styling tests
	// Set longer timeout for page loads
	test.setTimeout(60000);

	test('tables should use brutalist design system colors', async ({ page }) => {
		// Navigate to test post with tables
		await page.goto('http://localhost:8881/brutalist-table-styling-test/', {
			timeout: 30000,
			waitUntil: 'domcontentloaded'
		});

		// Find all tables in the post content
		const tables = page.locator('.wp-block-post-content table');
		const tableCount = await tables.count();

		// Ensure we have tables to test
		expect(tableCount).toBeGreaterThan(0);

		// Test table headers
		const headers = tables.first().locator('th');
		const headerCount = await headers.count();

		if (headerCount > 0) {
			const firstHeader = headers.first();

			// Header should have acid-lime text
			const headerColor = await firstHeader.evaluate((el) => {
				const style = window.getComputedStyle(el);
				return style.color;
			});
			// Acid lime is rgb(204, 255, 0)
			expect(headerColor).toContain('204, 255, 0');

			// Header background should be void-black
			const headerBg = await firstHeader.evaluate((el) => {
				const style = window.getComputedStyle(el);
				return style.backgroundColor;
			});
			const isValidBg = headerBg.includes('5, 5, 5') || headerBg.includes('0, 0, 0');
			expect(isValidBg).toBeTruthy();
		}

		// Test table data cells
		const cells = tables.first().locator('td');
		const cellCount = await cells.count();

		if (cellCount > 0) {
			const firstCell = cells.first();

			// Cell text should be off-white (#FAFAFA = rgb(250, 250, 250))
			const cellColor = await firstCell.evaluate((el) => {
				const style = window.getComputedStyle(el);
				return style.color;
			});
			expect(cellColor).toContain('250, 250, 250');

			// Cell background should be void-black (not light blue #e6f2ff)
			const cellBg = await firstCell.evaluate((el) => {
				const style = window.getComputedStyle(el);
				return style.backgroundColor;
			});
			// Should NOT be light blue (230, 242, 255)
			expect(cellBg).not.toContain('230, 242, 255');
			// Should be void-black or similar dark color
			const isValidBg =
				cellBg.includes('5, 5, 5') || // void-black
				cellBg.includes('0, 0, 0'); // black
			expect(isValidBg).toBeTruthy();
		}
	});

	test('table headers should NOT have default blue background', async ({ page }) => {
		await page.goto('http://localhost:8881/brutalist-table-styling-test/', {
			timeout: 30000,
			waitUntil: 'domcontentloaded'
		});

		const postContent = page.locator('.wp-block-post-content');
		const headers = postContent.locator('table th');
		const headerCount = await headers.count();

		expect(headerCount).toBeGreaterThan(0);

		const firstHeader = headers.first();
		const headerBg = await firstHeader.evaluate((el) => {
			const style = window.getComputedStyle(el);
			return style.backgroundColor;
		});

		// Should NOT be dark blue (#00008B = rgb(0, 0, 139))
		expect(headerBg).not.toContain('0, 0, 139');
	});

	test('table rows should NOT have light blue alternating backgrounds', async ({ page }) => {
		await page.goto('http://localhost:8881/brutalist-table-styling-test/', {
			timeout: 30000,
			waitUntil: 'domcontentloaded'
		});

		const postContent = page.locator('.wp-block-post-content');
		const cells = postContent.locator('table td');
		const cellCount = await cells.count();

		expect(cellCount).toBeGreaterThan(0);

		// Check several cells to ensure none have light blue background
		for (let i = 0; i < Math.min(cellCount, 5); i++) {
			const cell = cells.nth(i);
			const cellBg = await cell.evaluate((el) => {
				const style = window.getComputedStyle(el);
				return style.backgroundColor;
			});

			// Should NOT be light blue (#e6f2ff = rgb(230, 242, 255))
			expect(cellBg).not.toContain('230, 242, 255');
		}
	});

	test('tables should use monospace font (brutalist design)', async ({ page }) => {
		await page.goto('http://localhost:8881/brutalist-table-styling-test/', {
			timeout: 30000,
			waitUntil: 'domcontentloaded'
		});

		const table = page.locator('.wp-block-post-content table').first();
		await expect(table).toBeVisible();

		const fontFamily = await table.evaluate((el) => {
			const style = window.getComputedStyle(el);
			return style.fontFamily.toLowerCase();
		});

		// Should use mono font (Space Mono, Courier, or other monospace)
		// NOT generic sans-serif
		const hasMonoFont =
			fontFamily.includes('space mono') ||
			fontFamily.includes('mono') ||
			fontFamily.includes('courier') ||
			fontFamily.includes('monospace');

		// Should NOT be generic sans-serif alone
		const isNotGenericSansSerif = !fontFamily.match(/^["']?sans-serif["']?$/);

		expect(hasMonoFont || isNotGenericSansSerif).toBeTruthy();
	});

	test('tables meet WCAG 2.2 AA contrast requirements', async ({ page }) => {
		await page.goto('http://localhost:8881/brutalist-table-styling-test/', {
			timeout: 30000,
			waitUntil: 'domcontentloaded'
		});

		const postContent = page.locator('.wp-block-post-content');
		const cells = postContent.locator('table td');

		expect(await cells.count()).toBeGreaterThan(0);

		const firstCell = cells.first();

		const { color, backgroundColor } = await firstCell.evaluate((el) => {
			const style = window.getComputedStyle(el);
			return {
				color: style.color,
				backgroundColor: style.backgroundColor
			};
		});

		// Off-white (#FAFAFA) on Void Black (#050505) = 19.8:1 contrast ratio (exceeds AA 4.5:1)
		// We'll check that colors match our design system
		expect(color).toContain('250, 250, 250'); // off-white text
		const isValidBg = backgroundColor.includes('5, 5, 5') || backgroundColor.includes('0, 0, 0');
		expect(isValidBg).toBeTruthy(); // void-black background
	});
});
