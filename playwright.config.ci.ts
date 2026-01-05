import { defineConfig, devices } from '@playwright/test';

/**
 * Playwright Configuration for CI/CD Pipeline
 *
 * This is a simplified config for CI environments that:
 * - Uses Docker + MariaDB instead of WordPress Studio + SQLite
 * - Skips session-based database isolation (CI runs are isolated by default)
 * - Runs only smoke tests for quick validation
 * - Has longer timeouts for slower CI environments
 */
export default defineConfig({
  testDir: './tests/e2e',
  timeout: 60000, // Increased for CI
  expect: { timeout: 15000 }, // Increased for CI

  // Tests share database - must run sequentially
  fullyParallel: false,

  forbidOnly: true,
  retries: 2,
  workers: 1,

  reporter: [
    ['html', { outputFolder: 'playwright-report' }],
    ['list'],
    ['json', { outputFile: 'test-results/results.json' }],
    ['github'], // GitHub Actions reporter
  ],

  use: {
    baseURL: process.env.WP_HOME || 'http://localhost:8080',
    trace: 'retain-on-failure',
    screenshot: 'only-on-failure',
    video: 'retain-on-failure',
    ignoreHTTPSErrors: true,
    navigationTimeout: 30000, // Increased for CI
    actionTimeout: 15000, // Increased for CI
  },

  projects: [
    // Smoke tests only for CI - quick validation
    {
      name: 'smoke',
      testMatch: /.*smoke\.spec\.ts/,
      use: { ...devices['Desktop Chrome'] },
    },
  ],

  // No global setup/teardown for CI (uses Docker instead)
});
