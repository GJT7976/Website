# Deployment — Niagara Indie Apps Website

Audited operational facts only. **Never add passwords, tokens, API keys, or
other secret values to this file** — see `docs/07_NIAGARA_WEBSITE_DEPLOYMENT.md`
and `HOSTINGER_DEPLOYMENT.md` for the policy this follows.

## Production
- Domain: `https://niagaraindieapps.com`
- GitHub repository: `https://github.com/GJT7976/Website.git`, branch `master`
- Hosting: Hostinger shared PHP/MySQL hosting

## Deployment method (audited 2026-09-15)
Hostinger hPanel → **Website → Git**, connected to the GitHub repo above.
- Root directory: `public_html`
- Deploy is **pull-based, not automatic on push**: after pushing to
  `master`, someone must click **Redeploy** in that hPanel screen to pull
  the new commit into `public_html`.
- This is a plain `git pull` equivalent — it does **not** run
  `composer install`, `npm run build`, or any migration/seed command. Any
  change that needs a new Composer dependency or a frontend asset rebuild
  will not take effect from a Redeploy alone; the mechanism for that case
  is not yet established (no SSH — see below — and no build step observed
  in the Git panel as of this audit).
- Last commit observed as actually redeployed via this panel:
  `a2d7a8e` (2026-09-14 20:15). Commits `8355f80` and `82b87db`
  (2026-09-15 — Bar Tender Atlas catalogue addition, admin Seed Data
  feature, storage-serving fix) were pushed to GitHub but had not yet been
  clicked-through to Redeploy as of this file's creation — confirm current
  status before assuming they're live.

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
- **Migrations**: `php artisan migrate --force` has **no established
  trigger on this host** as of this audit — neither an admin-panel button
  (unlike maintenance/seed above) nor a confirmed Git-panel build hook.
  This is an open gap: a schema change pushed to `master` and redeployed
  will not actually migrate the production database via any mechanism
  audited so far.
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
