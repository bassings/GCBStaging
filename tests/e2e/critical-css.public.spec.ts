import { test, expect } from '@playwright/test';
import { gotoWithChallengeHandling } from '../utils/performance.js';

/**
 * Critical CSS Optimization Tests
 *
 * Validates that critical CSS inlining and resource hints are working correctly
 * to improve LCP, FCP, and prevent CLS from font loading.
 *
 * TDD Protocol: These tests are written FIRST before implementation.
 */
test.describe('Critical CSS Optimization', () => {
  test.beforeEach(async ({ request }) => {
    // Reset database state to prevent flaky tests
    try {
      await request.delete('/wp-json/gcb-testing/v1/reset', {
        headers: { 'GCB-Test-Key': 'test-secret-key-local' },
        timeout: 5000,
      });
    } catch {
      // Ignore reset failures - may not be available in all environments
    }
  });

  test('Critical CSS is inlined in document head', async ({ page }) => {
    await gotoWithChallengeHandling(page, '/');

    // Check for inline critical CSS style tag
    const criticalStyle = page.locator('style#gcb-critical-css');
    await expect(criticalStyle).toBeAttached();

    // Verify it contains essential CSS rules
    const cssContent = await criticalStyle.textContent();
    expect(cssContent).toContain('--wp--preset--color--void-black');
    expect(cssContent).toContain('.site-header');
    expect(cssContent).toContain('.skip-link');
    expect(cssContent).toContain('.logo-text');
  });

  test('Preconnect hint exists for fonts.gstatic.com', async ({ page }) => {
    await gotoWithChallengeHandling(page, '/');

    // Check for preconnect link tag
    const preconnect = page.locator(
      'link[rel="preconnect"][href="https://fonts.gstatic.com"]'
    );
    await expect(preconnect).toBeAttached();
    await expect(preconnect).toHaveAttribute('crossorigin', 'anonymous');
  });

  test('Critical fonts are preloaded', async ({ page }) => {
    await gotoWithChallengeHandling(page, '/');

    // Playfair Display preload
    const playfairPreload = page.locator(
      'link[rel="preload"][as="font"][href*="playfairdisplay"]'
    );
    await expect(playfairPreload).toBeAttached();
    await expect(playfairPreload).toHaveAttribute('crossorigin', 'anonymous');

    // Space Mono preload
    const monoPreload = page.locator(
      'link[rel="preload"][as="font"][href*="spacemono"]'
    );
    await expect(monoPreload).toBeAttached();
    await expect(monoPreload).toHaveAttribute('crossorigin', 'anonymous');
  });

  test('LCP hero image has fetchpriority=high', async ({ page }) => {
    await gotoWithChallengeHandling(page, '/');

    // Hero image should have fetchpriority="high"
    const heroImage = page.locator(
      '.bento-item--featured .gcb-bento-card__image'
    );
    await expect(heroImage).toHaveAttribute('fetchpriority', 'high');
    await expect(heroImage).toHaveAttribute('loading', 'eager');
  });

  test('LCP image is preloaded in document head', async ({ page }) => {
    await gotoWithChallengeHandling(page, '/');

    // Check for LCP image preload link
    const imagePreload = page.locator(
      'link[rel="preload"][as="image"][fetchpriority="high"]'
    );
    await expect(imagePreload).toBeAttached();

    // Should have imagesrcset for responsive images
    const srcset = await imagePreload.getAttribute('imagesrcset');
    expect(srcset).toBeTruthy();
  });

  test('Theme stylesheet loads without blocking critical CSS', async ({ page }) => {
    await gotoWithChallengeHandling(page, '/');

    // Critical CSS should be inlined (already tested separately)
    // Theme stylesheet may be deferred by our code or by Jetpack Boost
    // Either way, we verify that critical CSS is present in head before stylesheets

    const html = await page.content();

    // Critical CSS style tag should appear before any stylesheet links
    const criticalCssPos = html.indexOf('id="gcb-critical-css"');
    const firstStylesheet = html.indexOf('rel="stylesheet"');

    // If critical CSS exists, it should be before stylesheets
    if (criticalCssPos > -1) {
      expect(criticalCssPos).toBeLessThan(firstStylesheet);
    }

    // Verify the page has loaded stylesheets (either our deferred or Jetpack's)
    const hasStylesheet = firstStylesheet > -1;
    expect(hasStylesheet).toBeTruthy();
  });

  test('Non-hero bento cards still have lazy loading', async ({ page }) => {
    await gotoWithChallengeHandling(page, '/');

    // Standard cards should still use lazy loading
    const standardCards = page.locator(
      '.bento-item:not(.bento-item--featured) .gcb-bento-card__image'
    );

    const count = await standardCards.count();
    expect(count).toBeGreaterThan(0);

    // Check that all standard cards have lazy loading
    for (let i = 0; i < count; i++) {
      const loading = await standardCards.nth(i).getAttribute('loading');
      expect(loading).toBe('lazy');
    }
  });

  test('Header is styled by critical CSS on initial render', async ({ page }) => {
    await gotoWithChallengeHandling(page, '/');

    // Verify header has expected styling from critical CSS
    const header = page.locator('.site-header');
    await expect(header).toBeVisible();

    // Check that critical CSS styles are applied (background color from design system)
    const bgColor = await header.evaluate((el) => {
      return window.getComputedStyle(el).backgroundColor;
    });

    // Should have void-black background (#050505 = rgb(5, 5, 5))
    expect(bgColor).toMatch(/rgb\(5,\s*5,\s*5\)/);
  });

  test('Critical CSS contains correct design tokens', async ({ page }) => {
    await gotoWithChallengeHandling(page, '/');

    const criticalStyle = page.locator('style#gcb-critical-css');
    const cssContent = await criticalStyle.textContent();

    // Verify design token values match theme.json
    expect(cssContent).toContain('#050505'); // void-black
    expect(cssContent).toContain('#FAFAFA'); // off-white
    expect(cssContent).toContain('#0084FF'); // highlight
    expect(cssContent).toContain('#333333'); // brutal-border
    expect(cssContent).toContain('#AAAAAA'); // brutal-grey
  });
});
