import { test, expect } from '@playwright/test';
import { createDatabaseHelper } from '@utils/database';

/**
 * Smoke Tests - Infrastructure Validation
 *
 * These tests validate that all critical infrastructure components
 * are working correctly:
 * - WordPress is accessible
 * - WordPress admin is accessible
 * - REST API is functional
 * - Database reset endpoint works
 *
 * Run these tests first to ensure the environment is properly configured
 * before running feature-specific tests.
 */
test.describe('Smoke Tests - Infrastructure Validation', () => {

  test('WordPress is accessible', async ({ page }) => {
    await page.goto('/');
    expect(page.url()).toContain('localhost');
    console.log('✅ WordPress is accessible at', page.url());
  });

  test('WordPress admin is accessible', async ({ page }) => {
    await page.goto('/wp-admin');
    expect(page.url()).toContain('wp-admin');
    await expect(page.locator('#wpadminbar')).toBeVisible();
    console.log('✅ WordPress admin is accessible');
  });

  test('REST API is functional', async ({ request }) => {
    const response = await request.get('/wp-json/');
    expect(response.ok()).toBeTruthy();

    const data = await response.json();
    expect(data).toHaveProperty('name');
    expect(data).toHaveProperty('url');
    expect(data).toHaveProperty('namespaces');

    console.log('✅ REST API is functional');
    console.log('   Site name:', data.name);
    console.log('   Site URL:', data.url);
  });

  test('REST API namespaces include gcb-testing', async ({ request }) => {
    const response = await request.get('/wp-json/');
    expect(response.ok()).toBeTruthy();

    const data = await response.json();
    expect(data.namespaces).toContain('gcb-testing/v1');

    console.log('✅ gcb-testing/v1 namespace is registered');
  });

  test('Database reset endpoint is accessible', async ({ request, baseURL }) => {
    const dbHelper = createDatabaseHelper(request, baseURL);
    const endpointExists = await dbHelper.verifyEndpointExists();

    expect(endpointExists).toBe(true);
    console.log('✅ Database reset endpoint is accessible');
  });

  test('Database reset endpoint works', async ({ request, baseURL }) => {
    const dbHelper = createDatabaseHelper(request, baseURL);

    try {
      const result = await dbHelper.reset();
      expect(result.success).toBe(true);
      expect(result).toHaveProperty('deleted_posts');
      expect(result).toHaveProperty('deleted_pages');
      expect(result).toHaveProperty('deleted_media');
      expect(result).toHaveProperty('deleted_terms');

      console.log('✅ Database reset endpoint works');
      console.log('   Deleted posts:', result.deleted_posts);
      console.log('   Deleted pages:', result.deleted_pages);
      console.log('   Deleted media:', result.deleted_media);
      console.log('   Deleted terms:', result.deleted_terms);
    } catch (error) {
      console.error('❌ Database reset failed:', error);
      throw error;
    }
  });

  test('Database reset rejects unauthorized requests', async ({ request, baseURL }) => {
    // Test without X-Test-Key header
    const response1 = await request.delete(`${baseURL}/wp-json/gcb-testing/v1/reset`);
    expect(response1.status()).toBe(401);

    // Test with wrong X-Test-Key
    const response2 = await request.delete(`${baseURL}/wp-json/gcb-testing/v1/reset`, {
      headers: { 'X-Test-Key': 'wrong-key' },
    });
    expect(response2.status()).toBe(401);

    console.log('✅ Database reset correctly rejects unauthorized requests');
  });
});
