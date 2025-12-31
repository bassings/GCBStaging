import { test, expect } from '@playwright/test';

test.describe('GCB Content Intelligence - YouTube API Integration', () => {

  test('Fetches video metadata from YouTube API when video ID detected', async ({ request }) => {
    // Reset database
    await request.delete('/wp-json/gcb-testing/v1/reset', {
      headers: { 'GCB-Test-Key': 'test-secret-key-local' }
    });

    // Create post with YouTube URL (Rick Astley - Never Gonna Give You Up)
    const videoId = 'dQw4w9WgXcQ';
    const youtubeUrl = `https://www.youtube.com/watch?v=${videoId}`;

    const createResponse = await request.post('/wp-json/gcb-testing/v1/create-post', {
      data: {
        title: 'YouTube API Metadata Test',
        content: `<p>Check out this video:</p><p>${youtubeUrl}</p>`,
        status: 'publish'
      },
      headers: {
        'Content-Type': 'application/json',
        'GCB-Test-Key': 'test-secret-key-local'
      }
    });

    expect(createResponse.ok()).toBeTruthy();
    expect(createResponse.status()).toBe(201);

    const post = await createResponse.json();
    expect(post.id).toBeDefined();

    // Assert: Video ID was extracted
    expect(post.meta._gcb_video_id).toBe(videoId);

    // Assert: Video metadata was fetched from YouTube API
    expect(post.meta._gcb_video_metadata).toBeDefined();
    expect(post.meta._gcb_video_metadata).not.toBe('');

    // Metadata is returned as parsed object (not JSON string)
    const metadata = post.meta._gcb_video_metadata;

    // Assert: Required metadata fields are present
    expect(metadata.title).toBeDefined();
    expect(metadata.title).toContain('Never Gonna Give You Up'); // Known video title
    expect(metadata.duration).toBeDefined(); // ISO 8601 format (e.g., "PT3M33S")
    expect(metadata.uploadDate).toBeDefined(); // ISO 8601 date
    expect(metadata.thumbnailUrl).toBeDefined();
    expect(metadata.thumbnailUrl).toContain('ytimg.com'); // YouTube thumbnail domain

    // Assert: Content format is 'video'
    expect(post.meta._gcb_content_format).toBe('video');
  });

  test('Caches video metadata to avoid repeated API calls', async ({ request }) => {
    // Reset database
    await request.delete('/wp-json/gcb-testing/v1/reset', {
      headers: { 'GCB-Test-Key': 'test-secret-key-local' }
    });

    // Create post with YouTube URL
    const videoId = 'dQw4w9WgXcQ';
    const createResponse = await request.post('/wp-json/gcb-testing/v1/create-post', {
      data: {
        title: 'API Cache Test',
        content: `https://www.youtube.com/watch?v=${videoId}`,
        status: 'publish'
      },
      headers: {
        'Content-Type': 'application/json',
        'GCB-Test-Key': 'test-secret-key-local'
      }
    });

    const post = await createResponse.json();
    const originalMetadata = post.meta._gcb_video_metadata;
    expect(originalMetadata).toBeDefined();

    // Assert: Cache timestamp was recorded
    expect(post.meta._gcb_api_cache_time).toBeDefined();
    const cacheTime = parseInt(post.meta._gcb_api_cache_time);
    expect(cacheTime).toBeGreaterThan(0);

    // Trigger save_post again by creating another post with same video
    // (Cache should prevent re-fetching from API)
    const secondCreateResponse = await request.post(`/wp-json/gcb-testing/v1/create-post`, {
      data: {
        title: 'API Cache Test - Second Post',
        content: `https://www.youtube.com/watch?v=${videoId}`,
        status: 'publish'
      },
      headers: {
        'Content-Type': 'application/json',
        'GCB-Test-Key': 'test-secret-key-local'
      }
    });

    const secondPost = await secondCreateResponse.json();

    // Assert: Metadata is identical (deep equality)
    expect(secondPost.meta._gcb_video_metadata).toStrictEqual(originalMetadata);

    // Assert: Both posts have cache timestamps set
    expect(secondPost.meta._gcb_api_cache_time).toBeDefined();
    expect(parseInt(secondPost.meta._gcb_api_cache_time)).toBeGreaterThan(0);
  });

  test('Handles API failures gracefully', async ({ request }) => {
    // Reset database
    await request.delete('/wp-json/gcb-testing/v1/reset', {
      headers: { 'GCB-Test-Key': 'test-secret-key-local' }
    });

    // Create post with invalid video ID (will trigger API error)
    // Note: INVALID_ID is recognized by mock API as an error case
    const invalidVideoId = 'INVALID_ID_';
    const createResponse = await request.post('/wp-json/gcb-testing/v1/create-post', {
      data: {
        title: 'API Failure Test',
        content: `https://www.youtube.com/watch?v=${invalidVideoId}`,
        status: 'publish'
      },
      headers: {
        'Content-Type': 'application/json',
        'GCB-Test-Key': 'test-secret-key-local'
      }
    });

    expect(createResponse.ok()).toBeTruthy();
    const post = await createResponse.json();

    // Assert: Post was still created (graceful degradation)
    expect(post.id).toBeDefined();

    // Assert: Video ID was extracted (even though API will fail)
    expect(post.meta._gcb_video_id).toBe(invalidVideoId);

    // Assert: Content format is still 'video' (detection works even if API fails)
    expect(post.meta._gcb_content_format).toBe('video');

    // Assert: Metadata contains error flag (graceful degradation)
    expect(post.meta._gcb_video_metadata).toBeDefined();
    const metadata = post.meta._gcb_video_metadata;
    expect(metadata.error).toBeDefined();
  });

  test('Uses Avada shortcode video ID for API fetch after conversion', async ({ request }) => {
    // Reset database
    await request.delete('/wp-json/gcb-testing/v1/reset', {
      headers: { 'GCB-Test-Key': 'test-secret-key-local' }
    });

    // Create post with Avada shortcode (will be converted to embed block)
    const videoId = 'dQw4w9WgXcQ';
    const avadaShortcode = `[fusion_youtube id="${videoId}" width="600" height="350"]`;

    const createResponse = await request.post('/wp-json/gcb-testing/v1/create-post', {
      data: {
        title: 'Avada + API Test',
        content: `<p>Legacy shortcode:</p>${avadaShortcode}`,
        status: 'publish'
      },
      headers: {
        'Content-Type': 'application/json',
        'GCB-Test-Key': 'test-secret-key-local'
      }
    });

    const post = await createResponse.json();

    // Assert: Shortcode was converted
    expect(post.meta._gcb_shortcode_converted).toBeDefined();

    // Assert: Video ID was extracted (from converted embed block)
    expect(post.meta._gcb_video_id).toBe(videoId);

    // Assert: Metadata was fetched from YouTube API
    expect(post.meta._gcb_video_metadata).toBeDefined();
    const metadata = post.meta._gcb_video_metadata;
    expect(metadata.title).toContain('Never Gonna Give You Up');

    // Assert: Content format is 'video'
    expect(post.meta._gcb_content_format).toBe('video');
  });
});
