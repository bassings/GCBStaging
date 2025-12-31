import { test, expect } from '@playwright/test';

test.describe('GCB Content Intelligence - Video Post Template', () => {

  test('Displays video post with YouTube embed', async ({ page, request }) => {
    // Reset database
    await request.delete('/wp-json/gcb-testing/v1/reset', {
      headers: { 'GCB-Test-Key': 'test-secret-key-local' }
    });

    // Create video post
    const videoId = 'dQw4w9WgXcQ';
    const createResponse = await request.post('/wp-json/gcb-testing/v1/create-post', {
      data: {
        title: 'Rick Astley - Never Gonna Give You Up',
        content: `<p>Classic 80s music video that became an internet meme.</p><p>https://www.youtube.com/watch?v=${videoId}</p>`,
        status: 'publish'
      },
      headers: {
        'Content-Type': 'application/json',
        'GCB-Test-Key': 'test-secret-key-local'
      }
    });

    expect(createResponse.ok()).toBeTruthy();
    const post = await createResponse.json();

    // Navigate to the video post
    await page.goto(post.link, { waitUntil: 'networkidle' });

    // Assert: YouTube embed iframe is present (Fusion Builder uses lazy loading)
    const youtubeEmbed = page.locator('iframe[data-orig-src*="youtube.com/embed"], iframe[src*="youtube.com/embed"]');
    await expect(youtubeEmbed).toBeVisible();

    // Assert: Embed URL contains correct video ID (check data-orig-src for lazy loading, fallback to src)
    const embedSrc = await youtubeEmbed.getAttribute('data-orig-src') ?? await youtubeEmbed.getAttribute('src');
    expect(embedSrc).toContain(videoId);

    // Assert: Post title is displayed
    const postTitle = page.locator('h1, .entry-title, .wp-block-post-title');
    await expect(postTitle).toBeVisible();
    await expect(postTitle).toContainText('Rick Astley');
  });

  test('Displays video metadata from YouTube API', async ({ page, request }) => {
    // Reset database
    await request.delete('/wp-json/gcb-testing/v1/reset', {
      headers: { 'GCB-Test-Key': 'test-secret-key-local' }
    });

    // Create video post
    const videoId = 'dQw4w9WgXcQ';
    const createResponse = await request.post('/wp-json/gcb-testing/v1/create-post', {
      data: {
        title: 'Video Metadata Display Test',
        content: `https://www.youtube.com/watch?v=${videoId}`,
        status: 'publish'
      },
      headers: {
        'Content-Type': 'application/json',
        'GCB-Test-Key': 'test-secret-key-local'
      }
    });

    const post = await createResponse.json();
    await page.goto(post.link, { waitUntil: 'networkidle' });

    // Assert: Video duration is displayed (from metadata)
    // Should show something like "3:33" or "Duration: PT3M33S"
    const pageContent = await page.textContent('body');

    // Check for duration in various formats
    const hasDuration =
      pageContent?.includes('3:33') ||
      pageContent?.includes('PT3M33S') ||
      pageContent?.includes('3 minutes');

    expect(hasDuration).toBeTruthy();
  });

  test('Uses Editorial Brutalism design tokens', async ({ page, request }) => {
    // Reset database
    await request.delete('/wp-json/gcb-testing/v1/reset', {
      headers: { 'GCB-Test-Key': 'test-secret-key-local' }
    });

    // Create video post
    const videoId = 'dQw4w9WgXcQ';
    const createResponse = await request.post('/wp-json/gcb-testing/v1/create-post', {
      data: {
        title: 'Design Tokens Test',
        content: `https://www.youtube.com/watch?v=${videoId}`,
        status: 'publish'
      },
      headers: {
        'Content-Type': 'application/json',
        'GCB-Test-Key': 'test-secret-key-local'
      }
    });

    const post = await createResponse.json();
    await page.goto(post.link, { waitUntil: 'networkidle' });

    // Assert: Background is Void Black (#050505)
    const bodyBg = await page.locator('body').evaluate((el) => {
      return window.getComputedStyle(el).backgroundColor;
    });

    // RGB for #050505 is rgb(5, 5, 5)
    expect(bodyBg).toBe('rgb(5, 5, 5)');

    // Assert: Text color is Hyper White (#FAFAFA)
    const bodyColor = await page.locator('body').evaluate((el) => {
      return window.getComputedStyle(el).color;
    });

    // RGB for #FAFAFA is rgb(250, 250, 250)
    expect(bodyColor).toBe('rgb(250, 250, 250)');

    // Assert: Heading uses Playfair Display font
    const heading = page.locator('h1, .wp-block-post-title').first();
    const headingFont = await heading.evaluate((el) => {
      return window.getComputedStyle(el).fontFamily;
    });

    expect(headingFont).toContain('Playfair Display');
  });

  test('Video post has distinct visual treatment from standard posts', async ({ page, request }) => {
    // Reset database
    await request.delete('/wp-json/gcb-testing/v1/reset', {
      headers: { 'GCB-Test-Key': 'test-secret-key-local' }
    });

    // Create video post
    const videoResponse = await request.post('/wp-json/gcb-testing/v1/create-post', {
      data: {
        title: 'Video Post Visual Test',
        content: 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        status: 'publish'
      },
      headers: {
        'Content-Type': 'application/json',
        'GCB-Test-Key': 'test-secret-key-local'
      }
    });

    const videoPost = await videoResponse.json();
    await page.goto(videoPost.link, { waitUntil: 'networkidle' });

    // Assert: Body has a class indicating video content
    // This allows for CSS targeting of video posts
    const bodyClasses = await page.locator('body').getAttribute('class');

    const hasVideoClass =
      bodyClasses?.includes('single-format-video') ||
      bodyClasses?.includes('content-format-video') ||
      bodyClasses?.includes('tax-content_format');

    expect(hasVideoClass).toBeTruthy();

    // Assert: YouTube embed container exists (Fusion Builder or WordPress native)
    const embedContainer = page.locator('.video-shortcode, .wp-block-embed, .wp-block-embed-youtube, iframe[data-orig-src*="youtube"], iframe[src*="youtube"]');
    await expect(embedContainer.first()).toBeVisible();
  });

  test('Video embed is responsive', async ({ page, request }) => {
    // Reset database
    await request.delete('/wp-json/gcb-testing/v1/reset', {
      headers: { 'GCB-Test-Key': 'test-secret-key-local' }
    });

    // Create video post
    const createResponse = await request.post('/wp-json/gcb-testing/v1/create-post', {
      data: {
        title: 'Responsive Embed Test',
        content: 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        status: 'publish'
      },
      headers: {
        'Content-Type': 'application/json',
        'GCB-Test-Key': 'test-secret-key-local'
      }
    });

    const post = await createResponse.json();
    await page.goto(post.link, { waitUntil: 'networkidle' });

    // Test desktop viewport (Fusion Builder uses lazy loading)
    await page.setViewportSize({ width: 1400, height: 900 });
    const youtubeEmbedDesktop = page.locator('iframe[data-orig-src*="youtube.com/embed"], iframe[src*="youtube.com/embed"]');
    await expect(youtubeEmbedDesktop).toBeVisible();

    // Test mobile viewport
    await page.setViewportSize({ width: 375, height: 667 });
    const youtubeEmbedMobile = page.locator('iframe[data-orig-src*="youtube.com/embed"], iframe[src*="youtube.com/embed"]');
    await expect(youtubeEmbedMobile).toBeVisible();

    // Assert: Embed doesn't overflow viewport on mobile
    const embedWidth = await youtubeEmbedMobile.evaluate((el) => {
      return el.getBoundingClientRect().width;
    });

    expect(embedWidth).toBeLessThanOrEqual(375);
  });
});
