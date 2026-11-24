# Technical decisions — BusinessFlow

Dated 23 November 2026 (release 1.0), extended 24 November 2026 (release 1.1). All decisions
below are reflected in code/tests.

## D-001 — Custom session authentication instead of Breeze

**Decision:** implement login/logout directly with a Form Request, controller, throttling, session regeneration and activity logging.

**Why:** it keeps the authentication surface small and avoids adding a starter-kit UI that would need to be fully replaced. It also makes the throttling/regeneration/audit behaviour explicit and testable.

## D-002 — Gates rather than one policy class per model

**Decision:** register a Laravel Gate for each permission key and call `authorizePermission()` in every business action.

**Why:** the product needs a permission matrix, not record-level ownership rules. A Gate per capability maps directly to the editable matrix. Every controller action still performs an explicit authorization check, so the requirement for action-level protection is met without duplicating near-identical policies.

## D-003 — Integer minor units and BCMath

**Decision:** store all money as integer piasters; parse decimal input as strings; use BCMath for line math.

**Why:** floats cause rounding defects in financial documents. No monetary value passes through a float in computation. Quantity is decimal text because inventory quantities are not money.

## D-004 — Basis points for tax rates

**Decision:** store tax rates as integer basis points (1400 = 14%).

**Why:** allows fractional percentages without floats and keeps line tax calculations deterministic. Forms still present familiar percentages.

## D-005 — Proportional, pre-tax document discount

**Decision:** allocate a document-level discount across line subtotals proportionally, then calculate per-line tax on the discounted amount.

**Why:** the brief requires totals `subtotal − discount + tax` and per-line tax. Proportion allocation preserves the relationship and produces accurate per-line tax. A residual-allocation pass guarantees the sum of line discounts equals the document discount.

## D-006 — Warehouses are selectable on invoices

**Decision:** add `warehouse_id` to invoices (default preselected) and deduct tracked stock only from that warehouse.

**Why:** the brief requires on-hand per warehouse and asks invoice issue to create stock-out movements. Without a fulfilment warehouse, multi-warehouse stock would be ambiguous. Quotations do not need a warehouse because they do not affect stock.

## D-007 — Stock ledger, never mutable stock counts

**Decision:** derive on-hand from signed `stock_movements`; cancellation appends reversing entries.

**Why:** the brief explicitly requires a ledger. Reversals keep the audit trail intact instead of deleting evidence. Transfers produce paired rows with a shared UUID.

## D-008 — Issued invoices are immutable; payments are immutable

**Decision:** invoices can be deleted only as drafts; after issue, only cancellation is allowed. Payments cannot be edited/deleted in the UI.

**Why:** financial audit integrity. Cancellation is blocked after any payment. Corrections to payments require an administrator intervention outside this demo scope; this is documented in ROADMAP.

## D-009 — Printable HTML instead of a server PDF library

**Decision:** dedicated A4 print views with print CSS and a browser print button. No PDF generation dependency.

**Why:** every PHP PDF library considered has licensing or Arabic shaping trade-offs (GPL/LGPL or complex font shaping). The task explicitly allows a printable/PDF view, and browser Print → Save as PDF gives correct mixed RTL/LTR shaping without adding a non-permissive dependency. This limitation is recorded in ROADMAP.

## D-010 — SQLite only, service transactions

**Decision:** use the required SQLite database; wrap multi-step writes in `DB::transaction`; use a locked sequence per document type/year.

**Why:** the environment mandates SQLite. Transactions protect invoice + lines + ledger writes. SQLite serializes writes; `lockForUpdate` documents the intent for future migration to a server database.

## D-011 — JSON permissions on four seeded roles

**Decision:** four system roles (Admin, Accountant, Sales, Warehouse) with editable JSON permission arrays. No custom role creation.

**Why:** the brief names these four roles and requires an editable matrix. A JSON array is compact, easy to audit in activity logs and immediately effective in Gates. Custom roles are on the roadmap.

## D-012 — Original visual identity and no CDN

**Decision (updated in release 1.1):** a ledger-inspired identity — a “flow through a
ledger column” SVG mark, ink `#14213D` / paper `#F7F5F0` / brass `#B08D57` /
positive `#2F7D5B` / negative `#B4443C` / grid `#E4DFD5`, self-hosted **IBM Plex
Sans Arabic** and **IBM Plex Mono** via `@fontsource/*`, pure CSS/SVG charts, no
external analytics, CDN or icons.

**Why:** the product is a serious accounting/operations tool; the ledger
metaphor (paper, ruled columns, brass, tabular figures) tells that story directly
rather than through generic SaaS gradients. String parity and smoke tests guard
the UI, and a full brand guide lives in `docs/BRAND.md`.

## D-013 — Single-company settings, per-user locale

**Decision:** one `company_settings` row and a `locale` column per user. Company default locale seeds the guest experience; an authenticated user’s saved locale takes precedence.

**Why:** the brief requires company profile/default language plus per-user language preference. Multi-tenant/multi-company scope is explicitly not required.

## D-014 — Demo data is explicit and computed dashboards are live

**Decision:** demo records use realistic neutral Egyptian business names
(e.g. “شركة الواحة للتوزيع”, “Nile Supplies Co.”), a public `@businessflow.test`
email domain, `is_demo` flags and globally visible “Demo mode” labelling; all
dashboard/report numbers are SQL/Eloquent aggregations, never hard-coded.

**Why:** the release 1.1 brief asks for realistic demo names that are still clearly
labelled. Dashboard values remain computed, and the demo database itself is not
committed.

## D-015 — Tests for workflows and a UI smoke sweep

**Decision:** in addition to module CRUD tests, test financial arithmetic, stock effects, permission changes, localization, mixed-direction print output and every primary admin page.

**Why:** prevents “tests pass but page is broken” and protects the highest-risk calculations. release 1.1 final result: 77 passing tests, 1,796 assertions.

---

## release 1.1 decisions (24 November 2026)

## D-016 — Product-first naming, quiet ownership

**Decision:** the brand is the Latin wordmark **BusinessFlow** in both languages,
with the Arabic descriptor **نظام إدارة الأعمال**. The owner credit appears only
in the footer bottom line as “Designed & built by Fedaa Mohamed” /
“تصميم وتطوير: فداء محمد” linking to https://fedaamohamed.com; the copyright line
names the product. Page titles use `Page — BusinessFlow` (e.g.
`الفواتير — BusinessFlow`). No owner name appears under the logo or in titles.

**Why:** the release 1.1 brief requires the product to stand alone while keeping
ownership visible and quiet, and forbids owner names in titles, H1s and seeded
data names.

## D-017 — Demo mode is a first-class, env-controlled feature

**Decision:** `DEMO_MODE` (default true) enables one-click role login, a
persistent banner and an hourly `demo:reset`. A boolean `users.is_demo` marks the
seeded accounts. Demo login is rate-limited per IP. When `DEMO_MODE=false`, entry
points disappear and `POST /demo/login` returns 404.

**Why:** reviewers need a zero-friction entry, while a private install must be
able to switch the whole surface off. An explicit flag is simpler and safer than
sniffing email domains.

## D-018 — Demo accounts are protected, not privileged

**Decision:** demo accounts cannot change email or password, be deactivated or
deleted, and demo users cannot create or promote administrators. Validation
errors are collected so the user sees all restrictions at once.

**Why:** the demo must remain reproducible and must not become a path to an
unexpected administrator account.

## D-019 — Strict script CSP with a documented Alpine trade-off

**Decision:** remove every inline `onclick`/`onsubmit` handler in favour of
delegated listeners (`data-confirm`, `data-submit-form`, `data-print`,
`data-back`) so `script-src` needs no `'unsafe-inline'`. Alpine still requires
`'unsafe-eval'`, and dynamic chart styles require `style-src 'unsafe-inline'`.

**Why:** eliminating inline handlers is the highest-value CSP win here. Alpine’s
runtime expression compiler cannot run without `'unsafe-eval'`; migrating to the
Alpine CSP build is recorded as the next hardening step in ROADMAP and
docs/SECURITY-REVIEW.md instead of being silently ignored.

## D-020 — Real password reset with a strong policy

**Decision:** implement the Laravel password broker (60-minute expiry, throttled
requests, generic responses, log mailer) and require 10+ characters with mixed
case and a number. Demo accounts are excluded from reset.

**Why:** the brief lists password reset and a password policy under auth
hardening. Generic responses prevent account enumeration; log mail keeps the demo
self-contained.

## D-021 — Upload re-encoding and CSV formula-injection sanitation

**Decision:** re-encode uploaded logos through GD (strip metadata, cap at 512 px)
and prefix CSV cells that begin with `=`, `+`, `-`, `@`, tab or carriage return
with an apostrophe.

**Why:** both are cheap, testable defences against real classes of attack
(polyglot/metadata payloads and spreadsheet formula injection) that do not add
dependencies.

## D-022 — English validation messages added for true key parity

**Decision:** add `lang/en/validation.php` mirroring the Arabic key set, and add
a test that flattens and compares every ar/en translation key.

**Why:** English validation messages were previously missing (keys could leak
into the UI), and the brief requires identical key sets. The parity test makes
future drift a test failure.

## D-023 — Alpine CSP build and SVG charts (no eval, no inline styles)

**Decision:** use `@alpinejs/csp` (MIT) instead of the standard Alpine build,
pass line-item data through `data-*` attributes (the CSP build cannot evaluate
`JSON.parse` in expressions), and render chart bar heights/widths as SVG
attributes. Remove `'unsafe-eval'` and `'unsafe-inline'` from the CSP.

**Why:** the standard Alpine build needs `unsafe-eval`, and Blade-rendered inline
`style` attributes need `unsafe-inline`. Both are avoidable with a CSP-friendly
Alpine build, delegated event handlers and SVG. A feature test asserts the CSP
no longer contains either token, and a browser audit confirmed no CSP violations.

## D-024 — Arabic typography guardrails

**Decision:** never apply IBM Plex Mono or tracking to Arabic. `.ledger-stamp`
uses the UI sans face, `.code` is Latin-only, and an `[dir="rtl"]` rule forces
`letter-spacing: normal`. Plex Mono + tracking remain for Latin codes/numbers.

**Why:** Arabic is a joining script; mono fallbacks and letter-spacing break the
connections. The guardrail prevents future regressions.

## D-025 — Mobile lists as cards, filters as a sheet

**Decision:** on phones (`< 768px`) every list page renders stacked cards
(number, party, date, amount, status, actions) instead of a table, and filters
collapse behind a “تصفية” button. Desktop keeps the dense table. Codes use
`white-space: nowrap`. Tap targets are at least 44px.

**Why:** the reviewer found the filter stack pushing lists off-screen and tables
breaking at 375px. Cards and a collapsed filter keep the primary record visible
and remove horizontal scrolling.

## D-026 — Consistent date field

**Decision:** replace native `type="date"` inputs with a shared text component
that uses the explicit `YYYY-MM-DD` format, `inputmode="numeric"` and a matching
placeholder, validated server-side.

**Why:** native date inputs display the browser's `mm/dd/yyyy` format even in the
Arabic UI. A single explicit format is consistent across locales and avoids the
mismatch; the trade-off is losing the native picker, which is documented.

## D-027 — Addendum: owner credit, realistic demo business, public pages

**Decision:**
- Removed “by Fedaa” / “من فداء” from logos, headers and login cards. The footer
  bottom line reads “© 2026 BusinessFlow. All rights reserved.” plus a muted
  “Designed & built by Fedaa Mohamed” / “تصميم وتطوير: فداء محمد” linking to
  https://fedaamohamed.com. Legal ownership stays in
  LICENSE/COPYRIGHT.md/README/package metadata.
- Demo company is **النيل للتوريدات المكتبية / Nile Office Supply**, seeded with
  realistic data across six months: 26 customers, 19 office-supply products,
  24 quotations, 54 invoices (draft/issued/partially paid/paid/cancelled),
  30 payments and 36 expenses. Bulk order quantities make the dashboard and
  reports look alive; two products remain low-stock for the alert.
- Added public **About** (story, values, contact, FAQ, changelog), **Privacy** and
  **Terms** pages with Open Graph metadata and a locally generated
  `public/images/og.png`. The footer shows the version and a tiny demo note.
- Mobile filters are a real bottom sheet (overlay, close button, fixed panel) and
  the login demo entry sits above the credentials form on phones.
- Activity actions are localized; Arabic pages no longer expose raw English data
  labels; product/category names use `displayName()`.
- No new libraries; the OG image is generated from the project’s own HTML/CSS
  with the local browser.

**Why:** the owner asked for credits that look like a professional studio’s work,
a believable fictional business, and a product-like public surface without adding
dependencies or AI disclosure.

## D-029 — Arabic currency always in the brand sans face

**Decision:** `.amount` declares `font-family: var(--font-sans)` alongside
`font-variant-numeric: tabular-nums`; `<x-money>` keeps the number and currency
in separate sans spans (currency at `0.68em`); the login invoice preview total
uses `<x-money>` instead of `.code`. Monospace is reserved for Latin-only codes,
document numbers, SKUs, dates and IP addresses.

**Why:** setting “ج.م” in IBM Plex Mono breaks the Arabic letterforms, invites a
Latin fallback and looks foreign next to the product. Tabular digits in the
brand sans face keep table columns aligned without ever setting Arabic text in a
mono font.

## D-028 — CSS-first mobile drawer and a gap-free KPI grid

**Decision:** the mobile navigation drawer's closed state is enforced by CSS
before Alpine boots (`visibility: hidden`, logical off-screen transform), slides
from the logical start side, is capped at `85vw`, moves focus to the close button
and traps Tab. Esc, the overlay and any link close it and return focus to the
trigger. The dashboard KPI grid uses six columns at `xl`: the first three tiles
span two columns, the final two span three, so the 3+2 layout fills the full
width at 1280–1536 with no empty cell.

**Why:** round 4 found the drawer could appear open/flipped on navigation and the
3+2 grid left an empty gap. A CSS-first closed state removes any pre-Alpine flash
and direction ambiguity; the six-column layout keeps all five KPI values
single-line and balanced at 1280/1366/1440.
