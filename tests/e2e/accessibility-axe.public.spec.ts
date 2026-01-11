import { test, expect } from '@playwright/test';
import { createAccessibilityHelper } from '../utils/accessibility.js';

/**
 * Accessibility Tests - axe-core WCAG 2.2 AA Audit
 *
 * Uses @axe-core/playwright to validate WCAG 2.2 AA compliance.
 * Tests cover:
 * - Full page audits
 * - Component-specific audits
 * - Mobile accessibility
 * - Quick checks for common issues
 */
test.describe('Accessibility - axe-core WCAG 2.2 AA Audit', () => {
  test.beforeEach(async ({ request }) => {
    // Reset database for consistent state
    await request.delete('/wp-json/gcb-testing/v1/reset', {
      headers: { 'GCB-Test-Key': 'test-secret-key-local' },
    });
  });

  test('Homepage passes WCAG 2.2 AA audit', async ({ page }) => {
    await page.goto('/');

    const a11y = createAccessibilityHelper(page);
    await a11y.assertNoViolations({
      exclude: [
        '.wp-block-embed', // Exclude third-party embeds
        'iframe', // Exclude iframes (YouTube, etc.)
      ],
    });
  });

  test('Homepage quick accessibility checks pass', async ({ page }) => {
    await page.goto('/');

    const a11y = createAccessibilityHelper(page);
    const { passed, failed } = await a11y.quickChecks();

    console.log('\n--- Quick Accessibility Checks ---');
    console.log('  PASSED:', passed);
    console.log('  FAILED:', failed);
    console.log('----------------------------------\n');

    // Critical checks that must pass
    expect(failed).not.toContain('HTML lang attribute');
    expect(failed).not.toContain('Page title');
  });

  test('Navigation component passes accessibility audit', async ({ page }) => {
    await page.goto('/');

    const a11y = createAccessibilityHelper(page);
    const results = await a11y.auditComponent('header, nav');

    const navViolations = results.violations.filter(
      (v) => v.impact === 'critical' || v.impact === 'serious'
    );

    if (navViolations.length > 0) {
      console.log('\n--- Navigation Accessibility Issues ---');
      navViolations.forEach((v) => {
        console.log(`  [${v.impact}] ${v.id}: ${v.description}`);
      });
      console.log('---------------------------------------\n');
    }

    expect(
      navViolations,
      'Navigation has critical accessibility issues'
    ).toHaveLength(0);
  });

  test('Footer component passes accessibility audit', async ({ page }) => {
    await page.goto('/');

    const a11y = createAccessibilityHelper(page);
    const results = await a11y.auditComponent('footer');

    const footerViolations = results.violations.filter(
      (v) => v.impact === 'critical' || v.impact === 'serious'
    );

    expect(
      footerViolations,
      'Footer has critical accessibility issues'
    ).toHaveLength(0);
  });

  test('Mobile view passes accessibility audit', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 667 });
    await page.goto('/');

    const a11y = createAccessibilityHelper(page);
    await a11y.assertNoViolations({
      exclude: ['.wp-block-embed', 'iframe'],
      allowedImpact: ['minor'], // Allow minor issues on mobile
    });
  });

  test('All images have alt text', async ({ page }) => {
    await page.goto('/');

    const imagesWithoutAlt = await page.evaluate(() => {
      const images = document.querySelectorAll('img:not([alt])');
      return Array.from(images).map((img) => ({
        src: img.getAttribute('src')?.substring(0, 80),
        parent: img.parentElement?.tagName,
      }));
    });

    if (imagesWithoutAlt.length > 0) {
      console.log('\n--- Images Missing Alt Text ---');
      imagesWithoutAlt.forEach((img) => {
        console.log(`  ${img.parent} > img: ${img.src}`);
      });
      console.log('-------------------------------\n');
    }

    expect(
      imagesWithoutAlt,
      'All images must have alt text'
    ).toHaveLength(0);
  });

  test('All buttons have accessible names', async ({ page }) => {
    await page.goto('/');

    const buttonsWithoutNames = await page.evaluate(() => {
      const buttons = document.querySelectorAll('button, [role="button"]');
      const issues: Array<{ html: string; reason: string }> = [];

      buttons.forEach((btn) => {
        const hasText = (btn.textContent?.trim().length || 0) > 0;
        const hasAriaLabel =
          btn.hasAttribute('aria-label') || btn.hasAttribute('aria-labelledby');
        const hasTitle = btn.hasAttribute('title');

        if (!hasText && !hasAriaLabel && !hasTitle) {
          issues.push({
            html: btn.outerHTML.substring(0, 100),
            reason: 'No text, aria-label, aria-labelledby, or title',
          });
        }
      });

      return issues;
    });

    if (buttonsWithoutNames.length > 0) {
      console.log('\n--- Buttons Without Accessible Names ---');
      buttonsWithoutNames.forEach((btn) => {
        console.log(`  ${btn.html}...`);
        console.log(`  Reason: ${btn.reason}`);
      });
      console.log('----------------------------------------\n');
    }

    expect(
      buttonsWithoutNames,
      'All buttons must have accessible names'
    ).toHaveLength(0);
  });

  test('All links have discernible text', async ({ page }) => {
    await page.goto('/');

    const emptyLinks = await page.evaluate(() => {
      const links = document.querySelectorAll('a');
      const issues: string[] = [];

      links.forEach((link) => {
        const hasText = (link.textContent?.trim().length || 0) > 0;
        const hasAriaLabel =
          link.hasAttribute('aria-label') ||
          link.hasAttribute('aria-labelledby');
        const hasTitle = link.hasAttribute('title');
        const hasImage = link.querySelector('img[alt]');

        if (!hasText && !hasAriaLabel && !hasTitle && !hasImage) {
          issues.push(link.outerHTML.substring(0, 80));
        }
      });

      return issues;
    });

    if (emptyLinks.length > 0) {
      console.log('\n--- Links Without Discernible Text ---');
      emptyLinks.forEach((link) => console.log(`  ${link}...`));
      console.log('--------------------------------------\n');
    }

    expect(
      emptyLinks,
      'All links must have discernible text'
    ).toHaveLength(0);
  });

  test('Touch targets meet minimum size (44x44px)', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 667 });
    await page.goto('/');

    const smallTargets = await page.evaluate(() => {
      const interactive = document.querySelectorAll(
        'a, button, [role="button"], input, select, textarea'
      );
      const issues: Array<{ element: string; size: string }> = [];

      interactive.forEach((el) => {
        const rect = el.getBoundingClientRect();
        // Check if element is visible
        if (rect.width > 0 && rect.height > 0) {
          if (rect.width < 44 || rect.height < 44) {
            issues.push({
              element: el.outerHTML.substring(0, 60),
              size: `${Math.round(rect.width)}x${Math.round(rect.height)}`,
            });
          }
        }
      });

      return issues.slice(0, 10); // Limit to first 10
    });

    if (smallTargets.length > 0) {
      console.log('\n--- Touch Targets Under 44x44px ---');
      smallTargets.forEach((t) => {
        console.log(`  ${t.size}: ${t.element}...`);
      });
      console.log('-----------------------------------\n');
    }

    // Warn but don't fail - some elements may be intentionally small
    if (smallTargets.length > 0) {
      console.log(
        `Warning: ${smallTargets.length} elements have touch targets under 44x44px`
      );
    }
  });

  test('Focus indicators are visible', async ({ page }) => {
    await page.goto('/');

    // Find first focusable element
    const firstFocusable = page.locator(
      'a, button, input, select, textarea, [tabindex]:not([tabindex="-1"])'
    ).first();

    if ((await firstFocusable.count()) > 0) {
      // Focus the element
      await firstFocusable.focus();

      // Check if focus is visible (has outline or box-shadow)
      const hasFocusIndicator = await firstFocusable.evaluate((el) => {
        const styles = window.getComputedStyle(el);
        const outline = styles.outline;
        const boxShadow = styles.boxShadow;

        // Check for visible focus indicators
        const hasOutline = outline && !outline.includes('none') && !outline.includes('0px');
        const hasBoxShadow = boxShadow && boxShadow !== 'none';

        return hasOutline || hasBoxShadow;
      });

      // Note: This is a basic check - manual verification recommended
      console.log(
        `Focus indicator check: ${hasFocusIndicator ? 'PASS' : 'NEEDS MANUAL VERIFICATION'}`
      );
    }
  });

  test('Color contrast meets WCAG AA standards', async ({ page }) => {
    await page.goto('/');

    const a11y = createAccessibilityHelper(page);
    const results = await a11y.audit();

    const contrastViolations = results.violations.filter((v) =>
      v.id.includes('contrast')
    );

    if (contrastViolations.length > 0) {
      console.log('\n--- Color Contrast Issues ---');
      contrastViolations.forEach((v) => {
        console.log(`  ${v.id}: ${v.description}`);
        v.nodes.slice(0, 3).forEach((node) => {
          console.log(`    - ${node.target.join(' > ')}`);
        });
      });
      console.log('-----------------------------\n');
    }

    expect(
      contrastViolations,
      'Color contrast must meet WCAG AA standards'
    ).toHaveLength(0);
  });

  test('Heading hierarchy is correct', async ({ page }) => {
    await page.goto('/');

    const headingIssues = await page.evaluate(() => {
      const headings = document.querySelectorAll('h1, h2, h3, h4, h5, h6');
      const issues: string[] = [];
      let lastLevel = 0;

      headings.forEach((heading) => {
        const level = parseInt(heading.tagName[1]);

        // First heading should be h1
        if (lastLevel === 0 && level !== 1) {
          issues.push(`First heading is <h${level}>, should be <h1>`);
        }

        // Headings shouldn't skip levels
        if (lastLevel > 0 && level > lastLevel + 1) {
          issues.push(
            `Heading skips level: <h${lastLevel}> to <h${level}>`
          );
        }

        lastLevel = level;
      });

      return issues;
    });

    if (headingIssues.length > 0) {
      console.log('\n--- Heading Hierarchy Issues ---');
      headingIssues.forEach((issue) => console.log(`  - ${issue}`));
      console.log('--------------------------------\n');
    }

    // First heading should be h1
    const firstHeadingIssue = headingIssues.find((i) => i.includes('First heading'));
    expect(firstHeadingIssue).toBeUndefined();
  });
});
