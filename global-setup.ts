import { chromium, type FullConfig } from '@playwright/test';
import * as fs from 'fs';
import * as path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

/**
 * Global Setup for GCB Magazine E2E Tests
 *
 * 1. Creates a backup of the SQLite database
 * 2. Validates that WordPress Studio is running and accessible
 * 3. Fails fast if environment is not ready
 *
 * The database backup will be restored in global-teardown.ts
 */
async function globalSetup(config: FullConfig) {
  const baseURL = config.projects[0].use.baseURL || 'http://localhost:8881';

  // Backup database before tests run
  console.log('\n💾 Creating database backup...\n');

  const dbPath = path.join(__dirname, 'wp-content', 'database', '.ht.sqlite');
  const backupPath = path.join(__dirname, 'wp-content', 'database', '.ht.sqlite.backup');

  try {
    if (fs.existsSync(dbPath)) {
      fs.copyFileSync(dbPath, backupPath);
      const stats = fs.statSync(backupPath);
      const sizeMB = (stats.size / 1024 / 1024).toFixed(2);
      console.log(`✅ Database backed up to: .ht.sqlite.backup (${sizeMB} MB)`);
      console.log(`ℹ️  Original content will be restored after tests complete\n`);
    } else {
      console.log('ℹ️  No database file found - skipping backup\n');
    }
  } catch (error) {
    console.error('⚠️  Warning: Database backup failed:', error);
    console.error('Tests will continue, but content may not be restored.\n');
  }

  console.log('🔍 Validating WordPress Studio environment...\n');

  const browser = await chromium.launch();
  const context = await browser.newContext();
  const page = await context.newPage();

  try {
    // Test 1: WordPress is accessible (404 is OK - means no posts exist yet)
    const response = await page.goto(baseURL, {
      waitUntil: 'domcontentloaded',
      timeout: 30000,
    });

    if (!response) {
      throw new Error(
        `❌ WordPress Studio not accessible at ${baseURL}\n` +
        `   No response received\n` +
        `   Please ensure WordPress Studio is running.`
      );
    }

    // Accept 200 (has content) or 404 (no posts yet) as valid WordPress responses
    const status = response.status();
    if (status !== 200 && status !== 404) {
      throw new Error(
        `❌ WordPress Studio not accessible at ${baseURL}\n` +
        `   Status: ${status}\n` +
        `   Please ensure WordPress Studio is running.`
      );
    }

    console.log(`✅ WordPress accessible at ${baseURL} (Status: ${status})`);

    // Test 2: WordPress REST API is accessible
    const apiResponse = await page.goto(`${baseURL}/wp-json/`, { waitUntil: 'domcontentloaded', timeout: 30000 });
    if (!apiResponse || !apiResponse.ok()) {
      throw new Error(`❌ WordPress REST API not accessible`);
    }
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
