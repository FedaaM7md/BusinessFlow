# Architecture — BusinessFlow

## 1. Overview

BusinessFlow is a server-rendered Laravel monolith. Blade renders the UI,
Tailwind (with self-hosted IBM Plex Sans Arabic / IBM Plex Mono) provides the
visual system, Alpine handles small interactions, and SQLite stores all state.
Business logic lives in module services; controllers handle HTTP orchestration
and authorization. Demo mode and security headers are first-class middleware /
configuration.

```
HTTP request
  → web middleware (CSRF, session, locale, security headers)
  → auth / permission gate
  → Form Request validation
  → module controller
  → module service (transaction)
  → Eloquent model / ledger writes
  → activity log
  → redirect or Blade view
```

## 2. Module layout

```
app/
├── Console/Commands/ResetDemoWorkspace.php   # demo:reset
├── Http/
│   ├── Controllers/Controller.php            # permission + sort helpers
│   ├── Controllers/LocaleController.php
│   └── Middleware/                           # SetLocale, SecurityHeaders
├── Models/User.php                           # framework auth model (+ is_demo)
└── Modules/
    ├── Auth/       AuthController, PasswordResetController, Requests
    ├── Core/       CompanySetting, ActivityLog, services (Money, numbering, dashboard)
    ├── Users/      Role, user/role controllers, requests, services
    ├── CRM/        Customer, Contact, controllers, services
    ├── Suppliers/  Supplier
    ├── Catalog/    Product, Category
    ├── Inventory/  Warehouse, StockMovement, services
    ├── Sales/      Quotation(+Line), Invoice(+Line), Payment, services
    ├── Expenses/   Expense, ExpenseCategory
    ├── Reports/    ReportService and CSV controller (formula-injection safe)
    └── Settings/   company settings controller/service (GD logo re-encoding)
```

Views are grouped by feature under `resources/views/`; print layouts are isolated
under `resources/views/print/` and `layouts/print.blade.php`. `config/demo.php`
holds demo-mode switches and `routes/console.php` schedules `demo:reset`.

## 3. Data model

Main tables:

- `roles`, `users` (role, locale, active, `is_demo`, last login)
- `company_settings` (single company profile and document defaults)
- `document_sequences` (type + year + last number)
- `customers`, `contacts`, `suppliers`
- `categories`, `products`, `warehouses`, `stock_movements`
- `quotations`, `quotation_lines`, `invoices`, `invoice_lines`, `payments`
- `expense_categories`, `expenses`
- `activity_logs` (user, module, action, subject morph, changed fields, IP)
- Framework tables: sessions, cache, jobs, migrations, `password_reset_tokens`.

Every monetary column ends in `_minor` and is an integer. Tax rates are stored in
basis points (1400 = 14%). Quantities are decimal strings at the boundary.

## 4. Document lifecycle

### Quotations

`draft → sent → accepted | rejected | expired`

Only drafts are editable/deletable. Only an `accepted` quotation with no existing
invoice can be converted in a transaction; conversion copies line snapshots,
recalculates totals and creates a **draft** invoice.

### Invoices

`draft → issued → partially_paid → paid` plus `cancelled`.

- Issue checks aggregated stock, writes one signed stock-out movement per tracked
  line, and sets status to `issued`.
- Issued invoices are never deleted. Cancellation requires a reason, is blocked
  after any payment, and appends reversing stock movements.
- Payments derive status; balance is always `max(0, total − sum(payments))`.

## 5. Money and tax

`Core\Services\Money` converts decimal input strings to integer piasters without
floats. `DocumentCalculator` uses BCMath and computes per-line subtotal,
proportional pre-tax discount allocation, per-line tax, and document totals. The
document equation and the sum of line totals stay consistent. Mixed
Arabic/English descriptions are stored verbatim and rendered with `dir="auto"`.

## 6. Document numbering

`DocumentNumberService` runs inside a transaction: insert-or-ignore the
`(type, year)` sequence row, lock it, increment and format `PREFIX-YEAR-0001`.
Prefixes come from company settings and uniqueness is enforced by indexes.

## 7. Inventory ledger

`stock_movements` is the source of truth. Quantity is signed (in positive, out
negative, adjustment either, transfer paired rows). On-hand is `SUM(quantity)`,
optionally per warehouse. Invoice issue checks availability first; cancellation
adds reversals rather than deleting rows. Low stock compares the movement sum with
the product threshold, including products with zero movements.

## 8. Authorization

Permissions are declared in `config/permissions.php` and translated under
`permissions.*`. `AppServiceProvider` registers one Gate per permission. Every
business action calls `authorizePermission()`. Roles store a JSON permission array;
Administrator is always full-access and cannot be weakened. The matrix UI logs the
before/after arrays. Demo users are additionally blocked from email/password
changes, deactivation, deletion and admin escalation.

## 9. Demo mode

`config/demo.php` reads `DEMO_MODE` and `DEMO_RESET_HOURLY`.

- The login screen renders role buttons from seeded `is_demo` users; `POST
  /demo/login` signs in one role without a password, rate-limited per IP.
- Demo data is Arabic-first: customers, suppliers, warehouses and invoice line
  descriptions use Arabic names; products keep `name`/`name_ar` for bilingual
  display.
- A persistent banner appears on every authenticated page.
- `php artisan demo:reset` runs `migrate:fresh --seed`, restoring the labelled
  baseline; it is scheduled hourly and is a no-op when demo mode is off.
- With `DEMO_MODE=false`, the entry points disappear and the demo route returns
  404.

## 9b. Public pages and metadata

Three public routes (`/about`, `/privacy`, `/terms`) render through
`layouts/public.blade.php` and the `pages.*` views. Content is bilingual in
`lang/{en,ar}/pages.php`. `partials/meta.blade.php` provides the page title,
meta description, Open Graph tags and favicon for the app, guest and public
layouts; `public/images/og.png` is a locally generated 1200×630 card. The footer
carries the product copyright, a footer-only owner credit linking to
https://fedaamohamed.com, the version and a small demo note.

## 9c. Responsive lists

List pages render a dense table on desktop and stacked cards below `md`. Filters
collapse into a mobile bottom sheet (overlay, close button, fixed panel) and are
always visible on desktop. Codes use `nowrap`, and tap targets are at least
44 px on phones.

The navigation drawer is closed by CSS before Alpine boots, slides from the
logical start side, is capped at `85vw`, moves focus inside and traps Tab, and
closes on Esc, overlay tap or link click. On desktop it is always visible; on
phones it never persists an open state across page loads.

## 10. Localization

`SetLocale` runs on every web request: authenticated user locale wins, then the
session, then the company default, then `app.locale`. `<html lang dir>` is
server-side. All interface strings live in `lang/en` and `lang/ar`; one test
checks every literal translation key and another asserts full ar/en key-set
parity. The language switch posts to a CSRF-protected endpoint and updates both
the session and the user record. Logical Tailwind utilities keep both directions
correct.

## 11. Security

- `SecurityHeaders` sets CSP (`script-src 'self'` with the Alpine CSP build,
  `style-src 'self'` with SVG chart attributes — no eval and no inline styles),
  HSTS on HTTPS, X-Content-Type-Options, X-Frame-Options/frame-ancestors,
  Referrer-Policy, Permissions-Policy, COOP and CORP.
- Inline event handlers were replaced by delegated listeners and line-item data
  is passed through `data-*` attributes, so the CSP build runs without eval.
- CSRF on all forms, Form Requests on writes, fillable models, escaped Blade.
- Password reset uses expiring tokens and the log mailer; the password policy is
  10+ characters with mixed case and numbers.
- Logo uploads are type-checked, size-limited, GD-re-encoded and stored under
  random names; SVG is rejected.
- CSV exports neutralise formula injection.
- The activity log records meaningful events and excludes secrets.

See [SECURITY.md](../SECURITY.md) and [SECURITY-REVIEW.md](SECURITY-REVIEW.md).

## 12. Tests

Feature tests cover auth/throttling, permissions and IDOR, demo mode, security
headers/CSP, password policy and reset, CSV safety, each module's CRUD and
validation, quotation→invoice conversion, invoice totals/tax, numbering, stock
issue/cancel, payments, reports, settings and localization parity. Final result:
77 passing tests, 1,796 assertions.
