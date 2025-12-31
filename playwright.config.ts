import { defineConfig, devices } from '@playwright/test';

/**
 * Playwright Configuration for GCB Magazine E2E Testing
 *
 * See https://playwright.dev/docs/test-configuration
 */
export default defineConfig({
  testDir: './tests/e2e',
  timeout: 30000,
  expect: { timeout: 10000 },

  // Tests share database - must run sequentially
  fullyParallel: false,

  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 1,
  workers: process.env.CI ? 1 : undefined,

  reporter: [
    ['html', { outputFolder: 'playwright-report' }],
    ['list'],
    ['json', { outputFile: 'test-results/results.json' }],
  ],

  use: {
    baseURL: 'http://localhost:8881',
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
    video: 'retain-on-failure',
    ignoreHTTPSErrors: true,
    navigationTimeout: 15000,
    actionTimeout: 10000,
  },

  projects: [
    // Setup project - runs first to authenticate
    {
      name: 'setup',
      testMatch: /.*\.setup\.ts/,
      teardown: 'cleanup',
    },

    // Cleanup project - runs after all tests
    {
      name: 'cleanup',
      testMatch: /.*\.cleanup\.ts/,
    },

    // Admin tests - require authentication
    {
      name: 'admin',
      testMatch: /.*\.admin\.spec\.ts/,
      dependencies: ['setup'],
      use: {
        ...devices['Desktop Chrome'],
        storageState: 'tests/auth/.auth/admin.json',
      },
    },

    // Public tests - no authentication required
    {
      name: 'public',
      testMatch: /.*\.public\.spec\.ts/,
      use: { ...devices['Desktop Chrome'] },
    },

    // Smoke tests - infrastructure validation
    {
      name: 'smoke',
      testMatch: /.*smoke\.spec\.ts/,
      dependencies: ['setup'],
      use: {
        ...devices['Desktop Chrome'],
        storageState: 'tests/auth/.auth/admin.json',
      },
    },
  ],

  globalSetup: './global-setup.ts',
  globalTeardown: './global-teardown.ts',
});
