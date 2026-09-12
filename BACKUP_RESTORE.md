# Backup & Restore

**Admin → Backups** (owner-only) now exists: click "Back up database",
"Back up media", or "Full backup" to generate a downloadable archive
on-demand, or run `php artisan backup:run --type=full` (also accepts
`--type=database` or `--type=media`) from a cron entry — see "Automated
backups" below. What follows is also still accurate as the manual
procedure, useful if the admin UI/CLI is unavailable for some reason.

## What gets backed up

1. **The database** (all app/content/settings/support data) — dumped as a
   portable `.sql` script regardless of driver (see
   `App\Services\BackupService`).
2. **Uploaded media** — both `storage/app/public/` (icons, screenshots,
   feature graphics from the Media Library) **and** `storage/app/private/`
   (paid app release files — APKs/installers — from `ReleaseLibrary`,
   easy to forget since they're not web-accessible). The admin UI's
   "media"/"full" backup types include both; a manual copy should too.
3. **Never back up `.env`** or any file containing secrets/credentials —
   restoring a backup should never leak production keys, and a backup
   package should not need them to be useful for data recovery. The admin
   UI's archives never include it (`.env` lives outside `storage/app/`
   entirely, and the backups/ directory itself is excluded so a backup
   never nests inside another).

## Local (SQLite)

The entire database is one file:

```
copy database\database.sqlite database\backups\database-2026-09-11.sqlite
```

Media:

```
xcopy storage\app\public backups\media-2026-09-11\ /E /I
```

## Production (MySQL, e.g. Hostinger)

Database dump (run over SSH, or via hPanel's phpMyAdmin export):

```
mysqldump -u DB_USERNAME -p DB_DATABASE > backup-$(date +%F).sql
```

Media (uploaded files live under `storage/app/public/`, symlinked to
`public/storage`):

```
tar -czf media-backup-$(date +%F).tar.gz storage/app/public
```

Restore is the reverse: import the SQL dump into a fresh/target database,
extract the media archive back to `storage/app/public/`, then
`php artisan storage:link` if the symlink doesn't already exist.

## Automated backups

No in-app scheduler runs backups automatically — `backup:run` is a plain
artisan command, meant to be triggered by a real cron entry on the host.
Example (Hostinger or any cron-capable host), daily at 3am:

```
0 3 * * * cd /path/to/site && php artisan backup:run --type=full >> /dev/null 2>&1
```

Download the resulting archives off-server periodically (Admin → Backups
lists and downloads them) — a cron job alone doesn't get them off the host.

## Retention recommendation

Keep at least the last 7 daily backups and 4 weekly backups off-server
(downloaded somewhere other than the host itself) — a host-level incident
should never be the only copy lost. Admin → Backups only supports manual
deletion (owner-only, no automatic pruning) to avoid a retention policy
silently deleting an admin's only copy — pruning old backups is a manual
decision. Hostinger also offers host-level backup options in hPanel; those
are a useful second layer but are separate from, and shouldn't replace,
your own application-level backups above.
