# ChanaaCar — Redesign handoff

Apply this redesign to the existing chanaacar.com codebase. The four files in `design/` are the visual reference (one per page/breakpoint). They are design mockups, not production code: read them for layout, spacing, copy and behaviour, then rebuild them with the project's existing framework, components, routing, data and i18n. Do not copy their `<x-dc>`, `sc-for` or `{{ }}` syntax.

| File | Page |
|---|---|
| `design/home-desktop.html` | Homepage, desktop (≥ 1024px) |
| `design/home-mobile.html` | Homepage, mobile (390px) |
| `design/product-desktop.html` | Car detail page `/cars/[slug]`, desktop |
| `design/product-mobile.html` | Car detail page, mobile (sticky bottom bar) |

## Rules
- Keep all existing functionality: booking submission, WhatsApp, car data source, prices, the 4 languages (FR/EN/ES/AR, including RTL for Arabic), SEO tags and URLs.
- Car data, options, locations and prices must come from the existing data/API, never hard-coded from the mockups.
- Work page by page, starting with design tokens, then the header/footer, then the homepage, then the car page. Run the app and compare against the mockups after each step.

## Design tokens
| Token | Value | Use |
|---|---|---|
| `--bg` | `#0F0F0E` | Page background |
| `--surface` | `#1B1B19` | Photo frames, image backgrounds |
| `--surface-2` | `#171716` | Conditions box |
| `--text` | `#EDEBE6` | Main text |
| `--text-2` | `#BDBAB3` | Secondary text |
| `--muted` | `#8E8C86` | Labels, meta |
| `--line` | `#262624` | Hairline dividers |
| `--line-strong` | `#3A3A37` | Input and button borders |
| `--accent` | `#FF8F2A` | Brand orange: primary CTA, small labels, numbers |
| `--on-accent` | `#0F0F0E` | Text on orange |

Fonts (Google Fonts): **Schibsted Grotesk** 400/500/600 for headings and body; **IBM Plex Mono** 400/500 for labels, specs, prices units, step numbers.

Type: large headings use weight 500, tight tracking (`letter-spacing: -0.04em`), line-height ~0.95. Labels are mono, 11–12px, uppercase, `letter-spacing: 0.08em`, color `--muted`.

## Visual principles (keep these — they are what make it not look like a template)
- Square corners everywhere (no border-radius), no shadows, no gradients.
- Structure comes from 1px hairlines (`--line`), not cards or boxes.
- Orange is used sparingly: primary button, category labels, step numbers. Never large orange backgrounds except the contact block and primary CTAs.
- Fleet on the homepage is a **spec-sheet list** (numbered rows), not a card grid.
- Buttons: primary = orange fill, square, label left + arrow icon right. Secondary = 1px border. Minimum height 44px.

## Homepage
1. Top utility bar: "Disponible 24h/24 · 7j/7", phone, language switcher.
2. Header: wordmark "Chanaa**Car**" (Car in accent), nav, outlined "Réserver" button.
3. Hero: large two-line headline, short paragraph, full-width photo, booking strip joined to the bottom of the photo (pickup, return, departure, return date-time, orange "Voir les véhicules").
4. Fleet list with category filters (Tous / SUV / Premium, with counts). Each row: index, thumbnail, category · year, name, specs (places, boîte, carburant, bagages), price per day, arrow link to the car page.
5. "Réservez en 4 étapes" — 4 columns separated by hairlines.
6. Service zones as a large typographic list.
7. Orange contact block with WhatsApp + phone.
8. Footer.

## Car page (`/cars/[slug]`)
1. Breadcrumb.
2. Gallery (main photo + 4 thumbnails) and info column: category · year, name, price/day, deposit, spec table, orange "Réserver ce véhicule" + WhatsApp, live estimate line, 4 reassurance points, conditions box (21 ans, permis 1 an, documents, assurance).
3. Booking form, reordered for conversion:
   - 01 Quand et où ? (pickup, return defaulting to "Même lieu", dates, day counter)
   - 02 Options — show 3 first (Siège enfant, Conducteur supplémentaire, Option plein carburant), "Voir X autres options" reveals the rest; insurance as a separate toggle
   - 03 Vos coordonnées — name, phone + "joignable sur WhatsApp" checkbox (replaces the separate WhatsApp field), email optional, licence number, optional note
4. Summary panel (sticky on desktop): rental line, each selected option, total, deposit shown separately, "Envoyer ma demande", pre-filled WhatsApp link (car name + number of days), "Et ensuite ?" 3 steps.
5. Reviews block (placeholders until real Google reviews are provided) + "Bon à savoir" FAQ accordion.
6. "Vous aimerez aussi" — 3 cars of the same category.

Mobile car page: same order, single column, and a **fixed bottom bar** with the live total and "Envoyer ma demande".

## Behaviour
- Total = days × (daily price + 50 MAD × selected options + 300 MAD if insurance). Use the real prices from the data, and compute days from the selected dates if the form has them.
- WhatsApp link: `https://wa.me/212666623645?text=` + URL-encoded message with car name and duration.
- Accessibility: real `<label>`s on every field, `aria-pressed` on toggle buttons, `aria-expanded` on the FAQ and "more options", visible focus ring in accent, all tap targets ≥ 44px.

## Content still needed from the owner
Car photos (4 per car: avant, profil, intérieur, coffre), Google rating and 2–3 reviews, cancellation policy.
