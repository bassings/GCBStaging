import { Page, expect } from '@playwright/test';

/**
 * Core Web Vitals Thresholds (Google recommendations)
 *
 * Good | Needs Improvement | Poor
 * LCP:  <2.5s | 2.5s-4s | >4s
 * FID:  <100ms | 100ms-300ms | >300ms
 * CLS:  <0.1 | 0.1-0.25 | >0.25
 */
export const CORE_WEB_VITALS = {
  LCP: 2500, // Largest Contentful Paint: < 2.5s (good)
  FID: 100, // First Input Delay: < 100ms (good)
  CLS: 0.1, // Cumulative Layout Shift: < 0.1 (good)
  FCP: 1800, // First Contentful Paint: < 1.8s (good)
  TTFB: 800, // Time to First Byte: < 0.8s (good)
  TBT: 200, // Total Blocking Time: < 200ms (good)
};

/**
 * Performance Metrics Collected
 */
export interface PerformanceMetrics {
  lcp: number;
  fcp: number;
  cls: number;
  ttfb: number;
  domContentLoaded: number;
  loadComplete: number;
  resourceCount: number;
  totalTransferSize: number;
}

/**
 * Performance Testing Helper
 *
 * Uses browser Performance API and Navigation Timing API
 * to collect Core Web Vitals without requiring Lighthouse.
 *
 * Usage:
 * ```typescript
 * import { createPerformanceHelper } from '@utils/performance';
 *
 * test('Page meets performance thresholds', async ({ page }) => {
 *   await page.goto('/');
 *   const perf = createPerformanceHelper(page);
 *   await perf.assertCoreWebVitals();
 * });
 * ```
 */
export class PerformanceHelper {
  private page: Page;

  constructor(page: Page) {
    this.page = page;
  }

  /**
   * Collect performance metrics from page
   */
  async getMetrics(): Promise<PerformanceMetrics> {
    // Wait for page to be fully loaded
    await this.page.waitForLoadState('load');

    // Give time for LCP and CLS to settle
    await this.page.waitForTimeout(1000);

    const metrics = await this.page.evaluate(() => {
      const navigation = performance.getEntriesByType(
        'navigation'
      )[0] as PerformanceNavigationTiming;
      const paint = performance.getEntriesByType('paint');
      const resources =
        performance.getEntriesByType('resource') as PerformanceResourceTiming[];

      // Get Largest Contentful Paint
      let lcp = 0;
      const lcpEntries = performance.getEntriesByType(
        'largest-contentful-paint'
      );
      if (lcpEntries.length > 0) {
        lcp = (lcpEntries[lcpEntries.length - 1] as PerformancePaintTiming)
          .startTime;
      }

      // Get First Contentful Paint
      const fcpEntry = paint.find(
        (entry) => entry.name === 'first-contentful-paint'
      );
      const fcp = fcpEntry ? fcpEntry.startTime : 0;

      // Get Cumulative Layout Shift (simplified)
      let cls = 0;
      const layoutShiftEntries = performance.getEntriesByType('layout-shift');
      layoutShiftEntries.forEach((entry) => {
        const layoutShift = entry as PerformanceEntry & {
          hadRecentInput: boolean;
          value: number;
        };
        if (!layoutShift.hadRecentInput) {
          cls += layoutShift.value;
        }
      });

      // Calculate total transfer size
      const totalTransferSize = resources.reduce(
        (sum, r) => sum + (r.transferSize || 0),
        0
      );

      return {
        lcp,
        fcp,
        cls,
        ttfb: navigation.responseStart - navigation.requestStart,
        domContentLoaded:
          navigation.domContentLoadedEventEnd - navigation.startTime,
        loadComplete: navigation.loadEventEnd - navigation.startTime,
        resourceCount: resources.length,
        totalTransferSize,
      };
    });

    return metrics;
  }

  /**
   * Assert metrics meet Core Web Vitals thresholds
   */
  async assertCoreWebVitals(
    customThresholds?: Partial<typeof CORE_WEB_VITALS>
  ): Promise<PerformanceMetrics> {
    const thresholds = { ...CORE_WEB_VITALS, ...customThresholds };
    const metrics = await this.getMetrics();

    console.log('\n--- Performance Metrics ---');
    console.log(`  LCP: ${metrics.lcp.toFixed(0)}ms (threshold: ${thresholds.LCP}ms)`);
    console.log(`  FCP: ${metrics.fcp.toFixed(0)}ms (threshold: ${thresholds.FCP}ms)`);
    console.log(`  CLS: ${metrics.cls.toFixed(3)} (threshold: ${thresholds.CLS})`);
    console.log(`  TTFB: ${metrics.ttfb.toFixed(0)}ms (threshold: ${thresholds.TTFB}ms)`);
    console.log(`  Resources: ${metrics.resourceCount}`);
    console.log(`  Transfer Size: ${(metrics.totalTransferSize / 1024).toFixed(1)} KB`);
    console.log('---------------------------\n');

    // Assertions with clear error messages
    expect(
      metrics.lcp,
      `LCP ${metrics.lcp.toFixed(0)}ms exceeds threshold ${thresholds.LCP}ms`
    ).toBeLessThanOrEqual(thresholds.LCP);

    expect(
      metrics.fcp,
      `FCP ${metrics.fcp.toFixed(0)}ms exceeds threshold ${thresholds.FCP}ms`
    ).toBeLessThanOrEqual(thresholds.FCP);

    expect(
      metrics.cls,
      `CLS ${metrics.cls.toFixed(3)} exceeds threshold ${thresholds.CLS}`
    ).toBeLessThanOrEqual(thresholds.CLS);

    expect(
      metrics.ttfb,
      `TTFB ${metrics.ttfb.toFixed(0)}ms exceeds threshold ${thresholds.TTFB}ms`
    ).toBeLessThanOrEqual(thresholds.TTFB);

    return metrics;
  }

  /**
   * Get metrics without assertions (for custom validation)
   */
  async measure(): Promise<PerformanceMetrics> {
    return this.getMetrics();
  }

  /**
   * Log metrics summary to console
   */
  async logMetrics(): Promise<void> {
    const metrics = await this.getMetrics();

    const lcpStatus = metrics.lcp <= CORE_WEB_VITALS.LCP ? 'PASS' : 'FAIL';
    const fcpStatus = metrics.fcp <= CORE_WEB_VITALS.FCP ? 'PASS' : 'FAIL';
    const clsStatus = metrics.cls <= CORE_WEB_VITALS.CLS ? 'PASS' : 'FAIL';
    const ttfbStatus = metrics.ttfb <= CORE_WEB_VITALS.TTFB ? 'PASS' : 'FAIL';

    console.log('\n=== Core Web Vitals Report ===');
    console.log(`  [${lcpStatus}] LCP: ${metrics.lcp.toFixed(0)}ms`);
    console.log(`  [${fcpStatus}] FCP: ${metrics.fcp.toFixed(0)}ms`);
    console.log(`  [${clsStatus}] CLS: ${metrics.cls.toFixed(3)}`);
    console.log(`  [${ttfbStatus}] TTFB: ${metrics.ttfb.toFixed(0)}ms`);
    console.log('==============================\n');
  }
}

/**
 * Factory function for easy use in tests
 */
export function createPerformanceHelper(page: Page): PerformanceHelper {
  return new PerformanceHelper(page);
}
