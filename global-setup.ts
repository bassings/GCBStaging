import { chromium, type FullConfig } from '@playwright/test';

/**
 * Global Setup for GCB Magazine E2E Tests
 *
 * Validates that WordPress Studio is running and accessible
 * before any tests execute. Fails fast if environment is not ready.
 */
async function globalSetup(config: FullConfig) {
  const baseURL = config.projects[0].use.baseURL || 'http://localhost:8881';

  console.log('\n🔍 Validating WordPress Studio environment...\n');

  const browser = await chromium.launch();
  const context = await browser.newContext();
  const page = await context.newPage();

  try {
    // Test 1: WordPress is accessible
    const response = await page.goto(baseURL, {
      waitUntil: 'domcontentloaded',
      timeout: 30000,
    });

    if (!response || !response.ok()) {
      throw new Error(
        `❌ WordPress Studio not accessible at ${baseURL}\n` +
        `   Status: ${response?.status() || 'No response'}\n` +
        `   Please ensure WordPress Studio is running.`
      );
    }

    console.log(`✅ WordPress accessible at ${baseURL}`);

    // Test 2: WordPress REST API is accessible
    await page.goto(`${baseURL}/wp-json/`, { waitUntil: 'domcontentloaded', timeout: 30000 });
    console.log('✅ WordPress REST API accessible');

    console.log('\n🎉 Environment validation complete!\n');

  } catch (error) {
    console.error('\n❌ Global setup failed:', error);
    console.error('\n💡 Troubleshooting:');
    console.error('   1. Is WordPress Studio running?');
    console.error('   2. Is it accessible at http://localhost:8881?');
    console.error('   3. Try opening http://localhost:8881 in your browser\n');
    throw error;
  } finally {
    await browser.close();
  }
}

export default globalSetup;
