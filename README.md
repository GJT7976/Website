# Niagara Inde Apps — Website

The public website, app catalogue, live web demos, and administration
backend for **Niagara Inde Apps** — an independent Canadian app-development
business based in Niagara, Ontario.

Built with Laravel, Blade, Tailwind CSS, and Alpine.js. No WordPress or
page-builder involved — this is real source code.

## Status: Phase 1 (Foundation + Core)

This codebase implements the foundation and core of the full business
platform described in
`Niagara Inde Apps — Claude Website Build Prompt.md`. Delivered so far:

- Public site: home, app catalogue, individual app pages, live demos,
  about/support/contact, legal page templates
- A real, working live demo (**Bread Maker**, a baker's percentage
  calculator PWA) wired into the demo system
- Admin backend: authentication, dashboard, full app CRUD, media library,
  content editor (pages/FAQs), settings, support inbox, user management
- Database schema, migrations, seeders, and automated tests for all of
  the above

**Not yet implemented** (planned for Phase 2 — see each doc for what's
already scaffolded vs. still to build):

- Stripe checkout and webhooks (`STRIPE_SETUP.md`)
- The configurable Canadian tax engine
- Orders, Sales, and Accounting/export reporting
- Expense tracking and the profit overview
- Audit log, two-factor authentication, and backup tooling
  (`BACKUP_RESTORE.md`)

The admin sidebar shows these as clearly labeled "Phase 2 — Soon" so the
navigation matches the eventual full platform without faking data that
doesn't exist yet.

## Documentation

- **`LOCAL_SETUP.md`** — get this running on a Windows dev machine, start here
- **`ARCHITECTURE.md`** — full database schema (including planned Phase 2
  tables), folder layout, and design decisions
- **`ADMIN_GUIDE.md`** — how to use the admin backend day to day
- **`DEMO_DEPLOYMENT.md`** — how the live demo system works, and how to add
  another app's web build
- **`HOSTINGER_DEPLOYMENT.md`** — deploying what exists today to Hostinger
- **`STRIPE_SETUP.md`** — Phase 2 plan (not yet implemented)
- **`BACKUP_RESTORE.md`** — Phase 2 plan (not yet implemented)
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
