1. Update /readme.txt
2. Update /toc.php
3. Update /includes/globals.php
4. Github release
5. SVN tag

## Current Fork Notes

Before the next release, make sure the published notes reflect the current fork scope:

1. WPTOC+ is a TOC-only fork and does not include sitemap features.
2. WordPress.org plugin details and update flows are intentionally disabled for this fork.
3. Active section highlighting is now part of the frontend experience.
4. Per-post TOC overrides exist in the standard WordPress editor as a meta box, but may not appear in third-party builders such as Divi.
5. Several low-value legacy settings were removed from the admin UI and runtime.
6. Desktop display mode support now includes floating panel, sticky full-width, and sticky side-column modes.
7. Floating panel and sticky side-column modes can be placed on either the left or right side, while sticky full-width mode stays inside the main content column.
8. The settings screen now includes separate Appearance and Import / Export tabs.
9. Nested TOC sections can now collapse and expand when hierarchy mode is enabled, with a setting for whether branches start collapsed or expanded.
10. Sticky side-column mode now keeps the TOC above the article on smaller screens and follows the active Presentation styling instead of forcing its own shadow treatment.
11. The Appearance tab now includes curated design presets such as Minimal, Editorial, Docs, and Card.