import { APIRequestContext } from '@playwright/test';

/**
 * Response from database reset endpoint
 */
export interface ResetResponse {
  success: boolean;
  message?: string;
  deleted_posts?: number;
  deleted_pages?: number;
  deleted_media?: number;
  deleted_terms?: number;
  timestamp?: string;
}

/**
 * Database Helper for GCB Magazine E2E Tests
 *
 * Provides utilities for database operations, primarily
 * the reset() method to clear all database content between tests.
 */
export class DatabaseHelper {
  private apiContext: APIRequestContext;
  private baseURL: string;
  private testKey: string;

  /**
   * Create a new DatabaseHelper instance
   *
   * @param apiContext - Playwright API request context
   * @param baseURL - WordPress base URL (default: http://localhost:8881)
   */
  constructor(apiContext: APIRequestContext, baseURL: string = 'http://localhost:8881') {
    this.apiContext = apiContext;
    this.baseURL = baseURL;
    this.testKey = 'test-secret-key-local';
  }

  /**
   * Reset Database
   *
   * Calls the gcb-test-utils plugin's reset endpoint to delete
   * all posts, pages, media, terms, and comments.
   *
   * @returns Promise resolving to reset statistics
   * @throws Error if reset fails or returns non-200 status
   */
  async reset(): Promise<ResetResponse> {
    const response = await this.apiContext.delete(
      `${this.baseURL}/wp-json/gcb-testing/v1/reset`,
      {
        headers: {
          'X-Test-Key': this.testKey,
          'Content-Type': 'application/json',
        },
      }
    );

    if (!response.ok()) {
      const errorBody = await response.text().catch(() => 'Unknown error');
      throw new Error(
        `Database reset failed with status ${response.status()}: ${errorBody}`
      );
    }

    const data: ResetResponse = await response.json();
    console.log('🔄 Database reset:', data);
    return data;
  }

  /**
   * Verify Reset Endpoint is Accessible
   *
   * Checks if the reset endpoint exists and is responding.
   * Useful for smoke tests.
   *
   * @returns Promise resolving to true if endpoint is accessible
   */
  async verifyEndpointExists(): Promise<boolean> {
    try {
      const response = await this.apiContext.delete(
        `${this.baseURL}/wp-json/gcb-testing/v1/reset`,
        {
          headers: {
            'X-Test-Key': 'invalid-key',
          },
        }
      );

      // Should get 401 (unauthorized) if endpoint exists but key is wrong
      return response.status() === 401;
    } catch {
      return false;
    }
  }
}

/**
 * Factory function to create DatabaseHelper
 *
 * @param apiContext - Playwright API request context
 * @param baseURL - WordPress base URL (optional)
 * @returns DatabaseHelper instance
 */
export function createDatabaseHelper(
  apiContext: APIRequestContext,
  baseURL?: string
): DatabaseHelper {
  return new DatabaseHelper(apiContext, baseURL);
}
