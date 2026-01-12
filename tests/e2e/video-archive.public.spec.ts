import { test, expect } from '@playwright/test';

/**
 * Video Archive Page Tests
 *
 * Tests for the /video/ archive page that displays all YouTube videos
 * from the GCB channel in a grid layout with Editorial Brutalism styling.
 */
test.describe('Video Archive Page (/video/)', () => {

  test.beforeEach(async ({ request }) => {
    // Reset database state before each test
    await request.delete('/wp-json/gcb-testing/v1/reset', {
      headers: { 'GCB-Test-Key': 'test-secret-key-local' }
    });
  });

  test('Video archive page is accessible at /video/', async ({ page }) => {
    await page.goto('/video/', { waitUntil: 'domcontentloaded' });

    // Should not return 404
    const title = await page.title();
    expect(title).not.toMatch(/not found|404/i);

    // Page should have a heading indicating it's the video archive
    const heading = page.locator('h1, h2').filter({ hasText: /video/i }).first();
    await expect(heading).toBeVisible();
  });

  test('Video archive displays grid of video cards', async ({ page }) => {
    await page.goto('/video/', { waitUntil: 'domcontentloaded' });

    // Should have multiple video cards
    const videoCards = page.locator('.gcb-video-archive__card, .gcb-video-card, .video-archive-item');
    const cardCount = await videoCards.count();
    expect(cardCount).toBeGreaterThanOrEqual(1);

    // Each card should have a thumbnail
    const firstCard = videoCards.first();
    const thumbnail = firstCard.locator('img[src*="youtube"], img[src*="ytimg"]');
    await expect(thumbnail).toBeVisible();
  });

  test('Video archive cards have YouTube links that open in new tab', async ({ page }) => {
    await page.goto('/video/', { waitUntil: 'domcontentloaded' });

    // Cards should link to YouTube
    const videoLink = page.locator('a[href*="youtube.com/watch"]').first();
    await expect(videoLink).toBeVisible();

    // Links should open in new tab
    const target = await videoLink.getAttribute('target');
    expect(target).toBe('_blank');

    // Links should have noopener for security
    const rel = await videoLink.getAttribute('rel');
    expect(rel).toMatch(/noopener/);
  });

  test('Video archive uses Editorial Brutalism design tokens', async ({ page }) => {
    await page.goto('/video/', { waitUntil: 'domcontentloaded' });

    // Check page has void-black background
    const body = page.locator('body');
    const bgColor = await body.evaluate(el => window.getComputedStyle(el).backgroundColor);
    expect(bgColor).toBe('rgb(5, 5, 5)'); // #050505 Void Black

    // Check heading uses off-white color
    const heading = page.locator('h1, h2').filter({ hasText: /video/i }).first();
    const headingColor = await heading.evaluate(el => window.getComputedStyle(el).color);
    expect(headingColor).toBe('rgb(250, 250, 250)'); // #FAFAFA Off-White
  });

  test('Video archive displays video metadata (duration, views)', async ({ page }) => {
    await page.goto('/video/', { waitUntil: 'domcontentloaded' });

    const videoCard = page.locator('.gcb-video-archive__card, .gcb-video-card, .video-archive-item').first();

    // Should show duration and/or views
    const metaText = await videoCard.textContent();
    // Should contain either duration format (X:XX) or view count (XK Views)
    const hasMetadata = /\d+:\d+/.test(metaText || '') || /\d+[KM]?\s*Views/i.test(metaText || '');
    expect(hasMetadata).toBeTruthy();
  });

  test('Video archive has acid lime play button overlays', async ({ page }) => {
    await page.goto('/video/', { waitUntil: 'domcontentloaded' });

    // Play buttons should exist
    const playButton = page.locator('.video-play-button, svg[viewBox="0 0 100 100"]').first();
    await expect(playButton).toBeVisible();

    // Play button should be acid lime
    const color = await playButton.evaluate(el => window.getComputedStyle(el).color);
    expect(color).toBe('rgb(204, 255, 0)'); // Acid Lime
  });

  // Responsive tests
  test('Video archive is responsive on mobile (375px)', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 667 });
    await page.goto('/video/', { waitUntil: 'domcontentloaded' });

    // Content should be visible
    const heading = page.locator('h1, h2').filter({ hasText: /video/i }).first();
    await expect(heading).toBeVisible();

    // Video cards should be visible
    const videoCards = page.locator('.gcb-video-archive__card, .gcb-video-card, .video-archive-item');
    await expect(videoCards.first()).toBeVisible();

    // No horizontal overflow
    const hasOverflow = await page.evaluate(() => {
      return document.body.scrollWidth > window.innerWidth;
    });
    expect(hasOverflow).toBeFalsy();
  });

  test('Video archive is responsive on tablet (768px)', async ({ page }) => {
    await page.setViewportSize({ width: 768, height: 1024 });
    await page.goto('/video/', { waitUntil: 'domcontentloaded' });

    // Content should be visible
    const videoCards = page.locator('.gcb-video-archive__card, .gcb-video-card, .video-archive-item');
    await expect(videoCards.first()).toBeVisible();

    // Grid should show multiple columns on tablet
    const firstCard = videoCards.first();
    const cardBox = await firstCard.boundingBox();
    // On tablet, cards should be less than half viewport width if 2+ columns
    // or full width if single column - either is acceptable
    expect(cardBox?.width).toBeLessThanOrEqual(768);
  });

  test('Video archive is responsive on desktop (1920px)', async ({ page }) => {
    await page.setViewportSize({ width: 1920, height: 1080 });
    await page.goto('/video/', { waitUntil: 'domcontentloaded' });

    // Content should be visible
    const videoCards = page.locator('.gcb-video-archive__card, .gcb-video-card, .video-archive-item');
    const cardCount = await videoCards.count();
    expect(cardCount).toBeGreaterThanOrEqual(1);

    // On desktop, grid should show multiple cards per row
    // Check that cards are arranged horizontally
    if (cardCount >= 2) {
      const firstCard = await videoCards.nth(0).boundingBox();
      const secondCard = await videoCards.nth(1).boundingBox();
      // Cards should be side by side (same Y or close) on desktop
      // Allow for small variations due to rendering
      expect(Math.abs((firstCard?.y || 0) - (secondCard?.y || 0))).toBeLessThan(50);
    }
  });

  // Accessibility tests
  test('Video archive has proper accessibility attributes', async ({ page }) => {
    await page.goto('/video/', { waitUntil: 'domcontentloaded' });

    // All video links should have aria-labels
    const videoLinks = page.locator('a[href*="youtube.com/watch"]');
    const linkCount = await videoLinks.count();

    for (let i = 0; i < Math.min(linkCount, 5); i++) {
      const link = videoLinks.nth(i);
      const ariaLabel = await link.getAttribute('aria-label');
      // Should have aria-label or visible text
      const hasAccessibleName = ariaLabel !== null || (await link.textContent())?.trim() !== '';
      expect(hasAccessibleName).toBeTruthy();
    }
  });

  test('Video archive touch targets meet WCAG minimum (44px)', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 667 });
    await page.goto('/video/', { waitUntil: 'domcontentloaded' });

    // Check that video card links have adequate touch targets
    const videoLinks = page.locator('a[href*="youtube.com/watch"]');
    const linkCount = await videoLinks.count();

    for (let i = 0; i < Math.min(linkCount, 3); i++) {
      const link = videoLinks.nth(i);
      const box = await link.boundingBox();
      // Either width or height should be at least 44px
      const meetsMinimum = (box?.width || 0) >= 44 || (box?.height || 0) >= 44;
      expect(meetsMinimum).toBeTruthy();
    }
  });

  test('Video archive supports keyboard navigation', async ({ page }) => {
    await page.goto('/video/', { waitUntil: 'domcontentloaded' });

    // Tab through the page
    await page.keyboard.press('Tab');
    await page.keyboard.press('Tab');
    await page.keyboard.press('Tab');

    // Should be able to focus on video links
    const focusedElement = page.locator(':focus');
    await expect(focusedElement).toBeVisible();

    // Check that focus indicator is visible
    const outline = await focusedElement.evaluate(el => window.getComputedStyle(el).outline);
    // Should have some outline (not 'none' or '0px')
    expect(outline).not.toBe('none');
  });

  test('Video archive page title is descriptive', async ({ page }) => {
    await page.goto('/video/', { waitUntil: 'domcontentloaded' });

    const title = await page.title();
    // Title should mention videos and site name
    expect(title.toLowerCase()).toMatch(/video/);
  });

  // Navigation test - verifying the View All link works
  test('View All link from homepage navigates to video archive', async ({ page, request }) => {
    // Create a video post first
    await request.post('/wp-json/gcb-testing/v1/create-post', {
      data: {
        title: 'Navigation Test Video',
        content: 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        status: 'publish'
      },
      headers: {
        'Content-Type': 'application/json',
        'GCB-Test-Key': 'test-secret-key-local'
      }
    });

    await page.goto('/', { waitUntil: 'domcontentloaded' });

    // Click the View All link in video rail
    const viewAllLink = page.locator('a[href*="/video/"]:has-text("View All")');
    await expect(viewAllLink).toBeVisible();
    await viewAllLink.click();

    // Should navigate to /video/
    await expect(page).toHaveURL(/\/video\/?$/);

    // Video archive page should load successfully
    const heading = page.locator('h1, h2').filter({ hasText: /video/i }).first();
    await expect(heading).toBeVisible();
  });
});
