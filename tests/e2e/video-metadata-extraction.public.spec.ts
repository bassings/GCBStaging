import { test, expect } from '@playwright/test';

test.describe('Video Metadata Extraction', () => {
  test.beforeEach(async ({ request }) => {
    // Reset database state before each test
    const response = await request.delete('/wp-json/gcb-testing/v1/reset', {
      headers: { 'GCB-Test-Key': 'test-secret-key-local' }
    });
    expect(response.ok()).toBeTruthy();
  });

  test('should extract video ID from youtube.com/watch URL format', async ({ request }) => {
    // Create post with YouTube watch URL
    const createResponse = await request.post('/wp-json/gcb-testing/v1/create-post', {
      data: {
        title: 'Test Video - Watch URL',
        content: 'Check out this video: https://www.youtube.com/watch?v=dQw4w9WgXcQ Amazing content here!',
        status: 'publish'
      },
      headers: { 'GCB-Test-Key': 'test-secret-key-local' }
    });
    expect(createResponse.ok()).toBeTruthy();
    const post = await createResponse.json();

    // Verify video ID is extracted and stored
    expect(post.meta._gcb_video_id).toBe('dQw4w9WgXcQ');
  });

  test('should extract video ID from youtu.be short URL format', async ({ request }) => {
    // Create post with youtu.be short URL
    const createResponse = await request.post('/wp-json/gcb-testing/v1/create-post', {
      data: {
        title: 'Test Video - Short URL',
        content: 'Amazing video here: https://youtu.be/jNQXAC9IVRw Check it out!',
        status: 'publish'
      },
      headers: { 'GCB-Test-Key': 'test-secret-key-local' }
    });
    expect(createResponse.ok()).toBeTruthy();
    const post = await createResponse.json();

    // Verify video ID is extracted and stored
    expect(post.meta._gcb_video_id).toBe('jNQXAC9IVRw');
  });

  test('should extract video ID from embed URL format', async ({ request }) => {
    // Create post with YouTube embed URL (as text, not iframe since iframes are sanitized)
    const createResponse = await request.post('/wp-json/gcb-testing/v1/create-post', {
      data: {
        title: 'Test Video - Embed URL',
        content: 'Check out this embed: https://www.youtube.com/embed/M7lc1UVf-VE',
        status: 'publish'
      },
      headers: { 'GCB-Test-Key': 'test-secret-key-local' }
    });
    expect(createResponse.ok()).toBeTruthy();
    const post = await createResponse.json();

    // Verify video ID is extracted and stored
    expect(post.meta._gcb_video_id).toBe('M7lc1UVf-VE');
  });

  test('should set _gcb_has_video flag for video posts', async ({ request }) => {
    // Create video post
    const createResponse = await request.post('/wp-json/gcb-testing/v1/create-post', {
      data: {
        title: 'Video Post with Flag',
        content: 'Watch: https://www.youtube.com/watch?v=ScMzIvxBSi4 Great video!',
        status: 'publish'
      },
      headers: { 'GCB-Test-Key': 'test-secret-key-local' }
    });
    expect(createResponse.ok()).toBeTruthy();
    const post = await createResponse.json();

    // Verify has_video flag is set
    expect(post.meta._gcb_has_video).toBeTruthy();
    expect(post.meta._gcb_video_id).toBe('ScMzIvxBSi4');
  });

  test('should NOT set video metadata for standard posts without YouTube URLs', async ({ request }) => {
    // Create standard post with no video
    const createResponse = await request.post('/wp-json/gcb-testing/v1/create-post', {
      data: {
        title: 'Standard Post - No Video',
        content: 'This is a regular post about cars. No videos here, just text content discussing automotive trends.',
        status: 'publish'
      },
      headers: { 'GCB-Test-Key': 'test-secret-key-local' }
    });
    expect(createResponse.ok()).toBeTruthy();
    const post = await createResponse.json();

    // Verify NO video metadata is set
    expect(post.meta._gcb_video_id).toBeFalsy();
    expect(post.meta._gcb_has_video).toBeFalsy();
  });

  test('should display YouTube thumbnails in video rail with extracted video IDs', async ({ page, request }) => {
    // Create 3 video posts
    const videoIds = ['dQw4w9WgXcQ', 'jNQXAC9IVRw', 'M7lc1UVf-VE'];

    for (const videoId of videoIds) {
      await request.post('/wp-json/gcb-testing/v1/create-post', {
        data: {
          title: `Video Test ${videoId}`,
          content: `https://www.youtube.com/watch?v=${videoId}`,
          status: 'publish',
          meta: {
            _gcb_content_format: 'video-quick'
          }
        },
        headers: { 'GCB-Test-Key': 'test-secret-key-local' }
      });
    }

    // Visit homepage
    await page.goto('/');

    // Wait for video rail to load
    await page.waitForSelector('.gcb-video-rail__container');

    // Check for YouTube thumbnail images
    const thumbnails = await page.locator('.gcb-video-card img[src*="youtube"], .gcb-video-card img[src*="ytimg"]').count();
    expect(thumbnails).toBeGreaterThan(0);

    // Verify at least one thumbnail has correct YouTube URL format
    const firstThumbnail = page.locator('.gcb-video-card img[src*="youtube"], .gcb-video-card img[src*="ytimg"]').first();
    const src = await firstThumbnail.getAttribute('src');
    expect(src).toMatch(/https?:\/\/(img\.youtube\.com\/vi\/[\w-]+\/maxresdefault\.jpg|.*ytimg\.com)/);
  });
});
