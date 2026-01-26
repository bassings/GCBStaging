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

/**
 * LCP Optimization Tests
 *
 * Tests for specific optimizations to reduce LCP from 4689ms to under 2500ms:
 * 1. Preconnect hints for critical CDNs
 * 2. Single post LCP preload
 * 3. Lite-YouTube facade pattern
 */
test.describe('Performance - LCP Optimizations', () => {
  test.beforeEach(async ({ request }) => {
    // Ensure clean state
    await request.delete('/wp-json/gcb-testing/v1/reset', {
      headers: { 'GCB-Test-Key': 'test-secret-key-local' },
    });
  });

  test('Preconnect hints are present for critical CDNs', async ({ page }) => {
    await gotoWithChallengeHandling(page, '/');

    // Check for preconnect hints in the HTML head
    const preconnectLinks = page.locator('link[rel="preconnect"]');
    const preconnectHrefs = await preconnectLinks.evaluateAll((links) =>
      links.map((link) => link.getAttribute('href'))
    );

    // Should have preconnect for YouTube thumbnail CDN
    expect(
      preconnectHrefs,
      'Missing preconnect for YouTube thumbnail CDN (i.ytimg.com)'
    ).toContain('https://i.ytimg.com');

    // Should have preconnect for WP.com image CDN
    expect(
      preconnectHrefs,
      'Missing preconnect for WP.com image CDN (i0.wp.com)'
    ).toContain('https://i0.wp.com');

    // Should have preconnect for YouTube embed domain
    expect(
      preconnectHrefs,
      'Missing preconnect for YouTube embed domain (www.youtube-nocookie.com)'
    ).toContain('https://www.youtube-nocookie.com');
  });

  test('Single post has featured image preload', async ({ page }) => {
    // Navigate to a single post page
    await gotoWithChallengeHandling(page, '/single-post-test/');

    // Check for preload link for the featured image
    const preloadLinks = page.locator('link[rel="preload"][as="image"]');
    const preloadCount = await preloadLinks.count();

    expect(
      preloadCount,
      'Single post should have at least one image preload'
    ).toBeGreaterThan(0);

    // Check that the preload has fetchpriority="high"
    const firstPreload = preloadLinks.first();
    const fetchPriority = await firstPreload.getAttribute('fetchpriority');

    expect(fetchPriority, 'Preloaded image should have fetchpriority="high"').toBe(
      'high'
    );
  });

  test('Single post LCP is under 2.5 seconds', async ({ page }) => {
    // Test with a real post that has a featured image
    await gotoWithChallengeHandling(page, '/single-post-test/');

    const perf = createPerformanceHelper(page);
    const metrics = await perf.measure();

    expect(
      metrics.lcp,
      `Single post LCP of ${metrics.lcp}ms exceeds 2500ms threshold`
    ).toBeLessThanOrEqual(CORE_WEB_VITALS.LCP);
  });

  test('Lite-YouTube facade loads instead of iframe', async ({ page }) => {
    // Navigate to a post with an embedded YouTube video
    await gotoWithChallengeHandling(page, '/video-embed-test/');

    // Check that lite-youtube element exists
    const liteYoutube = page.locator('lite-youtube');
    const liteYoutubeCount = await liteYoutube.count();

    expect(
      liteYoutubeCount,
      'Should have lite-youtube element instead of iframe'
    ).toBeGreaterThan(0);

    // Check that no YouTube iframe is loaded initially
    const youtubeIframe = page.locator('iframe[src*="youtube.com"]');
    const iframeCount = await youtubeIframe.count();

    expect(
      iframeCount,
      'YouTube iframe should not be loaded until user clicks play'
    ).toBe(0);
  });

  test('YouTube iframe loads only after clicking play button', async ({ page }) => {
    // Navigate to a post with an embedded YouTube video
    await gotoWithChallengeHandling(page, '/video-embed-test/');

    // Verify lite-youtube element exists
    const liteYoutube = page.locator('lite-youtube').first();
    await expect(liteYoutube).toBeVisible();

    // Verify no iframe initially
    let iframeCount = await page.locator('iframe[src*="youtube.com"]').count();
    expect(iframeCount, 'No iframe should exist before clicking').toBe(0);

    // Click the play button
    await liteYoutube.click();

    // Wait for iframe to load
    await page.waitForSelector('iframe[src*="youtube.com"]', { timeout: 5000 });

    // Verify iframe now exists
    iframeCount = await page.locator('iframe[src*="youtube.com"]').count();
    expect(iframeCount, 'Iframe should load after clicking play').toBeGreaterThan(0);
  });

  test('Lite-YouTube facade has proper accessibility attributes', async ({ page }) => {
    // Navigate to a post with an embedded YouTube video
    await gotoWithChallengeHandling(page, '/video-embed-test/');

    const liteYoutube = page.locator('lite-youtube').first();
    await expect(liteYoutube).toBeVisible();

    // Should have videoid attribute
    const videoId = await liteYoutube.getAttribute('videoid');
    expect(videoId, 'lite-youtube should have videoid attribute').toBeTruthy();

    // Should be keyboard accessible
    const tabindex = await liteYoutube.getAttribute('tabindex');
    expect(
      tabindex === null || tabindex === '0',
      'lite-youtube should be keyboard accessible'
    ).toBeTruthy();

    // Play button should have ARIA label
    const playButton = liteYoutube.locator('[aria-label]').first();
    const ariaLabel = await playButton.getAttribute('aria-label');
    expect(
      ariaLabel,
      'Play button should have descriptive ARIA label'
    ).toBeTruthy();
  });
});
