# GCB Content Intelligence Plugin

**Version:** 1.0.0
**Requires PHP:** 8.3+
**WordPress:** 6.7+

## Overview

Automated content intelligence plugin for GayCarBoys.com that:

- **Detects video content** (YouTube embeds) automatically
- **Classifies posts** using hidden `content_format` taxonomy (video/standard/gallery)
- **Generates schema.org JSON-LD** (VideoObject for videos, NewsArticle for text posts)
- **Converts legacy Avada shortcodes** `[fusion_youtube]` to WordPress core blocks
- **Fetches video metadata** from YouTube Data API v3 (duration, views, upload date)

## Features

### 1. Content Format Detection

Posts are automatically classified when saved:

- **Video:** Contains YouTube URLs or embed codes
- **Standard:** Text-based articles without video content
- **Gallery:** Posts with image galleries (future)

### 2. Hidden Taxonomy

The `content_format` taxonomy is registered with:

```php
'public'            => false,  // Hidden from public
'show_ui'           => false,  // Not shown in admin UI
'show_in_rest'      => true,   // Available via REST API
'show_admin_column' => true,   // Visible in posts list
```

### 3. YouTube URL Detection

Supports multiple YouTube URL formats:

- `https://www.youtube.com/watch?v=VIDEO_ID`
- `https://youtu.be/VIDEO_ID`
- `https://www.youtube.com/embed/VIDEO_ID`

### 4. Post Meta Storage

Video-related metadata is stored in post meta:

- `_gcb_video_id`: YouTube video ID (e.g., `dQw4w9WgXcQ`)
- `_gcb_content_format`: Content format (e.g., `video`)
- `_gcb_video_duration`: Video duration in ISO 8601 format (e.g., `PT12M45S`)
- `_gcb_video_views`: View count
- `_gcb_video_upload_date`: Upload date in ISO 8601 format

## Architecture

### Class Structure

```
gcb-content-intelligence.php                 (Bootstrap)
├── includes/
│   ├── class-gcb-taxonomy-manager.php       (Taxonomy registration)
│   ├── class-gcb-content-detector.php       (YouTube URL detection)
│   ├── class-gcb-video-processor.php        (YouTube API integration)
│   ├── class-gcb-schema-generator.php       (Schema.org JSON-LD)
│   └── class-gcb-shortcode-converter.php    (Avada → Block converter)
```

### WordPress Hooks

- `init` (priority 10): Register taxonomy and post meta
- `save_post` (priority 20): Detect content format on post save
- `wp_footer` (priority 10): Inject schema.org JSON-LD

## Development

### TDD Workflow (RED → GREEN → REFACTOR)

This plugin is developed using Test-Driven Development:

1. **RED:** Write failing E2E test
2. **GREEN:** Implement minimum code to pass test
3. **REFACTOR:** Optimize code while keeping tests green

### Running E2E Tests

```bash
# Install dependencies
npm install

# Run all E2E tests
npm run test:e2e

# Run specific test file
npm run test:e2e -- content-intelligence
```

### Test Requirements

- Database must be reset before each test
- Use `gcb-test-utils` plugin for database reset endpoint
- Test against WordPress Studio (WASM/SQLite environment)

## Installation

### Manual Installation

1. Download or clone this repository
2. Copy `gcb-content-intelligence/` to `wp-content/plugins/`
3. Activate via WordPress admin: Plugins → GCB Content Intelligence → Activate
4. No configuration needed - plugin works automatically

### Via WP-CLI (if available)

```bash
wp plugin install gcb-content-intelligence.zip --activate
```

## Usage

### Automatic Detection

Plugin automatically detects content format when posts are saved. No manual action required.

### REST API Integration

Query posts by content format:

```bash
# Get all video posts
curl https://example.com/wp-json/wp/v2/posts?content_format=video

# Get post with video metadata
curl https://example.com/wp-json/wp/v2/posts/123
```

Response includes:

```json
{
  "id": 123,
  "title": { "rendered": "Video Post Title" },
  "content_format": ["video"],
  "meta": {
    "_gcb_video_id": "dQw4w9WgXcQ",
    "_gcb_content_format": "video",
    "_gcb_video_duration": "PT3M33S"
  }
}
```

## Configuration

### YouTube API Key (Optional)

To fetch video metadata from YouTube Data API v3:

1. Get API key from [Google Cloud Console](https://console.cloud.google.com/)
2. Add to `wp-config.php`:

```php
define( 'GCB_YOUTUBE_API_KEY', 'your-api-key-here' );
```

If not set, plugin will still detect videos but won't fetch metadata.

## Schema.org Output

### VideoObject (Video Posts)

```json
{
  "@context": "https://schema.org",
  "@type": "VideoObject",
  "name": "Video Post Title",
  "description": "Post excerpt",
  "uploadDate": "2025-01-01T00:00:00+00:00",
  "duration": "PT12M45S",
  "thumbnailUrl": "https://img.youtube.com/vi/VIDEO_ID/maxresdefault.jpg",
  "embedUrl": "https://www.youtube.com/embed/VIDEO_ID"
}
```

### NewsArticle (Standard Posts)

```json
{
  "@context": "https://schema.org",
  "@type": "NewsArticle",
  "headline": "Article Title",
  "author": {
    "@type": "Person",
    "name": "Author Name"
  },
  "datePublished": "2025-01-01T00:00:00+00:00",
  "image": "https://example.com/featured-image.jpg",
  "publisher": {
    "@type": "Organization",
    "name": "Gay Car Boys",
    "logo": {
      "@type": "ImageObject",
      "url": "https://example.com/logo.png"
    }
  }
}
```

## Compatibility

- **WordPress:** 6.7+ (Full Site Editing)
- **PHP:** 8.3+
- **Database:** MySQL 8.0+ or SQLite 3.8.8+
- **Theme:** Works with any FSE block theme (tested with `gcb-brutalist`)

## Performance

- **Caching:** YouTube API responses cached for 24 hours
- **Graceful degradation:** If API fails, video ID still saved
- **Batch processing:** Supports bulk content detection via WP-CLI

## Changelog

### 1.0.0 (2025-01-01)

- Initial release
- YouTube URL detection
- Content format taxonomy
- Post meta storage
- E2E test coverage

## Support

For issues or feature requests, see the main GCB project documentation.

## License

Proprietary - © Gay Car Boys 2025
