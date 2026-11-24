# Testing report — BusinessFlow

**Date:** 24 November 2026  
**Environment:** Windows, PHP 8.4.28 (`php`), Laravel 13.43.0, SQLite
`:memory:` for tests.

## Commands

```powershell
cd businessflow
php artisan test
npm run build
php vendor\bin\pint
composer audit
npm audit
```

## Final results

### PHPUnit / Laravel test suite

```
result: passed
tests: 87
passed: 87
failed: 0
assertions: 1904
```

Test groups include:

```
PASS  Tests\Unit\ExampleTest
PASS  Tests\Feature\ExampleTest
PASS  Tests\Feature\LocalizationTest
PASS  Tests\Feature\LocalizationKeyParityTest
PASS  Tests\Feature\UiSmokeTest
PASS  Tests\Feature\Auth\AuthenticationTest
PASS  Tests\Feature\Auth\PermissionsTest
PASS  Tests\Feature\CRM\CustomerTest
PASS  Tests\Feature\CRM\SupplierTest
PASS  Tests\Feature\Inventory\ProductAndInventoryTest
PASS  Tests\Feature\Sales\QuotationInvoiceTest
PASS  Tests\Feature\Sales\InvoicePaymentTest
PASS  Tests\Feature\Finance\ExpenseTest
PASS  Tests\Feature\Reports\ReportsAndDashboardTest
PASS  Tests\Feature\Settings\SettingsLocaleTest
PASS  Tests\Feature\Demo\DemoModeTest
PASS  Tests\Feature\Security\T001HardeningTest
PASS  Tests\Feature\PublicPagesTest
```

### Production asset build

```
vite v8.3.8 building client environment for production...
public/build/assets/app-*.css   ~78.6 kB  / gzip: ~12.4 kB
public/build/assets/app-*.js    ~55.8 kB  / gzip: ~19.7 kB
built in ~0.4s
```

All IBM Plex Sans Arabic and IBM Plex Mono font files are bundled locally.

### Code style and dependency audits

```
pint: passed
composer audit: No security vulnerability advisories found.
npm audit: found 0 vulnerabilities
```

## What is covered

- **Authentication:** login screen, guest redirects, session regeneration,
  inactive accounts, invalid credentials, logout audit records and rate limiting.
- **Password reset (release 1.1):** generic responses, expiring tokens, demo accounts
  excluded, strong password policy on reset.
- **RBAC:** Admin access, forbidden routes for Sales/Warehouse/Accountant,
  immediate permission-matrix changes and admin permission lock.
- **Demo mode (release 1.1):** one-click role entry, 404 when disabled, demo banner,
  locked email/password, blocked deletion and blocked admin escalation, reset
  command no-op when disabled.
- **Security headers (release 1.1):** CSP present and same-origin for scripts, **no
  `'unsafe-eval'` and no `'unsafe-inline'`**, HSTS for HTTPS, COOP/Referrer/
  Permissions headers; the dashboard renders with no inline `style` attributes.
- **Mobile & dates (fix round):** list pages render mobile card markup; filters
  render as a bottom sheet; date fields use the explicit `YYYY-MM-DD` text format
  (no native `mm/dd/yyyy`).
- **Dashboard widths (rounds 3–4):** at 1280/1366/1440/1536 the KPI grid uses a
  6-column 3+2 layout with the second row spanning the full width (no gap); every
  KPI value is 32px tall (one line), the currency shares the amount's baseline
  and no tile overflows.
- **Mobile drawer (round 4):** closed by default on every navigation, slides from
  the logical start side, width ≤85vw, moves focus to the close button and traps
  Tab; Esc, overlay tap and link click close it. Verified at 375/390/768 in
  Arabic and English, plus the fixed desktop sidebar at 1280.
- **Public surface (addendum):** About/Privacy/Terms return 200, footer credit
  links to https://fedaamohamed.com, copyright names the product, Arabic/English
  credits are correct, and Open Graph metadata is present.
- **Realistic demo business (addendum):** the demo company is
  النيل للتوريدات المكتبية / Nile Office Supply with 26 customers, 19 products,
  24 quotations, 54 invoices (paid/partial/issued/draft/cancelled), 30 payments
  and 36 expenses.
- **CSV safety (release 1.1):** formula-injection cells are neutralised on export.
- **Localization:** every literal `__('...')` key exists in both locales; full
  ar/en key-set parity is asserted; brand name is standalone.
- **CRM:** customer CRUD, generated codes, validation, filters/sorting, delete
  protection, contacts CRUD and cross-customer access protection.
- **Suppliers:** CRUD, validation, soft deletion and expense-protected deletion.
- **Catalog & inventory:** product/service CRUD, integer conversion, categories,
  warehouses, stock in/out/adjustment/transfer, insufficient-stock rejection and
  low-stock page.
- **Sales:** quotation totals, validation, status transitions, conversion copy,
  duplicate-conversion prevention, numbering uniqueness, invoice totals/tax,
  issue/cancel with stock movements, immutable issued invoices, print views with
  mixed Arabic/English descriptions and correct `dir`.
- **Payments & expenses:** partial/full payments, balance/status derivation,
  overpayment rejection, draft-payment rejection, category and expense CRUD.
- **Reports & dashboard:** live totals, aging buckets, stock valuation, CSV
  export, dashboard rendering and low-stock path.
- **Settings:** ar/en switching with server-rendered `dir`, settings validation,
  logo upload validation and GD re-encoding, numbering prefix effect, user CRUD,
  password hashing and self/last-admin protections.
- **UI smoke:** every primary index/create/show/edit/print page renders for an
  administrator.

## Screenshots

Screenshots were re-captured on 24 November 2026 from the running demo build with headless Chrome (desktop 1440×900, mobile 390×844, Arabic and English). The current files are in `docs/screenshots/` and are shown in the README.

## Notes

- Tests use `RefreshDatabase` and a fresh in-memory SQLite database. The base
  test case seeds roles and company settings; demo data is loaded only in the
  demo tests.
- No tests were skipped, deleted or weakened.
- Browser print-to-PDF is the supported PDF route; there is no server-side PDF
  renderer (see ROADMAP.md).
