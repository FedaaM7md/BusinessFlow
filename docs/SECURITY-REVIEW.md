# Security review — BusinessFlow (release 1.1)

**Date:** 24 November 2026  
**Scope:** the BusinessFlow Laravel application in this repository (demo build).  
**Status:** hardening review complete; not a formal penetration test.

## 1. Threat model

| Asset | Why it matters |
|---|---|
| Customer, supplier and contact records | Personal and commercial data |
| Invoices, quotations, payments, expenses | Financial integrity and audit trail |
| User accounts, roles and permissions | Access control |
| Company settings and uploaded logo | Brand and configuration |
| Session and demo entry points | Account takeover / abuse |

**Actors:** anonymous visitor (guest), demo visitor, authenticated staff by role
(Admin / Accountant / Sales / Warehouse), and a malicious authenticated user
attempting lateral access.

**Trust boundaries:** browser → HTTP(S) → Laravel web middleware → Form Request →
controller → service/transaction → SQLite. File uploads cross a boundary at the
settings screen; exports leave the system as CSV.

## 2. What was checked

### Response headers
- `Content-Security-Policy` with `default-src 'self'`, `script-src 'self'` (no
  `'unsafe-inline'` and no `'unsafe-eval'`), `style-src 'self'` (no
  `'unsafe-inline'`), `img-src 'self' data:`, `font-src 'self'`,
  `frame-ancestors 'self'`, `object-src 'none'`, `form-action 'self'`,
  `base-uri 'self'`. The UI uses the Alpine CSP build and SVG chart attributes
  instead of inline `style`.
- `Strict-Transport-Security` (`max-age=31536000; includeSubDomains`) when the
  request is HTTPS or `APP_URL` is HTTPS.
- `X-Content-Type-Options: nosniff`, `X-Frame-Options: SAMEORIGIN`,
  `Referrer-Policy: strict-origin-when-cross-origin`,
  `Permissions-Policy` (geo/mic/camera/payment/usb disabled),
  `Cross-Origin-Opener-Policy: same-origin`,
  `Cross-Origin-Resource-Policy: same-origin`,
  `X-Permitted-Cross-Domain-Policies: none`.

### Authentication
- Session regeneration on login, logout invalidation and CSRF-token rotation.
- Login throttling: `throttle:5,1` on the route plus a per-email/IP limiter with
  a 60-second decay.
- Inactive accounts are rejected at authentication.
- Password policy for created/reset accounts: minimum 10 characters, mixed case
  and at least one number.
- Password reset: signed token flow using Laravel’s broker (60-minute expiry,
  60-second throttle), generic responses that do not enumerate accounts, and
  mail delivered through the log mailer in the demo. Demo accounts can never be
  reset.
- Demo login is rate-limited per IP.

### Authorization
- Every business controller action calls `authorizePermission()`; routes are
  behind `auth` and guest routes behind `guest`.
- Administrator role is always full-access and cannot be weakened.
- Demo accounts are protected from email/password changes, deactivation,
  deletion and admin escalation.
- IDOR is covered by tests for cross-customer contact access and forbidden
  role routes.

### Input / output
- Form Requests validate every write endpoint; models use explicit `$fillable`.
- Blade output is escaped; the only `{!! !!}` is the fixed, application-owned
  icon path map.
- CSV exports neutralise spreadsheet formula injection (cells starting with
  `=`, `+`, `-`, `@`, tab or carriage return are prefixed with `'`).
- Sort columns are whitelisted to prevent SQL injection through ordering.

### CSRF / sessions
- All non-GET forms include `@csrf`; Laravel CSRF middleware is active.
- Session cookies are `HttpOnly`, `SameSite=Lax` by default, and `Secure` when
  served over HTTPS (`SESSION_SECURE_COOKIE`).

### Uploads
- Logo uploads allow only PNG/JPG/WebP, are checked as real images, capped at
  2 MB (1 MB in demo mode), and re-encoded through GD to strip metadata and cap
  dimensions at 512 px. SVG is rejected. Files are stored under random names on
  the `public` disk.

### Secrets and dependencies
- `.env` is git-ignored; `.env.example` contains dummy values and
  `APP_DEBUG=false`.
- `composer audit` and `npm audit` both report **0 vulnerabilities**.

## 3. Findings fixed in this review

1. **Inline event handlers blocked a strict script CSP.** All `onclick` /
   `onsubmit` handlers were replaced with delegated listeners in `app.js`
   (`data-confirm`, `data-submit-form`, `data-print`, `data-back`).
2. **No Content-Security-Policy / HSTS / COOP.** Added the full header set in
   `SecurityHeaders`, with a dev-server exception only when Vite’s hot file is
   present.
3. **No password reset.** Added a throttled, expiring reset flow using the log
   mailer; demo accounts are excluded.
4. **Weak password policy.** Raised to 10 characters with mixed case and digits.
5. **Demo accounts could be edited or escalated.** Email/password edits,
   deactivation, deletion and admin promotion are now blocked for demo users.
6. **CSV formula injection.** Export cells are sanitised.
7. **Logo metadata / polyglot risk.** Uploads are re-encoded through GD.
8. **`'unsafe-eval'` and `'unsafe-inline'` were removed in the fix round.** The UI
   migrated to `@alpinejs/csp` (no runtime `Function`), inline event handlers were
   already delegated, and chart bars moved to SVG `height`/`width` attributes so
   `style-src` no longer needs `'unsafe-inline'`.

## 4. Remaining risks (documented, not hidden)

- **Shared demo dataset.** There is no per-visitor isolation; the demo resets
  hourly as a shared workspace. This is intentional and documented.
- **Demo names are Arabic-first.** Single-name entities (customers, suppliers,
  warehouses) and invoice line descriptions use Arabic names; the English UI
  shows the same names. Products remain bilingual via `name`/`name_ar`.
- No two-factor authentication, email verification, WAF, malware scanning,
  database-at-rest encryption, intrusion monitoring or formal penetration test.
- SQLite is a demo datastore; concurrency semantics must be revalidated for a
  production database.
- Demo credentials are public by design and must be removed before any real use.

## 5. Test evidence

Security behaviour is covered by
`tests/Feature/Security/T001HardeningTest.php`, `tests/Feature/Demo/DemoModeTest.php`
and the existing permission/IDOR tests, plus CSP, inline-style, date-format and
mobile-card assertions. Full suite: **80 passing tests, 1,809 assertions**
(24 Nov 2026). The CSP header test asserts no `'unsafe-eval'` and no
`'unsafe-inline'`. Dependencies: `composer audit` and `npm audit` clean.
