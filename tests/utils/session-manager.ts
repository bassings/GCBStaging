import * as path from 'path';

/**
 * Session Manager for Multi-Session Test Isolation
 *
 * Provides session ID management to enable multiple Claude Code instances
 * to run tests simultaneously without interfering with each other's database state.
 *
 * Each test session gets its own isolated SQLite database:
 * - Session 1 → .ht.sqlite.session-1
 * - Session 2 → .ht.sqlite.session-2
 * - Development → .ht.sqlite (unchanged)
 *
 * @package GCB_Test_Utils
 */
export class SessionManager {
  private static sessionId: string | null = null;

  /**
   * Get Session ID
   *
   * Returns a unique identifier for the current test session.
   *
   * Priority:
   * 1. Environment variable TEST_SESSION_ID (e.g., "claude-1")
   * 2. Process ID (auto-generated, e.g., "12345")
   *
   * @returns Unique session identifier
   */
  static getSessionId(): string {
    if (!this.sessionId) {
      this.sessionId = process.env.TEST_SESSION_ID || `${process.pid}`;
    }
    return this.sessionId;
  }

  /**
   * Get Database Path
   *
   * Returns the absolute path to the session-specific database file.
   *
   * @returns Absolute path to session database (e.g., "wp-content/database/.ht.sqlite.session-12345")
   */
  static getDatabasePath(): string {
    const sessionId = this.getSessionId();
    return path.join('wp-content', 'database', `.ht.sqlite.session-${sessionId}`);
  }

  /**
   * Get Baseline Database Path
   *
   * Returns the path to the baseline database used as template for sessions.
   *
   * @returns Absolute path to baseline database
   */
  static getBaselineDatabasePath(): string {
    return path.join('wp-content', 'database', '.ht.sqlite.baseline');
  }

  /**
   * Get Development Database Path
   *
   * Returns the path to the main development database.
   *
   * @returns Absolute path to development database
   */
  static getDevelopmentDatabasePath(): string {
    return path.join('wp-content', 'database', '.ht.sqlite');
  }

  /**
   * Reset Session ID
   *
   * Clears the cached session ID. Useful for testing.
   */
  static resetSessionId(): void {
    this.sessionId = null;
  }
}
