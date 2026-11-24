# Brand guide — BusinessFlow

BusinessFlow is a calm, data-dense accounting and operations ledger for Egyptian
small and medium businesses. The brand is the product name alone; the owner
credit appears only in the footer, small and quiet.

- Product name: **BusinessFlow** — the Latin wordmark is used in **both** languages.
- Arabic descriptor: **نظام إدارة الأعمال** (business management system).
- Page title format: `Page — BusinessFlow` in both locales (e.g. `الفواتير — BusinessFlow`).

## Logo

The mark is a **flow line passing through a ledger column**: an ink rounded tile
containing a ruled paper column, crossed by a single brass curve. It is drawn in
original SVG and used at `resources/views/components/logo.blade.php` and
`public/images/favicon.svg`.

- Ink tile: `#14213D`
- Ledger column: `#F7F5F0` with ink rules
- Flow curve: `#B08D57`

Do not place the mark on a busy photograph, recolour the flow curve, rotate the
tile, or add letterforms inside it. Keep clear space of at least half the tile
width.

## Colour palette

| Token | Hex | Use |
|---|---|---|
| Ink | `#14213D` | Primary text, sidebar, primary buttons, headings |
| Paper | `#F7F5F0` | App and page background |
| Brass accent | `#B08D57` | Links, focus rings, accents, active hints |
| Positive | `#2F7D5B` | Paid, accepted, in-stock, success |
| Negative | `#B4443C` | Overdue, cancelled, rejected, errors |
| Grid lines | `#E4DFD5` | Borders, table rules, dividers |

Ink tints (`brand-50…950`) and brass tints (`accent-50…700`) are defined as
Tailwind v4 design tokens in `resources/css/app.css`. Every foreground pairing
targets WCAG AA contrast on paper and white.

## Typography

| Role | Family | Licence |
|---|---|---|
| UI, Arabic + Latin | **IBM Plex Sans Arabic** | OFL-1.1 (self-hosted via `@fontsource/ibm-plex-sans-arabic`) |
| Numbers, codes, stamps | **IBM Plex Mono** | OFL-1.1 (self-hosted via `@fontsource/ibm-plex-mono`) |

- Body: 400 / 500; headings: 600 / 700.
- Money and counts use **tabular figures** (`.amount`, `font-variant-numeric`).
- Document codes such as `INV-2026-0001` use IBM Plex Mono (`.code`).
- **Arabic is a joining script.** Never apply letter-spacing/tracking or IBM Plex
  Mono to Arabic words: Plex Mono has no Arabic glyphs and tracking breaks the
  connections. Plex Mono + tracking is reserved for Latin codes, numbers and
  dates. An RTL reset forces `letter-spacing: normal` on Arabic pages.
- No Cairo, no Google Fonts, no CDN. Fonts are bundled by Vite.

## Layout personality

Compact sidebar with grouped modules, dense tables with sticky headers,
right-aligned money, thin grid-line borders, small radii (6 px), restrained
shadows. The login screen is a split with a **real rendered invoice preview**
pulled from demo data, not a marketing hero.

## Voice

Precise, formal-friendly, accountant’s vocabulary. Short sentences. Real numbers.
Say what a control does; do not sell it.

**Prefer:** “Record a partial payment against an issued invoice.”
**Avoid:** “Seamlessly elevate your financial workflow in one click.”

## Terminology glossary (ar / en)

| English | Arabic |
|---|---|
| Invoice | فاتورة |
| Quotation | عرض سعر |
| Payment | دفعة |
| Receivable | ذمة مدينة / مستحقات |
| Stock movement | حركة مخزون |
| On hand | المتاح |
| Tax rate | نسبة الضريبة |
| Ledger | دفتر |
| Demo workspace | مساحة عمل تجريبية |

Keep these terms consistent across screens, validation messages and exports.

## Attribution and copyright

- The product name stands alone. No owner name appears under the logo, in
  headers, page titles or login cards.
- Footer bottom line: `© 2026 BusinessFlow. All rights reserved.` /
  `© 2026 BusinessFlow. جميع الحقوق محفوظة.`, followed by a muted
  `Designed & built by Fedaa Mohamed` / `تصميم وتطوير: فداء محمد` linking to
  https://fedaamohamed.com.
- Legal ownership (LICENSE, COPYRIGHT.md, README credits, package metadata)
  remains with Fedaa Mohamed.
- The BusinessFlow name and logo are trademarks reserved by the owner.
- Demo business: **النيل للتوريدات المكتبية / Nile Office Supply** (fictional).

## Do / don’t

- **Do** show real product UI with clearly labelled demo data.
- **Do** use tabular figures and logical CSS properties (`ms/me`, `ps/pe`).
- **Do** keep one consistent icon set (the original SVG set in `components/icon`).
- **Don’t** use purple/indigo gradients, glassmorphism, sparkle badges, emoji
  icons or “trusted by” rows.
- **Don’t** use hype words (“seamless”, “unlock”, “بكل سهولة”, “حلول متكاملة”).
- **Don’t** put the owner’s name into page titles, H1s or demo business names.
