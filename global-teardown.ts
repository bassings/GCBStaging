import * as fs from 'fs';
import * as path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

/**
 * Global Teardown for GCB Magazine E2E Tests
 *
 * Restores the database backup created before tests ran.
 * This ensures any pre-existing content is preserved.
 */
async function globalTeardown() {
  const dbPath = path.join(__dirname, 'wp-content', 'database', '.ht.sqlite');
  const backupPath = path.join(__dirname, 'wp-content', 'database', '.ht.sqlite.backup');

  console.log('\n🔄 Restoring database from backup...\n');

  try {
    if (fs.existsSync(backupPath)) {
      // Restore backup
      fs.copyFileSync(backupPath, dbPath);
      console.log(`✅ Database restored from backup`);

      // Remove backup file
      fs.unlinkSync(backupPath);
      console.log(`✅ Backup file removed`);

      console.log('\n🎉 Database restoration complete!\n');
    } else {
      console.log('ℹ️  No backup file found - skipping restoration\n');
    }
  } catch (error) {
    console.error('\n⚠️  Warning: Database restoration failed:', error);
    console.error('The backup file may still exist at:', backupPath);
    console.error('You can manually restore it if needed.\n');
    // Don't throw - we don't want to fail the entire test run if restoration fails
  }
}

export default globalTeardown;
