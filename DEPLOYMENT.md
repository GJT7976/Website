# Deployment — Niagara Indie Apps Website

Audited operational facts only. **Never add passwords, tokens, API keys, or
other secret values to this file** — see `docs/07_NIAGARA_WEBSITE_DEPLOYMENT.md`
and `HOSTINGER_DEPLOYMENT.md` for the policy this follows.

## Production
- Domain: `https://niagaraindieapps.com`
- GitHub repository: `https://github.com/GJT7976/Website.git`, branch `master`
- Hosting: Hostinger shared PHP/MySQL hosting

## Deployment method (audited 2026-09-15, updated same day)
Hostinger hPanel → **Website → Git**, connected to the GitHub repo above.
- Root directory: `public_html`
- **Auto-deployment is ON** (confirmed working both directions
  2026-09-15 — a marker comment was pushed and appeared live, then a
  second push removed it and that also went live, with nobody clicking
  Redeploy either time). A push to `master` deploys to `public_html` on
  its own; the **Redeploy** button in this panel is now only needed to
  manually re-trigger a deploy of the current commit (e.g. after an
  environment change), not for ordinary code changes.
- This is still a plain `git pull` equivalent — it does **not** run
  `composer install`, `npm run build`, or any migration/seed command.
  Any change needing a new Composer dependency or a frontend asset
  rebuild will not take effect automatically; that mechanism is still
  unestablished (no SSH — see below — and no build hook observed in the
  Git panel). Schema/seed changes specifically are now covered by the
  `POST /deploy-sync` endpoint below, added for exactly this gap.

## Automatic schema/seed sync — `POST /deploy-sync`
Added 2026-09-15 (`app/Http/Controllers/DeploySyncController.php`). Lets
the CLI sync migrations + seed data after a push, without a human logging
into `/admin` — the last manual-click gap now that Git deploy is
automatic. Details:
- Runs `php artisan migrate --force` then `php artisan db:seed --force`,
  returns JSON with both commands' output.
- Authenticated by an `Authorization: Bearer <token>` header, compared
  with `hash_equals()`. The token lives in production's `.env` as
  `DEPLOY_SYNC_TOKEN` — **not recorded in this file**. A copy is kept
  locally, outside this repository, at
  `C:\Users\User\Documents\BarTenderAtlas-signing-backup\deploy-sync-token.txt`
  (same convention as the MSIX signing cert password next to it).
- If `DEPLOY_SYNC_TOKEN` is unset in `.env`, the route 404s — the
  endpoint doesn't exist at all until deliberately configured. **This
  still needs that one-time manual step** (pasting the token into
  production's `.env` via hPanel's File Manager) before it does anything
  on production; it has only been verified locally as of this writing.
- Throttled (`throttle:5,1`) and excluded from CSRF validation in
  `bootstrap/app.php` (same reasoning as the existing Stripe webhook
  exclusion — a non-browser caller authenticated its own way).

## Database
- MySQL/MariaDB (Hostinger hPanel)
- Database name: `u466607184_niagara`
- Database user: `u466607184_admin`
- Password: **not recorded here** — kept by the site owner outside this
  repository (a password manager or Hostinger's own account settings, not
  a project file or Claude's memory).

## No SSH
This Hostinger plan has no SSH access by default, and `exec()`/`proc_open()`/
`symlink()` are disabled on its shared PHP stack. Consequences already
worked around in-app rather than requiring shell access:
- **Maintenance mode**: Admin → Maintenance Mode (`/admin/maintenance`)
  toggles `php artisan down`/`up` via `Artisan::call()` in-process.
- **Seeding**: Admin → Seed Data (`/admin/seed`, added 2026-09-15) re-runs
  `php artisan db:seed --force` the same way. Safe to run repeatedly —
  every seeder uses `updateOrCreate()`.
- **Migrations**: Admin → Migrations (`/admin/migrate`, added 2026-09-15)
  shows pending migrations and runs `php artisan migrate --force` the same
  way. Tested end-to-end locally against a temporary dummy migration
  before shipping. Note: the sidebar nav link for this page was blocked by
  a local permission classifier and needs adding by hand — see the commit
  message on `7e3afad` for the exact one-line change — the route itself
  works regardless, reachable directly at `/admin/migrate`.
- **`storage:link`**: cannot succeed here (`symlink()` disabled). Fixed
  2026-09-15 (commit `82b87db`) by enabling Laravel's built-in
  `'serve' => true` config on the `public` disk
  (`Illuminate\Filesystem\FilesystemServiceProvider`), which serves
  `storage/app/public` at `/storage/{path}` via a real route instead of a
  symlink. The private `local` disk (paid releases, DB backup zips) had
  the same flag set as an unused leftover, which made both publicly
  reachable at a guessable URL with no entitlement check — removed in the
  same commit; every real read of that disk already goes through
  `->path()` server-side.

## Rollback
Not yet audited — confirm in hPanel's Git panel whether an earlier
deployment/commit can be re-selected and redeployed directly, or whether
rollback means reverting on GitHub and redeploying that revert.

## Backups
Admin → Backups (`/admin/backups`) exists in the nav; its actual mechanics
were not audited as part of this pass.
