# Local installation — BusinessFlow

This document records the exact local setup used for this build on **Windows**
with the fixed toolchain. Never use `C:\php`.

## Environment used

| Tool | Version / path |
|---|---|
| PHP | 8.4.28 — `php` |
| Composer | `composer` |
| Laravel | 13.43.0 |
| Node.js | 24.12.0 |
| npm | 11.6.2 |
| Vite | 8.3.8 |
| Database | SQLite — `database/database.sqlite` |
| Git | 2.51.1 |

Required PHP extensions are enabled in `php.ini`: `pdo_sqlite`, `sqlite3`,
`zip`, `mbstring`, `intl`, `openssl`, `fileinfo`, `gd`, `bcmath`.

## Fresh setup (from a clone)

From `businessflow`:

```powershell
composer install --no-interaction
Copy-Item .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed --no-interaction
php artisan storage:link
npm install
npm run build
```

The project was originally created with:

```powershell
composer create-project laravel/laravel businessflow --no-interaction
```

`migrate:fresh --seed` recreates every table and loads:

- roles and the permission matrix (`RoleSeeder`)
- the demo company profile **النيل للتوريدات المكتبية / Nile Office Supply**
  (`SettingSeeder`)
- a realistic six-month demo dataset (`DemoSeeder`): 26 customers, 19 office
  supply products, 24 quotations, 54 invoices in varied states, 30 payments and
  36 expenses, plus stock movements and two low-stock products.

Public About, Privacy and Terms pages are available at `/about`, `/privacy` and
`/terms` without signing in.

## Configuration

`.env` is ignored by Git and is never committed. `.env.example` contains dummy
values only, with `APP_DEBUG=false`. Important local values:

```dotenv
APP_NAME="BusinessFlow"
APP_ENV=local
APP_DEBUG=false
APP_LOCALE=ar
APP_FALLBACK_LOCALE=en
DB_CONNECTION=sqlite
SESSION_DRIVER=database
DEMO_MODE=true
DEMO_RESET_HOURLY=true
```

## Demo mode

When `DEMO_MODE=true` (the default locally):

- the login screen shows **Try the demo** with a role picker (Admin, Accountant,
  Sales, Warehouse) that signs in immediately without a password;
- a persistent banner states that data resets automatically;
- demo accounts cannot change email/password, be deactivated or deleted, and
  demo users cannot create or promote administrators;
- `php artisan demo:reset` restores the seeded baseline and is scheduled hourly
  via `routes/console.php`.

Set `DEMO_MODE=false` for a private installation: demo entry points disappear and
`POST /demo/login` returns 404. The scheduler only runs when `demo:reset` is
enabled and a scheduler is running (`php artisan schedule:work` locally, or a
cron entry calling `schedule:run`).

Manual reset:

```powershell
php artisan demo:reset
```

## Demo accounts

These accounts exist **only after seeding**. They are for demonstration and must
never be used with real data. Password for all: `password`.

| Role | Email |
|---|---|
| Administrator | `admin@businessflow.test` |
| Accountant | `accountant@businessflow.test` |
| Sales | `sales@businessflow.test` |
| Warehouse | `warehouse@businessflow.test` |

## Password reset (demo)

Real (non-demo) accounts can request a reset link at `/forgot-password`. The demo
uses the log mailer (`MAIL_MAILER=log`), so the link is written to
`storage/logs/laravel.log` rather than sent. Tokens expire after 60 minutes.
Demo accounts are intentionally excluded from password reset.

## Running locally

```powershell
cd businessflow
php artisan serve
```

Open the URL printed by Artisan (normally `http://127.0.0.1:8000`). If a port is
assigned by your environment, use:

```powershell
php artisan serve --port=<assigned-port>
```

## Verification

```powershell
php artisan test
npm run build
php vendor\bin\pint
```

All must finish successfully. Recorded results are in [TESTING.md](TESTING.md).

## Rebuilding the frontend

```powershell
npm install
npm run build
```

Compiled assets are written to `public/build/` (ignored by Git). The UI has no
CDN dependency; IBM Plex Sans Arabic and IBM Plex Mono are bundled from their
OFL-licensed packages.

## Logo uploads

Settings stores the company logo on the `public` disk. The storage link is
required:

```powershell
php artisan storage:link
```

Uploads are restricted to PNG/JPG/WebP (rejected if not a real image), capped at
2 MB (1 MB in demo mode), and re-encoded through GD to strip metadata.

## Ownership

BusinessFlow is proprietary. Copyright (c) 2026 Fedaa Mohamed. All rights
reserved. See [LICENSE](LICENSE) and [COPYRIGHT.md](COPYRIGHT.md).
