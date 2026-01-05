import { test, expect } from '@playwright/test';

test.describe('Video Rail - YouTube API Integration', () => {

  test('Displays videos from YouTube channel (test mode)', async ({ page }) => {
    // Test mode uses mock data from GCB_YouTube_Mock_Data
    // No database reset needed - not creating posts
    await page.goto('/', { waitUntil: 'domcontentloaded' });

    // Assert: Video rail is visible
    const videoRail = page.locator('.gcb-video-rail, [data-pattern="video-rail"]').first();
    await expect(videoRail).toBeVisible();

    // Assert: 10 video cards displayed (max results from mock data)
    const videoCards = page.locator('.gcb-video-card, .video-rail-item');
    const count = await videoCards.count();
    expect(count).toBe(10);

    // Assert: First video is "2025 Porsche 911 GT3 RS" (mock data)
    const firstTitle = await videoCards.first().locator('.gcb-video-card__title').textContent();
    expect(firstTitle).toContain('2025 Porsche 911 GT3 RS');
  });

  test('Video cards link to YouTube.com (not WordPress posts)', async ({ page }) => {
    await page.goto('/', { waitUntil: 'domcontentloaded' });

    const firstCard = page.locator('.gcb-video-card, .video-rail-item').first();
    const link = firstCard.locator('a').first();

    // Assert: Link href points to YouTube
    const href = await link.getAttribute('href');
    expect(href).toMatch(/^https:\/\/www\.youtube\.com\/watch\?v=/);
    expect(href).toContain('dQw4w9WgXcQ'); // First mock video ID

    // Assert: Link has target="_blank"
    const target = await link.getAttribute('target');
    expect(target).toBe('_blank');

    // Assert: Link has rel="noopener noreferrer"
    const rel = await link.getAttribute('rel');
    expect(rel).toContain('noopener');
    expect(rel).toContain('noreferrer');

    // Assert: Link has ARIA label
    const ariaLabel = await link.getAttribute('aria-label');
    expect(ariaLabel).toContain('Watch');
    expect(ariaLabel).toContain('YouTube');
  });

  test('Video rail displays metadata from YouTube API (duration, views)', async ({ page }) => {
    await page.goto('/', { waitUntil: 'domcontentloaded' });

    const firstCard = page.locator('.gcb-video-card, .video-rail-item').first();
    const metaText = await firstCard.locator('.gcb-video-card__meta').textContent();

    // Assert: Duration is displayed (mock: "PT12M45S" -> "12:45")
    expect(metaText).toMatch(/12:45/);

    // Assert: View count is displayed (mock: 245678 -> "246K")
    expect(metaText).toMatch(/24[56]K Views/);
  });

  test('Video thumbnails use YouTube CDN URLs', async ({ page }) => {
    await page.goto('/', { waitUntil: 'domcontentloaded' });

    const firstThumbnail = page.locator('.gcb-video-card img').first();
    const src = await firstThumbnail.getAttribute('src');

    // Assert: Thumbnail URL is YouTube CDN
    expect(src).toMatch(/^https:\/\/(i\.ytimg\.com|img\.youtube\.com)\//);
    expect(src).toContain('dQw4w9WgXcQ'); // First mock video ID
  });

  test('Video rail maintains brutalist design (grayscale, acid lime)', async ({ page }) => {
    await page.goto('/', { waitUntil: 'domcontentloaded' });

    const firstCard = page.locator('.gcb-video-card, .video-rail-item').first();
    const thumbnail = firstCard.locator('img').first();

    // Assert: Thumbnail has grayscale filter
    const filter = await thumbnail.evaluate(el => window.getComputedStyle(el).filter);
    expect(filter).toContain('grayscale');

    // Assert: Play button is acid lime
    const playButton = firstCard.locator('svg').first();
    const color = await playButton.evaluate(el => window.getComputedStyle(el).color);
    expect(color).toBe('rgb(204, 255, 0)'); // Acid Lime (#CCFF00)
  });

  test('Video rail is keyboard navigable with focus indicators', async ({ page }) => {
    await page.goto('/', { waitUntil: 'domcontentloaded' });

    // Tab to first video card link
    await page.keyboard.press('Tab');
    await page.keyboard.press('Tab'); // May need multiple tabs depending on page structure

    // Find the focused element
    const focusedHref = await page.evaluate(() => {
      const focused = document.activeElement as HTMLAnchorElement;
      return focused?.href || '';
    });

    // Assert: Focused element is a YouTube link
    expect(focusedHref).toMatch(/youtube\.com/);
  });

  test('Video rail responsive on mobile (375px viewport)', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 667 });
    await page.goto('/', { waitUntil: 'domcontentloaded' });

    // Assert: Video rail visible on mobile
    const videoRail = page.locator('.gcb-video-rail').first();
    await expect(videoRail).toBeVisible();

    // Assert: Video cards are scrollable horizontally
    const railContainer = page.locator('.gcb-video-rail__container').first();
    const isScrollable = await railContainer.evaluate(el => el.scrollWidth > el.clientWidth);
    expect(isScrollable).toBeTruthy();

    // Assert: No horizontal page scroll
    const bodyScrollWidth = await page.evaluate(() => document.body.scrollWidth);
    expect(bodyScrollWidth).toBeLessThanOrEqual(375);
  });

  test('Video cards have adequate touch targets (44px minimum)', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 667 });
    await page.goto('/', { waitUntil: 'domcontentloaded' });

    const firstCard = page.locator('.gcb-video-card, .video-rail-item').first();
    const link = firstCard.locator('a').first();

    // Get bounding box
    const box = await link.boundingBox();
    expect(box).not.toBeNull();

    if (box) {
      // Assert: Height meets WCAG 2.2 touch target size (44px minimum)
      expect(box.height).toBeGreaterThanOrEqual(44);
    }
  });

});
