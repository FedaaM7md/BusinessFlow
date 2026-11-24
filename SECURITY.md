# Security notice — BusinessFlow

> **This is a demonstration application. It is not approved for production use.**
> Do not enter real customer, personal, payment, payroll, tax or confidential data.

A detailed threat model and review log lives in
[docs/SECURITY-REVIEW.md](docs/SECURITY-REVIEW.md).

## Security controls in place

- **Authentication:** hand-written session authentication, password hashing,
  inactive-account rejection, session regeneration on login, logout invalidation
  and CSRF-token rotation.
- **Login throttling:** POST login is limited to 5 attempts per minute per route
  and per email/IP; demo login is rate-limited per IP.
- **Password policy:** minimum 10 characters, mixed case and at least one number
  for created and reset passwords.
- **Password reset:** expiring single-use tokens (60 minutes), throttled requests,
  generic non-enumerable responses, log-mailer delivery in the demo. Demo
  accounts are excluded.
- **Authorization:** every business action calls a permission gate; routes are
  behind `auth` and guest routes behind `guest`. The Administrator role cannot be
  weakened.
- **Demo-account protection:** demo users cannot change email/password, be
  deactivated or deleted, or create/promote administrators.
- **CSRF:** all non-GET forms include `@csrf`; Laravel CSRF middleware is active.
- **Validation:** Form Requests validate every write endpoint; models use
  explicit `$fillable`.
- **Output escaping:** Blade escaped output for user data; the only raw markup is
  the fixed application-owned icon map.
- **CSV exports:** spreadsheet formula injection is neutralised.
- **Uploads:** PNG/JPG/WebP image validation, 2 MB limit (1 MB in demo), GD
  re-encoding to strip metadata, random filenames, SVG rejected.
- **Security headers:** Content-Security-Policy (`script-src 'self'` with the
  Alpine CSP build, `style-src 'self'` with SVG chart attributes — no
  `'unsafe-eval'` and no `'unsafe-inline'`), HSTS (on HTTPS),
  X-Content-Type-Options, X-Frame-Options, frame-ancestors, Referrer-Policy,
  Permissions-Policy, Cross-Origin-Opener-Policy and Cross-Origin-Resource-Policy.
- **Activity log:** login, logout, document, status, permission, settings and
  stock events with user, time, IP and changed fields; secrets are excluded.
- **Secrets:** `.env` is Git-ignored; `.env.example` has dummy values and
  `APP_DEBUG=false`.
- **Dependencies:** permissive licences only; `composer audit` and `npm audit`
  report zero vulnerabilities.

## Known security limitations

- The demo dataset is shared and resets hourly; there is no per-visitor sandbox.
- Demo names are Arabic-first; the English UI shows the same names for
  single-name entities.
- No two-factor authentication, email verification, WAF, malware scanning,
  database-at-rest encryption, monitoring or formal penetration test.
- SQLite is a local demo datastore; production database semantics are unreviewed.
- Demo credentials are public by design and must be removed before any real use.
- No backup/restore strategy, audit-log retention policy or incident-response plan.

## Reporting a concern

This is an internal demo build. Any deployment to a shared or public environment
requires a security review and the removal of demo accounts and sample data first.
