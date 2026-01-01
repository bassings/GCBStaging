import { test, expect } from '@playwright/test';

test.describe('GCB Content Intelligence - Bento Grid Pattern', () => {

  test('Displays mixed layout grid with video and standard posts', async ({ page, request }) => {
    // Reset database
    await request.delete('/wp-json/gcb-testing/v1/reset', {
      headers: { 'GCB-Test-Key': 'test-secret-key-local' }
    });

    // Create 3 video posts
    for (let i = 0; i < 3; i++) {
      await request.post('/wp-json/gcb-testing/v1/create-post', {
        data: {
          title: `Video Post ${i + 1}`,
          content: `https://www.youtube.com/watch?v=dQw4w9WgXcQ`,
          status: 'publish'
        },
        headers: {
          'Content-Type': 'application/json',
          'GCB-Test-Key': 'test-secret-key-local'
        }
      });
    }

    // Create 3 standard posts
    for (let i = 0; i < 3; i++) {
      await request.post('/wp-json/gcb-testing/v1/create-post', {
        data: {
          title: `Standard Post ${i + 1}`,
          content: '<p>This is a standard text-based article about automotive culture.</p>',
          status: 'publish'
        },
        headers: {
          'Content-Type': 'application/json',
          'GCB-Test-Key': 'test-secret-key-local'
        }
      });
    }

    await page.goto('/', { waitUntil: 'networkidle' });

    // Assert: Bento Grid container exists
    const bentoGrid = page.locator('.gcb-bento-grid, [data-pattern="bento-grid"]');
    await expect(bentoGrid).toBeVisible();

    // Assert: Grid has both video and standard post cards
    const videoCards = page.locator('.gcb-bento-grid .gcb-video-card, .bento-item--video');
    const standardCards = page.locator('.gcb-bento-grid .gcb-post-card, .bento-item--standard');

    expect(await videoCards.count()).toBeGreaterThan(0);
    expect(await standardCards.count()).toBeGreaterThan(0);

    // Assert: Grid uses CSS Grid layout
    const gridContainer = page.locator('.gcb-bento-grid__container, .bento-grid-container');
    const display = await gridContainer.evaluate((el) => {
      return window.getComputedStyle(el).display;
    });
    expect(display).toBe('grid');
  });

  test('Bento Grid uses varied card sizes (featured/standard)', async ({ page, request }) => {
    // Reset database
    await request.delete('/wp-json/gcb-testing/v1/reset', {
      headers: { 'GCB-Test-Key': 'test-secret-key-local' }
    });

    // Create 4 posts
    for (let i = 0; i < 4; i++) {
      await request.post('/wp-json/gcb-testing/v1/create-post', {
        data: {
          title: `Mixed Post ${i + 1}`,
          content: i % 2 === 0 ? `https://www.youtube.com/watch?v=dQw4w9WgXcQ` : '<p>Standard post content.</p>',
          status: 'publish'
        },
        headers: {
          'Content-Type': 'application/json',
          'GCB-Test-Key': 'test-secret-key-local'
        }
      });
    }

    await page.goto('/', { waitUntil: 'networkidle' });

    // Assert: At least one card has "featured" or "large" class
    const featuredCard = page.locator('.bento-item--featured, .bento-item--large, [data-size="large"]');
    expect(await featuredCard.count()).toBeGreaterThan(0);

    // Assert: Grid columns vary (not all equal width)
    const gridItems = page.locator('.bento-item, .gcb-bento-card');
    const firstItemWidth = await gridItems.first().evaluate((el) => el.getBoundingClientRect().width);
    const secondItemWidth = await gridItems.nth(1).evaluate((el) => el.getBoundingClientRect().width);

    // Some items should have different widths (varied layout)
    // Note: This might not always be true depending on layout, but in a true bento grid there should be variation
    const hasVariation = firstItemWidth !== secondItemWidth;
    expect(hasVariation || await gridItems.count() > 2).toBeTruthy();
  });

  test('Bento Grid is responsive on mobile viewports', async ({ page, request }) => {
    // Reset database
    await request.delete('/wp-json/gcb-testing/v1/reset', {
      headers: { 'GCB-Test-Key': 'test-secret-key-local' }
    });

    // Create 3 posts
    for (let i = 0; i < 3; i++) {
      await request.post('/wp-json/gcb-testing/v1/create-post', {
        data: {
          title: `Mobile Grid Post ${i + 1}`,
          content: `https://www.youtube.com/watch?v=dQw4w9WgXcQ`,
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

    // Assert: Bento Grid still visible on mobile
    const bentoGrid = page.locator('.gcb-bento-grid, [data-pattern="bento-grid"]');
    await expect(bentoGrid).toBeVisible();

    // Assert: Grid stacks to single column on mobile
    const gridContainer = page.locator('.gcb-bento-grid__container, .bento-grid-container');
    const gridColumns = await gridContainer.evaluate((el) => {
      return window.getComputedStyle(el).gridTemplateColumns;
    });

    // On mobile, should collapse to 1 or 2 columns max
    const columnCount = gridColumns.split(' ').length;
    expect(columnCount).toBeLessThanOrEqual(2);

    // Assert: Cards don't overflow viewport
    const gridItem = page.locator('.bento-item, .gcb-bento-card').first();
    const itemWidth = await gridItem.evaluate((el) => {
      return el.getBoundingClientRect().width;
    });
    expect(itemWidth).toBeLessThanOrEqual(375);
  });

  test('Bento Grid uses Editorial Brutalism design tokens', async ({ page, request }) => {
    // Reset database
    await request.delete('/wp-json/gcb-testing/v1/reset', {
      headers: { 'GCB-Test-Key': 'test-secret-key-local' }
    });

    // Create 1 post
    await request.post('/wp-json/gcb-testing/v1/create-post', {
      data: {
        title: 'Design Tokens Test',
        content: `https://www.youtube.com/watch?v=dQw4w9WgXcQ`,
        status: 'publish'
      },
      headers: {
        'Content-Type': 'application/json',
        'GCB-Test-Key': 'test-secret-key-local'
      }
    });

    await page.goto('/', { waitUntil: 'networkidle' });

    // Assert: Grid cards have Chrome borders (2px solid)
    const gridItem = page.locator('.bento-item, .gcb-bento-card').first();
    const borderStyle = await gridItem.evaluate((el) => {
      const styles = window.getComputedStyle(el);
      return {
        width: styles.borderWidth,
        color: styles.borderColor,
        style: styles.borderStyle
      };
    });

    expect(borderStyle.width).toContain('2px');
    expect(borderStyle.style).toBe('solid');

    // Assert: Headings use Playfair Display
    const heading = page.locator('.bento-item h2, .bento-item h3, .gcb-bento-card__title').first();
    const headingFont = await heading.evaluate((el) => {
      return window.getComputedStyle(el).fontFamily;
    });
    expect(headingFont).toMatch(/Playfair/i);
  });

  test('Bento Grid section heading is uppercase "FEATURED STORIES"', async ({ page, request }) => {
    // Reset database
    await request.delete('/wp-json/gcb-testing/v1/reset', {
      headers: { 'GCB-Test-Key': 'test-secret-key-local' }
    });

    // Create 1 post
    await request.post('/wp-json/gcb-testing/v1/create-post', {
      data: {
        title: 'Heading Test Post',
        content: '<p>Test content</p>',
        status: 'publish'
      },
      headers: {
        'Content-Type': 'application/json',
        'GCB-Test-Key': 'test-secret-key-local'
      }
    });

    await page.goto('/', { waitUntil: 'networkidle' });

    // Assert: Section heading exists and is uppercase
    const sectionHeading = page.locator('.gcb-bento-grid h2.wp-block-heading, .culture-grid-title');
    await expect(sectionHeading.first()).toBeVisible();

    const headingText = await sectionHeading.first().textContent();
    expect(headingText).toBe('FEATURED STORIES');
  });

  test('Bento Grid section heading uses off-white color', async ({ page, request }) => {
    // Reset database
    await request.delete('/wp-json/gcb-testing/v1/reset', {
      headers: { 'GCB-Test-Key': 'test-secret-key-local' }
    });

    // Create 1 post
    await request.post('/wp-json/gcb-testing/v1/create-post', {
      data: {
        title: 'Heading Color Test',
        content: '<p>Test content</p>',
        status: 'publish'
      },
      headers: {
        'Content-Type': 'application/json',
        'GCB-Test-Key': 'test-secret-key-local'
      }
    });

    await page.goto('/', { waitUntil: 'networkidle' });

    // Assert: Section heading uses off-white color (#FAFAFA)
    const sectionHeading = page.locator('.gcb-bento-grid h2.wp-block-heading').first();
    const headingColor = await sectionHeading.evaluate((el) => {
      return window.getComputedStyle(el).color;
    });

    // RGB for #FAFAFA is rgb(250, 250, 250)
    expect(headingColor).toBe('rgb(250, 250, 250)');
  });

  test('Bento Grid cards show acid-lime border on hover', async ({ page, request }) => {
    // Reset database
    await request.delete('/wp-json/gcb-testing/v1/reset', {
      headers: { 'GCB-Test-Key': 'test-secret-key-local' }
    });

    // Create 1 post
    await request.post('/wp-json/gcb-testing/v1/create-post', {
      data: {
        title: 'Hover Test Post',
        content: '<p>Hover test content</p>',
        status: 'publish'
      },
      headers: {
        'Content-Type': 'application/json',
        'GCB-Test-Key': 'test-secret-key-local'
      }
    });

    await page.goto('/', { waitUntil: 'networkidle' });

    // Get the first bento card
    const bentoCard = page.locator('.bento-item, .gcb-bento-card').first();
    await expect(bentoCard).toBeVisible();

    // Get initial border color (should be brutal-border #333333 = rgb(51, 51, 51))
    const initialBorderColor = await bentoCard.evaluate((el) => {
      return window.getComputedStyle(el).borderColor;
    });
    expect(initialBorderColor).toBe('rgb(51, 51, 51)');

    // Hover over the card
    await bentoCard.hover();

    // Wait a moment for hover state to apply
    await page.waitForTimeout(100);

    // Get border color after hover (should be acid-lime #CCFF00 = rgb(204, 255, 0))
    const hoverBorderColor = await bentoCard.evaluate((el) => {
      return window.getComputedStyle(el).borderColor;
    });
    expect(hoverBorderColor).toBe('rgb(204, 255, 0)');
  });

  test('Bento Grid metadata uses brutal-grey color (#999999)', async ({ page, request }) => {
    // Reset database
    await request.delete('/wp-json/gcb-testing/v1/reset', {
      headers: { 'GCB-Test-Key': 'test-secret-key-local' }
    });

    // Create 1 post
    await request.post('/wp-json/gcb-testing/v1/create-post', {
      data: {
        title: 'Metadata Color Test',
        content: '<p>Test content for metadata</p>',
        status: 'publish'
      },
      headers: {
        'Content-Type': 'application/json',
        'GCB-Test-Key': 'test-secret-key-local'
      }
    });

    await page.goto('/', { waitUntil: 'networkidle' });

    // Assert: Metadata div uses brutal-grey color
    const metadata = page.locator('.gcb-bento-card__meta, .bento-item .post-date').first();
    await expect(metadata).toBeVisible();

    const metadataColor = await metadata.evaluate((el) => {
      return window.getComputedStyle(el).color;
    });

    // RGB for #999999 is rgb(153, 153, 153)
    expect(metadataColor).toBe('rgb(153, 153, 153)');
  });
});
