import { test as base, expect, Page, ConsoleMessage } from '@playwright/test';

/**
 * Console Error Types to Capture
 */
export interface ConsoleError {
  type: 'error' | 'warning' | 'pageerror' | 'requestfailed';
  message: string;
  url?: string;
  stack?: string;
  timestamp: number;
}

/**
 * Extended Test Fixtures for GCB Magazine
 *
 * Provides automatic console error capture and optional auto-fail on JS errors.
 *
 * Features:
 * - consoleErrors: Array of captured console errors during test
 * - failOnConsoleError: Auto-fail tests on JS errors (configurable per-test)
 * - Ignores expected failures (analytics, third-party scripts)
 *
 * Usage:
 * ```typescript
 * import { test, expect } from '@utils/fixtures';
 *
 * test('Page loads without JS errors', async ({ page }) => {
 *   await page.goto('/');
 *   // Console errors automatically captured and asserted
 * });
 *
 * // Disable for specific test
 * test('Third-party widget may have errors', async ({ page }) => {
 *   test.use({ failOnConsoleError: false });
 *   await page.goto('/page-with-widget');
 * });
 * ```
 */
export const test = base.extend<{
  consoleErrors: ConsoleError[];
  failOnConsoleError: boolean;
}>({
  // Configuration option - can be overridden per test
  failOnConsoleError: [true, { option: true }],

  // Collected errors during test
  consoleErrors: async ({}, use) => {
    const errors: ConsoleError[] = [];
    await use(errors);
  },

  // Override page fixture to auto-attach console capture
  page: async ({ page, consoleErrors, failOnConsoleError }, use) => {
    // Patterns to ignore (analytics, third-party, etc.)
    const ignoredPatterns = [
      /google-analytics/i,
      /googletagmanager/i,
      /facebook\.com/i,
      /doubleclick/i,
      /analytics/i,
      /hotjar/i,
      /clarity\.ms/i,
      /gravatar\.com/i,
      // WordPress-specific
      /wp-json.*favicon/i,
      /s\.w\.org/i,
    ];

    const shouldIgnore = (url: string, message: string): boolean => {
      return ignoredPatterns.some(
        (pattern) => pattern.test(url) || pattern.test(message)
      );
    };

    // Capture console.error messages
    page.on('console', (msg: ConsoleMessage) => {
      if (msg.type() === 'error') {
        const message = msg.text();
        const url = page.url();

        if (!shouldIgnore(url, message)) {
          consoleErrors.push({
            type: 'error',
            message,
            url,
            timestamp: Date.now(),
          });
        }
      }
    });

    // Capture uncaught exceptions
    page.on('pageerror', (error: Error) => {
      const url = page.url();
      const message = error.message;

      if (!shouldIgnore(url, message)) {
        consoleErrors.push({
          type: 'pageerror',
          message,
          stack: error.stack,
          url,
          timestamp: Date.now(),
        });
      }
    });

    // Capture failed network requests
    page.on('requestfailed', (request) => {
      const url = request.url();
      const errorText = request.failure()?.errorText || 'Unknown error';

      if (!shouldIgnore(url, errorText)) {
        consoleErrors.push({
          type: 'requestfailed',
          message: `Request failed: ${errorText}`,
          url,
          timestamp: Date.now(),
        });
      }
    });

    await use(page);

    // After test: fail if console errors were captured
    if (failOnConsoleError && consoleErrors.length > 0) {
      const errorSummary = consoleErrors
        .map((e) => `  [${e.type}] ${e.message}${e.url ? ` (${e.url})` : ''}`)
        .join('\n');

      console.log('\n--- Console Errors Captured ---');
      console.log(errorSummary);
      console.log('-------------------------------\n');

      expect
        .soft(consoleErrors, `Console errors detected:\n${errorSummary}`)
        .toHaveLength(0);
    }
  },
});

export { expect } from '@playwright/test';

/**
 * Helper to manually check for specific error patterns
 */
export function hasError(
  errors: ConsoleError[],
  pattern: RegExp | string
): boolean {
  return errors.some((e) =>
    typeof pattern === 'string'
      ? e.message.includes(pattern)
      : pattern.test(e.message)
  );
}

/**
 * Filter errors by type
 */
export function filterErrors(
  errors: ConsoleError[],
  type: ConsoleError['type']
): ConsoleError[] {
  return errors.filter((e) => e.type === type);
}
