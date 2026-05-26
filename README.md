# WPTOC+

WPTOC+ is a fork of the original Table of Contents Plus WordPress plugin. It automatically creates a context specific index or table of contents (TOC) for long pages and custom post types.

Current author and contributor: Brian V. Rosario. Past contributors and maintainers include All in One SEO Team (aioseo), Michael Tran, conjur3r, smub, and benjaminprojas.

Built from the ground up and with Wikipedia in mind, the table of contents by default appears before the first heading on a page. This allows the author to insert lead-in content that may summarise or introduce the rest of the page. It also uses a unique numbering scheme that doesn't get lost through CSS differences across themes.

This plugin is a great companion for content rich sites such as content management system oriented configurations. That said, bloggers also have the same benefits when writing long structured articles.

Includes an administration options panel where you can customise settings like display position, define the minimum number of headings before an index is displayed, appearance, smooth scrolling, active section highlighting, desktop display modes, mobile display modes, collapsible nested sections, selector-based exclusions, and more. The settings screen now separates Appearance from the main options and includes a dedicated Import / Export tab for moving WPTOC+ settings between sites or keeping a JSON backup. For power users, the advanced options cover heading inclusion, anchor formatting, custom styling, and widget-only output. Using shortcodes, you can override default behaviour such as custom placement or hiding the table of contents on a specific piece of content.

Current runtime defaults in this fork include Top placement, a 4-heading minimum threshold, smooth scrolling enabled, hierarchy enabled, numeric list markers disabled, right wrapping, and inline display mode with inline mobile mode.

Prefer to include the index in the sidebar? Go to Appearance > Widgets and drag the WPTOC+ widget to your desired sidebar and position.

Custom post types are supported, however, auto insertion works only when the_content() has been used by the custom post type. Each post type will appear in the options panel, so enable the ones you want.

## What WPTOC+ Does

* Generates a table of contents from page and post headings
* Supports automatic insertion, shortcode placement, and widget output
* Supports smooth scrolling and active section highlighting while readers scroll
* Supports a floating panel display mode for desktop readers
* Supports a sticky full-width display mode that stays within the content column on desktop
* Supports a sticky side-column display mode that preserves the TOC width while the article continues in its own column
* Supports a configurable display top offset so floating and sticky TOCs can clear fixed site headers
* Supports importing and exporting plugin settings from the WPTOC+ admin screen
* Supports a mobile-only compact expandable TOC panel for smaller screens
* Supports collapsible nested TOC branches when hierarchy mode is enabled, including a default collapsed or expanded starting state
* Supports curated design presets such as Minimal, Editorial, Docs, and Card from the Appearance tab
* Supports excluding headings that appear inside specific classes, blocks, or simple CSS selectors
* Supports per-post overrides in the standard WordPress editor so authors can force show, force hide, or set a custom TOC title for one post only
* Supports custom post types when their content is rendered through the standard content flow

## What WPTOC+ Does Not Do

* It does not provide sitemap features. This fork is focused only on table of contents behaviour.
* It does not integrate with WordPress.org plugin detail or update flows for this fork.
* It does not generate TOCs in REST requests.
* It does not guarantee that per-post override controls will appear inside third-party visual builders such as Divi. Those controls are currently available in the standard WordPress post and page edit screen.
* Floating and sticky desktop TOC display modes fall back to the standard inline TOC on smaller screens.
* Floating panel mode can be placed on either the left or right side.
* Sticky full-width mode keeps the TOC inside the main content column instead of pinning it to the viewport edge.
* Sticky side-column mode keeps the TOC in its rendered side rail, can be placed on the left or right, reserves the remaining article column from that insertion point downward, and stacks the TOC above the article on smaller screens.
* Floating and sticky modes can be nudged downward with the display top offset setting when a theme uses a fixed header.
* Mobile mode can switch to a compact expandable panel on smaller screens.
* Nested TOC branches can collapse until expanded, while the active section path opens automatically.
* Design presets can adjust the TOC's spacing, borders, and title treatment without replacing the current Presentation colour controls.
* The Appearance tab separates layout, sizing, design presets, and Presentation styling from the main behavior settings.
* Selector-based exclusions support simple selectors such as `.class-name`, `#section-id`, `tag`, `tag.class-name`, and `tag#section-id`.

## Notes For Site Owners

* If you use Divi or another builder, set per-post overrides in the normal WordPress editor.
* The plugin now defaults to the unminified frontend assets used in this fork, which makes maintenance and debugging simpler.
* Several low-value legacy settings were removed from the settings page to keep the plugin focused on TOC behaviour.
* In this fork, some advanced controls (such as lowercase anchors, hyphenated anchors, CSS exclusion, smooth-scroll top offset, and the Help tab link) are intentionally shown only to super-admin style users.

Project website: https://github.com/psydox/WPTOC-Plus

## Links

* [Project website](https://github.com/psydox/WPTOC-Plus)
