# WPTOC+ Feature Ideas

## Planned Features

1. Gutenberg block
2. Copy link to heading
3. Reading progress integration
4. Accessibility enhancements

## Feature Suggestions

### 1. Active Section Highlighting
Highlight the current heading in the table of contents as the reader scrolls.

Why it matters:
- Makes long articles easier to navigate
- Gives the plugin a more modern reading experience
- Builds directly on the anchor and heading logic already in place

### 2. Per-Post TOC Override
Add a post/page level meta box so editors can force show, force hide, or override the TOC title for individual content.

Why it matters:
- Removes the need for shortcode-only workarounds
- Gives authors finer control without touching global settings
- Fits typical WordPress editorial workflows

### 3. Floating TOC Mode
Implemented with a desktop floating panel layout for long-form content.

Why it matters:
- Keeps navigation visible while reading
- Works especially well for guides, tutorials, and documentation pages
- Adds a clear premium-feeling feature without changing core parsing logic

### 4. Expand/Collapse Nested Sections
Implemented as an optional setting that collapses nested TOC branches until opened, while the active section path expands automatically.

Why it matters:
- Prevents very long TOCs from becoming visually heavy
- Improves readability on large documents
- Pairs well with hierarchy mode already supported by the plugin

### 5. Gutenberg Block
Create a native WPTOC+ block for editor-side insertion and configuration.

Why it matters:
- Cleaner authoring experience than relying only on shortcode placement
- Better fit for modern WordPress editing workflows
- Can expose common TOC options directly in block controls

### 6. Copy Link to Heading
Add a small link icon near headings or TOC items so readers can copy direct section URLs.

Why it matters:
- Useful for documentation, tutorials, and knowledge base content
- Encourages sharing exact sections
- Reuses the plugin's existing anchor-generation system

### 7. Better Mobile TOC Mode
Implemented as a compact expandable mobile panel option for smaller screens.

Why it matters:
- Improves usability on smaller screens
- Prevents the TOC from feeling too large or intrusive on mobile
- Makes the plugin feel more intentional across devices

### 8. Include/Exclude by Selector or Class
Implemented with selector and class based heading exclusions for specific containers and blocks.

Why it matters:
- Useful for tabs, accordions, FAQs, builders, and reusable sections
- More flexible than heading-level filtering alone
- Helps reduce noise in complex layouts

### 9. Reading Progress Integration
Add an optional reading progress indicator that works alongside the TOC.

Why it matters:
- Complements section navigation with reading feedback
- Useful for long-form editorial and documentation pages
- Can be built from the same heading map used by the TOC

### 10. Design Presets
Implemented as an Appearance setting with curated TOC presets such as Minimal, Editorial, Docs, and Card.

Why it matters:
- Makes customization easier without requiring CSS knowledge
- Improves first-run experience for users
- Gives the plugin a stronger visual identity

### 11. Accessibility Enhancements
Add stronger keyboard support, reduced-motion awareness, and clearer focus styling.

Why it matters:
- Improves usability for keyboard and assistive technology users
- Fits well with the plugin's navigation purpose
- Low risk and high value

### 12. Import/Export Settings
Implemented as an Import / Export admin tab with JSON backup and restore for plugin settings.

Why it matters:
- Useful for agencies and multi-site operators
- Makes staging-to-production rollout easier
- Reduces repetitive admin setup work

## Suggested Build Order

1. Build a Gutenberg block
2. Add copy link to heading
3. Add reading progress integration
4. Finish with accessibility enhancements across the TOC UI
