import { test, expect } from '@playwright/test';
import {
  createVisualRegression,
  DEFAULT_VIEWPORTS,
} from '../utils/visual-regression.js';

/**
 * Visual Regression Tests
 *
 * Captures and compares screenshots against baselines to detect
 * unintended visual changes.
 *
 * To update baselines after intentional changes:
 *   npm run test:visual:update
 *
 * Baselines are stored in: tests/visual-baselines/
 */
test.describe('Visual Regression - Key Pages', () => {
  test.beforeEach(async ({ request }) => {
    // Ensure consistent state for visual tests
    await request.delete('/wp-json/gcb-testing/v1/reset', {
      headers: { 'GCB-Test-Key': 'test-secret-key-local' },
    });
  });

  test('Homepage matches baseline across viewports', async ({ page }) => {
    await page.goto('/');

    // Wait for all images to load
    await page.waitForLoadState('networkidle');

    const visual = createVisualRegression(page);
    await visual.compareResponsive('homepage', {
      mask: [
        '.post-date', // Dynamic dates
        'time', // Time elements
        '.video-views', // View counts
        '.gcb-video-card__meta', // Video metadata
        'iframe', // Third-party embeds
      ],
      threshold: 0.15, // Allow 15% difference for dynamic content
      waitAfterLoad: 1000, // Extra wait for lazy loading
    });
  });

  test('Homepage header matches baseline', async ({ page }) => {
    await page.goto('/');
    await page.waitForLoadState('networkidle');

    const visual = createVisualRegression(page);
    await visual.compareComponent('header', 'header', {
      threshold: 0.1,
    });
  });

  test('Homepage footer matches baseline', async ({ page }) => {
    await page.goto('/');
    await page.waitForLoadState('networkidle');

    const visual = createVisualRegression(page);
    await visual.compareComponent('footer', 'footer', {
      threshold: 0.1,
    });
  });

  test('Navigation matches baseline on mobile', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 667 });
    await page.goto('/');
    await page.waitForLoadState('networkidle');

    const visual = createVisualRegression(page);
    await visual.compareMobile('navigation-mobile', {
      selector: 'header',
      threshold: 0.1,
    });
  });

  test('Mobile menu matches baseline when open', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 667 });
    await page.goto('/');

    // Open mobile menu (adjust selector as needed)
    const menuButton = page.locator(
      '.menu-toggle, .hamburger, [aria-label*="menu"], button[class*="menu"]'
    );

    if ((await menuButton.count()) > 0) {
      await menuButton.first().click();
      await page.waitForTimeout(500); // Wait for animation

      const visual = createVisualRegression(page);
      await visual.compareMobile('mobile-menu-open', {
        threshold: 0.1,
      });
    } else {
      console.log('Mobile menu button not found - skipping test');
    }
  });

  test('Desktop layout matches baseline at 1920px', async ({ page }) => {
    await page.setViewportSize({ width: 1920, height: 1080 });
    await page.goto('/');
    await page.waitForLoadState('networkidle');

    const visual = createVisualRegression(page);
    await visual.compareDesktop('desktop-layout', {
      mask: ['.post-date', 'time', 'iframe'],
      threshold: 0.15,
    });
  });

  test('Tablet layout matches baseline at 768px', async ({ page }) => {
    await page.setViewportSize({ width: 768, height: 1024 });
    await page.goto('/');
    await page.waitForLoadState('networkidle');

    const visual = createVisualRegression(page);
    await visual.compare({
      name: 'tablet-layout',
      viewports: [{ width: 768, height: 1024, name: 'tablet' }],
      mask: ['.post-date', 'time', 'iframe'],
      threshold: 0.15,
    });
  });
});

test.describe('Visual Regression - Components', () => {
  test.beforeEach(async ({ request }) => {
    await request.delete('/wp-json/gcb-testing/v1/reset', {
      headers: { 'GCB-Test-Key': 'test-secret-key-local' },
    });
  });

  test('Video rail component matches baseline', async ({ page }) => {
    await page.goto('/');

    // Find video rail component
    const videoRail = page.locator(
      '.gcb-video-rail, .video-rail, [class*="video-rail"]'
    );

    if ((await videoRail.count()) > 0) {
      await page.waitForLoadState('networkidle');

      const visual = createVisualRegression(page);
      await visual.compareComponent('video-rail', '.gcb-video-rail', {
        mask: ['.gcb-video-card__meta', '.video-views'],
        threshold: 0.15,
      });
    } else {
      console.log('Video rail not found - skipping test');
    }
  });

  test('Bento grid component matches baseline', async ({ page }) => {
    await page.goto('/');

    const bentoGrid = page.locator(
      '.gcb-bento-grid, .bento-grid, [class*="bento"]'
    );

    if ((await bentoGrid.count()) > 0) {
      await page.waitForLoadState('networkidle');

      const visual = createVisualRegression(page);
      await visual.compareComponent('bento-grid', '.gcb-bento-grid', {
        mask: ['.post-date', 'time'],
        threshold: 0.15,
      });
    } else {
      console.log('Bento grid not found - skipping test');
    }
  });

  test('Culture grid component matches baseline', async ({ page }) => {
    await page.goto('/');

    const cultureGrid = page.locator(
      '.gcb-culture-grid, .culture-grid, [class*="culture"]'
    );

    if ((await cultureGrid.count()) > 0) {
      await page.waitForLoadState('networkidle');

      const visual = createVisualRegression(page);
      await visual.compareComponent('culture-grid', '.gcb-culture-grid', {
        mask: ['.post-date', 'time'],
        threshold: 0.15,
      });
    } else {
      console.log('Culture grid not found - skipping test');
    }
  });
});

test.describe('Visual Regression - Dark Theme', () => {
  test('Dark theme renders correctly', async ({ page }) => {
    await page.goto('/');

    // Check if site has dark background (brutalist theme)
    const backgroundColor = await page.evaluate(() => {
      return window.getComputedStyle(document.body).backgroundColor;
    });

    console.log(`Body background color: ${backgroundColor}`);

    // GCB should have dark background (#050505 = rgb(5, 5, 5))
    const isDark =
      backgroundColor.includes('rgb(5, 5, 5)') ||
      backgroundColor.includes('rgb(0, 0, 0)') ||
      backgroundColor === '#050505' ||
      backgroundColor === '#000000';

    expect(isDark, 'Site should have dark (brutalist) background').toBeTruthy();
  });

  test('Acid lime accents are visible', async ({ page }) => {
    await page.goto('/');

    // Check for acid lime (#CCFF00) usage
    const hasAcidLime = await page.evaluate(() => {
      const elements = document.querySelectorAll('*');
      for (const el of elements) {
        const styles = window.getComputedStyle(el);
        const color = styles.color;
        const borderColor = styles.borderColor;
        const backgroundColor = styles.backgroundColor;

        // Check for acid lime (rgb(204, 255, 0))
        if (
          color.includes('204, 255, 0') ||
          borderColor.includes('204, 255, 0') ||
          backgroundColor.includes('204, 255, 0')
        ) {
          return true;
        }
      }
      return false;
    });

    expect(
      hasAcidLime,
      'Site should use acid lime (#CCFF00) accent color'
    ).toBeTruthy();
  });
});
