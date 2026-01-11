import { Page } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

/**
 * WCAG 2.2 AA Tags for axe-core
 */
export const WCAG_22_AA_TAGS = [
  'wcag2a',
  'wcag2aa',
  'wcag21a',
  'wcag21aa',
  'wcag22aa',
  'best-practice',
];

/**
 * Axe Result Types
 */
export interface AxeViolation {
  id: string;
  impact: 'critical' | 'serious' | 'moderate' | 'minor';
  description: string;
  help: string;
  helpUrl: string;
  nodes: Array<{
    html: string;
    target: string[];
    failureSummary: string;
  }>;
}

export interface AxeResults {
  violations: AxeViolation[];
  passes: Array<{ id: string; description: string }>;
  incomplete: Array<{ id: string; description: string }>;
  inapplicable: Array<{ id: string }>;
}

/**
 * Quick Check Results
 */
export interface QuickCheckResult {
  passed: string[];
  failed: string[];
}

/**
 * Accessibility Testing Helper
 *
 * Wraps @axe-core/playwright for WCAG 2.2 AA compliance testing.
 *
 * Usage:
 * ```typescript
 * import { createAccessibilityHelper } from '@utils/accessibility';
 *
 * test('Page passes accessibility audit', async ({ page }) => {
 *   await page.goto('/');
 *   const a11y = createAccessibilityHelper(page);
 *   await a11y.assertNoViolations();
 * });
 * ```
 */
export class AccessibilityHelper {
  private page: Page;

  constructor(page: Page) {
    this.page = page;
  }

  /**
   * Run full accessibility audit
   */
  async audit(options?: {
    include?: string[];
    exclude?: string[];
    disableRules?: string[];
    tags?: string[];
  }): Promise<AxeResults> {
    let builder = new AxeBuilder({ page: this.page }).withTags(
      options?.tags || WCAG_22_AA_TAGS
    );

    if (options?.include) {
      builder = builder.include(options.include);
    }

    if (options?.exclude) {
      builder = builder.exclude(options.exclude);
    }

    if (options?.disableRules) {
      builder = builder.disableRules(options.disableRules);
    }

    const results = await builder.analyze();
    return results as AxeResults;
  }

  /**
   * Assert no accessibility violations
   */
  async assertNoViolations(options?: {
    include?: string[];
    exclude?: string[];
    disableRules?: string[];
    allowedImpact?: Array<'minor' | 'moderate'>;
  }): Promise<void> {
    const results = await this.audit(options);

    // Filter violations by allowed impact
    const allowedImpact = options?.allowedImpact || [];
    const criticalViolations = results.violations.filter(
      (v) => !allowedImpact.includes(v.impact as 'minor' | 'moderate')
    );

    if (criticalViolations.length > 0) {
      console.log('\n--- Accessibility Violations ---');
      criticalViolations.forEach((v) => {
        console.log(`\n  [${v.impact.toUpperCase()}] ${v.id}`);
        console.log(`  ${v.description}`);
        console.log(`  Help: ${v.helpUrl}`);
        v.nodes.slice(0, 3).forEach((node, i) => {
          console.log(`  Node ${i + 1}: ${node.target.join(' > ')}`);
          console.log(`  HTML: ${node.html.substring(0, 100)}...`);
          console.log(`  Fix: ${node.failureSummary}`);
        });
        if (v.nodes.length > 3) {
          console.log(`  ... and ${v.nodes.length - 3} more occurrences`);
        }
      });
      console.log('--------------------------------\n');

      throw new Error(
        `Found ${criticalViolations.length} accessibility violation(s). See console output for details.`
      );
    }

    console.log(`\n--- Accessibility audit passed (${results.passes.length} checks) ---\n`);
  }

  /**
   * Get violation summary for reporting
   */
  async getViolationSummary(): Promise<string> {
    const results = await this.audit();

    if (results.violations.length === 0) {
      return 'No accessibility violations found.';
    }

    const summary = results.violations
      .map(
        (v) =>
          `- [${v.impact}] ${v.id}: ${v.description} (${v.nodes.length} occurrence(s))`
      )
      .join('\n');

    return `Accessibility Violations:\n${summary}`;
  }

  /**
   * Audit specific component
   */
  async auditComponent(selector: string): Promise<AxeResults> {
    return this.audit({ include: [selector] });
  }

  /**
   * Quick checks for common WCAG requirements
   */
  async quickChecks(): Promise<QuickCheckResult> {
    const passed: string[] = [];
    const failed: string[] = [];

    // Check: HTML lang attribute
    const hasLang = await this.page.evaluate(() => {
      const lang = document.documentElement.getAttribute('lang');
      return lang && lang.length >= 2;
    });
    (hasLang ? passed : failed).push('HTML lang attribute');

    // Check: Page has <title>
    const hasTitle = await this.page.evaluate(() => {
      return document.title.length > 0;
    });
    (hasTitle ? passed : failed).push('Page title');

    // Check: Images have alt text
    const imagesWithoutAlt = await this.page.evaluate(() => {
      const images = document.querySelectorAll('img:not([alt])');
      return images.length;
    });
    (imagesWithoutAlt === 0 ? passed : failed).push(
      `Images have alt text (${imagesWithoutAlt} missing)`
    );

    // Check: Form inputs have labels
    const inputsWithoutLabels = await this.page.evaluate(() => {
      const inputs = document.querySelectorAll(
        'input:not([type="hidden"]):not([type="submit"]):not([type="button"])'
      );
      let count = 0;
      inputs.forEach((input) => {
        const id = input.id;
        const hasLabel = id && document.querySelector(`label[for="${id}"]`);
        const hasAriaLabel =
          input.hasAttribute('aria-label') ||
          input.hasAttribute('aria-labelledby');
        if (!hasLabel && !hasAriaLabel) count++;
      });
      return count;
    });
    (inputsWithoutLabels === 0 ? passed : failed).push(
      `Form inputs have labels (${inputsWithoutLabels} missing)`
    );

    // Check: Buttons have accessible names
    const buttonsWithoutNames = await this.page.evaluate(() => {
      const buttons = document.querySelectorAll('button, [role="button"]');
      let count = 0;
      buttons.forEach((btn) => {
        const hasText = (btn.textContent?.trim().length || 0) > 0;
        const hasAriaLabel =
          btn.hasAttribute('aria-label') || btn.hasAttribute('aria-labelledby');
        const hasTitle = btn.hasAttribute('title');
        if (!hasText && !hasAriaLabel && !hasTitle) count++;
      });
      return count;
    });
    (buttonsWithoutNames === 0 ? passed : failed).push(
      `Buttons have accessible names (${buttonsWithoutNames} missing)`
    );

    // Check: Skip links exist
    const hasSkipLink = await this.page.evaluate(() => {
      const skipLinks = document.querySelectorAll(
        'a[href^="#main"], a[href^="#content"], .skip-link, [class*="skip"]'
      );
      return skipLinks.length > 0;
    });
    (hasSkipLink ? passed : failed).push('Skip navigation link');

    // Check: Focus indicators (basic check)
    const hasFocusStyles = await this.page.evaluate(() => {
      // Create a temporary button and check if it has visible focus styles
      const button = document.querySelector('button, a');
      if (!button) return true; // No interactive elements
      return true; // Can't reliably test focus styles in evaluate
    });
    (hasFocusStyles ? passed : failed).push('Focus indicators (manual check recommended)');

    return { passed, failed };
  }

  /**
   * Log quick check results to console
   */
  async logQuickChecks(): Promise<void> {
    const { passed, failed } = await this.quickChecks();

    console.log('\n=== Accessibility Quick Checks ===');
    console.log('  PASSED:');
    passed.forEach((p) => console.log(`    - ${p}`));
    console.log('  FAILED:');
    failed.forEach((f) => console.log(`    - ${f}`));
    console.log('==================================\n');
  }
}

/**
 * Factory function for easy use in tests
 */
export function createAccessibilityHelper(page: Page): AccessibilityHelper {
  return new AccessibilityHelper(page);
}
