# Backup & Restore

An **Admin → Backups** screen (one-click downloadable backup packages) is
not built yet — that's Phase 2. What follows is the manual procedure that
works today, both locally and on Hostinger, until that admin UI exists.

## What to back up

1. **The database** (all app/content/settings/support data).
2. **Uploaded media** — `storage/app/public/` (icons, screenshots, feature
   graphics, and anything else uploaded through the Media Library).
3. **Never back up `.env`** or any file containing secrets/credentials —
   restoring a backup should never leak production keys, and a backup
   package should not need them to be useful for data recovery.

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

## Retention recommendation

Keep at least the last 7 daily backups and 4 weekly backups off-server
(downloaded somewhere other than the host itself) — a host-level incident
should never be the only copy lost. Hostinger also offers host-level backup
options in hPanel; those are a useful second layer but are separate from,
and shouldn't replace, your own application-level backups above.

## Phase 2

Once built, **Admin → Backups** will wrap the steps above into a UI
(manual "back up now" plus documented automated/cron-based backups),
producing downloadable packages that explicitly exclude `.env` and any
plaintext credentials, per the spec's requirement.
