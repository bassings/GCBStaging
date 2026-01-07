import { test, expect } from '@playwright/test';

test.describe('GCB Content Intelligence - Video Rail Pattern', () => {

  test('Displays horizontal scrolling video rail with multiple videos', async ({ page, request }) => {
    test.setTimeout(60000); // Increase timeout for creating 5 posts
    // Reset database
    await request.delete('/wp-json/gcb-testing/v1/reset', {
      headers: { 'GCB-Test-Key': 'test-secret-key-local' }
    });

    // Create 5 video posts for the rail
    const videoIds = ['dQw4w9WgXcQ', 'jNQXAC9IVRw', 'kJQP7kiw5Fk', 'fJ9rUzIMcZQ', 'DLzxrzFCyOs'];
    const videoTitles = [
      'Classic 80s Hit',
      'Another Video',
      'Third Video',
      'Fourth Video',
      'Fifth Video'
    ];

    for (let i = 0; i < videoIds.length; i++) {
      await request.post('/wp-json/gcb-testing/v1/create-post', {
        data: {
          title: videoTitles[i],
          content: `https://www.youtube.com/watch?v=${videoIds[i]}`,
          status: 'publish'
        },
        headers: {
          'Content-Type': 'application/json',
          'GCB-Test-Key': 'test-secret-key-local'
        }
      });
    }

    // Navigate to homepage (or page with video rail pattern)
    await page.goto('/', { waitUntil: 'domcontentloaded' });

    // Assert: Video rail container exists
    const videoRail = page.locator('.gcb-video-rail, [data-pattern="video-rail"]');
    await expect(videoRail).toBeVisible();

    // Assert: Multiple video cards are present
    const videoCards = page.locator('.gcb-video-card, .video-rail-item');
    const cardCount = await videoCards.count();
    expect(cardCount).toBeGreaterThanOrEqual(3); // At least 3 videos visible

    // Assert: Horizontal scroll is enabled
    const railContainer = page.locator('.gcb-video-rail__container, .video-rail-scroll');
    const overflow = await railContainer.evaluate((el) => {
      return window.getComputedStyle(el).overflowX;
    });
    expect(overflow).toMatch(/scroll|auto/);

    // Assert: Video cards have thumbnails
    const firstVideoThumbnail = videoCards.first().locator('img[src*="youtube"], img[src*="ytimg"]');
    await expect(firstVideoThumbnail).toBeVisible();

    // Assert: Video cards have titles
    const firstVideoTitle = videoCards.first().locator('.gcb-video-card__title, .video-title, h3, h4');
    await expect(firstVideoTitle).toBeVisible();
  });

  test('Video rail is responsive on mobile viewports', async ({ page, request }) => {
    // Reset database
    await request.delete('/wp-json/gcb-testing/v1/reset', {
      headers: { 'GCB-Test-Key': 'test-secret-key-local' }
    });

    // Create 3 video posts
    for (let i = 0; i < 3; i++) {
      await request.post('/wp-json/gcb-testing/v1/create-post', {
        data: {
          title: `Mobile Video ${i + 1}`,
          content: 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
          status: 'publish'
        },
        headers: {
          'Content-Type': 'application/json',
          'GCB-Test-Key': 'test-secret-key-local'
        }
      });
    }

    // Set mobile viewport
    await page.setViewportSize({ width: 375, height: 667 });
    await page.goto('/', { waitUntil: 'domcontentloaded' });

    // Assert: Video rail still visible on mobile
    const videoRail = page.locator('.gcb-video-rail, [data-pattern="video-rail"]');
    await expect(videoRail).toBeVisible();

    // Assert: Horizontal scroll works on mobile
    const railContainer = page.locator('.gcb-video-rail__container, .video-rail-scroll');
    const isScrollable = await railContainer.evaluate((el) => {
      return el.scrollWidth > el.clientWidth;
    });
    expect(isScrollable).toBeTruthy();

    // Assert: Video cards don't overflow viewport
    const videoCard = page.locator('.gcb-video-card, .video-rail-item').first();
    const cardWidth = await videoCard.evaluate((el) => {
      return el.getBoundingClientRect().width;
    });
    expect(cardWidth).toBeLessThanOrEqual(375);
  });

  test('Video rail cards use 16:9 landscape aspect ratio', async ({ page, request }) => {
    // Reset database
    await request.delete('/wp-json/gcb-testing/v1/reset', {
      headers: { 'GCB-Test-Key': 'test-secret-key-local' }
    });

    // Create 1 video post
    await request.post('/wp-json/gcb-testing/v1/create-post', {
      data: {
        title: 'Landscape Aspect Ratio Test',
        content: 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        status: 'publish'
      },
      headers: {
        'Content-Type': 'application/json',
        'GCB-Test-Key': 'test-secret-key-local'
      }
    });

    await page.goto('/', { waitUntil: 'domcontentloaded' });

    // Assert: Video card aspect ratio is 16:9
    const videoCard = page.locator('.gcb-video-card, .video-rail-item').first();
    const aspectContainer = videoCard.locator('.gcb-video-card__aspect');

    const dimensions = await aspectContainer.boundingBox();
    expect(dimensions).not.toBeNull();

    // Calculate aspect ratio (width / height)
    const aspectRatio = dimensions!.width / dimensions!.height;

    // 16:9 = 1.777... (allow 0.1 tolerance for rounding)
    expect(aspectRatio).toBeGreaterThan(1.677);
    expect(aspectRatio).toBeLessThan(1.877);
  });

  test('Video rail cards are shorter on landscape (not portrait)', async ({ page, request }) => {
    await request.delete('/wp-json/gcb-testing/v1/reset', {
      headers: { 'GCB-Test-Key': 'test-secret-key-local' }
    });

    await request.post('/wp-json/gcb-testing/v1/create-post', {
      data: {
        title: 'Height Verification Test',
        content: 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        status: 'publish'
      },
      headers: {
        'Content-Type': 'application/json',
        'GCB-Test-Key': 'test-secret-key-local'
      }
    });

    // Desktop viewport
    await page.setViewportSize({ width: 1920, height: 1080 });
    await page.goto('/', { waitUntil: 'domcontentloaded' });

    const videoCard = page.locator('.gcb-video-card, .video-rail-item').first();
    const aspectContainer = videoCard.locator('.gcb-video-card__aspect');
    const cardDimensions = await aspectContainer.boundingBox();

    expect(cardDimensions).not.toBeNull();

    // Desktop card width is 288px
    // Landscape 16:9 height should be ~162px (288 * 0.5625)
    // Portrait 9:16 height would be ~512px (288 * 1.7778)

    expect(cardDimensions!.height).toBeLessThan(300); // Much less than portrait
    expect(cardDimensions!.height).toBeGreaterThan(140); // But not too short
    // 16:9 landscape: 288px * 0.5625 = 162px (allow ±5px for browser rounding)
    expect(cardDimensions!.height).toBeGreaterThanOrEqual(157);
    expect(cardDimensions!.height).toBeLessThanOrEqual(167);
  });

  test('Video rail uses Editorial Brutalism design tokens', async ({ page, request }) => {
    // Reset database
    await request.delete('/wp-json/gcb-testing/v1/reset', {
      headers: { 'GCB-Test-Key': 'test-secret-key-local' }
    });

    // Create 1 video post
    await request.post('/wp-json/gcb-testing/v1/create-post', {
      data: {
        title: 'Design Tokens Test Video',
        content: 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        status: 'publish'
      },
      headers: {
        'Content-Type': 'application/json',
        'GCB-Test-Key': 'test-secret-key-local'
      }
    });

    await page.goto('/', { waitUntil: 'domcontentloaded' });

    // Assert: Video rail has Acid Lime accent
    const videoRail = page.locator('.gcb-video-rail, [data-pattern="video-rail"]');
    const accentColor = await videoRail.evaluate((el) => {
      // Check for border-color or background-color containing Acid Lime (#CCFF00)
      const styles = window.getComputedStyle(el);
      return styles.borderColor || styles.backgroundColor || '';
    });

    // Check if any element in the rail uses Acid Lime
    const hasAcidLime = accentColor.includes('rgb(204, 255, 0)') ||
                        accentColor.includes('#ccff00');

    // If not on container, check video cards
    if (!hasAcidLime) {
      const cardAccent = await page.locator('.gcb-video-card, .video-rail-item').first().evaluate((el) => {
        const styles = window.getComputedStyle(el);
        const border = styles.borderColor || '';
        const bg = styles.backgroundColor || '';
        return border + bg;
      });
      expect(cardAccent).toMatch(/rgb\(204, 255, 0\)|#ccff00/i);
    } else {
      expect(hasAcidLime).toBeTruthy();
    }

    // Assert: Video card titles use Space Mono or Playfair
    const cardTitle = page.locator('.gcb-video-card__title, .video-title, h3, h4').first();
    const titleFont = await cardTitle.evaluate((el) => {
      return window.getComputedStyle(el).fontFamily;
    });
    expect(titleFont).toMatch(/Space Mono|Playfair|JetBrains Mono/i);
  });

  test('Video rail cards link to individual video posts', async ({ page, request }) => {
    // Reset database
    await request.delete('/wp-json/gcb-testing/v1/reset', {
      headers: { 'GCB-Test-Key': 'test-secret-key-local' }
    });

    // Create 2 video posts
    const createResponse = await request.post('/wp-json/gcb-testing/v1/create-post', {
      data: {
        title: 'Clickable Video Test',
        content: 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        status: 'publish'
      },
      headers: {
        'Content-Type': 'application/json',
        'GCB-Test-Key': 'test-secret-key-local'
      }
    });

    const post = await createResponse.json();

    await page.goto('/', { waitUntil: 'domcontentloaded' });

    // Assert: Video card has link to post
    const videoCard = page.locator('.gcb-video-card, .video-rail-item').first();
    const cardLink = videoCard.locator('a[href*="clickable-video-test"], a').first();
    await expect(cardLink).toBeVisible();

    // Click card and verify navigation to single post
    await Promise.all([
      page.waitForURL(/clickable-video-test/, { timeout: 30000 }),
      cardLink.click()
    ]);

    // Verify we're on the single post page
    const postTitle = page.locator('h1, .wp-block-post-title, .entry-title');
    await expect(postTitle).toContainText('Clickable Video Test');
  });

  test('Video rail shows video metadata (duration, views, date)', async ({ page, request }) => {
    // Reset database
    await request.delete('/wp-json/gcb-testing/v1/reset', {
      headers: { 'GCB-Test-Key': 'test-secret-key-local' }
    });

    // Create video post with metadata
    await request.post('/wp-json/gcb-testing/v1/create-post', {
      data: {
        title: 'Metadata Display Test',
        content: 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        status: 'publish'
      },
      headers: {
        'Content-Type': 'application/json',
        'GCB-Test-Key': 'test-secret-key-local'
      }
    });

    await page.goto('/', { waitUntil: 'domcontentloaded' });

    const videoCard = page.locator('.gcb-video-card, .video-rail-item').first();

    // Assert: Duration is displayed (from YouTube API)
    const hasDuration = await videoCard.locator('.gcb-video-card__duration, .video-duration, [data-duration]').count() > 0;
    expect(hasDuration).toBeTruthy();

    // Assert: Post date is displayed
    const hasDate = await videoCard.locator('.gcb-video-card__date, .video-date, time, .post-date').count() > 0;
    expect(hasDate).toBeTruthy();
  });

  test('Video rail cards display acid lime play button overlay', async ({ page, request }) => {
    // Reset database
    await request.delete('/wp-json/gcb-testing/v1/reset', {
      headers: { 'GCB-Test-Key': 'test-secret-key-local' }
    });

    // Create video post
    await request.post('/wp-json/gcb-testing/v1/create-post', {
      data: {
        title: 'Play Button Test Video',
        content: 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        status: 'publish'
      },
      headers: {
        'Content-Type': 'application/json',
        'GCB-Test-Key': 'test-secret-key-local'
      }
    });

    await page.goto('/', { waitUntil: 'domcontentloaded' });

    // Assert: Play button SVG exists with acid lime color
    const playButton = page.locator('.video-play-button, svg[viewBox="0 0 100 100"]').first();
    await expect(playButton).toBeVisible();

    const color = await playButton.evaluate(el => window.getComputedStyle(el).color);
    expect(color).toBe('rgb(204, 255, 0)'); // Acid Lime
  });

  test('Video rail displays metadata in prototype format (duration • views)', async ({ page, request }) => {
    // Reset database
    await request.delete('/wp-json/gcb-testing/v1/reset', {
      headers: { 'GCB-Test-Key': 'test-secret-key-local' }
    });

    // Create video post
    await request.post('/wp-json/gcb-testing/v1/create-post', {
      data: {
        title: 'Metadata Format Test',
        content: 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        status: 'publish'
      },
      headers: {
        'Content-Type': 'application/json',
        'GCB-Test-Key': 'test-secret-key-local'
      }
    });

    await page.goto('/', { waitUntil: 'domcontentloaded' });

    const videoCard = page.locator('.gcb-video-card, .video-rail-item').first();
    const metaText = await videoCard.locator('.gcb-video-card__meta').textContent();

    // Assert: Metadata format matches "duration • views" pattern
    expect(metaText).toMatch(/\d+:\d+ • \d+[KM]? Views/); // e.g., "3:33 • 245K Views"
  });

  test('Video rail cards have brutal border that changes to acid lime on hover', async ({ page, request }) => {
    // Reset database
    await request.delete('/wp-json/gcb-testing/v1/reset', {
      headers: { 'GCB-Test-Key': 'test-secret-key-local' }
    });

    // Create video post
    await request.post('/wp-json/gcb-testing/v1/create-post', {
      data: {
        title: 'Border Hover Test',
        content: 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        status: 'publish'
      },
      headers: {
        'Content-Type': 'application/json',
        'GCB-Test-Key': 'test-secret-key-local'
      }
    });

    await page.goto('/', { waitUntil: 'domcontentloaded' });

    const card = page.locator('.gcb-video-card, .video-rail-item').first();

    // Default state: Brutal Border
    const defaultBorder = await card.evaluate(el => window.getComputedStyle(el).borderColor);
    expect(defaultBorder).toBe('rgb(51, 51, 51)'); // #333333

    // Hover state: Acid Lime
    await card.hover();
    const hoverBorder = await card.evaluate(el => window.getComputedStyle(el).borderColor);
    expect(hoverBorder).toBe('rgb(204, 255, 0)'); // #CCFF00
  });

  test('Video rail header includes "View All" link', async ({ page, request }) => {
    // Reset database
    await request.delete('/wp-json/gcb-testing/v1/reset', {
      headers: { 'GCB-Test-Key': 'test-secret-key-local' }
    });

    // Create video post
    await request.post('/wp-json/gcb-testing/v1/create-post', {
      data: {
        title: 'View All Link Test',
        content: 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        status: 'publish'
      },
      headers: {
        'Content-Type': 'application/json',
        'GCB-Test-Key': 'test-secret-key-local'
      }
    });

    await page.goto('/', { waitUntil: 'domcontentloaded' });

    const viewAllLink = page.locator('a[href*="video"]:has-text("View All")');
    await expect(viewAllLink).toBeVisible();

    // Verify touch target
    const bbox = await viewAllLink.boundingBox();
    expect(bbox?.height).toBeGreaterThanOrEqual(44);
  });
});
