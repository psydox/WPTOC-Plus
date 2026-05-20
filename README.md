# WPTOC+

WPTOC+ is a fork of the original Table of Contents Plus WordPress plugin. It automatically creates a context specific index or table of contents (TOC) for long pages and custom post types.

Current author and contributor: Brian V. Rosario. Past contributors and maintainers include All in One SEO Team (aioseo), Michael Tran, conjur3r, smub, and benjaminprojas.

Built from the ground up and with Wikipedia in mind, the table of contents by default appears before the first heading on a page. This allows the author to insert lead-in content that may summarise or introduce the rest of the page. It also uses a unique numbering scheme that doesn't get lost through CSS differences across themes.

This plugin is a great companion for content rich sites such as content management system oriented configurations. That said, bloggers also have the same benefits when writing long structured articles.

Includes an administration options panel where you can customise settings like display position, define the minimum number of headings before an index is displayed, appearance, smooth scrolling, active section highlighting, desktop display modes, mobile display modes, selector-based exclusions, and more. For power users, the advanced options cover heading inclusion, anchor formatting, custom styling, and widget-only output. Using shortcodes, you can override default behaviour such as custom placement or hiding the table of contents on a specific piece of content.

Prefer to include the index in the sidebar? Go to Appearance > Widgets and drag the WPTOC+ widget to your desired sidebar and position.

Custom post types are supported, however, auto insertion works only when the_content() has been used by the custom post type. Each post type will appear in the options panel, so enable the ones you want.

## What WPTOC+ Does

* Generates a table of contents from page and post headings
* Supports automatic insertion, shortcode placement, and widget output
* Supports smooth scrolling and active section highlighting while readers scroll
* Supports sticky sidebar and floating panel display modes for desktop readers
* Supports a configurable display top offset so sticky and floating TOCs can clear fixed site headers
* Supports a mobile-only compact expandable TOC panel for smaller screens
* Supports excluding headings that appear inside specific classes, blocks, or simple CSS selectors
* Supports per-post overrides in the standard WordPress editor so authors can force show, force hide, or set a custom TOC title for one post only
* Supports custom post types when their content is rendered through the standard content flow

## What WPTOC+ Does Not Do

* It does not provide sitemap features. This fork is focused only on table of contents behaviour.
* It does not integrate with WordPress.org plugin detail or update flows for this fork.
* It does not generate TOCs in REST requests.
* It does not guarantee that per-post override controls will appear inside third-party visual builders such as Divi. Those controls are currently available in the standard WordPress post and page edit screen.
* Sticky and floating TOC display modes fall back to the standard inline TOC on smaller screens.
* Floating panel mode can be placed on either the left or right side.
* Sticky and floating modes can be nudged downward with the display top offset setting when a theme uses a fixed header.
* Mobile mode can switch to a compact expandable panel on smaller screens.
* Selector-based exclusions support simple selectors such as `.class-name`, `#section-id`, `tag`, `tag.class-name`, and `tag#section-id`.

## Notes For Site Owners

* If you use Divi or another builder, set per-post overrides in the normal WordPress editor.
* The plugin now defaults to the unminified frontend assets used in this fork, which makes maintenance and debugging simpler.
* Several low-value legacy settings were removed from the settings page to keep the plugin focused on TOC behaviour.

Project website: https://github.com/psydox/WPTOC-Plus

## Links

* [Project website](https://github.com/psydox/WPTOC-Plus)
