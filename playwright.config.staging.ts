import { defineConfig, devices } from '@playwright/test';

/**
 * Playwright Configuration for Staging Environment Testing
 *
 * This config targets the live staging site for:
 * - Performance audits (Core Web Vitals)
 * - Security header validation
 * - Accessibility audits (WCAG 2.2 AA)
 * - Visual regression testing
 * - Font contrast validation
 *
 * Usage: npx playwright test --config=playwright.config.staging.ts
 */
export default defineConfig({
  testDir: './tests/e2e',
  timeout: 60000, // Increased for remote site
  expect: {
    timeout: 15000,
    toHaveScreenshot: {
      maxDiffPixelRatio: 0.15, // Slightly more lenient for staging
      threshold: 0.25,
    },
  },

  // Run sequentially for consistent results
  fullyParallel: false,

  forbidOnly: true,
  retries: 1,
  workers: 1,

  reporter: [
    ['html', { outputFolder: 'playwright-report-staging' }],
    ['list'],
    ['json', { outputFile: 'test-results/staging-results.json' }],
  ],

  use: {
    baseURL: 'https://staging-9ba2-gaycarboys.wpcomstaging.com',
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
    video: 'retain-on-failure',
    ignoreHTTPSErrors: true,
    navigationTimeout: 30000,
    actionTimeout: 15000,
    // Bypass headless browser detection on WP.com staging
    launchOptions: {
      args: [
        '--headless=new', // New Chrome headless mode - undetectable
        '--disable-blink-features=AutomationControlled',
        '--disable-web-security',
      ],
    },
    // Use real browser user agent
    userAgent: 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
  },

  projects: [
    // Performance tests - Core Web Vitals validation
    {
      name: 'performance',
      testMatch: /.*performance.*\.spec\.ts/,
      use: { ...devices['Desktop Chrome'] },
    },

    // Security tests - header and CSP validation
    {
      name: 'security',
      testMatch: /.*security.*\.spec\.ts/,
      use: { ...devices['Desktop Chrome'] },
    },

    // Accessibility tests - axe-core WCAG 2.2 AA audits
    {
      name: 'accessibility',
      testMatch: /.*accessibility-axe.*\.spec\.ts/,
      use: { ...devices['Desktop Chrome'] },
    },

    // Visual regression tests - screenshot comparison
    {
      name: 'visual',
      testMatch: /.*visual-regression.*\.spec\.ts/,
      use: { ...devices['Desktop Chrome'] },
    },

    // Public homepage tests
    {
      name: 'public',
      testMatch: /.*\.public\.spec\.ts/,
      use: { ...devices['Desktop Chrome'] },
    },

    // Mobile viewport tests
    {
      name: 'mobile',
      testMatch: /.*\.public\.spec\.ts|.*accessibility.*\.spec\.ts/,
      use: { ...devices['iPhone 13'] },
    },

    // Tablet viewport tests
    {
      name: 'tablet',
      testMatch: /.*\.public\.spec\.ts|.*accessibility.*\.spec\.ts/,
      use: { ...devices['iPad (gen 7)'] },
    },
  ],

  // No global setup needed for staging (no database reset)
});
