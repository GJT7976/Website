# Hostinger Deployment

This covers deploying **what exists today** — the public site, app
catalogue, live demos, admin backend, and Stripe checkout/tax engine — to
a conventional Hostinger PHP/MySQL hosting plan. Full Accounting/Expenses/
Audit log/2FA/Backups deployment notes will be added once those systems
exist.

For Stripe specifically (live keys, the production webhook endpoint, and
verifying tax obligations before taking real payments), see
`STRIPE_SETUP.md` — steps 2, 3, and "Switching to live mode" there apply
directly to this environment once it's live.

## Requirements

- PHP 8.2+ with the same extensions as local dev: `curl`, `fileinfo`,
  `gd`, `mbstring`, `pdo_mysql`, `zip` (check via Hostinger's PHP
  configuration panel — most are enabled by default on Hostinger's shared
  PHP stack).
- MySQL/MariaDB database (create one via hPanel).
- HTTPS (Hostinger issues free SSL certificates — enable it for the domain).
- SSH or File Manager access, and ideally Composer available on the host
  (Hostinger's shared plans typically provide Composer via SSH; if not,
  run `composer install --no-dev --optimize-autoloader` locally and upload
  the resulting `vendor/` directory).

## Document root

Laravel's web root is `public/`, not the project root. On Hostinger, either:

- Point the domain's document root directly at the project's `public/`
  folder (preferred, if your plan allows changing it in hPanel), **or**
- Place the Laravel project *outside* `public_html`, and put only the
  contents of Laravel's `public/` folder inside `public_html`, editing
  `public_html/index.php`'s two `require` paths to point at the project's
  real `vendor/autoload.php` and `bootstrap/app.php` locations.

Never expose the project's `app/`, `.env`, `database/`, `routes/`, or
`vendor/` directories directly under `public_html`.

## Deployment steps

1. Upload the project (excluding `.env`, `node_modules/`, and anything in
   `.gitignore`) to the server, outside `public_html` if using the second
   document-root approach above.
2. `composer install --no-dev --optimize-autoloader`
3. Build frontend assets **before** uploading, or on the server if Node is
   available: `npm install && npm run build` — this produces
   `public/build/`, which must exist in production (Vite's manifest is
   read at request time; there is no dev server in production).
4. Create `.env` on the server (never upload your local one, and never
   commit real secrets to Git):
   - `APP_ENV=production`
   - `APP_DEBUG=false` — **critical**: never expose stack traces publicly
   - `APP_URL=https://yourdomain.tld`
   - `DB_CONNECTION=mysql` plus the real `DB_HOST`/`DB_DATABASE`/
     `DB_USERNAME`/`DB_PASSWORD` from hPanel
   - `MAIL_*` set to Hostinger's (or another) real SMTP provider
   - `SESSION_DOMAIN` set appropriately for the real domain
   - `STRIPE_KEY`/`STRIPE_SECRET`/`STRIPE_WEBHOOK_SECRET` — live-mode
     keys only once ready for real charges (see `STRIPE_SETUP.md`); leave
     blank until then and "Buy Now" will show a friendly error instead of
     breaking
5. `php artisan key:generate --force` (only if `.env` doesn't already have
   a key — never regenerate a key on a site with existing encrypted data).
6. `php artisan migrate --force`
7. `php artisan storage:link` — creates the `public/storage` symlink the
   media library depends on. If symlinks aren't available on the plan,
   this needs a different approach (e.g. Hostinger's supported alternative,
   or serving media through a dedicated route) — verify before launch.
8. Create the first administrator: `php artisan make:admin` over SSH.
   Never seed a default admin account in production.
9. `php artisan config:cache && php artisan route:cache && php artisan view:cache`
   for production performance.
10. Confirm HTTPS is active and working before announcing the site live.

## Cron

Nothing in Phase 1 requires a scheduled job yet. Once Phase 2 adds
scheduled reports/backups, Hostinger's cron panel should be pointed at
Laravel's scheduler:
```
* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1
```

## Do not hard-code localhost

Every URL in this codebase is generated via Laravel's `route()`/`url()`
helpers and environment configuration (`APP_URL`) — there are no hard-coded
`http://127.0.0.1:8000` references in application code, only in this
documentation and `.env.example`.
