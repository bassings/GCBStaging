import { chromium, type FullConfig } from '@playwright/test';
import * as fs from 'fs';
import * as path from 'path';
import { fileURLToPath } from 'url';
import { SessionManager } from './tests/utils/session-manager.js';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

/**
 * Global Setup for GCB Magazine E2E Tests
 *
 * Session-Based Isolation:
 * 1. Announces test session ID
 * 2. Creates baseline database from development database (if needed)
 * 3. Creates session-specific database from baseline
 * 4. Swaps database symlink to point to session database
 * 5. Validates that WordPress Studio is running and accessible
 *
 * This enables multiple Claude Code instances to run tests simultaneously
 * without interfering with each other's database state.
 */
async function globalSetup(config: FullConfig) {
  const baseURL = config.projects[0].use.baseURL || 'http://localhost:8881';
  const sessionId = SessionManager.getSessionId();

  console.log('\n🎯 Test Session ID:', sessionId);
  console.log('ℹ️  Each session uses an isolated database to prevent interference\n');

  // Check for existing session databases (might indicate another session is running)
  const databaseDir = path.join(__dirname, 'wp-content', 'database');
  const existingSessionDbs = fs.readdirSync(databaseDir)
    .filter(file => file.startsWith('.ht.sqlite.session-') && file !== `.ht.sqlite.session-${sessionId}`);

  if (existingSessionDbs.length > 0) {
    console.log('⚠️  Warning: Found existing session databases:', existingSessionDbs.join(', '));
    console.log('ℹ️  If another Claude instance is running tests, they may conflict.');
    console.log('ℹ️  Current implementation supports sequential sessions only.\n');
  }

  // Define paths
  const devDbPath = path.join(__dirname, SessionManager.getDevelopmentDatabasePath());
  const baselineDbPath = path.join(__dirname, SessionManager.getBaselineDatabasePath());
  const sessionDbPath = path.join(__dirname, SessionManager.getDatabasePath());
  const dbLinkPath = path.join(__dirname, 'wp-content', 'database', '.ht.sqlite');

  try {
    // Step 1: Create baseline database if it doesn't exist
    if (!fs.existsSync(baselineDbPath)) {
      console.log('📦 Creating baseline database from development database...');

      if (fs.existsSync(devDbPath)) {
        fs.copyFileSync(devDbPath, baselineDbPath);
        const stats = fs.statSync(baselineDbPath);
        const sizeMB = (stats.size / 1024 / 1024).toFixed(2);
        console.log(`✅ Baseline created: .ht.sqlite.baseline (${sizeMB} MB)\n`);
      } else {
        console.log('⚠️  Warning: No development database found');
        console.log('ℹ️  Tests will start with empty database\n');
      }
    } else {
      const stats = fs.statSync(baselineDbPath);
      const sizeMB = (stats.size / 1024 / 1024).toFixed(2);
      console.log(`✅ Using existing baseline: .ht.sqlite.baseline (${sizeMB} MB)\n`);
    }

    // Step 2: Create session database from baseline
    console.log(`💾 Creating session database: .ht.sqlite.session-${sessionId}`);

    if (fs.existsSync(baselineDbPath)) {
      fs.copyFileSync(baselineDbPath, sessionDbPath);
      const stats = fs.statSync(sessionDbPath);
      const sizeMB = (stats.size / 1024 / 1024).toFixed(2);
      console.log(`✅ Session database created (${sizeMB} MB)\n`);
    } else {
      console.log('⚠️  Warning: No baseline database found, session will start empty\n');
    }

    // Step 3: Backup original .ht.sqlite if it exists and isn't a symlink
    const originalBackupPath = path.join(__dirname, 'wp-content', 'database', '.ht.sqlite.original');

    if (fs.existsSync(dbLinkPath)) {
      const stats = fs.lstatSync(dbLinkPath);

      if (!stats.isSymbolicLink()) {
        // It's a real file, back it up
        console.log('💾 Backing up original development database...');
        fs.copyFileSync(dbLinkPath, originalBackupPath);
        console.log('✅ Original database backed up to .ht.sqlite.original\n');
      }

      // Remove existing file or symlink
      fs.unlinkSync(dbLinkPath);
    }

    // Step 4: Create symlink to session database
    console.log('🔗 Creating symlink to session database...');
    const relativePath = path.relative(
      path.dirname(dbLinkPath),
      sessionDbPath
    );
    fs.symlinkSync(relativePath, dbLinkPath);
    console.log(`✅ Symlink created: .ht.sqlite → ${relativePath}\n`);

  } catch (error) {
    console.error('❌ Session database setup failed:', error);
    throw error;
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
