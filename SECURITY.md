# Security

## Implemented today (Phase 1)

- **CSRF protection** — Laravel's default CSRF middleware on every state-
  changing form (`@csrf` on all admin and public forms).
- **Password hashing** — bcrypt (Laravel's default `Hash` facade), never
  plaintext, never logged.
- **No default credentials** — the first administrator is created only via
  the interactive `php artisan make:admin` command, which requires a
  12+ character password typed and confirmed; there is no seeded
  `admin/admin`-style account anywhere in this codebase.
- **Login throttling** — `throttle:5,1` on the admin login POST route
  (5 attempts per minute per IP+email combination, Laravel's default
  keying), tested in `tests/Feature/Admin/AuthTest.php`.
- **Role-based authorization** — `EnsureUserHasRole` middleware restricts
  Settings and Users to the `owner` role; a deactivated account
  (`is_active = false`) cannot sign in even with a correct password.
- **Validated, allow-listed file uploads** — the Media Library validates
  MIME type (sniffed from file contents, not just the extension), a
  fixed extension allow-list, and a file-size cap. **SVG uploads are
  deliberately rejected** — an SVG can embed executable script, and
  uploads here are served directly and unsanitized, so accepting SVG
  would be a stored-XSS risk.
- **Parameterized queries** — all data access goes through Eloquent/the
  query builder; no raw string-interpolated SQL exists in this codebase.
- **Mass-assignment protection** — every model declares its fillable
  attributes explicitly (via PHP attributes, e.g. `#[Fillable([...])]`).
- **Contact form spam protection** — a honeypot field (`website`, hidden
  from real users via CSS, expected to stay empty) plus request throttling
  (`throttle:5,1`) on the public contact form. No CAPTCHA is used —
  the spec asks not to make CAPTCHA unnecessarily intrusive, and the
  honeypot + rate limit is a reasonable first line of defense.
- **`APP_DEBUG` discipline** — `.env.example` and `HOSTINGER_DEPLOYMENT.md`
  both call out `APP_DEBUG=false` as required in production so stack traces
  are never shown to a public visitor.
- **Secrets excluded from Git** — `.env`, `database/*.sqlite`, and
  `composer.phar` are all git-ignored; `.env.example` contains placeholders
  only.
- **Stripe webhook signature verification** — `StripeWebhookController`
  verifies every request via `Stripe\Webhook::constructEvent` with
  `STRIPE_WEBHOOK_SECRET` before any order is touched; an invalid/missing
  signature is rejected with 400 and never reaches the handler. The route
  is CSRF-exempt (Stripe can't send a Laravel CSRF token) but nothing else
  is — see `bootstrap/app.php`.
- **Payment confirmation never trusted from the browser** — only the
  signature-verified webhook marks an order paid, refunded, or failed; the
  checkout success page just displays current status.
- **Refunds are owner-only** — a financial action, gated the same way as
  Settings/Users (`role:owner`).
- **Checkout rate limiting** — `throttle:10,1` on the checkout POST route,
  the same pattern as login/contact.
- **Two-factor authentication** — optional, self-service TOTP per admin
  account (`App\Services\TwoFactorAuthService`, `pragmarx/google2fa`).
  Recovery codes are bcrypt-hashed and one-time-use; the shared secret is
  rendered as an inline SVG QR code server-side, never sent to a
  third-party QR image API. `two_factor_secret`/`two_factor_recovery_codes`
  are deliberately excluded from `User`'s `#[Fillable]` list — they're only
  ever written via `forceFill()` from server-computed values, never from a
  mass-assigned request field.
- **Audit log** of administrator actions (`audit_logs` table, written via
  `App\Services\AuditLogger`) — app/price/tax/content changes, refunds,
  admin account changes. No delete/update route is ever registered for it
  (`AuditLogController` exposes only `index()`).
- **Backups never expose secrets or credentials** — archives are built
  from `storage/app/public` and `storage/app/private` only; `.env` lives
  outside `storage/app/` entirely and is never included, and the
  `backups/` directory itself is excluded so a backup never nests inside
  another. Archives are stored on the private `local` disk and served only
  through `BackupController::download()` (owner-only, entitlement-checked
  the same way `DownloadController` serves paid release files) — never a
  public URL.

- **License keys are never stored in plain text queryable form** — only
  `sha256($rawKey)` is used for activation/validation lookups (the same
  pattern Laravel Sanctum uses for API tokens). A separate
  `license_key_encrypted` column (Laravel's `encrypted` cast, AES-256 via
  `APP_KEY`) exists solely so an admin can use "Resend License Email"; the
  public activation/validation API never reads it.
- **License issuance only ever follows a webhook-confirmed payment** — the
  same rule as orders/entitlements above. `App\Services\LicenseService::
  createFromOrder()` is `firstOrCreate`-keyed on `order_item_id`, so a
  replayed Stripe webhook can't issue a second license for the same
  purchase.
- **Device identifiers are never raw hardware IDs** — the license API only
  accepts an opaque installation identifier the app itself generates
  (never a MAC address, IMEI, Android serial, or Windows product key), and
  only its sha256 hash is stored.
- **Offline entitlement tokens are signed, not just cached booleans** —
  `App\Services\LicenseTokenSigner` uses libsodium (Ed25519) to sign the
  payload returned from activate/validate. The private key lives only in
  this server's `.env` (`LICENSE_SIGNING_PRIVATE_KEY`, generated via
  `php artisan license:keys:generate`); only the public half is meant to
  ship inside a Flutter app, which can then verify a cached token offline
  without trusting a bare local `isPro = true` flag.
- **License management pages are signed and short-lived, not permanent**
  — unlike My Downloads' permanent link, `/license/manage/{license}` (and
  its deactivate action) require a temporary signed URL that expires in
  30 minutes, since this page can mutate state (deactivate a device)
  rather than only read one.
- **Self-service device-reset rate limiting** — `App\Services\
  LicenseService` caps customer-initiated device deactivations per
  license in a rolling window (`config/licensing.php`) before requiring
  an admin override, so a lost/rotated device ID can't be used to dodge
  the two-device limit indefinitely.

## Known gaps — a further phase

- **Security headers** (CSP, `X-Frame-Options`, etc.) beyond Laravel's
  framework defaults — to be reviewed once the demo iframe/embed strategy
  and Stripe.js/Checkout's own script needs are both finalized, since a
  strict CSP has to explicitly allow those.

## Reporting a concern

There is no public bug bounty program. If you find a security issue in
this codebase, report it through the business contact information on the
site rather than filing a public issue.
