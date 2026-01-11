import { Page, expect } from '@playwright/test';

/**
 * Visual Regression Configuration
 */
export interface VisualRegressionOptions {
  /** Name for the screenshot (used as filename) */
  name: string;
  /** Element selector to screenshot (default: full page) */
  selector?: string;
  /** Threshold for pixel difference (0-1, default: 0.1 = 10%) */
  threshold?: number;
  /** Mask dynamic elements (selectors) */
  mask?: string[];
  /** Viewport sizes to test */
  viewports?: Array<{ width: number; height: number; name: string }>;
  /** Wait for specific element before screenshot */
  waitForSelector?: string;
  /** Additional wait time in ms after page load */
  waitAfterLoad?: number;
}

/**
 * Default viewports for responsive testing
 */
export const DEFAULT_VIEWPORTS = [
  { width: 375, height: 667, name: 'mobile' },
  { width: 768, height: 1024, name: 'tablet' },
  { width: 1920, height: 1080, name: 'desktop' },
];

/**
 * Mobile-only viewport
 */
export const MOBILE_VIEWPORT = [{ width: 375, height: 667, name: 'mobile' }];

/**
 * Desktop-only viewport
 */
export const DESKTOP_VIEWPORT = [{ width: 1920, height: 1080, name: 'desktop' }];

/**
 * Visual Regression Helper
 *
 * Captures and compares screenshots against baselines.
 * Baselines stored in: tests/visual-baselines/{name}-{viewport}.png
 *
 * Usage:
 * ```typescript
 * import { createVisualRegression, DEFAULT_VIEWPORTS } from '@utils/visual-regression';
 *
 * test('Homepage matches baseline', async ({ page }) => {
 *   await page.goto('/');
 *   const visual = createVisualRegression(page);
 *   await visual.compareResponsive('homepage');
 * });
 * ```
 */
export class VisualRegression {
  private page: Page;

  constructor(page: Page) {
    this.page = page;
  }

  /**
   * Compare current page/element to baseline
   */
  async compare(options: VisualRegressionOptions): Promise<void> {
    const {
      name,
      selector,
      threshold = 0.1,
      mask = [],
      viewports = [{ width: 1920, height: 1080, name: 'desktop' }],
      waitForSelector,
      waitAfterLoad = 500,
    } = options;

    for (const viewport of viewports) {
      await this.page.setViewportSize({
        width: viewport.width,
        height: viewport.height,
      });

      // Wait for layout to stabilize
      await this.page.waitForLoadState('networkidle');

      // Wait for specific element if specified
      if (waitForSelector) {
        await this.page.waitForSelector(waitForSelector, { state: 'visible' });
      }

      // Additional wait for animations/lazy loading
      await this.page.waitForTimeout(waitAfterLoad);

      const screenshotName = `${name}-${viewport.name}`;

      // Mask dynamic elements (e.g., dates, ads, random content)
      const maskLocators = mask.map((s) => this.page.locator(s));

      if (selector) {
        // Element screenshot
        await expect(this.page.locator(selector)).toHaveScreenshot(
          `${screenshotName}.png`,
          {
            maxDiffPixelRatio: threshold,
            mask: maskLocators,
            animations: 'disabled',
          }
        );
      } else {
        // Full page screenshot
        await expect(this.page).toHaveScreenshot(`${screenshotName}.png`, {
          fullPage: true,
          maxDiffPixelRatio: threshold,
          mask: maskLocators,
          animations: 'disabled',
        });
      }
    }
  }

  /**
   * Compare responsive layout across all default viewports
   */
  async compareResponsive(
    name: string,
    options?: Partial<VisualRegressionOptions>
  ): Promise<void> {
    await this.compare({
      name,
      viewports: DEFAULT_VIEWPORTS,
      ...options,
    });
  }

  /**
   * Compare only mobile viewport
   */
  async compareMobile(
    name: string,
    options?: Partial<VisualRegressionOptions>
  ): Promise<void> {
    await this.compare({
      name,
      viewports: MOBILE_VIEWPORT,
      ...options,
    });
  }

  /**
   * Compare only desktop viewport
   */
  async compareDesktop(
    name: string,
    options?: Partial<VisualRegressionOptions>
  ): Promise<void> {
    await this.compare({
      name,
      viewports: DESKTOP_VIEWPORT,
      ...options,
    });
  }

  /**
   * Compare specific component
   */
  async compareComponent(
    name: string,
    selector: string,
    options?: Partial<VisualRegressionOptions>
  ): Promise<void> {
    await this.compare({
      name,
      selector,
      viewports: DEFAULT_VIEWPORTS,
      ...options,
    });
  }

  /**
   * Take a debug screenshot (saved to debug-screenshots/)
   */
  async debugScreenshot(label: string): Promise<string> {
    const timestamp = Date.now();
    const path = `debug-screenshots/${label}-${timestamp}.png`;
    await this.page.screenshot({
      path,
      fullPage: true,
    });
    console.log(`Screenshot saved: ${path}`);
    return path;
  }
}

/**
 * Factory function for easy use in tests
 */
export function createVisualRegression(page: Page): VisualRegression {
  return new VisualRegression(page);
}
