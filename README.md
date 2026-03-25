# Mopedgarage

Mopedgarage is a phpBB extension for managing motorcycles in user profiles, posts, and a public gallery/search page.

## Release status

- Version: 1.0.0
- Public initial release package
- Single migration baseline: `migrations/v100.php`
- Namespace: `bruno\mopedgarage`

## Features

- User-managed motorcycle entries in the UCP
- Multiple bikes per user
- Optional image upload with automatic resize and thumbnails
- Profile and post integration
- Public gallery/search by brand, model, year, ccm, and image availability
- ACP settings and ACP custom-field management
- German and English language packs
- Dedicated phpBB permissions for view, use, and ACP management

## Requirements

- phpBB 3.3.x
- PHP 7.4 or newer
- GD support for image processing
- Writable upload directories under `images/mopedgarage` and `images/mopedgarage/thumbs`

## Installation

1. Copy the extension to `ext/bruno/mopedgarage`
2. In ACP, open `Customise -> Manage extensions`
3. Enable `bruno/mopedgarage`
4. Configure the extension in `ACP -> Mopedgarage`
5. Purge the phpBB cache after updating templates or language files

## Initial migration policy

`v100` is the frozen initial migration for the first public release. It already contains:

- bike table
- custom field tables
- ACP/UCP module registration
- dedicated permissions
- default permission assignments
- extension defaults used by the shipped code

All later schema, config, permission, or module changes must go into new migrations such as `v101`, `v102`, and so on.

## Packaging notes

This release package intentionally contains:

- one active migration only
- no backup listeners
- no disabled development migrations
- no duplicate permission registration via `config/permissions.yml`

## Known install note

This package is intended as a clean initial release baseline. It should be used for fresh installs of the public `v1.0.0` line.

## Image handling

- Allowed formats: JPG, JPEG, PNG, WebP, GIF
- Images are resized server-side to configured maximum dimensions
- Uploads are validated by extension and MIME type
