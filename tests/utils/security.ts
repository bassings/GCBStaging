import { Page, Response } from '@playwright/test';

/**
 * Required Security Headers and Valid Values
 */
export const REQUIRED_SECURITY_HEADERS: Record<string, string[]> = {
  'x-frame-options': ['DENY', 'SAMEORIGIN'],
  'x-content-type-options': ['nosniff'],
  'x-xss-protection': ['1; mode=block', '1'],
  'referrer-policy': [
    'no-referrer',
    'no-referrer-when-downgrade',
    'strict-origin',
    'strict-origin-when-cross-origin',
  ],
};

/**
 * Recommended (but not required) Security Headers
 */
export const RECOMMENDED_SECURITY_HEADERS = {
  'strict-transport-security': 'max-age=',
  'content-security-policy': true,
  'permissions-policy': true,
};

/**
 * Content Security Policy Directives
 */
export interface CSPDirectives {
  'default-src'?: string[];
  'script-src'?: string[];
  'style-src'?: string[];
  'img-src'?: string[];
  'connect-src'?: string[];
  'font-src'?: string[];
  'frame-src'?: string[];
  'frame-ancestors'?: string[];
  'form-action'?: string[];
  'base-uri'?: string[];
  'upgrade-insecure-requests'?: boolean;
}

/**
 * Security Header Check Result
 */
export interface HeaderCheckResult {
  passed: string[];
  failed: string[];
  missing: string[];
}

/**
 * CSP Safety Check Result
 */
export interface CSPSafetyResult {
  safe: boolean;
  warnings: string[];
}

/**
 * XSS Vulnerability Check Result
 */
export interface XSSCheckResult {
  vulnerable: boolean;
  issues: string[];
}

/**
 * Security Testing Helper
 *
 * Validates security headers, CSP directives, and checks for XSS vulnerabilities.
 *
 * Usage:
 * ```typescript
 * import { createSecurityHelper } from '@utils/security';
 *
 * test('Page has required security headers', async ({ page }) => {
 *   const security = createSecurityHelper(page);
 *   await security.navigateAndCapture('/');
 *   const { passed, failed, missing } = security.checkRequiredHeaders();
 *   expect(failed).toHaveLength(0);
 * });
 * ```
 */
export class SecurityHelper {
  private page: Page;
  private response: Response | null = null;

  constructor(page: Page) {
    this.page = page;
  }

  /**
   * Navigate and capture response headers
   * Uses waitUntil: 'commit' to avoid blocking on resources that never complete
   */
  async navigateAndCapture(url: string): Promise<Response> {
    const response = await this.page.goto(url, { waitUntil: 'commit' });
    if (!response) {
      throw new Error(`Failed to navigate to ${url}`);
    }
    this.response = response;
    return response;
  }

  /**
   * Get all security-related headers
   */
  getSecurityHeaders(): Record<string, string> {
    if (!this.response) {
      throw new Error('No response captured. Call navigateAndCapture first.');
    }

    const headers = this.response.headers();
    const securityHeaders: Record<string, string> = {};

    const relevantHeaders = [
      'x-frame-options',
      'x-content-type-options',
      'x-xss-protection',
      'referrer-policy',
      'strict-transport-security',
      'content-security-policy',
      'content-security-policy-report-only',
      'permissions-policy',
      'cross-origin-opener-policy',
      'cross-origin-embedder-policy',
      'cross-origin-resource-policy',
    ];

    relevantHeaders.forEach((header) => {
      if (headers[header]) {
        securityHeaders[header] = headers[header];
      }
    });

    return securityHeaders;
  }

  /**
   * Check required security headers
   */
  checkRequiredHeaders(): HeaderCheckResult {
    const headers = this.getSecurityHeaders();
    const passed: string[] = [];
    const failed: string[] = [];
    const missing: string[] = [];

    for (const [header, validValues] of Object.entries(
      REQUIRED_SECURITY_HEADERS
    )) {
      const value = headers[header]?.toLowerCase();

      if (!value) {
        missing.push(header);
      } else if (validValues.some((v) => value.includes(v.toLowerCase()))) {
        passed.push(header);
      } else {
        failed.push(
          `${header}: got "${value}", expected one of [${validValues.join(', ')}]`
        );
      }
    }

    return { passed, failed, missing };
  }

  /**
   * Parse Content Security Policy
   */
  parseCSP(): CSPDirectives | null {
    const headers = this.getSecurityHeaders();
    const cspHeader =
      headers['content-security-policy'] ||
      headers['content-security-policy-report-only'];

    if (!cspHeader) {
      return null;
    }

    const directives: CSPDirectives = {};

    cspHeader.split(';').forEach((directive) => {
      const parts = directive.trim().split(/\s+/);
      const name = parts[0] as keyof CSPDirectives;
      const values = parts.slice(1);

      if (name === 'upgrade-insecure-requests') {
        directives[name] = true;
      } else if (values.length > 0) {
        (directives as Record<string, string[]>)[name] = values;
      }
    });

    return directives;
  }

  /**
   * Check for unsafe CSP directives
   */
  checkCSPSafety(): CSPSafetyResult {
    const csp = this.parseCSP();
    const warnings: string[] = [];

    if (!csp) {
      warnings.push('No Content-Security-Policy header found');
      return { safe: false, warnings };
    }

    // Check for unsafe-inline in script-src
    if (csp['script-src']?.includes("'unsafe-inline'")) {
      warnings.push("script-src contains 'unsafe-inline' - vulnerable to XSS");
    }

    // Check for unsafe-eval in script-src
    if (csp['script-src']?.includes("'unsafe-eval'")) {
      warnings.push(
        "script-src contains 'unsafe-eval' - vulnerable to code injection"
      );
    }

    // Check for wildcard in default-src
    if (csp['default-src']?.includes('*')) {
      warnings.push("default-src contains '*' - overly permissive");
    }

    // Check for missing frame-ancestors (clickjacking protection)
    if (!csp['frame-ancestors']) {
      warnings.push('Missing frame-ancestors directive - clickjacking possible');
    }

    return { safe: warnings.length === 0, warnings };
  }

  /**
   * Check for common XSS vulnerabilities in page content
   */
  async checkXSSVulnerabilities(): Promise<XSSCheckResult> {
    const issues: string[] = [];

    // Wait for DOM to be ready before evaluating
    await this.page.waitForSelector('body', { timeout: 10000 });

    // Check for inline event handlers
    const inlineHandlers = await this.page.evaluate(() => {
      const elements = document.querySelectorAll(
        '[onclick], [onload], [onerror], [onmouseover], [onfocus], [onblur]'
      );
      return elements.length;
    });

    if (inlineHandlers > 0) {
      issues.push(`Found ${inlineHandlers} elements with inline event handlers`);
    }

    // Check for javascript: URLs
    const jsUrls = await this.page.evaluate(() => {
      const links = document.querySelectorAll('a[href^="javascript:"]');
      return links.length;
    });

    if (jsUrls > 0) {
      issues.push(`Found ${jsUrls} links with javascript: URLs`);
    }

    // Check for data: URLs in scripts
    const dataScripts = await this.page.evaluate(() => {
      const scripts = document.querySelectorAll('script[src^="data:"]');
      return scripts.length;
    });

    if (dataScripts > 0) {
      issues.push(`Found ${dataScripts} scripts with data: URLs`);
    }

    // Check for innerHTML usage in visible scripts
    const unsafeAssignments = await this.page.evaluate(() => {
      const scripts = document.querySelectorAll('script:not([src])');
      let count = 0;
      scripts.forEach((script) => {
        const content = script.textContent || '';
        if (
          content.includes('innerHTML') ||
          content.includes('outerHTML') ||
          content.includes('document.write')
        ) {
          count++;
        }
      });
      return count;
    });

    if (unsafeAssignments > 0) {
      issues.push(
        `Found ${unsafeAssignments} scripts with potentially unsafe DOM manipulation`
      );
    }

    return { vulnerable: issues.length > 0, issues };
  }

  /**
   * Log security summary to console
   */
  logSecuritySummary(): void {
    const headers = this.getSecurityHeaders();
    const { passed, failed, missing } = this.checkRequiredHeaders();

    console.log('\n=== Security Headers Summary ===');
    console.log(`  PASSED: ${passed.join(', ') || 'none'}`);
    console.log(`  FAILED: ${failed.join(', ') || 'none'}`);
    console.log(`  MISSING: ${missing.join(', ') || 'none'}`);
    console.log('================================\n');
  }
}

/**
 * Factory function for easy use in tests
 */
export function createSecurityHelper(page: Page): SecurityHelper {
  return new SecurityHelper(page);
}
