/**
 * Culture Grid Pattern - E2E Tests (Public View)
 *
 * Tests the "Culture Grid" pattern implementation using TDD workflow.
 * This pattern displays text-only editorial content cards in a responsive grid.
 *
 * Specification:
 * - 4-column responsive grid (1 col mobile, 2 tablet, 4 desktop)
 * - Text-only cards (NO images) for high information density
 * - Category labels with acid lime color
 * - Large Playfair Display headlines
 * - Mono font excerpts in Brutal Grey
 * - Date only (no author displayed on cards)
 * - Border: 1px solid Brutal Border, hover → Acid Lime
 */

import { test, expect } from '@playwright/test';

test.describe('Culture Grid Pattern - Public View', () => {

  test.beforeEach(async ({ request, page }) => {
    // Reset database before each test
    await request.delete('http://localhost:8881/wp-json/gcb-testing/v1/reset', {
      headers: { 'GCB-Test-Key': 'test-secret-key-local' }
    });

    // Create multiple standard posts (not videos) for the grid
    for (let i = 1; i <= 8; i++) {
      await request.post('http://localhost:8881/wp-json/gcb-testing/v1/create-post', {
        headers: {
          'Content-Type': 'application/json',
          'GCB-Test-Key': 'test-secret-key-local'
        },
        data: {
          title: `Culture Post ${i}: Automotive Innovation`,
          content: `<p>This is an editorial piece about automotive culture and innovation. It explores the intersection of design, technology, and driving experience. The piece examines how modern cars are reshaping our relationship with mobility. We dive deep into the cultural significance of automotive evolution.</p>`,
          status: 'publish',
          category: i % 4 === 0 ? 'Technology' : i % 3 === 0 ? 'Safety' : i % 2 === 0 ? 'Lifestyle' : 'News'
        }
      });
    }

    // Navigate to homepage
    await page.goto('http://localhost:8881/');

    // Wait for page to be fully loaded
    await page.waitForLoadState('networkidle');
  });

  test('Culture Grid container exists on homepage', async ({ page }) => {
    // Assert the culture grid section exists
    const cultureGrid = page.locator('[data-pattern="culture-grid"]');
    await expect(cultureGrid).toBeVisible({ timeout: 10000 });

    // Assert section heading exists
    const heading = page.locator('[data-pattern="culture-grid"] h2, [data-pattern="culture-grid"] .culture-grid-title');
    await expect(heading).toBeVisible();

    // Assert heading text indicates editorial content
    const headingText = await heading.textContent();
    expect(headingText?.toLowerCase()).toContain('culture');
  });

  test('Displays 4-column grid on desktop viewport', async ({ page }) => {
    // Set viewport to desktop size
    await page.setViewportSize({ width: 1280, height: 720 });

    // Locate the grid container
    const gridContainer = page.locator('[data-pattern="culture-grid"] .culture-grid-container');
    await expect(gridContainer).toBeVisible();

    // Check CSS grid columns using computed styles
    const gridColumns = await gridContainer.evaluate((el) => {
      return window.getComputedStyle(el).gridTemplateColumns;
    });

    // Should have 4 equal columns on desktop
    const columnCount = gridColumns.split(' ').length;
    expect(columnCount).toBe(4);
  });

  test('Displays text-only cards with NO images', async ({ page }) => {
    // Get all culture grid cards
    const cards = page.locator('[data-pattern="culture-grid"] .culture-card');
    const cardCount = await cards.count();

    // Should have at least 4 cards
    expect(cardCount).toBeGreaterThanOrEqual(4);

    // Verify NO images are present in any card
    for (let i = 0; i < Math.min(cardCount, 8); i++) {
      const card = cards.nth(i);
      const images = card.locator('img');
      const imageCount = await images.count();
      expect(imageCount).toBe(0); // Text-only cards, no images
    }
  });

  test('Displays category labels with acid lime styling', async ({ page }) => {
    // Get first card's category label
    const firstCard = page.locator('[data-pattern="culture-grid"] .culture-card').first();
    const categoryLabel = firstCard.locator('.culture-card-category, .category-label');

    await expect(categoryLabel).toBeVisible();

    // Check category label color (should be acid lime #CCFF00)
    const categoryColor = await categoryLabel.evaluate((el) => {
      return window.getComputedStyle(el).color;
    });

    // Acid lime #CCFF00 converts to rgb(204, 255, 0)
    expect(categoryColor).toBe('rgb(204, 255, 0)');

    // Category text should be uppercase (Editorial Brutalism style)
    const categoryText = await categoryLabel.textContent();
    expect(categoryText).toBe(categoryText?.toUpperCase());
  });

  test('Headlines use Playfair Display font', async ({ page }) => {
    // Get first card's headline
    const firstCard = page.locator('[data-pattern="culture-grid"] .culture-card').first();
    const headline = firstCard.locator('h3, .culture-card-title');

    await expect(headline).toBeVisible();

    // Check font family
    const fontFamily = await headline.evaluate((el) => {
      return window.getComputedStyle(el).fontFamily;
    });

    // Should use Playfair Display (serif)
    expect(fontFamily.toLowerCase()).toContain('playfair');

    // Check font size (should be large, text-2xl equivalent)
    const fontSize = await headline.evaluate((el) => {
      return window.getComputedStyle(el).fontSize;
    });

    // text-2xl is typically 24px (1.5rem)
    const fontSizeNum = parseFloat(fontSize);
    expect(fontSizeNum).toBeGreaterThanOrEqual(20); // At least 20px
  });

  test('Excerpts use mono font with Brutal Grey color', async ({ page }) => {
    // Get first card's excerpt
    const firstCard = page.locator('[data-pattern="culture-grid"] .culture-card').first();
    const excerpt = firstCard.locator('.culture-card-excerpt, .excerpt');

    await expect(excerpt).toBeVisible();

    // Check font family (should be mono font like Space Mono or JetBrains Mono)
    const fontFamily = await excerpt.evaluate((el) => {
      return window.getComputedStyle(el).fontFamily;
    });

    // Should contain "mono" in font family name
    expect(fontFamily.toLowerCase()).toMatch(/mono|monospace/);

    // Check color (should be Brutal Grey #AAAAAA)
    const excerptColor = await excerpt.evaluate((el) => {
      return window.getComputedStyle(el).color;
    });

    // Brutal Grey #AAAAAA converts to rgb(170, 170, 170)
    expect(excerptColor).toBe('rgb(170, 170, 170)');
  });

  test('Displays date only (no author on cards)', async ({ page }) => {
    // Get first card's metadata
    const firstCard = page.locator('[data-pattern="culture-grid"] .culture-card').first();
    const metadata = firstCard.locator('.culture-card-meta, .card-meta');

    await expect(metadata).toBeVisible();

    // Get metadata text
    const metaText = await metadata.textContent();

    // Should contain date-like text (e.g., "January", "2025", or date format)
    expect(metaText).toMatch(/\d{4}|Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec/i);

    // Should NOT contain "by" or author name indicators
    expect(metaText?.toLowerCase()).not.toContain('by');

    // Should NOT have author link or author class
    const authorLink = firstCard.locator('a[href*="author"], .author, .by-line');
    await expect(authorLink).toHaveCount(0);
  });

  test('Cards have Brutal Border with Acid Lime hover effect', async ({ page }) => {
    // Get first card
    const firstCard = page.locator('[data-pattern="culture-grid"] .culture-card').first();

    await expect(firstCard).toBeVisible();

    // Check default border (should be 1px solid Brutal Border #333333)
    const defaultBorder = await firstCard.evaluate((el) => {
      return window.getComputedStyle(el).border;
    });

    // Should have 1px border
    expect(defaultBorder).toContain('1px');

    // Border color should be Brutal Border (rgb(51, 51, 51) = #333333)
    expect(defaultBorder).toMatch(/rgb\(51,\s*51,\s*51\)/);

    // Hover over the card
    await firstCard.hover();

    // Wait a moment for hover state
    await page.waitForTimeout(100);

    // Check hover border (should change to Acid Lime #CCFF00)
    const hoverBorder = await firstCard.evaluate((el) => {
      return window.getComputedStyle(el).borderColor;
    });

    // Acid Lime #CCFF00 converts to rgb(204, 255, 0)
    expect(hoverBorder).toBe('rgb(204, 255, 0)');
  });

  test('Responsive: 2 columns on tablet viewport', async ({ page }) => {
    // Set viewport to tablet size (768px)
    await page.setViewportSize({ width: 768, height: 1024 });

    // Locate the grid container
    const gridContainer = page.locator('[data-pattern="culture-grid"] .culture-grid-container');
    await expect(gridContainer).toBeVisible();

    // Check CSS grid columns
    const gridColumns = await gridContainer.evaluate((el) => {
      return window.getComputedStyle(el).gridTemplateColumns;
    });

    // Should have 2 columns on tablet
    const columnCount = gridColumns.split(' ').length;
    expect(columnCount).toBe(2);
  });

  test('Responsive: 1 column on mobile viewport', async ({ page }) => {
    // Set viewport to mobile size (375px)
    await page.setViewportSize({ width: 375, height: 667 });

    // Locate the grid container
    const gridContainer = page.locator('[data-pattern="culture-grid"] .culture-grid-container');
    await expect(gridContainer).toBeVisible();

    // Check CSS grid columns
    const gridColumns = await gridContainer.evaluate((el) => {
      return window.getComputedStyle(el).gridTemplateColumns;
    });

    // Should have 1 column on mobile
    const columnCount = gridColumns.split(' ').length;
    expect(columnCount).toBe(1);
  });

});
