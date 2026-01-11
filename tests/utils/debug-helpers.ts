import { Page } from '@playwright/test';

/**
 * Debug Helpers for Interactive Development
 *
 * Provides utilities for visual debugging during test development.
 *
 * Usage:
 * ```typescript
 * import { createDebugHelpers } from '@utils/debug-helpers';
 *
 * test('Debug example', async ({ page }) => {
 *   const debug = createDebugHelpers(page);
 *   await page.goto('/');
 *   await debug.highlight('.my-element');
 *   await debug.logState();
 *   await debug.pause('Checking layout');
 * });
 * ```
 */
export class DebugHelpers {
  private page: Page;

  constructor(page: Page) {
    this.page = page;
  }

  /**
   * Pause execution and open browser inspector
   * Usage: await debug.pause('Checking layout');
   */
  async pause(reason?: string): Promise<void> {
    if (reason) {
      console.log(`\n>>> DEBUG PAUSE: ${reason}`);
    }
    await this.page.pause();
  }

  /**
   * Highlight an element on page (useful for visual debugging)
   */
  async highlight(
    selector: string,
    color: string = '#CCFF00'
  ): Promise<boolean> {
    const highlighted = await this.page.evaluate(
      ({ sel, col }) => {
        const element = document.querySelector(sel);
        if (element) {
          (element as HTMLElement).style.outline = `3px solid ${col}`;
          (element as HTMLElement).style.outlineOffset = '2px';
          return true;
        }
        return false;
      },
      { sel: selector, col: color }
    );

    if (highlighted) {
      console.log(`Highlighted: ${selector} (${color})`);
    } else {
      console.log(`Element not found: ${selector}`);
    }

    return highlighted;
  }

  /**
   * Highlight multiple elements
   */
  async highlightAll(
    selector: string,
    color: string = '#CCFF00'
  ): Promise<number> {
    const count = await this.page.evaluate(
      ({ sel, col }) => {
        const elements = document.querySelectorAll(sel);
        elements.forEach((element) => {
          (element as HTMLElement).style.outline = `3px solid ${col}`;
          (element as HTMLElement).style.outlineOffset = '2px';
        });
        return elements.length;
      },
      { sel: selector, col: color }
    );

    console.log(`Highlighted ${count} elements matching: ${selector}`);
    return count;
  }

  /**
   * Clear all highlights
   */
  async clearHighlights(): Promise<void> {
    await this.page.evaluate(() => {
      document.querySelectorAll('*').forEach((element) => {
        (element as HTMLElement).style.outline = '';
        (element as HTMLElement).style.outlineOffset = '';
      });
    });
    console.log('Cleared all highlights');
  }

  /**
   * Log page state for debugging
   */
  async logState(): Promise<void> {
    const url = this.page.url();
    const title = await this.page.title();
    const viewportSize = this.page.viewportSize();

    console.log('\n>>> Page State:');
    console.log(`    URL: ${url}`);
    console.log(`    Title: ${title}`);
    console.log(`    Viewport: ${viewportSize?.width}x${viewportSize?.height}`);
  }

  /**
   * Log element info
   */
  async logElement(selector: string): Promise<void> {
    const info = await this.page.evaluate((sel) => {
      const element = document.querySelector(sel);
      if (!element) return null;

      const rect = element.getBoundingClientRect();
      const styles = window.getComputedStyle(element);

      return {
        tagName: element.tagName,
        id: element.id,
        classes: element.className,
        rect: {
          x: Math.round(rect.x),
          y: Math.round(rect.y),
          width: Math.round(rect.width),
          height: Math.round(rect.height),
        },
        display: styles.display,
        visibility: styles.visibility,
        opacity: styles.opacity,
      };
    }, selector);

    if (info) {
      console.log(`\n>>> Element Info: ${selector}`);
      console.log(`    Tag: ${info.tagName}`);
      console.log(`    ID: ${info.id || '(none)'}`);
      console.log(`    Classes: ${info.classes || '(none)'}`);
      console.log(
        `    Position: ${info.rect.x}, ${info.rect.y}`
      );
      console.log(
        `    Size: ${info.rect.width}x${info.rect.height}`
      );
      console.log(
        `    Display: ${info.display}, Visibility: ${info.visibility}, Opacity: ${info.opacity}`
      );
    } else {
      console.log(`\n>>> Element not found: ${selector}`);
    }
  }

  /**
   * Take a labeled screenshot during debugging
   */
  async screenshot(label: string): Promise<string> {
    const timestamp = Date.now();
    const path = `debug-screenshots/${label}-${timestamp}.png`;
    await this.page.screenshot({
      path,
      fullPage: true,
    });
    console.log(`>>> Screenshot saved: ${path}`);
    return path;
  }

  /**
   * Take a screenshot of a specific element
   */
  async screenshotElement(label: string, selector: string): Promise<string> {
    const timestamp = Date.now();
    const path = `debug-screenshots/${label}-${timestamp}.png`;
    const element = this.page.locator(selector);
    await element.screenshot({ path });
    console.log(`>>> Element screenshot saved: ${path}`);
    return path;
  }

  /**
   * Log all console messages from page
   */
  async enableConsoleLogging(): Promise<void> {
    this.page.on('console', (msg) => {
      const type = msg.type();
      const text = msg.text();
      console.log(`[${type.toUpperCase()}] ${text}`);
    });
    console.log('>>> Console logging enabled');
  }

  /**
   * Log network requests
   */
  async enableNetworkLogging(): Promise<void> {
    this.page.on('request', (request) => {
      console.log(`[REQ] ${request.method()} ${request.url()}`);
    });
    this.page.on('response', (response) => {
      console.log(`[RES] ${response.status()} ${response.url()}`);
    });
    console.log('>>> Network logging enabled');
  }

  /**
   * Wait and show countdown (useful for observing changes)
   */
  async waitWithCountdown(seconds: number): Promise<void> {
    console.log(`>>> Waiting ${seconds} seconds...`);
    for (let i = seconds; i > 0; i--) {
      console.log(`    ${i}...`);
      await this.page.waitForTimeout(1000);
    }
    console.log('>>> Done waiting');
  }

  /**
   * Scroll to element and highlight
   */
  async scrollToAndHighlight(
    selector: string,
    color: string = '#CCFF00'
  ): Promise<void> {
    await this.page.locator(selector).scrollIntoViewIfNeeded();
    await this.highlight(selector, color);
  }

  /**
   * Get element count
   */
  async count(selector: string): Promise<number> {
    const count = await this.page.locator(selector).count();
    console.log(`>>> Count of "${selector}": ${count}`);
    return count;
  }

  /**
   * Check if element exists
   */
  async exists(selector: string): Promise<boolean> {
    const count = await this.page.locator(selector).count();
    const exists = count > 0;
    console.log(`>>> "${selector}" exists: ${exists}`);
    return exists;
  }

  /**
   * Check if element is visible
   */
  async isVisible(selector: string): Promise<boolean> {
    const visible = await this.page.locator(selector).isVisible();
    console.log(`>>> "${selector}" visible: ${visible}`);
    return visible;
  }
}

/**
 * Factory function for easy use in tests
 */
export function createDebugHelpers(page: Page): DebugHelpers {
  return new DebugHelpers(page);
}
