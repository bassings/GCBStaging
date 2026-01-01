import * as fs from 'fs';
import * as path from 'path';
import { fileURLToPath } from 'url';
import { SessionManager } from './tests/utils/session-manager.js';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

/**
 * Global Teardown for GCB Magazine E2E Tests
 *
 * Session Cleanup:
 * 1. Removes session-specific database
 * 2. Removes .ht.sqlite symlink
 * 3. Restores original .ht.sqlite file (if backed up)
 *
 * This cleanup ensures the development database is restored after tests complete.
 */
async function globalTeardown() {
  const sessionId = SessionManager.getSessionId();

  console.log('\n🧹 Cleaning up test session:', sessionId, '\n');

  const sessionDbPath = path.join(__dirname, SessionManager.getDatabasePath());
  const dbLinkPath = path.join(__dirname, 'wp-content', 'database', '.ht.sqlite');
  const originalBackupPath = path.join(__dirname, 'wp-content', 'database', '.ht.sqlite.original');

  try {
    // Step 1: Remove symlink
    if (fs.existsSync(dbLinkPath)) {
      const stats = fs.lstatSync(dbLinkPath);

      if (stats.isSymbolicLink()) {
        fs.unlinkSync(dbLinkPath);
        console.log('✅ Symlink removed: .ht.sqlite');
      }
    }

    // Step 2: Restore original .ht.sqlite if backup exists
    if (fs.existsSync(originalBackupPath)) {
      fs.copyFileSync(originalBackupPath, dbLinkPath);
      console.log('✅ Original database restored: .ht.sqlite');

      // Remove backup
      fs.unlinkSync(originalBackupPath);
      console.log('✅ Backup removed: .ht.sqlite.original');
    } else {
      console.log('ℹ️  No original database backup found - development database was symlink');
    }

    // Step 3: Remove session database
    if (fs.existsSync(sessionDbPath)) {
      fs.unlinkSync(sessionDbPath);
      console.log(`✅ Session database removed: .ht.sqlite.session-${sessionId}`);
    } else {
      console.log(`ℹ️  Session database not found: .ht.sqlite.session-${sessionId}`);
    }

    console.log('\n🎉 Session cleanup complete!\n');

  } catch (error) {
    console.error('\n⚠️  Warning: Session cleanup failed:', error);
    console.error('You may need to manually restore .ht.sqlite.original\n');
    // Don't throw - we don't want to fail the entire test run if cleanup fails
  }
}

export default globalTeardown;
