<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;

/**
 * The CLI counterpart to Admin → Backups' "create backup" buttons —
 * created_by is left null (no HTTP admin context), matching the nullable
 * FK. This is what a documented cron entry (see BACKUP_RESTORE.md) would
 * call; no in-app scheduler is registered anywhere for it (spec §30 asks
 * for automated backup *instructions*, not built-in automation).
 */
class BackupRunCommand extends Command
{
    protected $signature = 'backup:run {--type=full : database|media|full}';

    protected $description = 'Create a backup archive (database, media, or full).';

    public function handle(BackupService $backups): int
    {
        $type = $this->option('type');

        $backup = match ($type) {
            'database' => $backups->createDatabaseDump(),
            'media' => $backups->createMediaArchive(),
            'full' => $backups->createFullBackup(),
            default => null,
        };

        if ($backup === null) {
            $this->error("Unknown backup type \"{$type}\" — use database, media, or full.");

            return self::FAILURE;
        }

        $this->info("Backup created: {$backup->filename} ({$backup->humanSize()})");

        return self::SUCCESS;
    }
}
