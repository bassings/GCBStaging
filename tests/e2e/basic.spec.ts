import { test, expect } from '@playwright/test';

test('homepage loads', async ({ page }) => {
  await page.goto('/');
  await expect(page).toHaveTitle(/Gay Car Boys/i);
});

test('article page loads', async ({ page }) => {
  await page.goto('/');
  const firstPost = page.locator('article').first();
  await firstPost.click();
  // After clicking an article, we should navigate to a new page
  await page.waitForLoadState('networkidle');
  await expect(page.locator('article').first()).toBeVisible();
});


