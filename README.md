# Niagara Inde Apps — Website

The public website, app catalogue, live web demos, and administration
backend for **Niagara Inde Apps** — an independent Canadian app-development
business based in Niagara, Ontario.

Built with Laravel, Blade, Tailwind CSS, and Alpine.js. No WordPress or
page-builder involved — this is real source code.

## Status: Phase 1 + Phase 2 (Foundation, Core, and Checkout)

This codebase implements the foundation/core of the full business platform
described in `Niagara Inde Apps — Claude Website Build Prompt.md`, plus
Stripe checkout and the Canadian tax engine. Delivered so far:

- Public site: home, app catalogue, individual app pages, live demos,
  about/support/contact, legal page templates
- A real, working live demo (**Bread Maker**, a baker's percentage
  calculator PWA) wired into the demo system
- Admin backend: authentication, dashboard, full app CRUD, media library,
  content editor (pages/FAQs), settings, support inbox, user management
- **Real purchasing**: Stripe Checkout, a configurable/admin-editable
  Canadian tax engine, Orders, Payments, Refunds, and a Sales view with
  CSV export (`STRIPE_SETUP.md`)
- **Platform/edition-aware selling**: per-app Editions (Android / Windows /
  bundle / Web / Complete), protected per-platform downloads, a "My
  Downloads" customer area reached by an emailed magic link (no customer
  accounts), and admin tools for release uploads and entitlement overrides
  — see "Claude Prompt — Niagara Inde Apps Platform Selection & Purchase
  System.md" and the schema table in `ARCHITECTURE.md`
- **Permanent PRO license keys** for website-sold Android/Windows
  editions of the Flutter apps: a cryptographically random license key
  issued on purchase, a 2-device activation limit, self-service and admin
  device management, and signed offline entitlement tokens — see
  `LICENSE_SYSTEM.md`. This is the backend/API half only; the Flutter-side
  PRO-lock screen lives in each app's own repository.
- Database schema, migrations, seeders, and automated tests for all of
  the above

**Not yet implemented** (a further phase — see each doc for what's already
scaffolded vs. still to build):

- Full Accounting (fiscal-year exports, by-province/by-country breakdowns,
  Stripe processing fees)
- Expense tracking and the profit overview
- Audit log, two-factor authentication, and backup tooling
  (`BACKUP_RESTORE.md`)

The admin sidebar shows these as clearly labeled "Phase 2 — Soon" so the
navigation matches the eventual full platform without faking data that
doesn't exist yet.

## Documentation

- **`LOCAL_SETUP.md`** — get this running on a Windows dev machine, start here
- **`ARCHITECTURE.md`** — full database schema, folder layout, and design decisions
- **`ADMIN_GUIDE.md`** — how to use the admin backend day to day
- **`DEMO_DEPLOYMENT.md`** — how the live demo system works, and how to add
  another app's web build
- **`HOSTINGER_DEPLOYMENT.md`** — deploying what exists today to Hostinger
- **`STRIPE_SETUP.md`** — Stripe Checkout + webhook setup (implemented — add your keys)
- **`LICENSE_SYSTEM.md`** — the license-key/device-activation system for
  website-sold Android/Windows editions
- **`BACKUP_RESTORE.md`** — manual backup steps today; an admin UI is a further phase
- **`SECURITY.md`** — what's implemented today and what's planned

## Quick start

```
composer install
npm install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan make:admin
npm run dev   # in one terminal
php artisan serve   # in another
```

Then visit `http://127.0.0.1:8000` and `http://127.0.0.1:8000/admin`.
Full details, including troubleshooting, are in `LOCAL_SETUP.md`.

## Tests

```
php artisan test
```
