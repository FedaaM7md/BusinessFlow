# Third-party notices — BusinessFlow

BusinessFlow is built from original application code. It does **not** copy or
port code from Dolibarr, Odoo, ERPNext or any other ERP/business application.

Only permissive dependencies were selected. Versions below are those installed
on 24 November 2026.

## PHP / Composer direct dependencies

| Package | Version | License | Purpose |
|---|---|---|---|
| laravel/framework | 13.43.0 | MIT | Application framework |
| laravel/tinker | 3.2.0 | MIT | Local REPL tooling |
| fakerphp/faker | 1.24.1 | MIT | Test/demo data generation (dev) |
| laravel/pail | 1.3.0 | MIT | Local log viewer (dev) |
| laravel/pao | 1.1.6 | MIT | Test output adapter (dev) |
| laravel/pint | 1.32.3 | MIT | Code formatter (dev) |
| mockery/mockery | 1.6.15 | BSD-3-Clause | Test doubles (dev) |
| nunomaduro/collision | 8.9.6 | MIT | Console error handling (dev) |
| phpunit/phpunit | 12.5.45 | BSD-3-Clause | Test runner (dev) |
| laravel/laravel | 13.11.0 skeleton | MIT | Initial project skeleton only |

Laravel brings transitive MIT/BSD packages (Symfony, PSR, Carbon, Flysystem,
etc.). `nette/schema` and `nette/utils` are dual-licensed
**BSD-3-Clause OR GPL-2.0-only OR GPL-3.0-only**; this project uses them under
the BSD-3-Clause option. No GPL/AGPL/LGPL-only runtime package is used.

## Frontend direct dependencies

| Package | Version | License | Purpose |
|---|---|---|---|
| @alpinejs/csp | 3.17.5 | MIT | CSP-friendly UI interactions (no `'unsafe-eval'`) |
| @fontsource/ibm-plex-sans-arabic | 5.3.0 | MIT package; OFL-1.1 font | Self-hosted UI font |
| @fontsource/ibm-plex-mono | 5.3.0 | MIT package; OFL-1.1 font | Self-hosted numeric/code font |
| @tailwindcss/vite | 4.3.5 | MIT | Tailwind integration for Vite |
| laravel-vite-plugin | 3.2.1 | MIT | Laravel asset bundling |
| tailwindcss | 4.3.5 | MIT | Utility CSS framework |
| vite | 8.3.8 | MIT | Frontend build tool |

IBM Plex Sans Arabic and IBM Plex Mono are part of the IBM Plex typeface
family, licensed under the SIL Open Font License 1.1. Font files are bundled
locally — no font CDN is used.

## Not used

- No Dolibarr, Odoo, ERPNext or other ERP source code.
- No charting dependency; dashboard charts are original CSS/SVG.
- No third-party icon font; the SVG icon set is original application artwork.
- No analytics, tracker, CDN or remote font requests.
- No GPL/AGPL/LGPL-only dependency.
- No payment/SMS/email provider SDK.

## License of this application

The application itself is proprietary and not open source. See
[LICENSE](LICENSE) and [COPYRIGHT.md](COPYRIGHT.md). Third-party
libraries and fonts keep their own licences; ownership is never claimed over
third-party code or fonts.
