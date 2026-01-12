# YouTube API Key Setup Guide

This guide explains how to create, manage, and configure a YouTube Data API key for the Gay Car Boys website.

## Overview

The video rail and video archive pages use the YouTube Data API v3 to fetch videos from the [@gaycarboys](https://www.youtube.com/@gaycarboys) channel. This requires a valid API key.

**Current Configuration:**
- Cache Duration: 15 minutes
- API Quota: ~1,152 units/day (limit: 10,000/day)
- Videos Fetched: Up to 200 (video archive), 10 (video rail)

---

## Part 1: Creating a YouTube API Key

### Step 1: Access Google Cloud Console

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Sign in with your Google account
   - Use the account that will own/manage the API key
   - Recommendation: Use a shared team account, not a personal one

### Step 2: Create or Select a Project

1. Click the project dropdown at the top of the page (next to "Google Cloud")
2. Either:
   - **Select an existing project** (e.g., "Gay Car Boys Website")
   - **Create a new project:**
     1. Click "New Project"
     2. Enter project name: `Gay Car Boys Website`
     3. Click "Create"
     4. Wait for the project to be created, then select it

### Step 3: Enable the YouTube Data API

1. In the left sidebar, click **"APIs & Services"** → **"Library"**
2. Search for `YouTube Data API v3`
3. Click on **"YouTube Data API v3"** in the results
4. Click the **"Enable"** button
5. Wait for the API to be enabled

### Step 4: Create an API Key

1. In the left sidebar, click **"APIs & Services"** → **"Credentials"**
2. Click **"+ Create Credentials"** at the top
3. Select **"API key"**
4. A new API key will be generated and displayed
5. **Copy the API key** immediately (you'll need it later)
6. Click **"Close"**

### Step 5: Restrict the API Key (Recommended)

For security, restrict what the API key can access:

1. In the Credentials list, click on your newly created API key
2. Under **"API restrictions"**:
   - Select **"Restrict key"**
   - Check only **"YouTube Data API v3"**
3. Under **"Application restrictions"** (optional):
   - For websites: Select "HTTP referrers" and add your domains:
     ```
     gaycarboys.com/*
     *.gaycarboys.com/*
     staging-9ba2-gaycarboys.wpcomstaging.com/*
     localhost:*/*
     ```
4. Click **"Save"**

---

## Part 2: Configuring the API Key in WordPress

You have two options for storing the API key:

### Option A: wp-config.php (Recommended for self-hosted)

Add this line to your `wp-config.php` file:

```php
define( 'GCB_YOUTUBE_API_KEY', 'YOUR_API_KEY_HERE' );
```

**Location:** Add it before the line that says `/* That's all, stop editing! */`

### Option B: WordPress Database (Required for WordPress.com)

Since WordPress.com doesn't allow editing `wp-config.php`, use a database option:

**Via WP-CLI:**
```bash
wp option update gcb_youtube_api_key 'YOUR_API_KEY_HERE'
```

**Via phpMyAdmin or database tool:**
```sql
INSERT INTO wp_options (option_name, option_value, autoload)
VALUES ('gcb_youtube_api_key', 'YOUR_API_KEY_HERE', 'yes')
ON DUPLICATE KEY UPDATE option_value = 'YOUR_API_KEY_HERE';
```

**Via WordPress Admin (if you add a settings page):**
- This would require custom code to add a settings field

---

## Part 3: Verifying the API Key Works

### Clear the Cache

After changing the API key, clear the video cache:

**Option 1: Wait 15 minutes** (cache expires automatically)

**Option 2: Clear manually via database:**
```sql
DELETE FROM wp_options WHERE option_name LIKE '%gcb_youtube%_videos%';
```

**Option 3: Via WP-CLI:**
```bash
wp transient delete gcb_youtube_channel_videos
wp transient delete gcb_youtube_all_videos
```

### Test the Video Rail

1. Visit your homepage
2. Scroll to the "LATEST VIDEOS" section
3. Verify videos are displaying with thumbnails
4. Check browser console for any API errors (F12 → Console)

### Check for Errors

If videos aren't loading, check the WordPress error log for messages like:
- `GCB: YouTube API key not configured`
- `GCB: YouTube API error - ...`

---

## Part 4: Managing API Quota

### Current Usage

| Component | API Calls | Units per Refresh |
|-----------|-----------|-------------------|
| Video Rail (10 videos) | 3 | ~3 |
| Video Archive (200 videos) | ~9 | ~9 |
| **Total per refresh** | | **~12** |

With 15-minute cache: ~1,152 units/day (limit: 10,000)

### Monitoring Quota

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Navigate to **"APIs & Services"** → **"Dashboard"**
3. Click on **"YouTube Data API v3"**
4. View the **"Quotas"** tab to see usage

### If You Hit Quota Limits

1. **Increase cache duration** in `class-gcb-youtube-channel-fetcher.php`:
   ```php
   private const CACHE_DURATION = HOUR_IN_SECONDS; // Back to 1 hour
   ```

2. **Request quota increase** from Google:
   - Go to **"Quotas"** in the API dashboard
   - Click **"Edit Quotas"**
   - Request an increase (requires justification)

---

## Part 5: Rotating or Revoking Keys

### To Create a New Key (Rotation)

1. Follow Part 1 steps to create a new API key
2. Update the key in WordPress (Part 2)
3. Clear the cache (Part 3)
4. Verify it works
5. Delete the old key (see below)

### To Revoke/Delete a Key

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Navigate to **"APIs & Services"** → **"Credentials"**
3. Find the API key to delete
4. Click the **trash icon** or select and click **"Delete"**
5. Confirm deletion

**Warning:** Deleting an active key will immediately break the video rail until a new key is configured.

---

## Troubleshooting

### Videos Not Loading

| Symptom | Cause | Solution |
|---------|-------|----------|
| No videos shown | API key missing | Add key to wp-config.php or database |
| No videos shown | API key invalid | Check key in Google Console |
| No videos shown | API not enabled | Enable YouTube Data API v3 |
| No videos shown | Quota exceeded | Wait 24 hours or increase cache duration |
| Partial videos | API timeout | Check server connectivity |

### Common Error Messages

**"GCB: YouTube API key not configured"**
- The API key is not set in wp-config.php or database

**"GCB: YouTube API error - quotaExceeded"**
- You've hit the daily quota limit
- Increase cache duration or request quota increase

**"GCB: YouTube API error - keyInvalid"**
- The API key is incorrect or has been deleted
- Create a new key and update configuration

---

## Quick Reference

| Setting | Location | Value |
|---------|----------|-------|
| API Key (preferred) | `wp-config.php` | `define('GCB_YOUTUBE_API_KEY', '...');` |
| API Key (WP.com) | Database | `gcb_youtube_api_key` option |
| Cache Duration | `class-gcb-youtube-channel-fetcher.php` | `15 * MINUTE_IN_SECONDS` |
| Channel ID | `class-gcb-youtube-channel-fetcher.php` | `UCYAQE20p01w8TZXkvcjna8Q` |

---

## Links

- [Google Cloud Console](https://console.cloud.google.com/)
- [YouTube Data API Documentation](https://developers.google.com/youtube/v3)
- [API Quota Calculator](https://developers.google.com/youtube/v3/determine_quota_cost)
- [GCB YouTube Channel](https://www.youtube.com/@gaycarboys)
