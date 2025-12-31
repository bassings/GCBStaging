import { test, expect } from '@playwright/test';
import { createDatabaseHelper } from '@utils/database';
import { activateTheme } from '@utils/theme';
import { createPost } from '@utils/wordpress';

/**
 * No Stickers Test
 *
 * Confirms that sticker functionality has been completely removed from the site.
 * Stickers should NOT appear on any posts.
 */
test.describe('No Stickers - Feature Removed', () => {

  test.beforeEach(async ({ request, page }) => {
    // Reset database for clean slate
    const dbHelper = createDatabaseHelper(request);
    await dbHelper.reset();

    // Activate gcb-brutalist theme
    await activateTheme(request, 'gcb-brutalist');
  });

  test('stickers do NOT appear on posts', async ({ page }) => {
    // Arrange: Create a test post
    await createPost(page, 'Test Post Without Stickers', '<p>This post should have NO stickers.</p>');

    // Act: Navigate to the post
    await page.goto('/');
    const firstPostLink = page.locator('.wp-block-post-title a').first();
    await firstPostLink.click();
    await page.waitForLoadState('networkidle');

    // Assert: NO stickers are present
    const stickers = page.locator('.gcb-sticker');
    await expect(stickers).toHaveCount(0);

    console.log('✅ Confirmed: No stickers found on post');
  });

  test('no sticker JavaScript is loaded', async ({ page }) => {
    // Arrange: Create post
    await createPost(page, 'JS Test Post', '<p>Testing JavaScript removal.</p>');

    // Act: Navigate to post
    await page.goto('/');
    const firstPostLink = page.locator('.wp-block-post-title a').first();
    await firstPostLink.click();
    await page.waitForLoadState('networkidle');

    // Assert: GCBStickerDragger class does NOT exist
    const hasStrickerClass = await page.evaluate(() => {
      return typeof (window as any).GCBStickerDragger !== 'undefined';
    });

    expect(hasStrickerClass).toBe(false);

    console.log('✅ Confirmed: Sticker JavaScript not loaded');
  });

  test('no sticker CSS is loaded', async ({ page }) => {
    // Arrange: Create post
    await createPost(page, 'CSS Test Post', '<p>Testing CSS removal.</p>');

    // Act: Navigate to post
    await page.goto('/');
    const firstPostLink = page.locator('.wp-block-post-title a').first();
    await firstPostLink.click();
    await page.waitForLoadState('networkidle');

    // Assert: No gcb-stickers stylesheet in DOM
    const stickerStylesheet = page.locator('link[href*="stickers.css"]');
    await expect(stickerStylesheet).toHaveCount(0);

    console.log('✅ Confirmed: Sticker CSS not loaded');
  });
});
