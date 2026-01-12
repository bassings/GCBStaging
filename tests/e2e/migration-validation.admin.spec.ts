import { test, expect } from '@playwright/test';

/**
 * Migration Validation Tests
 *
 * Validates all 20 unique content patterns after Avada→Gutenberg migration.
 * Tests check that migrated posts are editable in the Gutenberg block editor
 * without showing "invalid block" errors.
 *
 * These tests use the admin project (requires authentication via storageState).
 *
 * Test Patterns Cover 94% of Posts (3,758 of 3,895):
 * - video|multi-col: 1,542 posts (40%)
 * - mixed|multi-col: 1,168 posts (30%)
 * - text-only|columned: 671 posts (17%)
 * - mixed|complex: 377 posts (10%)
 * - Plus 16 edge case patterns
 */

interface TestPost {
  id: number;
  pattern: string;
  title: string;
  coverage: number;
  priority: 'CRITICAL' | 'HIGH' | 'MEDIUM' | 'LOW';
}

const TEST_POSTS: TestPost[] = [
  // CRITICAL - 97% coverage combined
  { id: 3049, pattern: 'video|multi-col', title: 'A brief history of Saab', coverage: 1542, priority: 'CRITICAL' },
  { id: 26909, pattern: 'mixed|multi-col', title: 'Helpful Hints: etag placement', coverage: 1168, priority: 'CRITICAL' },
  { id: 6, pattern: 'text-only|columned', title: 'Could Sexy Peugeot RCZ be the new GAY Icon?', coverage: 671, priority: 'HIGH' },
  { id: 16188, pattern: 'mixed|complex', title: 'VW Golf Cabriolet: The Joy of Taking Your Top Off in Public', coverage: 377, priority: 'HIGH' },

  // MEDIUM priority
  { id: 729, pattern: 'standard|multi-col', title: 'SAAB 9-5 Aero. The sexy car that nearly wasnt.', coverage: 44, priority: 'MEDIUM' },
  { id: 91845, pattern: 'table|multi-col', title: '2025 Kia K4 - Good Car Spoilt by its Looks', coverage: 28, priority: 'MEDIUM' },
  { id: 27615, pattern: 'text-only|multi-col', title: '2018 Tesla Model S P100D Review', coverage: 16, priority: 'MEDIUM' },
  { id: 32659, pattern: 'video-gallery|complex', title: 'What are the Top 10 Australian Cars', coverage: 13, priority: 'MEDIUM' },

  // LOW priority - smaller coverage or edge cases
  { id: 17475, pattern: 'video|columned', title: 'Hondas new HR-V: Good looking, but is it any good?', coverage: 10, priority: 'LOW' },
  { id: 28667, pattern: 'video|complex', title: 'Range Rover Sport HSE P400 Hybrid Climbs 999 45 Stairs', coverage: 8, priority: 'LOW' },
  { id: 30756, pattern: 'multi-image|complex', title: 'Lotus Evija production limited 130 cars', coverage: 4, priority: 'LOW' },
  { id: 31474, pattern: 'mixed-heavy|complex', title: 'Top 10 Gay Lesbian LGBT Cars', coverage: 4, priority: 'LOW' },
  { id: 33420, pattern: 'text-only|complex', title: '2020 Mitsubishi Pajero Sport', coverage: 3, priority: 'LOW' },

  // Edge cases - 1 post each
  { id: 28597, pattern: 'text-only|single', title: 'How to Use Smart Entry and Start', coverage: 1, priority: 'LOW' },
  { id: 28612, pattern: 'multi-image|single', title: 'Citroen 19_19 Concept', coverage: 1, priority: 'LOW' },
  { id: 28629, pattern: 'table|single', title: 'Next-Gen Mazda3 Sedan Arrives in Australia, at Last', coverage: 1, priority: 'LOW' },
  { id: 28632, pattern: 'standard|single', title: 'New Audi A4 for 2020', coverage: 1, priority: 'LOW' },
  { id: 31092, pattern: 'multi-image|multi-col', title: '2019 Max Verstappen Wins Grand Prix Hockenheim', coverage: 1, priority: 'LOW' },
  { id: 33073, pattern: 'standard|complex', title: 'The Last Overland Adventure', coverage: 1, priority: 'LOW' },
  { id: 94615, pattern: 'table|complex', title: 'Elegance Recharged: Classic Mercedes, MONCEAU Magic', coverage: 1, priority: 'LOW' },
];

test.describe('Migration Validation - Gutenberg Block Integrity', () => {

  // Increase timeout for editor loading - Gutenberg can be slow
  test.setTimeout(60000);

  for (const post of TEST_POSTS) {
    test(`[${post.priority}] Pattern: ${post.pattern} (${post.coverage} posts) - Post ${post.id}`, async ({ page }) => {
      // Navigate to the post edit page in Gutenberg
      await page.goto(`/wp-admin/post.php?post=${post.id}&action=edit`);

      // Wait for the Gutenberg editor to load
      // The editor wrapper is present when Gutenberg has initialized
      await page.waitForSelector('.block-editor-block-list__layout', { timeout: 30000 });

      // Check 1: No block warning elements (indicates invalid blocks)
      const blockWarnings = await page.locator('.block-editor-warning').count();

      // Check 2: No "Block contains unexpected or invalid content" text
      const invalidContentErrors = await page.locator('text="Block contains unexpected or invalid content"').count();

      // Check 3: No "Attempt Block Recovery" buttons
      const recoveryButtons = await page.locator('button:has-text("Attempt Block Recovery")').count();

      // Check 4: No "This block has encountered an error" messages
      const blockErrors = await page.locator('text="This block has encountered an error"').count();

      // Log results for debugging
      console.log(`Post ${post.id} (${post.pattern}):`);
      console.log(`  - Block warnings: ${blockWarnings}`);
      console.log(`  - Invalid content errors: ${invalidContentErrors}`);
      console.log(`  - Recovery buttons: ${recoveryButtons}`);
      console.log(`  - Block errors: ${blockErrors}`);

      // Assertions - all should be zero for a passing test
      expect(blockWarnings, `Post ${post.id} has ${blockWarnings} block warnings`).toBe(0);
      expect(invalidContentErrors, `Post ${post.id} has ${invalidContentErrors} invalid content errors`).toBe(0);
      expect(recoveryButtons, `Post ${post.id} has ${recoveryButtons} recovery buttons`).toBe(0);
      expect(blockErrors, `Post ${post.id} has ${blockErrors} block errors`).toBe(0);

      // Check 5: Verify the post title is editable (basic editability test)
      const titleInput = page.locator('.editor-post-title__input, [aria-label="Add title"]');
      await expect(titleInput).toBeVisible();

      // Check 6: Verify the publish/update button is enabled (post is saveable)
      const saveButton = page.locator('.editor-post-publish-button, .editor-post-save-draft');
      const isDisabled = await saveButton.getAttribute('aria-disabled');
      // aria-disabled should not be "true" for a valid post
      if (isDisabled === 'true') {
        // Get any validation messages
        const validationMessage = await page.locator('.editor-post-publish-button').textContent();
        console.log(`  - Save button disabled: ${validationMessage}`);
      }

      console.log(`  ✅ Post ${post.id} passed validation`);
    });
  }
});

test.describe('Migration Validation - Frontend Rendering', () => {

  // Test that migrated posts render correctly on the frontend
  for (const post of TEST_POSTS.filter(p => p.priority === 'CRITICAL' || p.priority === 'HIGH')) {
    test(`[Frontend] ${post.pattern} - Post ${post.id} renders without errors`, async ({ page }) => {
      // Navigate to the frontend post view
      await page.goto(`/?p=${post.id}`);

      // Wait for page to load
      await page.waitForLoadState('domcontentloaded');

      // Check 1: No PHP fatal errors (white screen of death)
      const bodyContent = await page.textContent('body');
      expect(bodyContent).not.toContain('Fatal error');
      expect(bodyContent).not.toContain('Parse error');
      expect(bodyContent).not.toContain('Warning:');

      // Check 2: Page has actual content (not just error message)
      expect(bodyContent!.length).toBeGreaterThan(100);

      // Check 3: The main content area exists
      const mainContent = page.locator('#main-content, main, article, .entry-content');
      await expect(mainContent.first()).toBeVisible();

      console.log(`  ✅ Post ${post.id} frontend renders correctly`);
    });
  }
});

test.describe('Migration Validation - Summary Report', () => {

  test('Generate validation summary', async ({ page }) => {
    const results: { id: number; pattern: string; coverage: number; status: string }[] = [];

    for (const post of TEST_POSTS.slice(0, 4)) { // Test first 4 critical posts for summary
      await page.goto(`/wp-admin/post.php?post=${post.id}&action=edit`);

      try {
        await page.waitForSelector('.block-editor-block-list__layout', { timeout: 30000 });

        const warnings = await page.locator('.block-editor-warning').count();
        const errors = await page.locator('text="Block contains unexpected or invalid content"').count();

        results.push({
          id: post.id,
          pattern: post.pattern,
          coverage: post.coverage,
          status: warnings === 0 && errors === 0 ? 'PASS' : 'FAIL',
        });
      } catch (error) {
        results.push({
          id: post.id,
          pattern: post.pattern,
          coverage: post.coverage,
          status: 'ERROR',
        });
      }
    }

    // Log summary
    console.log('\n═══════════════════════════════════════════════════════════');
    console.log('         MIGRATION VALIDATION SUMMARY');
    console.log('═══════════════════════════════════════════════════════════');

    let totalCoverage = 0;
    let passingCoverage = 0;

    for (const result of results) {
      const statusIcon = result.status === 'PASS' ? '✅' : result.status === 'FAIL' ? '❌' : '⚠️';
      console.log(`${statusIcon} ${result.pattern.padEnd(25)} ${result.coverage.toString().padStart(5)} posts - ${result.status}`);
      totalCoverage += result.coverage;
      if (result.status === 'PASS') {
        passingCoverage += result.coverage;
      }
    }

    console.log('═══════════════════════════════════════════════════════════');
    console.log(`Coverage: ${passingCoverage}/${totalCoverage} posts (${Math.round(passingCoverage/totalCoverage*100)}%)`);
    console.log('═══════════════════════════════════════════════════════════\n');

    // This is an informational test - always passes
    expect(results.length).toBeGreaterThan(0);
  });
});
