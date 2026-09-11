# Local Setup (Windows)

Exact steps to run this website on a Windows development machine, using
**SQLite** so no MySQL/Laragon install is required to get started. The
production site on Hostinger uses MySQL/MariaDB instead — see
`HOSTINGER_DEPLOYMENT.md` — switching is a `.env` change only, not a code
change.

## 1. Prerequisites

- **PHP 8.2+** (this project was built against PHP 8.3). Confirm with:
  ```
  php -v
  ```
  If you don't have PHP, the quickest install on Windows is
  `winget install PHP.PHP.8.3`.

- **Required PHP extensions.** Find your `php.ini`:
  ```
  php --ini
  ```
  Open it and make sure these lines are **uncommented** (remove the leading
  `;`): `curl`, `fileinfo`, `gd`, `mbstring`, `exif`, `zip`, and — only if
  you intend to test against a local MySQL server instead of SQLite —
  `pdo_mysql`. `pdo_sqlite` and `sqlite3` are usually enabled by default.
  Restart your terminal after editing `php.ini`.

- **Composer.** Confirm with `composer -V`. If missing:
  ```
  winget install Composer.Composer
  ```
  or follow the official installer at https://getcomposer.org/download/
  (always verify the installer's SHA-384 signature as documented there
  before running it).

- **Node.js 18+** (for Vite/Tailwind). Confirm with `node -v` and `npm -v`.

No MySQL, no Laragon, and no internet connection are required for ordinary
day-to-day development after the one-time installs above — only Composer/npm
package installation needs the internet.

## 2. Install dependencies

From the project root:

```
composer install
npm install
```

## 3. Configure the environment

```
copy .env.example .env
php artisan key:generate
```

The example `.env` already sets `DB_CONNECTION=sqlite`. Create the empty
database file:

```
type nul > database\database.sqlite
```

(or in PowerShell: `New-Item -ItemType File -Path database\database.sqlite`
— **don't** use `-Force` if the file might already exist, it would wipe it.)

## 4. Run migrations and seed sample data

```
php artisan migrate --seed
```

This creates every Phase 1 table and seeds:
- App categories and platforms
- **Bread Maker** — the one real, published app, with its actual icon and
  two screenshots captured from the running demo, wired to its live demo
- Two clearly-labeled `[SEED]` placeholder apps, kept as **drafts** (never
  publicly visible) — only so the admin/catalogue UI can be exercised with
  more than one row
- Draft content for About/Privacy/Terms/Refunds, homepage/business
  settings, and a few general FAQs

Re-running `migrate --seed` is safe — the seeders use `updateOrCreate` and
won't duplicate rows.

## 5. Create your first administrator account

Never use a default password. Run the interactive command and follow the
prompts:

```
php artisan make:admin
```

It asks for a name, email, role (`owner` has full access; `content_editor`
can't touch Settings or Users), and a password (minimum 12 characters,
hidden as you type, confirmed).

## 6. Start the site

In one terminal, build/watch frontend assets:

```
npm run dev
```

In another terminal, run Laravel's dev server:

```
php artisan serve
```

Open:
- **Public site:** http://127.0.0.1:8000
- **Admin:** http://127.0.0.1:8000/admin — sign in with the account from
  step 5

## 7. Everyday admin workflow (what you can test locally)

- **Add a new app:** Admin → Apps → Add App → fill in details → save →
  upload an icon/screenshots/feature graphic on the edit page → add
  feature bullets → set Status to Published.
- **Feature it on the homepage:** toggle "Featured" on the app (or use the
  Feature button in the Apps list). Only featured, published apps ever
  appear on the homepage — this is deliberate, so a new app's imagery
  never shows up on the landing page by accident.
- **Set up a live demo:** on the app's edit page, enable Demo, set the
  Demo URL to `/demo-builds/{slug}/index.html` after placing a static web
  build at `public/demo-builds/{slug}/` (see `DEMO_DEPLOYMENT.md` — note
  the folder is `demo-builds`, not `demos`, to avoid colliding with the
  `/demos` catalogue route).
- **Media library:** Admin → Media Library to upload, search, and delete
  unused images (an image still attached to an app can't be deleted until
  it's removed from that app).
- **Edit site content:** Admin → Pages to edit About/Privacy/Terms/Refunds
  without touching Blade templates; Admin → FAQs for the FAQ list; Admin →
  Business/Site/Store Settings (owner only) for homepage hero text, footer
  text, and business contact info.
- **Support inbox:** submit the public `/contact` form, then check
  Admin → Support Inbox — the message appears there (and is emailed to the
  configured business email if `MAIL_MAILER` is set to something other
  than `log`).

## 8. Running tests

```
php artisan test
```

Tests use an in-memory SQLite database (configured in `phpunit.xml`) and
never touch your real `database/database.sqlite`.

## Troubleshooting

- **"could not find driver" / SQLite errors** — the `pdo_sqlite` and
  `sqlite3` PHP extensions aren't enabled; see step 1.
- **Blank/broken styling** — run `npm run dev` (or `npm run build` for a
  one-off production build) so `public/build/` exists; Laravel's `@vite()`
  directive needs it.
- **"Class ... not found" after pulling changes** — run
  `composer dump-autoload`.
- **Changed a migration and things look wrong** — for local dev only,
  `php artisan migrate:fresh --seed` rebuilds the SQLite database from
  scratch. Never run this against a real/production database.
