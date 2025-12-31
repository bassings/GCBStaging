import { test, expect } from '@playwright/test';

test.describe('GCB Content Intelligence - Video Rail Pattern', () => {

  test('Displays horizontal scrolling video rail with multiple videos', async ({ page, request }) => {
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
    await page.goto('/', { waitUntil: 'networkidle' });

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
    await page.goto('/', { waitUntil: 'networkidle' });

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

    await page.goto('/', { waitUntil: 'networkidle' });

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

    await page.goto('/', { waitUntil: 'networkidle' });

    // Assert: Video card has link to post
    const videoCard = page.locator('.gcb-video-card, .video-rail-item').first();
    const cardLink = videoCard.locator('a[href*="clickable-video-test"], a');
    await expect(cardLink).toBeVisible();

    // Click card and verify navigation to single post
    await cardLink.click();
    await page.waitForURL(/clickable-video-test/);

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

    await page.goto('/', { waitUntil: 'networkidle' });

    const videoCard = page.locator('.gcb-video-card, .video-rail-item').first();

    // Assert: Duration is displayed (from YouTube API)
    const hasDuration = await videoCard.locator('.gcb-video-card__duration, .video-duration, [data-duration]').count() > 0;
    expect(hasDuration).toBeTruthy();

    // Assert: Post date is displayed
    const hasDate = await videoCard.locator('.gcb-video-card__date, .video-date, time, .post-date').count() > 0;
    expect(hasDate).toBeTruthy();
  });
});
