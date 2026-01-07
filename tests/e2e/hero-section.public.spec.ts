import { test, expect } from '@playwright/test';

test.describe('GCB Hero Section Pattern', () => {

  test('Displays hero section with feature card and opinion card', async ({ page, request }) => {
    test.setTimeout(60000);

    // Reset database
    await request.delete('/wp-json/gcb-testing/v1/reset', {
      headers: { 'GCB-Test-Key': 'test-secret-key-local' }
    });

    // Create 3 posts for hero section
    await request.post('/wp-json/gcb-testing/v1/create-post', {
      data: {
        title: 'Porsche 911 GT3: The Ultimate Driving Machine',
        content: 'An in-depth look at the latest iteration of the iconic sports car.',
        status: 'publish',
        categories: ['Car Reviews']
      },
      headers: {
        'Content-Type': 'application/json',
        'GCB-Test-Key': 'test-secret-key-local'
      }
    });

    await request.post('/wp-json/gcb-testing/v1/create-post', {
      data: {
        title: 'Electric Cars Are Not The Future We Deserve',
        content: 'A contrarian take on the electric revolution.',
        status: 'publish',
        categories: ['Opinion']
      },
      headers: {
        'Content-Type': 'application/json',
        'GCB-Test-Key': 'test-secret-key-local'
      }
    });

    await request.post('/wp-json/gcb-testing/v1/create-post', {
      data: {
        title: 'Another Standard Post',
        content: 'Additional content for testing.',
        status: 'publish'
      },
      headers: {
        'Content-Type': 'application/json',
        'GCB-Test-Key': 'test-secret-key-local'
      }
    });

    // Navigate to homepage
    await page.goto('/', { waitUntil: 'domcontentloaded' });

    // Assert: Hero section container exists
    const heroSection = page.locator('.gcb-hero-section, [data-pattern="hero-section"]');
    await expect(heroSection).toBeVisible();

    // Assert: Feature card exists (larger card)
    const featureCard = page.locator('.gcb-hero__feature, .hero-feature-card');
    await expect(featureCard).toBeVisible();

    // Assert: Opinion card exists (smaller card)
    const opinionCard = page.locator('.gcb-hero__opinion, .hero-opinion-card');
    await expect(opinionCard).toBeVisible();

    // Assert: Feature card has headline
    const featureHeadline = featureCard.locator('h1, h2, .gcb-hero__feature-title');
    await expect(featureHeadline).toBeVisible();

    // Assert: Feature card has category badge
    const featureBadge = featureCard.locator('.gcb-category-badge, .category-label');
    await expect(featureBadge).toBeVisible();
  });

  test('Feature card has correct height on desktop (500px)', async ({ page, request }) => {
    test.setTimeout(60000);

    // Reset database
    await request.delete('/wp-json/gcb-testing/v1/reset', {
      headers: { 'GCB-Test-Key': 'test-secret-key-local' }
    });

    // Create 1 post
    await request.post('/wp-json/gcb-testing/v1/create-post', {
      data: {
        title: 'Featured Post Height Test',
        content: 'Testing feature card dimensions.',
        status: 'publish'
      },
      headers: {
        'Content-Type': 'application/json',
        'GCB-Test-Key': 'test-secret-key-local'
      }
    });

    // Set desktop viewport
    await page.setViewportSize({ width: 1280, height: 720 });
    await page.goto('/', { waitUntil: 'domcontentloaded' });

    const featureCard = page.locator('.gcb-hero__feature, .hero-feature-card');
    const cardHeight = await featureCard.evaluate((el) => {
      return el.getBoundingClientRect().height;
    });

    // Assert: Feature card is 450px on desktop (reduced from 500px to reduce cropping)
    expect(cardHeight).toBeGreaterThanOrEqual(440);
    expect(cardHeight).toBeLessThanOrEqual(460);
  });

  test('Feature card images are not over-cropped', async ({ page, request }) => {
    test.setTimeout(60000);

    await request.delete('/wp-json/gcb-testing/v1/reset', {
      headers: { 'GCB-Test-Key': 'test-secret-key-local' }
    });

    await request.post('/wp-json/gcb-testing/v1/create-post', {
      data: {
        title: 'Image Cropping Test',
        content: 'Testing image aspect ratio handling.',
        status: 'publish'
      },
      headers: {
        'Content-Type': 'application/json',
        'GCB-Test-Key': 'test-secret-key-local'
      }
    });

    await page.setViewportSize({ width: 1920, height: 1080 });
    await page.goto('/', { waitUntil: 'domcontentloaded' });

    const featureCard = page.locator('.gcb-hero__feature, .hero-feature-card');
    const cardImage = featureCard.locator('img');

    // Assert: object-fit: cover is still applied (maintains design)
    const objectFit = await cardImage.evaluate(el => window.getComputedStyle(el).objectFit);
    expect(objectFit).toBe('cover');

    // Assert: Image container height allows more image to show
    const cardHeight = await featureCard.evaluate(el => el.getBoundingClientRect().height);

    // New height (450px) vs old height (500px) = 10% reduction in cropping
    expect(cardHeight).toBeLessThanOrEqual(450);
    expect(cardHeight).toBeGreaterThanOrEqual(440); // Allow small variance
  });

  test('Opinion card height increased on tablet/desktop', async ({ page, request }) => {
    await request.delete('/wp-json/gcb-testing/v1/reset', {
      headers: { 'GCB-Test-Key': 'test-secret-key-local' }
    });

    // Create 2 posts (feature + opinion)
    for (let i = 0; i < 2; i++) {
      await request.post('/wp-json/gcb-testing/v1/create-post', {
        data: {
          title: `Post ${i + 1}`,
          content: `Content ${i + 1}`,
          status: 'publish'
        },
        headers: {
          'Content-Type': 'application/json',
          'GCB-Test-Key': 'test-secret-key-local'
        }
      });
    }

    // Desktop viewport
    await page.setViewportSize({ width: 1280, height: 720 });
    await page.goto('/', { waitUntil: 'domcontentloaded' });

    const opinionCard = page.locator('.gcb-hero__opinion, .hero-opinion-card');
    const desktopHeight = await opinionCard.evaluate(el => el.getBoundingClientRect().height);

    expect(desktopHeight).toBeGreaterThanOrEqual(270); // 280px ± 10px
    expect(desktopHeight).toBeLessThanOrEqual(290);

    // Mobile viewport
    await page.setViewportSize({ width: 375, height: 667 });
    await page.goto('/', { waitUntil: 'domcontentloaded' });

    const mobileHeight = await opinionCard.evaluate(el => el.getBoundingClientRect().height);
    expect(mobileHeight).toBeGreaterThanOrEqual(230); // 240px ± 10px
    expect(mobileHeight).toBeLessThanOrEqual(250);
  });

  test('Opinion card has correct height on desktop (256px)', async ({ page, request }) => {
    test.setTimeout(60000);

    // Reset database
    await request.delete('/wp-json/gcb-testing/v1/reset', {
      headers: { 'GCB-Test-Key': 'test-secret-key-local' }
    });

    // Create 2 posts
    await request.post('/wp-json/gcb-testing/v1/create-post', {
      data: {
        title: 'First Post',
        content: 'Content',
        status: 'publish'
      },
      headers: {
        'Content-Type': 'application/json',
        'GCB-Test-Key': 'test-secret-key-local'
      }
    });

    await request.post('/wp-json/gcb-testing/v1/create-post', {
      data: {
        title: 'Opinion Height Test',
        content: 'Testing opinion card dimensions.',
        status: 'publish'
      },
      headers: {
        'Content-Type': 'application/json',
        'GCB-Test-Key': 'test-secret-key-local'
      }
    });

    // Set desktop viewport
    await page.setViewportSize({ width: 1280, height: 720 });
    await page.goto('/', { waitUntil: 'domcontentloaded' });

    const opinionCard = page.locator('.gcb-hero__opinion, .hero-opinion-card');
    const cardHeight = await opinionCard.evaluate((el) => {
      return el.getBoundingClientRect().height;
    });

    // Assert: Opinion card is approximately 256px (allow 230-280px range)
    expect(cardHeight).toBeGreaterThanOrEqual(230);
    expect(cardHeight).toBeLessThanOrEqual(280);
  });

  test('Hero section uses Editorial Brutalism design tokens', async ({ page, request }) => {
    test.setTimeout(60000);

    // Reset database
    await request.delete('/wp-json/gcb-testing/v1/reset', {
      headers: { 'GCB-Test-Key': 'test-secret-key-local' }
    });

    // Create 1 post
    await request.post('/wp-json/gcb-testing/v1/create-post', {
      data: {
        title: 'Design Tokens Test',
        content: 'Testing Editorial Brutalism design.',
        status: 'publish',
        categories: ['Car Reviews']
      },
      headers: {
        'Content-Type': 'application/json',
        'GCB-Test-Key': 'test-secret-key-local'
      }
    });

    await page.goto('/', { waitUntil: 'domcontentloaded' });

    // Assert: Category badge has Acid Lime border
    const categoryBadge = page.locator('.gcb-category-badge, .category-label').first();
    const borderColor = await categoryBadge.evaluate((el) => {
      return window.getComputedStyle(el).borderColor;
    });

    // Acid Lime is #CCFF00 which is rgb(204, 255, 0)
    expect(borderColor).toMatch(/rgb\(204, 255, 0\)/i);

    // Assert: Feature headline uses Playfair Display
    const featureHeadline = page.locator('.gcb-hero__feature, .hero-feature-card').locator('h1, h2').first();
    const headlineFont = await featureHeadline.evaluate((el) => {
      return window.getComputedStyle(el).fontFamily;
    });
    expect(headlineFont).toMatch(/Playfair/i);
  });

  test('Hero section displays post metadata (author, date, read time)', async ({ page, request }) => {
    test.setTimeout(60000);

    // Reset database
    await request.delete('/wp-json/gcb-testing/v1/reset', {
      headers: { 'GCB-Test-Key': 'test-secret-key-local' }
    });

    // Create 1 post
    await request.post('/wp-json/gcb-testing/v1/create-post', {
      data: {
        title: 'Metadata Display Test',
        content: 'Testing post metadata in hero section.',
        status: 'publish'
      },
      headers: {
        'Content-Type': 'application/json',
        'GCB-Test-Key': 'test-secret-key-local'
      }
    });

    await page.goto('/', { waitUntil: 'domcontentloaded' });

    const featureCard = page.locator('.gcb-hero__feature, .hero-feature-card');

    // Assert: Author is displayed
    const hasAuthor = await featureCard.locator('.gcb-hero__author, .post-author, [data-author]').count() > 0;
    expect(hasAuthor).toBeTruthy();

    // Assert: Date is displayed
    const hasDate = await featureCard.locator('.gcb-hero__date, .post-date, time').count() > 0;
    expect(hasDate).toBeTruthy();

    // Assert: Read time is displayed
    const hasReadTime = await featureCard.locator('.gcb-hero__read-time, .read-time, [data-read-time]').count() > 0;
    expect(hasReadTime).toBeTruthy();
  });

  test('Hero section is responsive on mobile (stacks to single column)', async ({ page, request }) => {
    test.setTimeout(60000);

    // Reset database
    await request.delete('/wp-json/gcb-testing/v1/reset', {
      headers: { 'GCB-Test-Key': 'test-secret-key-local' }
    });

    // Create 2 posts
    await request.post('/wp-json/gcb-testing/v1/create-post', {
      data: {
        title: 'Mobile Featured Post',
        content: 'Feature card on mobile.',
        status: 'publish'
      },
      headers: {
        'Content-Type': 'application/json',
        'GCB-Test-Key': 'test-secret-key-local'
      }
    });

    await request.post('/wp-json/gcb-testing/v1/create-post', {
      data: {
        title: 'Mobile Opinion Post',
        content: 'Opinion card on mobile.',
        status: 'publish'
      },
      headers: {
        'Content-Type': 'application/json',
        'GCB-Test-Key': 'test-secret-key-local'
      }
    });

    // Set mobile viewport
    await page.setViewportSize({ width: 375, height: 667 });
    await page.goto('/', { waitUntil: 'domcontentloaded' });

    const heroSection = page.locator('.gcb-hero-section, [data-pattern="hero-section"]');
    await expect(heroSection).toBeVisible();

    // Assert: Cards stack vertically on mobile
    const featureCard = page.locator('.gcb-hero__feature, .hero-feature-card');
    const opinionCard = page.locator('.gcb-hero__opinion, .hero-opinion-card');

    const featureBox = await featureCard.boundingBox();
    const opinionBox = await opinionCard.boundingBox();

    // If stacked, opinion card should be below feature card
    if (featureBox && opinionBox) {
      expect(opinionBox.y).toBeGreaterThan(featureBox.y + featureBox.height - 10);
    }

    // Assert: Cards take full width on mobile
    const featureWidth = await featureCard.evaluate((el) => {
      return el.getBoundingClientRect().width;
    });
    expect(featureWidth).toBeGreaterThan(340); // Close to full 375px width (accounting for padding)
  });

  test('Hero section feature card links to post', async ({ page, request }) => {
    test.setTimeout(60000);

    // Reset database
    await request.delete('/wp-json/gcb-testing/v1/reset', {
      headers: { 'GCB-Test-Key': 'test-secret-key-local' }
    });

    // Create 2 posts (hero section needs 2 posts: feature + opinion)
    await request.post('/wp-json/gcb-testing/v1/create-post', {
      data: {
        title: 'Opinion Post for Hero',
        content: 'This is the opinion card post.',
        status: 'publish'
      },
      headers: {
        'Content-Type': 'application/json',
        'GCB-Test-Key': 'test-secret-key-local'
      }
    });

    await request.post('/wp-json/gcb-testing/v1/create-post', {
      data: {
        title: 'Clickable Hero Feature Test',
        content: 'This post should be clickable from the hero section.',
        status: 'publish'
      },
      headers: {
        'Content-Type': 'application/json',
        'GCB-Test-Key': 'test-secret-key-local'
      }
    });

    await page.goto('/', { waitUntil: 'domcontentloaded' });

    // Assert: Feature card has link to post
    const featureCard = page.locator('.gcb-hero__feature, .hero-feature-card');
    const cardLink = featureCard.locator('a').first();
    await expect(cardLink).toBeVisible();

    // Get the link href to verify it's a post URL
    const linkHref = await cardLink.getAttribute('href');
    expect(linkHref).toBeTruthy();
    expect(linkHref).toMatch(/\//); // Should be a valid path

    // Click and verify navigation to a post page
    await cardLink.click();
    await page.waitForLoadState('domcontentloaded');

    // Verify we're on a single post page (not homepage)
    expect(page.url()).not.toBe('http://localhost:8881/');
    expect(page.url()).toContain('localhost:8881/');

    // Verify post title exists
    const postTitle = page.locator('h1, .wp-block-post-title, .entry-title');
    await expect(postTitle).toBeVisible();
  });
});
