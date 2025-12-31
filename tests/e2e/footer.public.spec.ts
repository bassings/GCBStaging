import { test, expect } from '@playwright/test';

test.describe('Footer - Social Media Icons & Branding', () => {

	test.beforeEach(async ({ page, request }) => {
		// Reset database state
		await request.delete('http://localhost:8881/wp-json/gcb-testing/v1/reset');

		// Navigate to homepage
		await page.goto('http://localhost:8881/');
		await page.waitForLoadState('networkidle');
	});

	test('Footer displays GCB logo and tagline', async ({ page }) => {
		const footerLogo = page.locator('.footer-branding h2');
		await expect(footerLogo).toBeVisible();
		await expect(footerLogo).toContainText('GCB');

		const tagline = page.locator('.footer-branding p').first();
		await expect(tagline).toBeVisible();
		await expect(tagline).toContainText('Gay Car Reviews & Gay Lifestyle');
	});

	test('Footer displays all 4 social media icons', async ({ page }) => {
		// YouTube
		const youtube = page.locator('a[href*="youtube"][aria-label="YouTube"]');
		await expect(youtube).toBeVisible();

		// Instagram
		const instagram = page.locator('a[href*="instagram"][aria-label="Instagram"]');
		await expect(instagram).toBeVisible();

		// Twitter/X
		const twitter = page.locator('a[href*="twitter"][aria-label="Twitter"]');
		await expect(twitter).toBeVisible();

		// Facebook (NEW)
		const facebook = page.locator('a[href*="facebook"][aria-label="Facebook"]');
		await expect(facebook).toBeVisible();
	});

	test('Social media icons meet WCAG touch target requirements', async ({ page }) => {
		const socialIcons = page.locator('.social-icon');
		const count = await socialIcons.count();
		expect(count).toBe(4); // YouTube, Instagram, Twitter, Facebook

		for (let i = 0; i < count; i++) {
			const icon = socialIcons.nth(i);
			const bbox = await icon.boundingBox();
			expect(bbox?.width).toBeGreaterThanOrEqual(44);
			expect(bbox?.height).toBeGreaterThanOrEqual(44);
		}
	});

	test('Social media icons change to acid lime on hover', async ({ page }) => {
		const youtubeIcon = page.locator('.social-icon[aria-label="YouTube"]');

		// Default state: Brutal Border
		const defaultColor = await youtubeIcon.evaluate(el =>
			window.getComputedStyle(el).color
		);
		expect(defaultColor).toBe('rgb(51, 51, 51)'); // #333333

		// Hover state: Acid Lime
		await youtubeIcon.hover();
		const hoverColor = await youtubeIcon.evaluate(el =>
			window.getComputedStyle(el).color
		);
		expect(hoverColor).toBe('rgb(204, 255, 0)'); // #CCFF00
	});

	test('Footer displays copyright with founder attribution', async ({ page }) => {
		const copyright = page.locator('.footer-bottom p');
		await expect(copyright).toBeVisible();
		await expect(copyright).toContainText('© 2025 Gay Car Boys');
		await expect(copyright).toContainText('Founded by Alan Zurvas');
		await expect(copyright).toContainText('LGBTQ+ Automotive Reviews & Lifestyle');
	});

	test('Footer uses Editorial Brutalism design tokens', async ({ page }) => {
		const footerLogo = page.locator('.footer-branding h2');

		// Check font family
		const fontFamily = await footerLogo.evaluate(el =>
			window.getComputedStyle(el).fontFamily
		);
		expect(fontFamily.toLowerCase()).toContain('playfair');

		// Check footer border
		const footer = page.locator('.gcb-footer');
		const borderTopColor = await footer.evaluate(el =>
			window.getComputedStyle(el).borderTopColor
		);
		expect(borderTopColor).toBe('rgb(51, 51, 51)'); // Brutal Border
	});

	test('Footer is responsive on mobile viewport', async ({ page }) => {
		await page.setViewportSize({ width: 375, height: 667 });

		const footer = page.locator('.gcb-footer');
		await expect(footer).toBeVisible();

		// On mobile, footer should stack vertically
		const footerTop = page.locator('.footer-top');
		const flexDirection = await footerTop.evaluate(el =>
			window.getComputedStyle(el).flexDirection
		);
		expect(flexDirection).toBe('column');
	});

	test('Footer social icons have accessible ARIA labels', async ({ page }) => {
		const socialIcons = [
			{ label: 'YouTube', href: 'youtube' },
			{ label: 'Instagram', href: 'instagram' },
			{ label: 'Twitter', href: 'twitter' },
			{ label: 'Facebook', href: 'facebook' }
		];

		for (const icon of socialIcons) {
			const link = page.locator(`a[aria-label="${icon.label}"]`);
			await expect(link).toBeVisible();

			const ariaLabel = await link.getAttribute('aria-label');
			expect(ariaLabel).toBe(icon.label);
		}
	});
});
