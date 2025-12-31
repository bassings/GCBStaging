import { test, expect } from '@playwright/test';

/**
 * Database Reset API E2E Tests
 *
 * These tests validate the gcb-test-utils plugin's database reset functionality.
 * The reset endpoint is critical for maintaining test isolation and preventing
 * flaky tests caused by stale data.
 *
 * TDD Workflow: This test is written FIRST (RED phase) before the plugin implementation.
 */
test.describe('Database Reset API', () => {

  test('should reset database and remove all posts', async ({ page, request }) => {
    const testPostTitle = `Test Post ${Date.now()}`;
    const resetEndpoint = '/wp-json/gcb-testing/v1/reset';
    const testKey = 'test-secret-key-local';

    // Step 1: Create a test post
    await page.goto('/wp-admin/post-new.php');
    await page.waitForLoadState('networkidle');

    // Fill in post title (handles both Block Editor and Classic Editor)
    const titleInput = page.locator('textarea[placeholder*="Add title"], #title');
    await titleInput.fill(testPostTitle);

    // Publish the post
    const publishButton = page.locator('button:has-text("Publish"), #publish');
    await publishButton.click();

    // Handle Gutenberg's two-step publish (if present)
    const confirmButton = page.locator('button:has-text("Publish")').last();
    if (await confirmButton.isVisible({ timeout: 2000 }).catch(() => false)) {
      await confirmButton.click();
    }

    // Verify post was published
    await expect(page.locator('text=/Published|Post published/')).toBeVisible({ timeout: 10000 });

    // Step 2: Call database reset endpoint
    const response = await request.delete(resetEndpoint, {
      headers: {
        'X-Test-Key': testKey,
        'Content-Type': 'application/json',
      },
    });

    // Step 3: Assertions on reset response
    expect(response.ok()).toBeTruthy();
    expect(response.status()).toBe(200);

    const responseBody = await response.json();
    expect(responseBody).toHaveProperty('success', true);
    expect(responseBody).toHaveProperty('deleted_posts');
    expect(responseBody.deleted_posts).toBeGreaterThanOrEqual(1);

    console.log('✅ Database reset response:', responseBody);

    // Step 4: Verify post was deleted from frontend
    await page.goto('/');
    await page.waitForLoadState('networkidle');

    const postExists = await page.locator(`text="${testPostTitle}"`)
      .isVisible({ timeout: 2000 })
      .catch(() => false);

    expect(postExists).toBe(false);
  });

  test('should reject requests without valid test key', async ({ request }) => {
    const resetEndpoint = '/wp-json/gcb-testing/v1/reset';

    // Test 1: No test key provided
    const response1 = await request.delete(resetEndpoint);
    expect(response1.status()).toBe(401);
    console.log('✅ Request without test key rejected (401)');

    // Test 2: Wrong test key provided
    const response2 = await request.delete(resetEndpoint, {
      headers: { 'X-Test-Key': 'wrong-key-invalid' },
    });
    expect(response2.status()).toBe(401);
    console.log('✅ Request with wrong test key rejected (401)');
  });
});
