import { test, expect } from '@playwright/test';

/**
 * Quick Staging Tests
 *
 * Uses domcontentloaded instead of load to avoid hanging on slow external resources.
 */
test.describe('Staging Quick Tests', () => {
  test('Homepage loads successfully', async ({ page }) => {
    const response = await page.goto('/', {
      waitUntil: 'domcontentloaded',
    });

    expect(response?.status()).toBe(200);

    // Verify key content is visible
    await expect(page.locator('text=FEATURED STORIES')).toBeVisible();
    await expect(page.locator('h2:has-text("GCB")')).toBeVisible();
  });

  test('Navigation is accessible', async ({ page }) => {
    await page.goto('/', { waitUntil: 'domcontentloaded' });

    // Check navigation exists
    const nav = page.locator('nav, [role="navigation"]');
    await expect(nav.first()).toBeVisible();

    // Check skip link exists (accessibility)
    const skipLink = page.locator('a[href="#main-content"], .skip-link');
    await expect(skipLink.first()).toBeAttached();
  });

  test('Footer social icons visible and have correct color', async ({ page }) => {
    await page.goto('/', { waitUntil: 'domcontentloaded' });

    // Check footer exists
    const footer = page.locator('footer, .gcb-footer, [class*="footer"]');
    await expect(footer.first()).toBeVisible();

    // Check for social links
    const youtube = page.locator('a[aria-label="YouTube"]');
    await expect(youtube).toBeVisible();

    // Verify color is brutal-grey (#999999) not brutal-border (#333333)
    const color = await youtube.evaluate((el) =>
      window.getComputedStyle(el).color
    );

    // rgb(153, 153, 153) = #999999 (brutal-grey)
    // rgb(51, 51, 51) = #333333 (brutal-border - incorrect)
    console.log('Social icon color:', color);
    expect(color).not.toBe('rgb(51, 51, 51)'); // Should NOT be brutal-border
  });

  test('Page has proper contrast - no brutal-border text', async ({ page }) => {
    await page.goto('/', { waitUntil: 'domcontentloaded' });

    // Check post dates use readable color
    const postDate = page.locator('.gcb-post-meta, .wp-block-post-date, time').first();
    if (await postDate.count() > 0) {
      const color = await postDate.evaluate((el) =>
        window.getComputedStyle(el).color
      );
      console.log('Post date color:', color);
      // Should NOT be rgb(51, 51, 51) which is #333333 brutal-border
      expect(color).not.toBe('rgb(51, 51, 51)');
    }
  });

  test('Culture grid cards render', async ({ page }) => {
    await page.goto('/', { waitUntil: 'domcontentloaded' });

    // Check for culture grid section
    const cultureSection = page.locator('text=LATEST REVIEWS');
    if (await cultureSection.count() > 0) {
      await expect(cultureSection).toBeVisible();
    }
  });
});
