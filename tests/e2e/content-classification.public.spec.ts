import { test, expect } from '@playwright/test';

test.describe('GCB Content Intelligence - Auto Classification', () => {

  test.beforeEach(async ({ request }) => {
    // Reset database before each test
    await request.delete('/wp-json/gcb-testing/v1/reset', {
      headers: { 'GCB-Test-Key': 'test-secret-key-local' }
    });
  });

  test('Assigns video-quick to posts with YouTube URL and < 300 words', async ({ request }) => {
    // Create post with YouTube URL and short content (< 300 words)
    const response = await request.post('/wp-json/gcb-testing/v1/create-post', {
      data: {
        title: 'Short Video Post',
        content: `<p>Check out this quick video review:</p>
                  <p>https://www.youtube.com/watch?v=dQw4w9WgXcQ</p>
                  <p>This is a short post with minimal text content.</p>`,
        status: 'publish'
      },
      headers: {
        'Content-Type': 'application/json',
        'GCB-Test-Key': 'test-secret-key-local'
      }
    });

    const post = await response.json();
    expect(response.ok()).toBeTruthy();

    // Fetch post to verify taxonomy assignment
    const postResponse = await request.get(`/wp-json/wp/v2/posts/${post.id}`);
    const postData = await postResponse.json();

    // Assert: content_format taxonomy exists
    expect(postData.content_format).toBeDefined();
    expect(Array.isArray(postData.content_format)).toBeTruthy();

    // Assert: Assigned to video-quick term
    const termsResponse = await request.get(`/wp-json/wp/v2/content_format`);
    const terms = await termsResponse.json();
    const videoQuickTerm = terms.find((t: { slug: string }) => t.slug === 'video-quick');

    expect(postData.content_format).toContain(videoQuickTerm.id);
  });

  test('Assigns video-feature to posts with YouTube URL and > 300 words', async ({ request }) => {
    // Create long content (> 300 words)
    const longContent = '<p>' + 'word '.repeat(350) + '</p>';

    const response = await request.post('/wp-json/gcb-testing/v1/create-post', {
      data: {
        title: 'Feature Video Post',
        content: `<p>https://www.youtube.com/watch?v=dQw4w9WgXcQ</p>${longContent}`,
        status: 'publish'
      },
      headers: {
        'Content-Type': 'application/json',
        'GCB-Test-Key': 'test-secret-key-local'
      }
    });

    const post = await response.json();
    const postResponse = await request.get(`/wp-json/wp/v2/posts/${post.id}`);
    const postData = await postResponse.json();

    // Get video-feature term
    const termsResponse = await request.get(`/wp-json/wp/v2/content_format`);
    const terms = await termsResponse.json();
    const videoFeatureTerm = terms.find((t: { slug: string }) => t.slug === 'video-feature');

    expect(postData.content_format).toContain(videoFeatureTerm.id);
  });

  test('Assigns standard to posts without video content', async ({ request }) => {
    const response = await request.post('/wp-json/gcb-testing/v1/create-post', {
      data: {
        title: 'Standard Text Post',
        content: '<p>This is a standard text post with no video content. Just regular automotive journalism.</p>',
        status: 'publish'
      },
      headers: {
        'Content-Type': 'application/json',
        'GCB-Test-Key': 'test-secret-key-local'
      }
    });

    const post = await response.json();
    const postResponse = await request.get(`/wp-json/wp/v2/posts/${post.id}`);
    const postData = await postResponse.json();

    const termsResponse = await request.get(`/wp-json/wp/v2/content_format`);
    const terms = await termsResponse.json();
    const standardTerm = terms.find((t: { slug: string }) => t.slug === 'standard');

    expect(postData.content_format).toContain(standardTerm.id);
  });

  test('Detects YouTube URLs in core/embed blocks', async ({ request }) => {
    const response = await request.post('/wp-json/gcb-testing/v1/create-post', {
      data: {
        title: 'Embed Block Video',
        content: `<!-- wp:embed {"url":"https://www.youtube.com/watch?v=dQw4w9WgXcQ","type":"video","providerNameSlug":"youtube"} -->
                  <figure class="wp-block-embed is-type-video is-provider-youtube">
                    <div class="wp-block-embed__wrapper">
                      https://www.youtube.com/watch?v=dQw4w9WgXcQ
                    </div>
                  </figure>
                  <!-- /wp:embed -->
                  <p>Short description here.</p>`,
        status: 'publish'
      },
      headers: {
        'Content-Type': 'application/json',
        'GCB-Test-Key': 'test-secret-key-local'
      }
    });

    const post = await response.json();
    const postResponse = await request.get(`/wp-json/wp/v2/posts/${post.id}`);
    const postData = await postResponse.json();

    const termsResponse = await request.get(`/wp-json/wp/v2/content_format`);
    const terms = await termsResponse.json();
    const videoQuickTerm = terms.find((t: { slug: string }) => t.slug === 'video-quick');

    expect(postData.content_format).toContain(videoQuickTerm.id);
  });

  test('WP-CLI command classifies all existing posts', async ({ request }) => {
    // Create test posts
    await request.post('/wp-json/gcb-testing/v1/create-post', {
      data: {
        title: 'Old Video Post',
        content: '<p>https://www.youtube.com/watch?v=VIDEO1</p><p>Short content.</p>',
        status: 'publish'
      },
      headers: {
        'Content-Type': 'application/json',
        'GCB-Test-Key': 'test-secret-key-local'
      }
    });

    await request.post('/wp-json/gcb-testing/v1/create-post', {
      data: {
        title: 'Old Standard Post',
        content: '<p>No video here, just text content about cars.</p>',
        status: 'publish'
      },
      headers: {
        'Content-Type': 'application/json',
        'GCB-Test-Key': 'test-secret-key-local'
      }
    });

    // Note: CLI command testing requires WP-CLI to be available
    // This test verifies the REST endpoint exists for triggering classification
    const classifyResponse = await request.post('/wp-json/gcb-content-intelligence/v1/classify-all', {
      headers: {
        'GCB-Test-Key': 'test-secret-key-local'
      }
    });

    expect(classifyResponse.ok()).toBeTruthy();
    const result = await classifyResponse.json();
    expect(result.success).toBeTruthy();
    expect(result.classified).toBeGreaterThanOrEqual(2);
  });
});
