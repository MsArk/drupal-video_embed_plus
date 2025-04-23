# Video Embed Plus

**Video Embed Plus** is a lightweight and flexible Drupal module that allows you to automatically add a `field_video_embed` field to selected content types and provides a token for retrieving video thumbnail URLs.

## Features

- Adds a configurable video embed field to content types via admin UI.
- Automatically displays the field on content editing and view pages.
- Supports YouTube and Vimeo videos.
- Provides a token `[node:video_thumbnail]` for retrieving the video’s thumbnail URL.
- Caches Vimeo API responses for performance.

## Installation

1. Download and enable the module:
   - Via Composer:
     ```bash
     composer require drupal/video_embed_plus
     ```
   - Or manually place in `/modules/custom/video_embed_plus`

2. Enable the module:
   ```bash
   drush en video_embed_plus
   ```

3. Go to **Configuration > Media > Video Embed Plus** (`/admin/config/media/video-embed-plus`) and select the content types you want to enable the field for.

## Dependencies

- [Token](https://www.drupal.org/project/token)
- [Video Embed Field](https://www.drupal.org/project/video_embed_field)

Make sure both modules are installed and enabled before using Video Embed Plus.

## Tokens

| Token | Description |
|-------|-------------|
| `[node:video_thumbnail]` | Returns the thumbnail URL of the video in the `field_video_embed` field (YouTube or Vimeo) |

## Maintainers

This module was created and is maintained by [msark](https://www.drupal.org/u/msark) to simplify video integration in structured content types.

## License

GPL-2.0-or-later. See `LICENSE.txt` for details.
