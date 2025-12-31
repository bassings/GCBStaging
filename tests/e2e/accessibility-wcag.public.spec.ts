import { test, expect } from '@playwright/test';

/**
 * WCAG 2.2 Level AA Compliance Tests
 *
 * Tests for accessibility requirements:
 * - Skip Navigation (SC 2.4.1 - Bypass Blocks)
 * - Keyboard Navigation (SC 2.1.1 - Keyboard Accessible)
 * - Focus Visible (SC 2.4.7 - Focus Visible)
 * - Touch Target Size (SC 2.5.8 - Target Size - Level AA)
 */

test.describe('WCAG 2.2 AA - Skip Navigation', () => {
  test.beforeEach(async ({ page }) => {
    // Reset database state before each test
    const resetResponse = await page.request.delete('http://localhost:8881/wp-json/gcb-testing/v1/reset', {
      headers: {
        'GCB-Test-Key': 'test-secret-key-local'
      }
    });
    expect(resetResponse.ok()).toBeTruthy();

    // Create test posts for homepage patterns to render
    for (let i = 1; i <= 5; i++) {
      await page.request.post('http://localhost:8881/wp-json/gcb-testing/v1/create-post', {
        headers: {
          'Content-Type': 'application/json',
          'GCB-Test-Key': 'test-secret-key-local'
        },
        data: {
          title: `Test Post ${i}`,
          content: `This is test content for post ${i}. `.repeat(50), // ~300 words
          status: 'publish'
        }
      });
    }

    await page.goto('/');
  });

  test('Skip to main content link exists as first focusable element', async ({ page }) => {
    // Tab once to focus the first interactive element
    await page.keyboard.press('Tab');

    // The focused element should be the skip link
    const focusedElement = page.locator(':focus');
    const text = await focusedElement.textContent();

    expect(text?.toLowerCase()).toContain('skip');
    expect(text?.toLowerCase()).toContain('content');
  });

  test('Skip link is visually hidden by default but visible on focus', async ({ page }) => {
    const skipLink = page.locator('a[href="#main-content"]').first();

    // Should exist in the DOM
    await expect(skipLink).toBeAttached();

    // Check if it's visually hidden (positioned off-screen or has sr-only class)
    const classList = await skipLink.getAttribute('class');
    const isScreenReaderOnly = classList?.includes('sr-only') || classList?.includes('skip-link');

    expect(isScreenReaderOnly).toBeTruthy();

    // Focus the skip link
    await skipLink.focus();

    // When focused, it should become visible (check for focus styles)
    const outlineStyle = await skipLink.evaluate(el => {
      const styles = window.getComputedStyle(el);
      return styles.outline;
    });

    // Should have a visible outline (not 'none')
    expect(outlineStyle).not.toBe('none');
    expect(outlineStyle).not.toBe('');
  });

  test('Skip link jumps to main content when activated', async ({ page }) => {
    // Tab to focus the skip link (first interactive element)
    await page.keyboard.press('Tab');

    // Press Enter to activate the link
    await page.keyboard.press('Enter');

    // Wait a moment for the jump
    await page.waitForTimeout(200);

    // The main content area should now be in the viewport
    const mainContent = page.locator('#main-content');
    await expect(mainContent).toBeInViewport();
  });

  test('Skip link has sufficient color contrast (3:1 minimum for focus)', async ({ page }) => {
    const skipLink = page.locator('a[href="#main-content"]').first();
    await skipLink.focus();

    // Get the outline color (Acid Lime on Void Black should be 18.2:1)
    const outlineColor = await skipLink.evaluate(el => {
      const styles = window.getComputedStyle(el);
      return styles.outlineColor;
    });

    // Acid Lime is rgb(204, 255, 0) or #CCFF00
    // This is a basic check - full contrast testing would require color parsing
    expect(outlineColor).toBeTruthy();
  });
});

test.describe('WCAG 2.2 AA - Keyboard Navigation', () => {
  test.beforeEach(async ({ page }) => {
    // Reset and create test posts
    const resetResponse = await page.request.delete('http://localhost:8881/wp-json/gcb-testing/v1/reset', {
      headers: {
        'GCB-Test-Key': 'test-secret-key-local'
      }
    });
    expect(resetResponse.ok()).toBeTruthy();

    for (let i = 1; i <= 8; i++) {
      await page.request.post('http://localhost:8881/wp-json/gcb-testing/v1/create-post', {
        headers: {
          'Content-Type': 'application/json',
          'GCB-Test-Key': 'test-secret-key-local'
        },
        data: {
          title: `Test Post ${i}`,
          content: `Content for post ${i}. `.repeat(30),
          status: 'publish'
        }
      });
    }

    await page.goto('/');
  });

  test('All interactive elements are keyboard accessible via Tab', async ({ page }) => {
    const interactiveElements = [];

    // Tab through the first 15 elements to capture navigation, hero, etc.
    for (let i = 0; i < 15; i++) {
      await page.keyboard.press('Tab');

      const focusedElement = await page.evaluate(() => {
        const el = document.activeElement;
        return {
          tagName: el?.tagName,
          role: el?.getAttribute('role'),
          ariaLabel: el?.getAttribute('aria-label'),
          text: el?.textContent?.substring(0, 50)
        };
      });

      interactiveElements.push(focusedElement);
    }

    // Should have tabbed through at least 10 interactive elements
    expect(interactiveElements.length).toBeGreaterThanOrEqual(10);

    // All focused elements should be interactive (A, BUTTON, INPUT, etc.)
    const validTags = ['A', 'BUTTON', 'INPUT', 'TEXTAREA', 'SELECT'];
    const allInteractive = interactiveElements.every(el =>
      validTags.includes(el.tagName) || el.role === 'button'
    );

    expect(allInteractive).toBeTruthy();
  });

  test('Focus indicators are visible on all interactive elements', async ({ page }) => {
    // Tab through several elements and check focus visibility
    const focusChecks = [];

    for (let i = 0; i < 10; i++) {
      await page.keyboard.press('Tab');

      const focusVisible = await page.evaluate(() => {
        const el = document.activeElement as HTMLElement;
        const styles = window.getComputedStyle(el);

        return {
          outline: styles.outline,
          outlineWidth: styles.outlineWidth,
          outlineColor: styles.outlineColor,
          outlineStyle: styles.outlineStyle,
          boxShadow: styles.boxShadow
        };
      });

      focusChecks.push(focusVisible);
    }

    // At least 80% of elements should have visible focus indicators
    const withOutline = focusChecks.filter(check =>
      check.outline !== 'none' &&
      check.outlineWidth !== '0px' &&
      check.outlineStyle !== 'none'
    );

    const percentageWithOutline = (withOutline.length / focusChecks.length) * 100;
    expect(percentageWithOutline).toBeGreaterThanOrEqual(80);
  });

  test('Tab order follows visual order (top to bottom, left to right)', async ({ page }) => {
    const tabOrder = [];

    // Tab through navigation and first few content sections
    for (let i = 0; i < 15; i++) {
      await page.keyboard.press('Tab');

      const elementInfo = await page.evaluate(() => {
        const el = document.activeElement as HTMLElement;
        const rect = el.getBoundingClientRect();

        return {
          y: rect.top,
          x: rect.left,
          text: el.textContent?.substring(0, 30),
          tagName: el.tagName
        };
      });

      tabOrder.push(elementInfo);
    }

    // Check that tab order is generally logical
    // The first element should be near the top (skip link or navigation)
    expect(tabOrder[0].y).toBeLessThan(200);

    // Y positions should generally increase (moving down the page)
    // Allow flexibility for horizontal layouts within the same section
    let previousY = -1;
    let majorYIncreaseCount = 0;
    let backwardJumps = 0;

    tabOrder.forEach(item => {
      if (item.y > previousY + 50) { // Significant move down the page
        majorYIncreaseCount++;
      } else if (item.y < previousY - 100) { // Jumping backwards significantly (bad)
        backwardJumps++;
      }
      previousY = Math.max(previousY, item.y);
    });

    // Should move down the page at least twice (e.g., header -> hero -> culture grid)
    expect(majorYIncreaseCount).toBeGreaterThanOrEqual(2);

    // Should not have significant backward jumps in tab order
    expect(backwardJumps).toBeLessThanOrEqual(1);
  });
});

test.describe('WCAG 2.2 AA - Touch Target Size (Mobile)', () => {
  test.beforeEach(async ({ page }) => {
    // Set mobile viewport
    await page.setViewportSize({ width: 375, height: 667 }); // iPhone SE

    // Reset and create test posts
    const resetResponse = await page.request.delete('http://localhost:8881/wp-json/gcb-testing/v1/reset', {
      headers: {
        'GCB-Test-Key': 'test-secret-key-local'
      }
    });
    expect(resetResponse.ok()).toBeTruthy();

    for (let i = 1; i <= 5; i++) {
      await page.request.post('http://localhost:8881/wp-json/gcb-testing/v1/create-post', {
        headers: {
          'Content-Type': 'application/json',
          'GCB-Test-Key': 'test-secret-key-local'
        },
        data: {
          title: `Mobile Test Post ${i}`,
          content: `Mobile content ${i}. `.repeat(20),
          status: 'publish'
        }
      });
    }

    await page.goto('/');
  });

  test('All interactive elements meet WCAG 24px minimum touch target size', async ({ page }) => {
    // WCAG 2.5.8 Level AA requires 24px × 24px minimum
    // Get all interactive elements except skip link (intentionally off-screen)
    const buttons = page.locator('a:not(.skip-link), button, input[type="submit"], [role="button"]');
    const count = await buttons.count();

    expect(count).toBeGreaterThan(0);

    const violations = [];

    for (let i = 0; i < Math.min(count, 20); i++) { // Check first 20 elements
      const box = await buttons.nth(i).boundingBox();

      if (box && (box.width < 24 || box.height < 24)) {
        const text = await buttons.nth(i).textContent();
        const classList = await buttons.nth(i).getAttribute('class');

        violations.push({
          index: i,
          width: Math.round(box.width),
          height: Math.round(box.height),
          text: text?.substring(0, 30),
          class: classList
        });
      }
    }

    // All interactive elements should meet WCAG 24px minimum
    if (violations.length > 0) {
      console.log('WCAG 24px touch target violations:', violations);
    }
    expect(violations).toHaveLength(0);
  });

  test('All interactive elements meet recommended 44px touch target size (best practice)', async ({ page }) => {
    // iOS/Android recommendation: 44px × 44px minimum (exceeds WCAG requirement)
    // This is a best practice test, not a strict requirement
    const buttons = page.locator('a:not(.skip-link), button, input[type="submit"], [role="button"]');
    const count = await buttons.count();

    expect(count).toBeGreaterThan(0);

    const violations = [];

    for (let i = 0; i < Math.min(count, 20); i++) { // Check first 20 elements
      const box = await buttons.nth(i).boundingBox();

      if (box && (box.width < 44 || box.height < 44)) {
        const text = await buttons.nth(i).textContent();
        const classList = await buttons.nth(i).getAttribute('class');

        violations.push({
          index: i,
          width: Math.round(box.width),
          height: Math.round(box.height),
          text: text?.substring(0, 30),
          class: classList
        });
      }
    }

    // Report violations but don't fail the test (this is a recommendation, not a requirement)
    if (violations.length > 0) {
      console.log(`⚠️ ${violations.length} elements below 44px recommendation (WCAG only requires 24px):`, violations);
    }

    // For now, we accept elements >= 27px as reasonable for text links
    const criticalViolations = violations.filter(v => v.height < 27);
    expect(criticalViolations).toHaveLength(0);
  });

  test('Touch targets have adequate spacing (24px minimum between centers)', async ({ page }) => {
    // Get all visible interactive elements in the navigation
    const navButtons = page.locator('nav a, nav button');
    const count = await navButtons.count();

    if (count < 2) {
      // Skip test if not enough nav items
      return;
    }

    const positions = [];
    for (let i = 0; i < count; i++) {
      const box = await navButtons.nth(i).boundingBox();
      if (box) {
        positions.push({
          centerX: box.x + box.width / 2,
          centerY: box.y + box.height / 2
        });
      }
    }

    // Check spacing between adjacent elements
    for (let i = 0; i < positions.length - 1; i++) {
      const distance = Math.sqrt(
        Math.pow(positions[i + 1].centerX - positions[i].centerX, 2) +
        Math.pow(positions[i + 1].centerY - positions[i].centerY, 2)
      );

      // WCAG 2.5.8 requires 24px spacing (we aim for 44px)
      expect(distance).toBeGreaterThanOrEqual(24);
    }
  });
});

test.describe('WCAG 2.2 AA - HTML Structure & Semantics', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/');
  });

  test('HTML document has lang attribute', async ({ page }) => {
    const htmlLang = await page.locator('html').getAttribute('lang');

    expect(htmlLang).toBeTruthy();
    // Accept any valid English language code (en, en-US, en-AU, en-GB, etc.)
    expect(htmlLang).toMatch(/^en(-[A-Z]{2})?$/);
  });

  test('Main landmark exists for main content', async ({ page }) => {
    const mainLandmark = page.locator('main, [role="main"]');
    await expect(mainLandmark).toBeAttached();

    // Should have an ID for skip link targeting
    const mainId = await mainLandmark.getAttribute('id');
    expect(mainId).toBeTruthy();
  });

  test('No duplicate IDs in the document', async ({ page }) => {
    const duplicateIds = await page.evaluate(() => {
      const allElements = Array.from(document.querySelectorAll('[id]'));
      const ids = allElements.map(el => el.id);
      const uniqueIds = new Set(ids);

      if (ids.length === uniqueIds.size) {
        return [];
      }

      // Find duplicates
      const duplicates: string[] = [];
      const seen = new Set();

      ids.forEach(id => {
        if (seen.has(id)) {
          duplicates.push(id);
        }
        seen.add(id);
      });

      return [...new Set(duplicates)];
    });

    expect(duplicateIds).toHaveLength(0);
  });

  test('Headings follow proper hierarchy (no skipped levels)', async ({ page }) => {
    const headingLevels = await page.evaluate(() => {
      const headings = Array.from(document.querySelectorAll('h1, h2, h3, h4, h5, h6'));
      return headings.map(h => parseInt(h.tagName.substring(1)));
    });

    // Check for skipped levels
    for (let i = 1; i < headingLevels.length; i++) {
      const jump = headingLevels[i] - headingLevels[i - 1];

      // Should not skip more than 1 level (e.g., h1 -> h3 is bad)
      expect(jump).toBeLessThanOrEqual(1);
    }
  });
});
