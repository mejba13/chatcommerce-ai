# ChatCommerce AI Design System
## Modern Classic Design Language

Version: 1.0.0
Last Updated: 2025-10-30

---

## Overview

The ChatCommerce AI design system implements a "Modern Classic" aesthetic that balances contemporary design trends with timeless usability principles. The system prioritizes:

- **Clarity**: High contrast, generous whitespace, clear visual hierarchy
- **Accessibility**: WCAG 2.1 AA compliant by default
- **Performance**: Lightweight, CSS-only components, minimal JavaScript
- **Consistency**: Unified token system across all interfaces
- **Professionalism**: Premium feel suitable for enterprise customers

---

## 🎨 Design Tokens

All design values are centralized as CSS custom properties in `assets/css/design-tokens.css`. This ensures consistency and enables easy theming.

### Color System

**Neutral Palette** (High-contrast base):
- `--cc-color-neutral-50` through `--cc-color-neutral-900`
- Used for backgrounds, text, borders
- 4.5:1 contrast minimum for AA compliance

**Primary Accent**:
- `--cc-color-primary-500` (default: #3b82f6 - Blue)
- Used for CTAs, links, focus states
- Customizable per brand

**Semantic Colors**:
- Success: Green (`--cc-color-success-*`)
- Warning: Amber (`--cc-color-warning-*`)
- Error: Red (`--cc-color-error-*`)
- Info: Blue (`--cc-color-info-*`)

### Spacing Scale

Based on 4px increments for mathematical precision:
```
--cc-space-1: 4px
--cc-space-2: 8px
--cc-space-3: 12px
--cc-space-4: 16px
--cc-space-6: 24px
--cc-space-8: 32px
--cc-space-12: 48px
--cc-space-16: 64px
```

**Usage Guidelines**:
- Use multiples of 4px for all spacing
- Prefer `--cc-space-*` tokens over hardcoded values
- Common combos: 4/8/16, 8/16/24, 12/24/32

### Typography

**Font Stack**:
```css
--cc-font-sans: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto...
```
System font stack for fast loading and native feel.

**Type Scale** (Clear H1-H6 hierarchy):
- H1: 36px (2.25rem) - Page titles
- H2: 30px (1.875rem) - Section titles
- H3: 24px (1.5rem) - Subsection titles
- H4: 20px (1.25rem) - Card titles
- H5: 18px (1.125rem) - Small headings
- H6: 16px (1rem) - Inline headings
- Body: 16px (1rem) - Default text
- Small: 14px (0.875rem) - Meta text
- Tiny: 12px (0.75rem) - Labels, badges

**Font Weights**:
- Normal: 400 (body text)
- Medium: 500 (emphasized text)
- Semibold: 600 (headings)
- Bold: 700 (large headings)

**Line Heights**:
- Tight: 1.25 (large headings)
- Normal: 1.5 (body text)
- Relaxed: 1.625 (long-form content)

### Border Radius

Soft, modern corners:
- Small: 6px (inputs, buttons)
- Medium: 8px (cards, modals)
- Large: 12px (major containers)
- Extra Large: 16px (hero cards)
- Full: 9999px (pills, badges)

### Shadows

Layered elevation system:
- XS: Subtle lift (1-2px)
- SM: Raised elements (2-4px)
- MD: Floating elements (4-6px)
- LG: Modals, dropdowns (10-15px)
- XL: Overlays (20-25px)

All shadows use rgba(0, 0, 0, 0.1) for consistency.

### Motion

**Durations**:
- Fast: 150ms (hover, focus)
- Normal: 200ms (standard transitions)
- Slow: 250ms (modals, drawers)
- Slower: 300ms (page transitions)

**Easing**:
- Ease-out: Default for most animations
- Ease-in-out: For reversible actions

**Reduced Motion**:
System respects `prefers-reduced-motion` media query. All animations are disabled for users who prefer reduced motion.

---

## 🧩 Component Library

Components are located in `assets/css/components.css`. Each component follows a consistent naming convention: `.cc-component-name`.

### Card (`cc-card`)

**Usage**: Content containers, stat displays, quick action tiles

**Classes**:
- `.cc-card` - Base card style
- `.cc-card-hover` - Add hover lift effect
- `.cc-card-compact` - Reduced padding
- `.cc-card-header` - Top section with bottom border
- `.cc-card-body` - Main content area
- `.cc-card-footer` - Bottom section with top border

**Example**:
```html
<div class="cc-card cc-card-hover">
    <div class="cc-card-header">
        <h3 class="cc-heading-4">Card Title</h3>
    </div>
    <div class="cc-card-body">
        <p>Card content goes here.</p>
    </div>
    <div class="cc-card-footer">
        <button class="cc-button cc-button-primary">Action</button>
    </div>
</div>
```

### Stat Card (`cc-stat-card`)

**Usage**: KPI displays, metric cards

**Elements**:
- `.cc-stat-icon` - Optional icon (48x48px)
- `.cc-stat-label` - Metric name (uppercase, small)
- `.cc-stat-value` - Large number display
- `.cc-stat-meta` - Supporting text below value

**Example**:
```html
<div class="cc-stat-card cc-stat-card-hover">
    <svg class="cc-stat-icon">...</svg>
    <span class="cc-stat-label">Total Sessions</span>
    <span class="cc-stat-value">1,234</span>
    <span class="cc-stat-meta">All-time conversations</span>
</div>
```

### Badge (`cc-badge`)

**Usage**: Status indicators, labels, counts

**Variants**:
- `.cc-badge-success` - Green (completed, active)
- `.cc-badge-warning` - Amber (pending, cautionary)
- `.cc-badge-error` - Red (failed, critical)
- `.cc-badge-info` - Blue (informational)
- `.cc-badge-neutral` - Gray (default)

**Example**:
```html
<span class="cc-badge cc-badge-success">Active</span>
```

### Button (`cc-button`)

**Usage**: Actions, links, CTAs

**Variants**:
- `.cc-button-primary` - High-emphasis actions
- `.cc-button-secondary` - Medium-emphasis actions
- `.cc-button-ghost` - Low-emphasis actions
- `.cc-button-danger` - Destructive actions

**Sizes**:
- `.cc-button-sm` - Compact (10px padding)
- Default - Standard (12px padding)
- `.cc-button-lg` - Large (16px padding)

**States**:
- `:hover` - Lift and darken
- `:focus` - Blue focus ring
- `:disabled` - 50% opacity, no interaction

**Example**:
```html
<button class="cc-button cc-button-primary">
    Save Settings
</button>
```

### Form Components

**Form Row** (`.cc-form-row`):
Complete form field with label, input, and description.

**Elements**:
- `.cc-form-label` - Field label
- `.cc-form-label-required` - Adds red asterisk
- `.cc-form-input` - Text input
- `.cc-form-textarea` - Multi-line input
- `.cc-form-select` - Dropdown select
- `.cc-form-description` - Help text below field
- `.cc-form-error-message` - Error message

**Example**:
```html
<div class="cc-form-row">
    <label class="cc-form-label cc-form-label-required">
        API Key
    </label>
    <input type="text" class="cc-form-input" />
    <span class="cc-form-description">
        Enter your OpenAI API key
    </span>
</div>
```

### Toggle Switch (`cc-toggle`)

**Usage**: Boolean settings

**Example**:
```html
<label class="cc-toggle">
    <input type="checkbox" class="cc-toggle-input" />
    <span class="cc-toggle-slider"></span>
    <span class="cc-toggle-label">Enable Feature</span>
</label>
```

### Alert (`cc-alert`)

**Usage**: Inline notifications, warnings, errors

**Variants**:
- `.cc-alert-success`
- `.cc-alert-warning`
- `.cc-alert-error`
- `.cc-alert-info`

**Example**:
```html
<div class="cc-alert cc-alert-warning">
    <svg class="cc-alert-icon">...</svg>
    <div class="cc-alert-content">
        <div class="cc-alert-title">Warning</div>
        <div class="cc-alert-description">
            Configuration required.
        </div>
    </div>
</div>
```

### Empty State (`cc-empty-state`)

**Usage**: No data, onboarding

**Elements**:
- `.cc-empty-state-icon` - Large icon (64x64px)
- `.cc-empty-state-title` - Heading
- `.cc-empty-state-description` - Explanation
- `.cc-empty-state-action` - CTA button

### Grid System (`cc-grid`)

**Usage**: Responsive layouts

**Classes**:
- `.cc-grid` - Base grid container
- `.cc-grid-cols-[1-4]` - Column count (mobile)
- `.cc-grid-md-cols-[2-4]` - Tablet columns
- `.cc-grid-lg-cols-[3-4]` - Desktop columns
- `.cc-grid-auto-fit` - Auto-responsive grid (min 250px)

**Example**:
```html
<div class="cc-grid cc-grid-cols-1 cc-grid-md-cols-2 cc-grid-lg-cols-4">
    <div class="cc-card">Card 1</div>
    <div class="cc-card">Card 2</div>
    <div class="cc-card">Card 3</div>
    <div class="cc-card">Card 4</div>
</div>
```

---

## 📐 Layout Patterns

### Page Template

Standard page structure:

```html
<div class="wrap chatcommerce-ai-wrap chatcommerce-ai-[page-name]">
    <!-- Page Header -->
    <div class="cc-page-header">
        <div>
            <h1 class="cc-page-title">Page Title</h1>
            <p class="cc-text-small">Page description</p>
        </div>
        <div class="cc-page-actions">
            <button class="cc-button cc-button-primary">
                Primary Action
            </button>
        </div>
    </div>

    <!-- Page Content -->
    <div class="cc-grid cc-grid-cols-1 cc-grid-lg-cols-4">
        <!-- Cards, stats, content -->
    </div>
</div>
```

### Responsive Breakpoints

- **XS**: 0-639px (Mobile)
- **SM**: 640-767px (Large mobile)
- **MD**: 768-1023px (Tablet)
- **LG**: 1024-1279px (Desktop)
- **XL**: 1280px+ (Large desktop)

---

## ♿ Accessibility Guidelines

All components are designed for WCAG 2.1 AA compliance:

### Color Contrast
- Text: 4.5:1 minimum
- Large text (18px+): 3:1 minimum
- Use color + icon/text to convey meaning

### Keyboard Navigation
- All interactive elements are keyboard accessible
- Tab order follows visual order
- Focus states are clearly visible
- Escape key closes overlays

### Screen Readers
- Use semantic HTML (`<button>`, `<nav>`, `<main>`)
- Add ARIA labels where needed
- Live regions for dynamic content
- Skip links for navigation

### Motion
- Respect `prefers-reduced-motion`
- Provide static alternatives
- No auto-playing animations

---

## 🎭 Content Guidelines

### Voice & Tone
- Clear and concise
- Action-oriented
- Professional but friendly
- Avoid jargon

### Microcopy Best Practices

**Buttons**:
- ✅ "Save Settings", "View Leads", "Sync Now"
- ❌ "Submit", "Click Here", "Go"

**Descriptions**:
- One sentence, under 15 words
- Explain value, not mechanics
- End without punctuation (unless multiple sentences)

**Empty States**:
- Explain why empty
- Provide single next step
- Use encouraging tone

**Error Messages**:
- State what happened
- Explain why (if relevant)
- Provide clear recovery action

---

## 🚀 Performance Guidelines

### CSS Best Practices
- Use CSS custom properties for theming
- Avoid deep nesting (max 3 levels)
- Prefer logical properties (`margin-inline`, `padding-block`)
- Use `will-change` sparingly

### Loading Strategy
- Critical CSS inline (design tokens)
- Components lazy-loaded per page
- Defer non-critical scripts

### Bundle Targets
- **Admin CSS**: ≤ 50KB gzipped
- **Widget CSS**: ≤ 30KB gzipped
- **Admin JS**: ≤ 100KB gzipped
- **Widget JS**: ≤ 85KB gzipped

---

## 🔧 Implementation Checklist

When creating a new admin page:

- [ ] Use `.chatcommerce-ai-wrap` wrapper
- [ ] Add `.cc-page-header` with title and actions
- [ ] Use design tokens for all spacing and colors
- [ ] Employ component library classes
- [ ] Test keyboard navigation
- [ ] Verify color contrast
- [ ] Test on mobile (< 768px)
- [ ] Add ARIA attributes for screen readers
- [ ] Respect `prefers-reduced-motion`
- [ ] Test with WordPress 6.4+

---

## 📦 File Structure

```
assets/
├── css/
│   ├── design-tokens.css    # CSS custom properties
│   ├── components.css        # Component library
│   └── admin.css            # Page-specific styles
└── js/
    └── admin.js             # Admin interactions

templates/
└── admin/
    ├── dashboard.php        # Dashboard page
    ├── settings.php         # Settings page
    ├── conversations.php    # Conversations page
    ├── leads.php           # Leads page
    └── sync.php            # Content sync page
```

---

## 🎓 Training Resources

### For Developers
- Review `design-tokens.css` for available tokens
- Use components library for consistency
- Follow accessibility checklist
- Test across breakpoints

### For Designers
- Design in Figma using token system
- Ensure 4.5:1 contrast minimum
- Provide hover/focus/disabled states
- Consider mobile-first

### For QA
- Verify keyboard navigation
- Test with screen readers (NVDA, VoiceOver)
- Check color contrast (axe DevTools)
- Validate at all breakpoints
- Test reduced motion preference

---

## 📝 Change Log

### v1.0.0 (2025-10-30)
- Initial design system implementation
- Created token system with CSS custom properties
- Built component library (12 core components)
- Redesigned dashboard and settings pages
- Added accessibility features (WCAG 2.1 AA)
- Implemented responsive grid system
- Added motion preferences support

---

## 🤝 Contributing

When adding new components:

1. **Define in `components.css`** following naming convention
2. **Use design tokens** exclusively (no hardcoded values)
3. **Test accessibility** (keyboard, screen reader, contrast)
4. **Document usage** in this file with examples
5. **Add to implementation checklist** if applicable

For questions or suggestions, contact the design system team.

---

**Maintained by**: ChatCommerce AI Team
**License**: GPL v2 or later
