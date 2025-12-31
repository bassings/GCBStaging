import { test, expect } from '@playwright/test';

/**
 * Helper function to find a specific schema type on the page
 * Handles multiple JSON-LD scripts (from other plugins) and arrays
 */
async function findSchemaByType(page: any, type: string): Promise<any> {
  const allSchemas = await page.locator('script[type="application/ld+json"]').all();

  for (const scriptEl of allSchemas) {
    const content = await scriptEl.textContent();
    if (content) {
      const parsed = JSON.parse(content);
      // Handle both single objects and arrays
      const schemas = Array.isArray(parsed) ? parsed : [parsed];
      const matchingSchema = schemas.find((s: any) => s['@type'] === type);
      if (matchingSchema) {
        return matchingSchema;
      }
    }
  }

  return null;
}

test.describe('GCB Content Intelligence - Schema.org Generation', () => {

  test('Generates VideoObject schema for video posts', async ({ page, request }) => {
    // Reset database
    await request.delete('/wp-json/gcb-testing/v1/reset', {
      headers: { 'GCB-Test-Key': 'test-secret-key-local' }
    });

    // Create post with YouTube URL
    const videoId = 'dQw4w9WgXcQ';
    const youtubeUrl = `https://www.youtube.com/watch?v=${videoId}`;

    const createResponse = await request.post('/wp-json/gcb-testing/v1/create-post', {
      data: {
        title: 'Video Schema Test',
        content: `<p>Check out this amazing video:</p><p>${youtubeUrl}</p>`,
        status: 'publish'
      },
      headers: {
        'Content-Type': 'application/json',
        'GCB-Test-Key': 'test-secret-key-local'
      }
    });

    expect(createResponse.ok()).toBeTruthy();
    const post = await createResponse.json();

    // Navigate to the post page
    await page.goto(post.link, { waitUntil: 'networkidle' });

    // Find the VideoObject schema (may be among multiple schemas from other plugins)
    const schema = await findSchemaByType(page, 'VideoObject');

    expect(schema).not.toBeNull();
    expect(schema).toBeDefined();

    // Assert: VideoObject schema structure
    expect(schema['@context']).toBe('https://schema.org');
    expect(schema['@type']).toBe('VideoObject');

    // Assert: Required VideoObject properties
    // Note: Uses video metadata title (from YouTube API), not post title
    expect(schema.name).toContain('Never Gonna Give You Up');
    expect(schema.description).toBeDefined();
    expect(schema.thumbnailUrl).toContain('ytimg.com'); // YouTube thumbnail
    expect(schema.uploadDate).toBeDefined(); // ISO 8601 date
    expect(schema.duration).toBe('PT3M33S'); // ISO 8601 duration
    expect(schema.embedUrl).toBe(`https://www.youtube.com/embed/${videoId}`);

    // Assert: Publisher information
    expect(schema.publisher).toBeDefined();
    expect(schema.publisher['@type']).toBe('Organization');
    expect(schema.publisher.name).toBeDefined();
  });

  test('Generates NewsArticle schema for standard posts', async ({ page, request }) => {
    // Reset database
    await request.delete('/wp-json/gcb-testing/v1/reset', {
      headers: { 'GCB-Test-Key': 'test-secret-key-local' }
    });

    // Create standard post (no video)
    const createResponse = await request.post('/wp-json/gcb-testing/v1/create-post', {
      data: {
        title: 'Standard News Article Test',
        content: '<p>This is a standard news article about the automotive industry.</p><p>It contains no videos, just text content.</p>',
        status: 'publish'
      },
      headers: {
        'Content-Type': 'application/json',
        'GCB-Test-Key': 'test-secret-key-local'
      }
    });

    expect(createResponse.ok()).toBeTruthy();
    const post = await createResponse.json();

    // Navigate to the post page
    await page.goto(post.link, { waitUntil: 'networkidle' });

    // Find the NewsArticle schema (may be among multiple schemas from other plugins)
    const schema = await findSchemaByType(page, 'NewsArticle');

    expect(schema).not.toBeNull();
    expect(schema).toBeDefined();

    // Assert: NewsArticle schema structure
    expect(schema['@context']).toBe('https://schema.org');
    expect(schema['@type']).toBe('NewsArticle');

    // Assert: Required NewsArticle properties
    expect(schema.headline).toBe('Standard News Article Test');
    expect(schema.articleBody).toBeDefined();
    expect(schema.datePublished).toBeDefined(); // ISO 8601 date
    expect(schema.dateModified).toBeDefined(); // ISO 8601 date

    // Assert: Author information
    expect(schema.author).toBeDefined();
    expect(schema.author['@type']).toBe('Person');
    expect(schema.author.name).toBeDefined();

    // Assert: Publisher information
    expect(schema.publisher).toBeDefined();
    expect(schema.publisher['@type']).toBe('Organization');
    expect(schema.publisher.name).toBeDefined();
  });

  test('Uses video metadata in VideoObject schema', async ({ page, request }) => {
    // Reset database
    await request.delete('/wp-json/gcb-testing/v1/reset', {
      headers: { 'GCB-Test-Key': 'test-secret-key-local' }
    });

    // Create post with YouTube URL (will fetch mock metadata)
    const videoId = 'dQw4w9WgXcQ';
    const createResponse = await request.post('/wp-json/gcb-testing/v1/create-post', {
      data: {
        title: 'Rick Astley Test',
        content: `https://www.youtube.com/watch?v=${videoId}`,
        status: 'publish'
      },
      headers: {
        'Content-Type': 'application/json',
        'GCB-Test-Key': 'test-secret-key-local'
      }
    });

    const post = await createResponse.json();

    // Navigate to the post page
    await page.goto(post.link, { waitUntil: 'networkidle' });

    // Find the VideoObject schema
    const schema = await findSchemaByType(page, 'VideoObject');

    expect(schema).not.toBeNull();
    expect(schema).toBeDefined();

    // Assert: Metadata from YouTube API is used
    expect(schema.name).toContain('Never Gonna Give You Up'); // From YouTube metadata
    expect(schema.description).toContain('Rick Astley'); // From YouTube metadata
    expect(schema.thumbnailUrl).toBe('https://i.ytimg.com/vi/dQw4w9WgXcQ/hqdefault.jpg');
    expect(schema.uploadDate).toBe('2009-10-25T06:57:33Z'); // From YouTube metadata
  });

  test('Includes contentUrl for video posts', async ({ page, request }) => {
    // Reset database
    await request.delete('/wp-json/gcb-testing/v1/reset', {
      headers: { 'GCB-Test-Key': 'test-secret-key-local' }
    });

    // Create video post
    const videoId = 'dQw4w9WgXcQ';
    const createResponse = await request.post('/wp-json/gcb-testing/v1/create-post', {
      data: {
        title: 'Content URL Test',
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

    // Find the VideoObject schema
    const schema = await findSchemaByType(page, 'VideoObject');

    expect(schema).not.toBeNull();
    expect(schema).toBeDefined();

    // Assert: contentUrl points to YouTube watch URL
    expect(schema.contentUrl).toBe(`https://www.youtube.com/watch?v=${videoId}`);
    expect(schema.embedUrl).toBe(`https://www.youtube.com/embed/${videoId}`);
  });
});
