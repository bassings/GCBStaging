import { APIRequestContext } from '@playwright/test';

/**
 * Theme Helper Utilities for GCB E2E Tests
 *
 * Provides programmatic theme activation and management via REST API.
 * Requires gcb-test-utils plugin with theme activation endpoints.
 */

/**
 * Activate a WordPress Theme
 *
 * Uses the gcb-test-utils REST API to switch the active theme.
 * Requires authentication via X-Test-Key header.
 *
 * @param apiContext - Playwright API request context
 * @param themeSlug - Theme directory name (e.g., 'gcb-brutalist')
 * @returns Promise resolving to true if activation succeeds
 * @throws Error if theme activation fails
 */
export async function activateTheme(
  apiContext: APIRequestContext,
  themeSlug: string
): Promise<boolean> {
  const response = await apiContext.post(
    '/wp-json/gcb-testing/v1/activate-theme',
    {
      data: { theme: themeSlug },
      headers: {
        'X-Test-Key': 'test-secret-key-local',
        'Content-Type': 'application/json',
      },
    }
  );

  if (!response.ok()) {
    const errorBody = await response.text().catch(() => 'Unknown error');
    throw new Error(
      `Theme activation failed with status ${response.status()}: ${errorBody}`
    );
  }

  const data = await response.json();
  console.log(`✅ Theme activated: ${themeSlug}`, data);
  return data.success;
}

/**
 * Get Currently Active Theme
 *
 * Retrieves the slug of the currently active WordPress theme.
 *
 * @param apiContext - Playwright API request context
 * @returns Promise resolving to active theme slug
 * @throws Error if request fails
 */
export async function getActiveTheme(
  apiContext: APIRequestContext
): Promise<string> {
  const response = await apiContext.get(
    '/wp-json/gcb-testing/v1/active-theme',
    {
      headers: {
        'X-Test-Key': 'test-secret-key-local',
      },
    }
  );

  if (!response.ok()) {
    throw new Error(`Failed to get active theme: ${response.status()}`);
  }

  const data = await response.json();
  return data.theme;
}

/**
 * Theme Activation Response
 */
export interface ThemeActivationResponse {
  success: boolean;
  message?: string;
  theme?: string;
  theme_name?: string;
  theme_version?: string;
}

/**
 * Active Theme Response
 */
export interface ActiveThemeResponse {
  theme: string;
  theme_name: string;
  theme_version: string;
}
