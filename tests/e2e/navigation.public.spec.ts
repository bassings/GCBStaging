/**
 * Navigation Enhancements - E2E Tests (Public View)
 *
 * Tests the navigation system implementation using TDD workflow.
 * This includes sticky header, mobile menu, and accessibility features.
 *
 * Specification:
 * - Desktop: Sticky header, logo, nav links, terminal search button, shadow on scroll
 * - Mobile: Slide-out drawer (256px), overlay, hamburger toggle, body scroll lock
 * - Interactions: Close on link click, overlay click, ESC key
 * - Accessibility: ARIA labels, focus management, keyboard navigation
 */

import { test, expect } from '@playwright/test';

test.describe('Navigation - Desktop View', () => {

  test.beforeEach(async ({ page }) => {
    await page.goto('http://localhost:8881/');
    await page.waitForLoadState('networkidle');
  });

  test('Header is sticky and stays visible on scroll', async ({ page }) => {
    // Set desktop viewport
    await page.setViewportSize({ width: 1280, height: 720 });

    // Locate header
    const header = page.locator('header.site-header, header[role="banner"]');
    await expect(header).toBeVisible();

    // Check sticky positioning
    const position = await header.evaluate((el) => {
      return window.getComputedStyle(el).position;
    });
    expect(position).toBe('sticky');

    // Check z-index for layering
    const zIndex = await header.evaluate((el) => {
      return window.getComputedStyle(el).zIndex;
    });
    expect(parseInt(zIndex)).toBeGreaterThanOrEqual(50);

    // Scroll down the page
    await page.evaluate(() => window.scrollTo(0, 500));
    await page.waitForTimeout(200);

    // Header should still be visible at top of viewport
    await expect(header).toBeVisible();

    // Check that header is at the top of the viewport
    const headerBox = await header.boundingBox();
    expect(headerBox?.y).toBeLessThanOrEqual(10); // Should be near top
  });

  test('Header shows shadow on scroll', async ({ page }) => {
    // Set desktop viewport
    await page.setViewportSize({ width: 1280, height: 720 });

    const header = page.locator('header.site-header, header[role="banner"]');
    await expect(header).toBeVisible();

    // Scroll down to trigger shadow
    await page.evaluate(() => window.scrollTo(0, 100));
    await page.waitForTimeout(200);

    // Check for box shadow (should be 0 2px 0 #333333)
    const boxShadow = await header.evaluate((el) => {
      return window.getComputedStyle(el).boxShadow;
    });

    // Should have a shadow (not 'none')
    expect(boxShadow).not.toBe('none');

    // Shadow should contain Brutal Border color (#333 = rgb(51, 51, 51))
    expect(boxShadow).toContain('51, 51, 51');
  });

  test('Logo displays "GCB" in Playfair Display font', async ({ page }) => {
    // Set desktop viewport
    await page.setViewportSize({ width: 1280, height: 720 });

    // Locate logo text element
    const logoText = page.locator('.logo-text, .site-logo span');
    await expect(logoText).toBeVisible();

    // Check logo text
    const text = await logoText.textContent();
    expect(text?.trim()).toBe('GCB');

    // Check font family (should be Playfair Display)
    const fontFamily = await logoText.evaluate((el) => {
      return window.getComputedStyle(el).fontFamily;
    });
    expect(fontFamily.toLowerCase()).toContain('playfair');
  });

  test('Navigation links are present and accessible', async ({ page }) => {
    // Set desktop viewport
    await page.setViewportSize({ width: 1280, height: 720 });

    // Check for main navigation
    const nav = page.locator('nav.main-nav, nav[role="navigation"]');
    await expect(nav).toBeVisible();

    // Check for expected navigation links
    const expectedLinks = ['Car Reviews', 'Car News', 'Electric Cars', 'Brands'];

    for (const linkText of expectedLinks) {
      const link = nav.locator(`a:has-text("${linkText}")`);
      await expect(link).toBeVisible();
    }
  });

  test('Terminal-style search button is present with >_ prompt', async ({ page }) => {
    // Set desktop viewport
    await page.setViewportSize({ width: 1280, height: 720 });

    // Locate search button
    const searchBtn = page.locator('.search-toggle, button[aria-label*="Search"]');
    await expect(searchBtn).toBeVisible();

    // Check for terminal prompt symbol
    const btnText = await searchBtn.textContent();
    expect(btnText).toContain('>_');
  });

});

test.describe('Navigation - Mobile Menu', () => {

  test.beforeEach(async ({ page }) => {
    await page.goto('http://localhost:8881/');
    await page.waitForLoadState('networkidle');

    // Set mobile viewport
    await page.setViewportSize({ width: 375, height: 667 });
  });

  test('Mobile menu toggle button is visible on mobile', async ({ page }) => {
    // Locate hamburger menu button
    const menuToggle = page.locator('.menu-toggle, button[aria-label="Open Menu"]');
    await expect(menuToggle).toBeVisible();

    // Should have ARIA label for accessibility
    const ariaLabel = await menuToggle.getAttribute('aria-label');
    expect(ariaLabel).toBeTruthy();
  });

  test('Mobile menu opens when toggle is clicked', async ({ page }) => {
    // Locate menu toggle and menu drawer
    const menuToggle = page.locator('.menu-toggle, button[aria-label="Open Menu"]');
    const mobileMenu = page.locator('.mobile-menu, nav.mobile-nav, [data-menu="mobile"]');

    // Menu should be hidden initially
    await expect(mobileMenu).toBeHidden();

    // Click menu toggle
    await menuToggle.click();
    await page.waitForTimeout(300); // Wait for animation

    // Menu should now be visible
    await expect(mobileMenu).toBeVisible();

    // Check menu width (should be 256px)
    const menuWidth = await mobileMenu.evaluate((el) => {
      return window.getComputedStyle(el).width;
    });
    expect(parseInt(menuWidth)).toBe(256);
  });

  test('Mobile menu slides in from the left', async ({ page }) => {
    const menuToggle = page.locator('.menu-toggle, button[aria-label="Open Menu"]');
    const mobileMenu = page.locator('.mobile-menu, nav.mobile-nav, [data-menu="mobile"]');

    // Click to open menu
    await menuToggle.click();
    await page.waitForTimeout(300);

    // Menu should be visible on the left side (allow sub-pixel tolerance)
    const menuBox = await mobileMenu.boundingBox();
    expect(menuBox?.x).toBeLessThanOrEqual(1); // Should be at or near left edge (sub-pixel rendering)
  });

  test('Dark overlay appears when mobile menu is open', async ({ page }) => {
    const menuToggle = page.locator('.menu-toggle, button[aria-label="Open Menu"]');
    const overlay = page.locator('.menu-overlay, .mobile-menu-overlay, [data-overlay="mobile-menu"]');

    // Overlay should not be visible initially
    await expect(overlay).toBeHidden();

    // Open menu
    await menuToggle.click();
    await page.waitForTimeout(300);

    // Overlay should now be visible
    await expect(overlay).toBeVisible();

    // Overlay should have dark semi-transparent background
    const bgColor = await overlay.evaluate((el) => {
      return window.getComputedStyle(el).backgroundColor;
    });

    // Should be rgba with alpha channel (semi-transparent)
    expect(bgColor).toMatch(/rgba?\(/);
  });

  test('Body scroll is locked when mobile menu is open', async ({ page }) => {
    const menuToggle = page.locator('.menu-toggle, button[aria-label="Open Menu"]');

    // Open menu
    await menuToggle.click();
    await page.waitForTimeout(300);

    // Check body overflow style
    const bodyOverflow = await page.evaluate(() => {
      return window.getComputedStyle(document.body).overflow;
    });

    expect(bodyOverflow).toBe('hidden');
  });

  test('Mobile menu closes when toggle is clicked again', async ({ page }) => {
    const menuToggle = page.locator('.menu-toggle, button[aria-label="Open Menu"]');
    const mobileMenu = page.locator('.mobile-menu, nav.mobile-nav, [data-menu="mobile"]');

    // Open menu
    await menuToggle.click();
    await page.waitForTimeout(300);
    await expect(mobileMenu).toBeVisible();

    // Click toggle again to close
    await menuToggle.click();
    await page.waitForTimeout(300);

    // Menu should be hidden
    await expect(mobileMenu).toBeHidden();
  });

  test('Mobile menu closes when overlay is clicked', async ({ page }) => {
    const menuToggle = page.locator('.menu-toggle, button[aria-label="Open Menu"]');
    const mobileMenu = page.locator('.mobile-menu, nav.mobile-nav, [data-menu="mobile"]');
    const overlay = page.locator('.menu-overlay, .mobile-menu-overlay, [data-overlay="mobile-menu"]');

    // Open menu
    await menuToggle.click();
    await page.waitForTimeout(300);
    await expect(mobileMenu).toBeVisible();

    // Click overlay on the right side (where menu doesn't cover) - mobile viewport is 375px, menu is 256px
    await overlay.click({ position: { x: 320, y: 200 } });
    await page.waitForTimeout(300);

    // Menu should be closed
    await expect(mobileMenu).toBeHidden();
  });

  test('Mobile menu closes when ESC key is pressed', async ({ page }) => {
    const menuToggle = page.locator('.menu-toggle, button[aria-label="Open Menu"]');
    const mobileMenu = page.locator('.mobile-menu, nav.mobile-nav, [data-menu="mobile"]');

    // Open menu
    await menuToggle.click();
    await page.waitForTimeout(300);
    await expect(mobileMenu).toBeVisible();

    // Press ESC key
    await page.keyboard.press('Escape');
    await page.waitForTimeout(300);

    // Menu should be closed
    await expect(mobileMenu).toBeHidden();
  });

  test('Mobile menu closes when a link is clicked', async ({ page }) => {
    const menuToggle = page.locator('.menu-toggle, button[aria-label="Open Menu"]');
    const mobileMenu = page.locator('.mobile-menu, nav.mobile-nav, [data-menu="mobile"]');

    // Open menu
    await menuToggle.click();
    await page.waitForTimeout(300);
    await expect(mobileMenu).toBeVisible();

    // Click a navigation link
    const navLink = mobileMenu.locator('a').first();
    await navLink.click();
    await page.waitForTimeout(300);

    // Menu should be closed
    await expect(mobileMenu).toBeHidden();
  });

  test('Body scroll is restored when mobile menu closes', async ({ page }) => {
    const menuToggle = page.locator('.menu-toggle, button[aria-label="Open Menu"]');

    // Open menu
    await menuToggle.click();
    await page.waitForTimeout(300);

    // Close menu
    await menuToggle.click();
    await page.waitForTimeout(300);

    // Check body overflow is restored
    const bodyOverflow = await page.evaluate(() => {
      return window.getComputedStyle(document.body).overflow;
    });

    expect(bodyOverflow).not.toBe('hidden');
  });

  test('Menu toggle button has ARIA expanded attribute', async ({ page }) => {
    const menuToggle = page.locator('.menu-toggle, button[aria-label="Open Menu"]');

    // Initially should be false or not expanded
    let ariaExpanded = await menuToggle.getAttribute('aria-expanded');
    expect(ariaExpanded).toBe('false');

    // Open menu
    await menuToggle.click();
    await page.waitForTimeout(300);

    // Should be true when open
    ariaExpanded = await menuToggle.getAttribute('aria-expanded');
    expect(ariaExpanded).toBe('true');

    // Close menu
    await menuToggle.click();
    await page.waitForTimeout(300);

    // Should be false again
    ariaExpanded = await menuToggle.getAttribute('aria-expanded');
    expect(ariaExpanded).toBe('false');
  });

});
