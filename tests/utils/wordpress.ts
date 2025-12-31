import { Page, expect } from '@playwright/test';

/**
 * WordPress Helper Utilities
 *
 * Provides common WordPress operations for E2E tests.
 */

/**
 * Navigate to WordPress Admin Area
 *
 * @param page - Playwright page object
 * @param path - Admin path (e.g., 'post-new.php')
 * @returns Promise that resolves when navigation completes
 */
export async function navigateToAdmin(page: Page, path: string = ''): Promise<void> {
  const adminPath = path.startsWith('/') ? path : `/${path}`;
  await page.goto(`/wp-admin${adminPath}`);
  await page.waitForLoadState('networkidle');
}

/**
 * Verify WordPress Admin Bar is Visible
 *
 * @param page - Playwright page object
 * @returns Promise that resolves when admin bar is confirmed visible
 */
export async function verifyAdminBar(page: Page): Promise<void> {
  await expect(page.locator('#wpadminbar')).toBeVisible({ timeout: 10000 });
}

/**
 * Create a WordPress Post
 *
 * @param page - Playwright page object
 * @param title - Post title
 * @param content - Post content (optional)
 * @returns Promise resolving to the post title
 */
export async function createPost(
  page: Page,
  title: string,
  content?: string
): Promise<string> {
  await navigateToAdmin(page, 'post-new.php');

  // Fill in title (handles both Block Editor and Classic Editor)
  const titleInput = page.locator('textarea[placeholder*="Add title"], #title');
  await titleInput.fill(title);

  // Fill in content if provided (Block Editor)
  if (content) {
    const contentBlock = page.locator('.editor-post-text-editor, #content');
    if (await contentBlock.isVisible({ timeout: 2000 }).catch(() => false)) {
      await contentBlock.fill(content);
    }
  }

  // Publish the post
  const publishButton = page.locator('button:has-text("Publish"), #publish');
  await publishButton.click();

  // Handle Gutenberg's two-step publish
  const confirmButton = page.locator('button:has-text("Publish")').last();
  if (await confirmButton.isVisible({ timeout: 2000 }).catch(() => false)) {
    await confirmButton.click();
  }

  // Wait for success message
  await expect(page.locator('text=/Published|Post published/')).toBeVisible({
    timeout: 10000,
  });

  return title;
}

/**
 * Create a WordPress Page
 *
 * @param page - Playwright page object
 * @param title - Page title
 * @param content - Page content (optional)
 * @returns Promise resolving to the page title
 */
export async function createPage(
  page: Page,
  title: string,
  content?: string
): Promise<string> {
  await navigateToAdmin(page, 'post-new.php?post_type=page');

  const titleInput = page.locator('textarea[placeholder*="Add title"], #title');
  await titleInput.fill(title);

  if (content) {
    const contentBlock = page.locator('.editor-post-text-editor, #content');
    if (await contentBlock.isVisible({ timeout: 2000 }).catch(() => false)) {
      await contentBlock.fill(content);
    }
  }

  const publishButton = page.locator('button:has-text("Publish"), #publish');
  await publishButton.click();

  const confirmButton = page.locator('button:has-text("Publish")').last();
  if (await confirmButton.isVisible({ timeout: 2000 }).catch(() => false)) {
    await confirmButton.click();
  }

  await expect(page.locator('text=/Published|Page published/')).toBeVisible({
    timeout: 10000,
  });

  return title;
}

/**
 * Verify Content Exists on Frontend
 *
 * @param page - Playwright page object
 * @param searchText - Text to search for
 * @returns Promise resolving to true if content is found
 */
export async function verifyContentExists(
  page: Page,
  searchText: string
): Promise<boolean> {
  await page.goto('/');
  await page.waitForLoadState('networkidle');

  return page.locator(`text="${searchText}"`).isVisible({ timeout: 5000 }).catch(() => false);
}

/**
 * Verify Content Does Not Exist on Frontend
 *
 * @param page - Playwright page object
 * @param searchText - Text to search for
 * @returns Promise resolving to true if content is NOT found
 */
export async function verifyContentDoesNotExist(
  page: Page,
  searchText: string
): Promise<boolean> {
  await page.goto('/');
  await page.waitForLoadState('networkidle');

  const exists = await page.locator(`text="${searchText}"`).isVisible({ timeout: 2000 }).catch(() => false);
  return !exists;
}
