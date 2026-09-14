# License System

How the website's permanent PRO license-key + 2-device activation system
works — the backend half of it. This is a distinct, additional layer on
top of the existing Stripe checkout / `AppEdition` / `customer_entitlements`
purchase system described in `ARCHITECTURE.md`: that system still governs
who can *download* an Android APK or Windows installer file; this one
governs whether the *installed app* unlocks PRO. A license is generated
alongside a `CustomerEntitlement` for any purchased edition that grants
Android and/or Windows *download* access — a Web/PWA-only edition never
gets a license, since there's no installed binary to license.

**This repo is the licensing backend only.** The Flutter-side PRO-lock
screen, license-key entry UI, secure local storage, and offline token
verification live in each app's own separate repository and consume the
API described below — they are not part of this codebase.

## Why a license key on top of a download link?

The existing download system controls access to the *installer file*.
Once a customer has that file, nothing stops them sharing it — a signed
APK/EXE is just a file. The license key is what the installed app itself
checks before unlocking PRO features, independent of how the customer
got the installer, and it's what enforces the 2-device limit.

## Data model

| Table | Purpose |
|---|---|
| `licenses` | One permanent license per purchased order item that includes Android/Windows download access. `license_key_hash` (sha256, used for lookups) and `license_key_encrypted` (AES-256, decryptable only for the "Resend License Email" admin action) — never a plaintext lookup column. `platform_entitlement` is `android_only`/`windows_only`/`android_windows_bundle`. `status` is `active`/`revoked`/`refunded`/`chargeback`/`disabled`. |
| `license_devices` | One row per device activated against a license (capped at `maximum_devices`, default 2). `device_identifier_hash` is sha256 of the opaque installation ID the app itself generates — never a MAC address, IMEI, Android serial, or Windows product key. |
| `license_events` | Append-only activation/validation/deactivation/rejection history — backs the admin "license history" view and the self-service reset abuse limiting. Kept separate from the general `audit_logs` table, which stays scoped to admin-initiated actions. |
| `license_verification_codes` | One-time codes for the self-service device-management flow. |

## How a license is issued

`App\Services\LicenseService::createFromOrder(Order $order)` is called
from `App\Services\StripeWebhookHandler::handleCheckoutCompleted()`,
right alongside the existing `EntitlementService::createFromOrder()` call
— **only** once Stripe's webhook has confirmed payment, never from the
checkout success page (see `CLAUDE.md`'s payment-confirmation rule, which
applies identically here). For each order item whose edition grants
Android and/or Windows download access, it:

1. Works out the `platform_entitlement` from which platforms the edition
   includes.
2. Generates a cryptographically random `XXXX-XXXX-XXXX-XXXX` key
   (`App\Services\LicenseKeyGenerator`, `random_int` over an unambiguous
   32-character alphabet — never sequential).
3. Creates the `License` row, `firstOrCreate`-keyed on `order_item_id` so
   a replayed webhook can never issue a second license for the same
   purchase.

The raw key is shown to the customer exactly once: on the checkout
success page and in the order receipt email (`resources/views/emails/
order-receipt.blade.php`, via `resources/views/emails/partials/
license-block.blade.php`). After that, only the sha256 hash is used to
resolve it.

## Configuring prices

There's no separate price list for licenses — a license is issued for
whatever `AppEdition` the customer bought, so prices are configured
exactly as documented in `ARCHITECTURE.md` (Admin → an app → Editions).
`config/licensing.php` only holds the *device* limit and reset-abuse
settings, not pricing.

## Adding another application

Nothing app-specific to configure beyond what `ARCHITECTURE.md` already
describes for adding an app and its editions. Once an app has an
Android and/or Windows edition with `download` entitlements, purchasing
it automatically issues licenses the same way. Each Flutter app just
needs its numeric `app_id` (visible in the admin) to call the activation
API correctly.

## The API

Registered in `routes/api.php` (Laravel's stateless `api` middleware
group — no CSRF/session, which is what a Flutter client needs) and
implemented by `App\Http\Controllers\Api\LicenseController`, which
delegates every decision to `LicenseService`:

- `POST /api/license/activate` — `{license_key, app_id, platform,
  device_id, app_version}` → `{unlocked, reason, message, token}`.
  `device_id` is the opaque installation ID the app generates; never a
  hardware serial. Registers a new device (if under the limit) or
  re-touches an already-registered one without consuming another slot.
- `POST /api/license/validate` — `{license_key, device_id}` → same shape
  as activate, for periodic re-validation (see "Offline use" below).
- `POST /api/license/deactivate` — `{license_key, device_id}` →
  `{ok, message}`. Customer self-service, subject to the reset-abuse
  limit in `config/licensing.php`.
- `POST /api/license/manage/request-code` — `{email, license_key}` →
  always the same generic response, regardless of whether they matched
  (never confirm/deny a purchase to an anonymous caller). Emails a code
  to the license's own `customer_email` if they did.
- `POST /api/license/manage/verify-code` — `{email, code}` →
  `{management_url}`, a short-lived signed URL to the device-management
  page (`/license/manage/{license}`, also reachable by a human via
  `/license/manage`).

Every activate/validate/deactivate call — success or rejection — is
logged to `license_events` with an IP address, which is what backs both
the admin's "license history" view and the self-service reset-abuse
limiting.

## The two-device rule

Enforced entirely in `LicenseService::activate()`:

1. License must be `active`.
2. `platform_entitlement` must permit the requested platform
   (`android_only` rejects a `windows` activation, etc.).
3. If this exact device (by hash) is already an active registration,
   re-touch it — no new slot consumed.
4. Otherwise, if fewer than `maximum_devices` are currently active,
   register it as a new device.
5. Otherwise, reject with `reason: "max_devices"` and the customer-facing
   message from §9 of the original spec.

The bundle (`android_windows_bundle`) shares the same device count across
both platforms — it is not 2 Android + 2 Windows, it's 2 total.

## Offline use and signed entitlement tokens

A successful `activate`/`validate` response includes a `token`:
`{payload, signature}`. `payload` is a small JSON object (license id,
device id, platform, issued/valid-until timestamps); `signature` is an
Ed25519 signature over it (`App\Services\LicenseTokenSigner`, via PHP's
built-in libsodium — `sodium_crypto_sign_detached`). The private signing
key lives only in this server's `.env`
(`LICENSE_SIGNING_PRIVATE_KEY`) — generate a keypair with:

```
php artisan license:keys:generate
```

The command prints both halves; only the **public** key is meant to be
embedded in a Flutter app (e.g. as a compiled-in constant — it's public
by design, not a secret). The app should cache the last-verified
`{payload, signature}` and, while offline, verify the signature locally
(any Ed25519-capable library, e.g. Dart's `cryptography` package) rather
than trusting a bare local `isPro = true` boolean, and re-validate online
whenever a connection is available. `valid_until` in the payload gives a
natural "how stale is too stale to trust offline" cutoff for the Flutter
app to enforce itself.

## Revoking a license and handling refunds/chargebacks

- **Admin-initiated**: Admin → Licenses → open a license → Revoke /
  Restore / Mark Refunded, or Admin → Orders → Issue Refund (which now
  also revokes any licenses tied to that order, alongside the existing
  entitlement revocation).
- **Automatic**: `StripeWebhookHandler` calls
  `LicenseService::revokeForOrder($order, 'refunded')` on a full Stripe
  refund and `LicenseService::revokeForOrder($order, 'chargeback')` on a
  `charge.dispute.created` event — the same trigger points that already
  revoke `customer_entitlements`. A partial refund leaves licenses
  (and entitlements) untouched, matching existing behavior.
- Either way, the installed app finds out on its next `validate` call —
  there's no push mechanism, so a revoked license stays "unlocked" on a
  device until it goes online again and re-validates. Keep the app's
  revalidation interval reasonably short if this matters for your case.

## Resetting devices

Two ways, both ending in the same `LicenseService::deactivateByAdmin()`/
`deactivateByCustomer()` code path:

- **Customer self-service**: `/license/manage` → email + license key →
  emailed one-time code → device list → Deactivate. Rate-limited
  (`config('licensing.self_service_reset_limit')`, default 3 per 30
  days) so it can't be used to dodge the two-device cap indefinitely,
  without blocking someone replacing one or two genuinely broken devices.
- **Admin**: Admin → Licenses → a license → deactivate an individual
  device, or "Reset Activations" to clear all of them at once. Never
  rate-limited.

## Deploying

Nothing beyond the existing deployment process (`HOSTINGER_DEPLOYMENT.md`)
plus:

1. Run the new migrations (`php artisan migrate`).
2. Generate a **production** signing keypair (`php artisan
   license:keys:generate`) and set `LICENSE_SIGNING_PRIVATE_KEY`/
   `LICENSE_SIGNING_PUBLIC_KEY` in the production `.env` — never reuse a
   local dev keypair in production, and never commit either value.
3. Hand the printed public key to whoever builds each Flutter app's
   website edition, so it can verify entitlement tokens offline.

## Environment variables

| Variable | Purpose |
|---|---|
| `LICENSE_SIGNING_PRIVATE_KEY` | Base64 Ed25519 secret key. Server-only, never shipped to a client. |
| `LICENSE_SIGNING_PUBLIC_KEY` | Base64 Ed25519 public key. Safe to embed in a Flutter app. |
| `LICENSING_DEFAULT_MAX_DEVICES` | Default `maximum_devices` for a new license (default 2). |
| `LICENSING_SELF_SERVICE_RESET_LIMIT` | Self-service deactivations allowed per license per window (default 3). |
| `LICENSING_SELF_SERVICE_RESET_WINDOW_DAYS` | The rolling window for the above (default 30). |
| `LICENSING_MANAGEMENT_CODE_TTL_MINUTES` | How long an emailed management code stays valid (default 15). |

See `config/licensing.php` for defaults.
