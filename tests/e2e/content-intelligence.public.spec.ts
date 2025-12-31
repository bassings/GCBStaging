import { test, expect } from '@playwright/test';

test.describe('GCB Content Intelligence - Video Detection', () => {

  test('Detects YouTube URL and assigns video taxonomy', async ({ request }) => {
    // Reset database
    await request.delete('/wp-json/gcb-testing/v1/reset', {
      headers: { 'GCB-Test-Key': 'test-secret-key-local' }
    });

    // Create post with YouTube URL via test endpoint (no authentication required)
    const videoUrl = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';

    const createResponse = await request.post('/wp-json/gcb-testing/v1/create-post', {
      data: {
        title: 'Video Post Test',
        content: `<p>${videoUrl}</p>`,
        status: 'publish'
      },
      headers: {
        'Content-Type': 'application/json',
        'GCB-Test-Key': 'test-secret-key-local'
      }
    });

    // Verify post creation succeeded
    expect(createResponse.ok()).toBeTruthy();
    expect(createResponse.status()).toBe(201);

    const post = await createResponse.json();
    expect(post.id).toBeDefined();

    // Assert: taxonomy assigned (WordPress REST API returns array of term IDs)
    expect(post.content_format).toBeDefined();
    expect(Array.isArray(post.content_format)).toBeTruthy();
    expect(post.content_format.length).toBeGreaterThan(0);

    // Assert: video ID extracted and stored in meta
    expect(post.meta._gcb_video_id).toBe('dQw4w9WgXcQ');
    expect(post.meta._gcb_content_format).toBe('video');
  });
});
