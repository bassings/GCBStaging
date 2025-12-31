import { test as setup, expect } from '@playwright/test';
import * as path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const authFile = path.join(__dirname, '..', 'auth', '.auth', 'admin.json');

/**
 * Authentication Setup for GCB Magazine E2E Tests
 *
 * This setup runs once before admin tests to save the admin session state.
 * Uses programmatic login with test credentials.
 *
 * The session state is saved to tests/auth/.auth/admin.json and reused
 * across all admin tests to avoid repeated logins.
 */
setup('authenticate as admin', async ({ page }) => {
  console.log('\n🔐 Starting authentication setup...\n');

  await page.goto('/wp-login.php');

  // Check if already authenticated
  if (page.url().includes('wp-admin')) {
    console.log('✅ Already authenticated\n');
    await page.context().storageState({ path: authFile });
    return;
  }

  console.log('Logging in with test credentials...');

  // Fill in login form
  await page.fill('#user_login', 'gcb-admin');
  await page.fill('#user_pass', 'GCB-Test-Password-2025');

  // Submit login form
  await page.click('#wp-submit');

  // Wait for successful login (URL changes to wp-admin)
  await page.waitForURL('**/wp-admin/**', { timeout: 30000 });

  // Verify admin bar is visible
  await expect(page.locator('#wpadminbar')).toBeVisible({ timeout: 10000 });

  console.log('✅ Login successful!');

  // Save authentication state
  await page.context().storageState({ path: authFile });
  console.log(`✅ Authentication state saved to ${authFile}\n`);

  console.log('═══════════════════════════════════════════════════════════');
  console.log('🎉 Authentication setup complete!');
  console.log('═══════════════════════════════════════════════════════════');
  console.log('');
  console.log('All admin tests will now reuse this session.\n');
});
