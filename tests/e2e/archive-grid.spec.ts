import { test, expect } from '@playwright/test';
import { createDatabaseHelper } from '@utils/database';
import { activateTheme } from '@utils/theme';
import { createPost } from '@utils/wordpress';

/**
 * Archive Broken Grid Layout E2E Tests
 *
 * Tests the brutalist "broken grid" layout for the blog archive/index page.
 * Design philosophy: intentional disruption via offset rhythm creates visual tension.
 *
 * Grid Configuration:
 * - Desktop: 3 columns
 * - Tablet: 2 columns (782px breakpoint)
 * - Mobile: 1 column (600px breakpoint)
 *
 * Design Elements:
 * - Every 2nd post card has 50px vertical offset (broken grid effect)
 * - Hard borders: 2px solid Void Black (#050505)
 * - Hover effect: Hyper Blue (#3366FF) on post titles
 * - No smooth transitions (brutalist = instant snap)
 */
test.describe('Archive Broken Grid Layout', () => {

  test.beforeEach(async ({ request, page }) => {
    // Reset database for clean slate
    const dbHelper = createDatabaseHelper(request);
    await dbHelper.reset();

    // Activate gcb-brutalist theme
    await activateTheme(request, 'gcb-brutalist');

    // Create 6 sample posts for grid display
    for (let i = 1; i <= 6; i++) {
      await createPost(page, `Test Post ${i}`, `This is content for test post ${i}. Editorial brutalism in action.`);
    }
  });

  test('archive displays posts in 3-column grid (not single column)', async ({ page }) => {
    await page.goto('/');
    await page.waitForLoadState('networkidle');

    const postGrid = page.locator('.wp-block-post-template');

    // Check grid container exists
    await expect(postGrid).toBeVisible();

    // Get computed grid-template-columns
    const gridCols = await postGrid.evaluate(el => {
      return window.getComputedStyle(el).gridTemplateColumns;
    });

    // Should have 3 columns (not 1 single column)
    const columnCount = gridCols.split(' ').length;
    expect(columnCount).toBe(3);

    console.log(`✅ Grid has ${columnCount} columns:`, gridCols);
  });

  test('every 2nd post card has vertical offset (broken grid effect)', async ({ page }) => {
    await page.goto('/');
    await page.waitForLoadState('networkidle');

    const postCards = page.locator('.wp-block-post');
    const secondCard = postCards.nth(1); // 2nd card (index 1)

    // Get margin-top of 2nd card
    const marginTop = await secondCard.evaluate(el => {
      return window.getComputedStyle(el).marginTop;
    });

    // Should have 50px offset
    expect(marginTop).toBe('50px');

    console.log('✅ 2nd post card has broken grid offset:', marginTop);
  });

  test('each post card has visible black border', async ({ page }) => {
    await page.goto('/');
    await page.waitForLoadState('networkidle');

    const firstPostCard = page.locator('.wp-block-post').first();

    const border = await firstPostCard.evaluate(el => {
      const styles = window.getComputedStyle(el);
      return {
        width: styles.borderWidth,
        style: styles.borderStyle,
        color: styles.borderColor,
      };
    });

    expect(border.width).toBe('2px');
    expect(border.style).toBe('solid');
    expect(border.color).toBe('rgb(5, 5, 5)'); // Void Black #050505

    console.log('✅ Post card border:', border);
  });

  test('post titles use Hyper Blue on hover', async ({ page }) => {
    await page.goto('/');
    await page.waitForLoadState('networkidle');

    const firstPostTitle = page.locator('.wp-block-post-title a').first();

    // Default color should be Hyper White
    const defaultColor = await firstPostTitle.evaluate(el => {
      return window.getComputedStyle(el).color;
    });
    expect(defaultColor).toBe('rgb(250, 250, 250)'); // Hyper White #FAFAFA

    // Hover and check color change to Hyper Blue
    await firstPostTitle.hover();

    const hoverColor = await firstPostTitle.evaluate(el => {
      return window.getComputedStyle(el).color;
    });
    expect(hoverColor).toBe('rgb(51, 102, 255)'); // Hyper Blue #3366FF

    console.log('✅ Post title colors - Default:', defaultColor, '→ Hover:', hoverColor);
  });

  test('hover transition is instant (no fade - brutalist snap effect)', async ({ page }) => {
    await page.goto('/');
    await page.waitForLoadState('networkidle');

    const firstPostTitle = page.locator('.wp-block-post-title a').first();

    const transition = await firstPostTitle.evaluate(el => {
      return window.getComputedStyle(el).transition;
    });

    // Should be 'none' or '0s' (brutalist = snap, not fade)
    expect(transition).toMatch(/none|0s|all 0s/);

    console.log('✅ Transition style:', transition);
  });

  test('4th post card also has broken grid offset (every 2nd = 2, 4, 6)', async ({ page }) => {
    await page.goto('/');
    await page.waitForLoadState('networkidle');

    const postCards = page.locator('.wp-block-post');
    const fourthCard = postCards.nth(3); // 4th card (index 3)

    const marginTop = await fourthCard.evaluate(el => {
      return window.getComputedStyle(el).marginTop;
    });

    // 4th card should also have offset (every 2nd card)
    expect(marginTop).toBe('50px');

    console.log('✅ 4th post card also has offset:', marginTop);
  });
});

test.describe('Archive Grid - Responsive Behavior', () => {

  test.beforeEach(async ({ request, page }) => {
    const dbHelper = createDatabaseHelper(request);
    await dbHelper.reset();

    await activateTheme(request, 'gcb-brutalist');

    // Create posts for responsive testing
    for (let i = 1; i <= 4; i++) {
      await createPost(page, `Responsive Post ${i}`, `Content ${i}`);
    }
  });

  test('grid collapses to 2 columns on tablet viewport', async ({ page }) => {
    // Set tablet viewport (782px is WordPress tablet breakpoint)
    await page.setViewportSize({ width: 782, height: 1024 });

    await page.goto('/');
    await page.waitForLoadState('networkidle');

    const postGrid = page.locator('.wp-block-post-template');

    const gridCols = await postGrid.evaluate(el => {
      return window.getComputedStyle(el).gridTemplateColumns;
    });

    const columnCount = gridCols.split(' ').length;
    expect(columnCount).toBe(2);

    console.log('✅ Tablet grid has 2 columns:', gridCols);
  });

  test('grid collapses to 1 column on mobile viewport', async ({ page }) => {
    // Set mobile viewport
    await page.setViewportSize({ width: 375, height: 667 });

    await page.goto('/');
    await page.waitForLoadState('networkidle');

    const postGrid = page.locator('.wp-block-post-template');

    const gridCols = await postGrid.evaluate(el => {
      return window.getComputedStyle(el).gridTemplateColumns;
    });

    const columnCount = gridCols.split(' ').length;
    expect(columnCount).toBe(1);

    console.log('✅ Mobile grid has 1 column:', gridCols);
  });
});
