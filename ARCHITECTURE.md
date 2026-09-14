# Architecture

## Stack

Laravel 13 (PHP 8.3), Blade, Tailwind CSS v4 (via the official Vite
plugin), Alpine.js. SQLite for local development, MySQL/MariaDB in
production. No permanent Node.js server — Vite is a build-time tool only.

## Folder layout (non-default additions)

```
app/
  Console/Commands/MakeAdminCommand.php   php artisan make:admin
  Http/Controllers/                       public site controllers
  Http/Controllers/Admin/                 admin backend controllers
  Http/Requests/                          public form requests (Contact)
  Http/Requests/Admin/                    admin form requests (App, Media)
  Http/Middleware/EnsureUserHasRole.php   role:owner / role:content_editor
  Mail/                                   Mailables (contact notification)
  Models/                                 Eloquent models, see schema below
  Services/ImageService.php               GD-based thumbnail generation
  Services/MediaLibrary.php               shared upload handling
  Services/TaxCalculator.php              effective-dated tax lookup
  Services/StripeCheckout.php             the only class that calls the Stripe SDK
  Services/StripeWebhookHandler.php       pure webhook event logic (no SDK calls)
database/
  migrations/                             see schema below
  seeders/                                AppCategory, Platform, App
                                           (Bread Maker + seed placeholders +
                                           a checkout test app), Page,
                                           Setting, Faq, TaxRule
resources/views/
  components/                             app-layout, admin-layout, site-nav,
                                           site-footer, app-card, price,
                                           platform-badge, feature-list,
                                           screenshot-gallery, cta-block,
                                           alert, admin/* (data-table,
                                           form-field, metric-card)
  home.blade.php, apps/, demos/, pages/,  public pages
  pricing.blade.php, support.blade.php,
  contact.blade.php
  admin/                                  admin backend views
routes/
  web.php                                 public routes
  admin.php                               admin routes (loaded with the
                                           "admin." name/path prefix from
                                           bootstrap/app.php)
public/
  fonts/lato/                             self-hosted Lato font files
  demo-builds/{slug}/                     static web builds for live demos
                                           (NOT "demos" — see below)
```

## Authentication & roles

There is a single `users` table used only for admin accounts in Phase 1
(no public customer registration exists yet — see "Customer accounts" under
Phase 2 below). A `role` enum (`owner` | `content_editor`) plus `is_active`
drive access:

- The default `auth`/`guest` middleware protect `/admin/*`, redirecting to
  `admin.login` / `admin.dashboard` respectively (configured in
  `bootstrap/app.php` via `redirectGuestsTo`/`redirectUsersTo`, since this
  is currently the only authenticated area on the site).
- A custom `role:owner` middleware (`App\Http\Middleware\EnsureUserHasRole`)
  gates Settings and Users to the `owner` role; everything else
  (Apps, Media, Content, FAQs, Support) is available to both roles.
- The first administrator is always created via the interactive
  `php artisan make:admin` command — never a seeded default password.

This intentionally does not use a package like spatie/laravel-permission or
a separate `admin` guard — two roles with one gate is simple enough that
the extra dependency/complexity isn't justified yet. If roles grow more
complex later, migrating to a permissions package is straightforward.

## Money

All prices are stored as **integer cents** (`price_cents`, `sale_price_cents`
on `apps`), never floats, per the spec's money-handling requirement.
`currency` is stored alongside every price-bearing row (default `CAD`) —
`$` is never assumed to mean CAD internally.

## Database schema — implemented

| Table | Purpose |
|---|---|
| `users` | Admin accounts only. `role`, `is_active` added to Laravel's default table. `two_factor_enabled`/`two_factor_confirmed_at`/`two_factor_secret`/`two_factor_recovery_codes` added for optional self-service TOTP 2FA — the latter two are deliberately excluded from `#[Fillable]` (see the 2FA section below). |
| `app_categories` | Lookup: Business, Food & Recipes, Productivity, Utilities, Education, Lifestyle, Other. |
| `platforms` | Lookup: android, windows, web, pwa, ios, macos. |
| `apps` | The catalogue. Core fields, pricing (cents), store links, `direct_purchase_enabled` (now functional), demo fields (1:1 — no separate `demos` table needed), SEO fields, `status` (draft/published/archived), `is_featured` (also gates homepage visibility), soft deletes. |
| `app_platform` | Pivot: apps ↔ platforms. |
| `media` | Media library items: disk/path/thumbnail, mime, size, dimensions, alt/title/description, uploader. |
| `app_media` | Pivot: apps ↔ media, with `type` (icon / feature_graphic / screenshot) and `sort_order`. |
| `app_features` | Feature bullets shown on an app's page. |
| `pages` | About/Privacy/Terms/Refunds — slug, title, SEO fields, published. |
| `page_sections` | Ordered content blocks per page (heading/body/optional image) — lets the owner edit these pages without touching Blade. |
| `faqs` | General (site-wide) or app-specific FAQs. |
| `settings` | Key/value site settings, grouped (`business`, `site`, `store`). |
| `support_requests` | Contact form submissions / support inbox. |
| `orders` | No separate `customers` table (guest checkout only) — billing info is snapshotted directly on the order for financial auditability. `order_number`, billing fields, money in cents, `payment_status`/`order_status`, Stripe session/intent ids. |
| `order_items` | Snapshotted at purchase time (`app_name_snapshot`, `unit_price_cents`) — never re-reads the live `apps` row for a historical order. |
| `payments` | One immutable row per **webhook-confirmed** payment event — only ever written by `StripeWebhookHandler`, never by the checkout request itself. |
| `refunds` | `administrator_id` is null when a refund originated in the Stripe dashboard rather than this admin. |
| `tax_rules` | Effective-dated Canadian tax configuration (country/province, tax name, percentage, effective/expiry dates) — never a single hard-coded Ontario rate. Admin-editable at Settings → Taxes. |
| `sales_tax_lines` | Tax actually applied to a given order, snapshotted independently of later `tax_rules` edits. |
| `app_editions` | What a customer can buy per app — Android / Windows / a bundle / Web / Complete, each with its own price. An app with no active editions still sells at its single flat `price_cents` (unchanged legacy behavior). |
| `edition_entitlements` | Template of what an edition grants — platform + access type (`download` or `web_access`). Changing this only affects future purchases of that edition. |
| `app_releases` | The actual installer file per app/platform (§17 of the platform-selection spec) — private `local` disk, never a public URL. One `is_current` release per app/platform is what customer downloads always resolve to (§24) — not a specific historical file. `customer_downloadable` is forced `false` for an uploaded `.aab` regardless of what the admin form requested. |
| `customer_entitlements` | The actual per-purchase (or admin-granted) access grant — distinct from `edition_entitlements`, which is just the edition's template. Created only by the Stripe webhook once payment is confirmed paid, revoked only on a *full* refund; `granted_by`/`revoked_by`/`revoked_reason` on the row itself remains the audit trail for admin overrides specifically (see note below — the general `audit_logs` table below doesn't replace this). |
| `audit_logs` | General "who did what" trail — see note below. Immutable: no route/controller method exists to update or delete a row. |
| `backups` | One row per generated backup archive (database/media/full) — disk/path/checksum, never a public URL. A row is only inserted after the archive is fully written; a failed attempt writes nothing. |
| `licenses` | A permanent PRO license for a website-sold Android/Windows edition — separate from and additional to `customer_entitlements` above (which still gates the download file itself). One per order item that includes Android/Windows download access; `license_key_hash` (sha256, for lookups) and `license_key_encrypted` (AES-256, admin-resend only) — never a plaintext lookup column. See `LICENSE_SYSTEM.md`. |
| `license_devices` | One activated device per license, capped at `maximum_devices` (default 2 total, shared across platforms for a bundle license). `device_identifier_hash` is sha256 of an opaque app-generated installation ID — never a hardware serial. |
| `license_events` | Append-only activation/validation/deactivation/rejection history per license — backs admin license history and self-service reset-abuse limiting. Deliberately separate from `audit_logs`, which stays scoped to admin-initiated actions. |
| `license_verification_codes` | One-time codes for the self-service device-management flow at `/license/manage`. |

## Database schema — further phase (not yet migrated)

| Table | Purpose |
|---|---|
| `customers` | Customer accounts (optional — guest checkout must remain possible). |
| `expenses`, `expense_categories` | Lightweight business expense tracking — deliberately not built; see `CLAUDE.md`. Accounting more broadly (fiscal-year exports, Stripe fee reconciliation, tax liability summaries) is likewise deferred in favor of exporting Sales CSV data into real accounting software. |

## Checkout & tax engine

`App\Services\TaxCalculator::calculate()` looks up all active `tax_rules`
matching the billing country (and province, or a province-null country-wide
rule) effective on the order date — no match means $0 tax, never a guessed
obligation. `CheckoutController` creates the `Order`/`OrderItem`/
`SalesTaxLine` rows, then `App\Services\StripeCheckout` (the **only** class
that calls the Stripe SDK) creates a Checkout Session and redirects there.

Payment confirmation is **never** trusted from the browser's return to the
success URL — only `StripeWebhookController` (signature-verified via
`Stripe\Webhook::constructEvent`) marks an order paid, via
`App\Services\StripeWebhookHandler`, which is deliberately a plain class
with no Stripe-SDK/HTTP dependency of its own so tests can call it directly
with `\Stripe\Event::constructFrom([...])` — no network calls, no signature
verification needed in tests. See `STRIPE_SETUP.md`.

## Platform/edition selling, downloads, and "My Downloads"

An app with at least one active `AppEdition` (`App::hasEditions()`) sells
through a platform-selection panel (`x-edition-picker`) instead of the
single flat price — Android, Windows, a bundle, Web, or Complete, each
with its own Stripe checkout route (`checkout.create.edition`/
`checkout.store.edition` alongside the original edition-less routes,
which now 404 once an app has editions, so a customer can't bypass
selection). `App\Services\EntitlementService` is the only place
`customer_entitlements` rows are created, revoked, or restored:
`StripeWebhookHandler` creates them right after a confirmed payment and
revokes them on a *full* refund (a partial refund leaves access alone);
`Admin\EntitlementController` covers manual admin grants/revokes.

There is deliberately no customer-accounts table or login. "My Downloads"
(`MyDownloadsController`) is a permanent signed URL
(`URL::signedRoute('my-downloads.show', ['email' => ...])`) mailed on
every order receipt and re-issuable from `/my-downloads` — matching the
guest-checkout-only philosophy already established for orders. The page
itself then mints short-lived (~10 minute) signed download URLs per
platform, so the outer link can stay valid indefinitely without any raw
file URL being bookmarkable forever. `DownloadController` re-verifies the
entitlement is active, its order (if any) is paid, and the resolved
release is `customer_downloadable` before streaming — never trusting the
signature alone.

Release files live on the private `local` disk
(`storage/app/private/releases/{app_id}/{platform_code}/`, never
`public/`) and `App\Services\ReleaseLibrary` enforces an allow-list of
extensions per platform as a hard rule, not just an admin-UI default: a
customer-downloadable Android release must be a real `.apk`; an uploaded
`.aab` is always forced `customer_downloadable = false` regardless of what
the upload form requested, because an AAB is a Play Store publishing
artifact, never something a customer should receive.

**Audit trail scope:** admin overrides (grant/revoke/restore entitlement)
still stamp `granted_by`/`revoked_by`/`revoked_reason`/timestamps directly
on the `customer_entitlements` row itself — that per-row stamping wasn't
replaced when the general `audit_logs` table was added. `App\Services\
AuditLogger::record()` is the single write path for that table, called
explicitly (one line each, matching this app's existing preference for
explicit service calls over model observers/events) from the admin
controllers that mutate something worth tracking: app/edition/tax-rule
create-update-delete, order refunds, admin account/role changes, content
and settings updates, and backup create/delete. `AuditLogController`
exposes only `index()` — no route to update or delete a row exists, the
literal code-level enforcement of "don't let a normal admin action erase
the audit log."

## Permanent PRO license keys

A distinct, additional layer on top of platform/edition selling above:
`App\Services\LicenseService::createFromOrder()` is called from
`StripeWebhookHandler` right next to `EntitlementService::createFromOrder()`
and issues one `License` (with a cryptographically random
`XXXX-XXXX-XXXX-XXXX` key) per order item whose edition grants Android
and/or Windows *download* access — a Web/PWA-only edition never gets one.
This is the backend/API half only (`routes/api.php`,
`App\Http\Controllers\Api\LicenseController`); the Flutter-side PRO-lock
screen and license entry UI live in each app's own separate repository.
Full design, the two-device rule, offline signed entitlement tokens
(`App\Services\LicenseTokenSigner`, Ed25519 via libsodium), and the
self-service device-management flow are documented in
`LICENSE_SYSTEM.md`.

## Two-factor authentication

Optional, self-service, per admin account (`App\Http\Controllers\Admin\
TwoFactorSettingsController`) — the platform-selection spec recommends but
doesn't mandate 2FA for Owner/Admin, so it isn't forced on anyone.
`App\Services\TwoFactorAuthService` wraps `pragmarx/google2fa` (TOTP) and
`bacon/bacon-qr-code` (QR rendered as inline SVG server-side, so the
shared secret is never sent to a third-party QR image API). A user with
`User::hasTwoFactorEnabled()` is routed through a second login step
(`TwoFactorChallengeController`) after a correct password — `AuthController
@store` logs the (correct-password) session back out and stashes only a
short-lived pending marker rather than a real authenticated session, until
a valid TOTP or one-time recovery code is submitted. `two_factor_secret`
and `two_factor_recovery_codes` are deliberately excluded from `User`'s
`#[Fillable]` list — every write to them goes through `forceFill()` from a
server-computed value, never from a mass-assigned request field, so
there's no way to plant a secret via a crafted form field. An owner can
force-disable another admin's 2FA (`UserController::disableTwoFactor`, for
the "lost phone, no recovery codes" case) but can never enable it on
someone else's behalf — enrollment stays self-service.

## Backups

On-demand only (`Admin\BackupController` + `App\Services\BackupService`,
owner-only) — database, media, or both bundled together. No in-app
scheduler exists; `php artisan backup:run` is the CLI equivalent a real
cron entry can call (see `BACKUP_RESTORE.md`). The database dump is a
portable `.sql` script for every driver, including SQLite — deliberately
not SQLite's `VACUUM INTO`, which cannot run inside a transaction (a real
SQLite limitation, not just a testing artifact) and so would be fragile if
a request ever ran inside one. Media archives zip both the `public` disk
(icons/screenshots) and the private `local` disk (paid release files) but
always exclude the `backups/` directory itself and never touch `.env`
(which lives outside `storage/app/` entirely). Archives are stored on the
private `local` disk and only ever served through
`BackupController::download()` — never a public URL, the same pattern
`DownloadController` uses for paid release files.

## Demo system — the routing gotcha

Static demo builds live at **`public/demo-builds/{slug}/`**, not
`public/demos/{slug}/` as an earlier draft of this system used. The reason:
`public/demos` as a literal directory collides with the `/demos` catalogue
route at the web-server level — PHP's built-in dev server, and Apache/Nginx
in production (both route "does a file/directory exist on disk?" checks
before handing a request to `index.php`), would resolve `/demos` to that
directory and return a bare 404 without Laravel's router ever running,
breaking the catalogue page. `tests/Feature/BreadMakerSeedTest.php` and
`tests/Feature/Public/PublicPagesTest.php` both assert against this
regression. See `DEMO_DEPLOYMENT.md` for the full demo system.

## Fonts

TOLA is the intended final brand typeface but hasn't been supplied yet.
**Lato** (SIL Open Font License — free to use permanently, files in
`public/fonts/lato/`, license text alongside) is used as the real interim
typeface rather than a generic system-font placeholder. `resources/css/app.css`
has commented-out `@font-face` blocks ready for TOLA `.woff2` files — supply
them at `public/fonts/tola/` and flip the `--font-display`/`--font-body`
theme tokens to `'TOLA'` to switch over.

## Design tokens

Tailwind v4's `@theme` block in `resources/css/app.css` defines the color
palette (`--color-niagara-*` for the brand green, `--color-cta-*` for a
stronger call-to-action green, `--color-water-*` for the blue accent,
`--color-navy*` for text, plus paper/offwhite/mist neutrals) and generates
matching utility classes automatically (e.g. `bg-niagara-500`,
`text-cta-600`) — there's no need for Tailwind's `[--color-x]` arbitrary
bracket syntax for these; a plain utility class name is enough. A parallel
semantic type scale (`.text-display`, `.text-h1`…`.text-small`, `.text-label`,
`.text-nav`) keeps typography consistent without hand-picking font sizes
per component.
