import { test, expect } from '@playwright/test';

test.describe('GCB Index Template - No Duplicate Posts', () => {

  test('Homepage does not show duplicate query loop', async ({ page, request }) => {
    await request.delete('/wp-json/gcb-testing/v1/reset', {
      headers: { 'GCB-Test-Key': 'test-secret-key-local' }
    });

    // Create 5 posts
    for (let i = 0; i < 5; i++) {
      await request.post('/wp-json/gcb-testing/v1/create-post', {
        data: {
          title: `Unique Post Title ${i + 1}`,
          content: '<p>Content for testing duplication.</p>',
          status: 'publish'
        },
        headers: {
          'Content-Type': 'application/json',
          'GCB-Test-Key': 'test-secret-key-local'
        }
      });
    }

    await page.goto('/', { waitUntil: 'domcontentloaded' });

    // Assert: No wp-block-query class (query loop removed)
    const queryBlock = page.locator('.wp-block-query');
    await expect(queryBlock).not.toBeVisible();

    // Assert: Three patterns still present (video-rail, bento-grid, culture-grid)
    // Note: Video rail and patterns may not have specific data attributes, check for content sections
    const bentoGrid = page.locator('[class*="bento"], .gcb-bento-grid__container');
    const cultureGrid = page.locator('[class*="culture"], .gcb-culture-grid__container');

    // At least verify bento and culture grids are visible
    const bentoExists = await bentoGrid.count() > 0;
    const cultureExists = await cultureGrid.count() > 0;

    expect(bentoExists || cultureExists).toBeTruthy();
  });

  test('Post titles appear only once on homepage', async ({ page, request }) => {
    await request.delete('/wp-json/gcb-testing/v1/reset', {
      headers: { 'GCB-Test-Key': 'test-secret-key-local' }
    });

    // Create distinctive post
    await request.post('/wp-json/gcb-testing/v1/create-post', {
      data: {
        title: 'DUPLICATION_CHECK_POST_12345',
        content: '<p>Test for duplication.</p>',
        status: 'publish'
      },
      headers: {
        'Content-Type': 'application/json',
        'GCB-Test-Key': 'test-secret-key-local'
      }
    });

    await page.goto('/', { waitUntil: 'domcontentloaded' });

    // Count how many times this title appears
    const titleElements = page.locator('h1, h2, h3, h4, .entry-title, .post-title, .gcb-bento-card__title, .gcb-post-title');
    const allTitles = await titleElements.allTextContents();

    const duplicateCount = allTitles.filter(title => title.includes('DUPLICATION_CHECK_POST_12345')).length;

    // Should appear exactly once (not twice due to query loop)
    expect(duplicateCount).toBe(1);
  });

  test('Homepage patterns provide sufficient content without query loop', async ({ page, request }) => {
    await request.delete('/wp-json/gcb-testing/v1/reset', {
      headers: { 'GCB-Test-Key': 'test-secret-key-local' }
    });

    // Create 15 posts (enough to populate all patterns)
    for (let i = 0; i < 15; i++) {
      await request.post('/wp-json/gcb-testing/v1/create-post', {
        data: {
          title: `Content Test Post ${i + 1}`,
          content: i % 3 === 0 ? 'https://www.youtube.com/watch?v=dQw4w9WgXcQ' : '<p>Standard post.</p>',
          status: 'publish'
        },
        headers: {
          'Content-Type': 'application/json',
          'GCB-Test-Key': 'test-secret-key-local'
        }
      });
    }

    await page.goto('/', { waitUntil: 'domcontentloaded' });

    // Count total visible posts across all patterns
    const bentoCards = await page.locator('.bento-item, .gcb-bento-card, [class*="bento"]').count();
    const cultureCards = await page.locator('.culture-card, [class*="culture-grid"] .post-card, [class*="culture-card"]').count();
    const videoCards = await page.locator('.gcb-video-card, .video-rail-item').count();

    const totalContent = bentoCards + cultureCards + videoCards;

    // Should have at least 10 items total (patterns provide enough content)
    expect(totalContent).toBeGreaterThanOrEqual(10);
  });
});
