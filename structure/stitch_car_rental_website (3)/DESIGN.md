---
name: Kinetic Velocity
colors:
  surface: '#fff8f7'
  surface-dim: '#f2d3d3'
  surface-bright: '#fff8f7'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#fff0f0'
  surface-container: '#ffe9e9'
  surface-container-high: '#ffe1e1'
  surface-container-highest: '#fbdbdb'
  on-surface: '#281718'
  on-surface-variant: '#5c3f40'
  inverse-surface: '#3f2b2c'
  inverse-on-surface: '#ffedec'
  outline: '#906f70'
  outline-variant: '#e5bdbe'
  surface-tint: '#be0037'
  primary: '#b80035'
  on-primary: '#ffffff'
  primary-container: '#e11d48'
  on-primary-container: '#fffaf9'
  inverse-primary: '#ffb3b6'
  secondary: '#565e74'
  on-secondary: '#ffffff'
  secondary-container: '#dae2fd'
  on-secondary-container: '#5c647a'
  tertiary: '#006a2d'
  on-tertiary: '#ffffff'
  tertiary-container: '#00863b'
  on-tertiary-container: '#f2ffef'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#ffdada'
  primary-fixed-dim: '#ffb3b6'
  on-primary-fixed: '#40000c'
  on-primary-fixed-variant: '#920028'
  secondary-fixed: '#dae2fd'
  secondary-fixed-dim: '#bec6e0'
  on-secondary-fixed: '#131b2e'
  on-secondary-fixed-variant: '#3f465c'
  tertiary-fixed: '#6bff8f'
  tertiary-fixed-dim: '#4ae176'
  on-tertiary-fixed: '#002109'
  on-tertiary-fixed-variant: '#005321'
  background: '#fff8f7'
  on-background: '#281718'
  surface-variant: '#fbdbdb'
  surface-muted: '#F8FAFC'
  border-subtle: '#E2E8F0'
  text-description: '#64748B'
  whatsapp-green: '#25D366'
typography:
  display-lg:
    fontFamily: Inter
    fontSize: 48px
    fontWeight: '800'
    lineHeight: '1.1'
    letterSpacing: -0.02em
  headline-xl:
    fontFamily: Inter
    fontSize: 36px
    fontWeight: '700'
    lineHeight: '1.2'
  headline-lg:
    fontFamily: Inter
    fontSize: 24px
    fontWeight: '700'
    lineHeight: '1.3'
  headline-lg-mobile:
    fontFamily: Inter
    fontSize: 20px
    fontWeight: '700'
    lineHeight: '1.3'
  body-md:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: '1.6'
  body-sm:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '400'
    lineHeight: '1.5'
  label-bold:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '600'
    lineHeight: '1.2'
  label-xs:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '500'
    lineHeight: '1.2'
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  container-max: 1280px
  gutter: 1.5rem
  margin-mobile: 1rem
  section-padding: 5rem
  stack-sm: 0.5rem
  stack-md: 1rem
  stack-lg: 2rem
---

## Brand & Style
The design system is engineered for the high-energy, fast-paced world of automotive rental and travel services. It targets modern travelers and commuters who value efficiency, transparency, and a touch of excitement. 

The aesthetic is **Corporate Modern with a Kinetic Edge**. It combines a clean, utilitarian structure with high-contrast accenting to evoke reliability and speed. The visual language is defined by spacious layouts, crisp photographic presentation of inventory, and an unapologetic use of vibrant red to drive action and emphasize urgency. The goal is to feel professional yet approachable, ensuring the user feels empowered to make decisions quickly.

## Colors
The palette is dominated by a clean white base and high-contrast "Slate" neutrals to ensure maximum legibility. 

- **Primary Red (#E11D48):** Used exclusively for primary calls to action, headings that require immediate attention, and critical brand accents. It represents energy and the "start" of a journey.
- **Secondary Slate (#0F172A):** Provides the professional grounding. Used for primary text and dark UI elements like navigation bars or footers.
- **Tertiary/Success Green (#22C55E):** Reserved for positive status indicators and specialized contact methods (e.g., WhatsApp integration), providing a secondary "high-trust" signal.
- **Surface Muted (#F8FAFC):** Used for background sections and card hover states to provide subtle depth without cluttering the interface.

## Typography
This design system utilizes **Inter** across all levels to maintain a systematic, highly legible, and neutral tone. 

The hierarchy relies on significant weight shifts rather than just size. Headlines use `700` and `800` weights to create a sense of authority. Body text is kept at `400` for long-form readability, while metadata (like car specifications) uses `500` or `600` weight at smaller sizes to remain distinct. Tight letter spacing is applied to large display text to maintain a modern, "compact" feel.

## Layout & Spacing
The layout follows a **12-column fluid grid** with a maximum content width of 1280px to ensure readability on ultra-wide monitors. 

- **Grid:** Columns are separated by a 24px (1.5rem) gutter. In card-heavy views (like car listings), a 4-column layout is used for desktop, 2-column for tablet, and 1-column for mobile.
- **Sectioning:** Distinct sections are separated by significant vertical padding (80px on desktop) to allow the "Why Choose Us" and "Testimonial" sections to breathe.
- **Mobile:** Margins compress to 16px. Search bars and primary filters should become sticky or full-width to accommodate touch targets.

## Elevation & Depth
The design system employs **Tonal Layers** and **Ambient Shadows** to create a clean, modern sense of depth.

- **Level 0 (Base):** Pure white background (#FFFFFF).
- **Level 1 (Cards/Inputs):** A subtle 1px border (#E2E8F0) combined with a very soft, diffused shadow (0px 4px 6px -1px rgba(0,0,0,0.05)).
- **Level 2 (Hover/Active):** When a user interacts with a card, the shadow should deepen (0px 10px 15px -3px rgba(0,0,0,0.1)) and the border color should shift slightly darker.
- **Overlays:** Modals and tooltips use a standard backdrop blur (8px) to maintain context while focusing user attention.

## Shapes
The shape language is consistently **Rounded** to soften the professional aesthetic and make the UI feel more approachable. 

- **Standard Elements:** Buttons, input fields, and car cards use a 0.5rem (8px) corner radius.
- **Large Containers:** Content sections and featured banners use a 1rem (16px) radius for a more modern, "app-like" feel.
- **Pills:** Category filters and tags (e.g., "SUV," "Automatic") use a fully rounded/pill shape to distinguish them from actionable buttons.

## Components
- **Buttons:** 
  - *Primary:* Solid #E11D48 with white text. High-contrast, bold weight.
  - *Secondary:* Outline #E11D48 or a ghost style with #0F172A text. 
  - *Special:* A dedicated green button for WhatsApp functionality to align with global messaging conventions.
- **Car Cards:** Should feature a white background, subtle shadow, and a clear vertical hierarchy: Image > Title > Specs (using icons) > Pricing > Dual CTA (Book & WhatsApp).
- **Inputs:** Search fields and date pickers use a light gray background or subtle border with clear icons (location pin, calendar) for quick scanning.
- **Chips/Filters:** Use a pill-shaped design. The "Active" state should be a dark neutral (#000000) with white text, while the "Inactive" state is a light gray with dark text.
- **Testimonial Cards:** These use a centered layout with star ratings in gold and a simple circular avatar for the client. 
- **Iconography:** Use line-style icons (e.g., Lucide or Heroicons) with a consistent 2px stroke weight to match the Inter typeface.