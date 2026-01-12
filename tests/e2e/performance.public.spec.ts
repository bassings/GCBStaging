import { test, expect } from '@playwright/test';
import {
  createPerformanceHelper,
  CORE_WEB_VITALS,
  gotoWithChallengeHandling,
} from '../utils/performance.js';

/**
 * Performance Tests - Core Web Vitals
 *
 * Validates that pages meet Google's Core Web Vitals thresholds:
 * - LCP (Largest Contentful Paint): < 2.5s
 * - FCP (First Contentful Paint): < 1.8s
 * - CLS (Cumulative Layout Shift): < 0.1
 * - TTFB (Time to First Byte): < 0.8s
 */
test.describe('Performance - Core Web Vitals', () => {
  test.beforeEach(async ({ request }) => {
    // Ensure clean state
    await request.delete('/wp-json/gcb-testing/v1/reset', {
      headers: { 'GCB-Test-Key': 'test-secret-key-local' },
    });
  });

  test('Homepage meets Core Web Vitals thresholds', async ({ page }) => {
    await gotoWithChallengeHandling(page, '/');

    const perf = createPerformanceHelper(page);
    const metrics = await perf.assertCoreWebVitals();

    // Additional assertions
    expect(metrics.resourceCount).toBeLessThan(100);
    expect(metrics.totalTransferSize).toBeLessThan(5 * 1024 * 1024); // 5MB max
  });

  test('Homepage LCP is under 2.5 seconds', async ({ page }) => {
    await gotoWithChallengeHandling(page, '/');

    const perf = createPerformanceHelper(page);
    const metrics = await perf.measure();

    expect(
      metrics.lcp,
      `LCP of ${metrics.lcp}ms exceeds 2500ms threshold`
    ).toBeLessThanOrEqual(CORE_WEB_VITALS.LCP);
  });

  test('Homepage has minimal layout shift (CLS < 0.1)', async ({ page }) => {
    await gotoWithChallengeHandling(page, '/');

    const perf = createPerformanceHelper(page);
    const metrics = await perf.measure();

    expect(
      metrics.cls,
      `CLS of ${metrics.cls} exceeds 0.1 threshold`
    ).toBeLessThanOrEqual(CORE_WEB_VITALS.CLS);
  });

  test('Homepage TTFB is under 800ms', async ({ page }) => {
    await gotoWithChallengeHandling(page, '/');

    const perf = createPerformanceHelper(page);
    const metrics = await perf.measure();

    expect(
      metrics.ttfb,
      `TTFB of ${metrics.ttfb}ms exceeds 800ms threshold`
    ).toBeLessThanOrEqual(CORE_WEB_VITALS.TTFB);
  });

  test('Mobile performance meets relaxed thresholds', async ({ page }) => {
    // Set mobile viewport
    await page.setViewportSize({ width: 375, height: 667 });

    await gotoWithChallengeHandling(page, '/');

    const perf = createPerformanceHelper(page);
    const metrics = await perf.measure();

    // Mobile thresholds are more lenient
    expect(metrics.lcp).toBeLessThan(4000); // 4s for mobile
    expect(metrics.fcp).toBeLessThan(3000); // 3s for mobile
    expect(metrics.cls).toBeLessThan(0.15); // Slightly more CLS allowed on mobile
  });

  test('Resource count is reasonable', async ({ page }) => {
    await gotoWithChallengeHandling(page, '/');

    const perf = createPerformanceHelper(page);
    const metrics = await perf.measure();

    // Too many resources slow down the page
    expect(
      metrics.resourceCount,
      `Too many resources: ${metrics.resourceCount}`
    ).toBeLessThan(80);
  });

  test('Total transfer size is reasonable', async ({ page }) => {
    await gotoWithChallengeHandling(page, '/');

    const perf = createPerformanceHelper(page);
    const metrics = await perf.measure();

    const transferMB = metrics.totalTransferSize / (1024 * 1024);

    // Page should be under 3MB total
    expect(
      transferMB,
      `Transfer size ${transferMB.toFixed(2)}MB is too large`
    ).toBeLessThan(3);
  });

  test('DOM Content Loaded is under 2 seconds', async ({ page }) => {
    await gotoWithChallengeHandling(page, '/');

    const perf = createPerformanceHelper(page);
    const metrics = await perf.measure();

    expect(
      metrics.domContentLoaded,
      `DOM Content Loaded at ${metrics.domContentLoaded}ms is too slow`
    ).toBeLessThan(2000);
  });
});
