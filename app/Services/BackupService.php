<?php

namespace App\Services;

use App\Models\Backup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use ZipArchive;

/**
 * On-demand backup creation (spec §30). Follows ReleaseLibrary's pattern:
 * real filesystem paths via Storage::disk('local')->path(...), a
 * hash_file('sha256', ...) checksum, never a public URL — see
 * BackupController::download(), which streams from the private disk.
 *
 * Backups never include .env (it lives outside storage/app/ entirely) or
 * the backups/ directory itself (no nesting a backup inside a backup).
 */
class BackupService
{
    private const DIRECTORY = 'backups';

    public function createDatabaseDump(?int $createdBy = null): Backup
    {
        [$path, $filename] = $this->dumpDatabase();

        return $this->persist('database', $path, $filename, $createdBy);
    }

    public function createMediaArchive(?int $createdBy = null): Backup
    {
        [$path, $filename] = $this->zipMedia();

        return $this->persist('media', $path, $filename, $createdBy);
    }

    public function createFullBackup(?int $createdBy = null): Backup
    {
        [$dbPath, $dbFilename] = $this->dumpDatabase();
        [$mediaPath, $mediaFilename] = $this->zipMedia();

        $filename = 'full-backup-'.now()->format('Y-m-d-His').'.zip';
        $path = self::DIRECTORY.'/'.$filename;
        $fullPath = Storage::disk('local')->path($path);

        $zip = new ZipArchive;
        if ($zip->open($fullPath, ZipArchive::CREATE) !== true) {
            throw new RuntimeException("Couldn't create full backup archive.");
        }
        $zip->addFile(Storage::disk('local')->path($dbPath), $dbFilename);
        $zip->addFile(Storage::disk('local')->path($mediaPath), $mediaFilename);
        $zip->close();

        Storage::disk('local')->delete([$dbPath, $mediaPath]);

        return $this->persist('full', $path, $filename, $createdBy);
    }

    public function delete(Backup $backup): void
    {
        Storage::disk($backup->disk)->delete($backup->path);
        $backup->delete();
    }

    /**
     * @return array{0: string, 1: string} [disk-relative path, filename]
     */
    private function dumpDatabase(): array
    {
        $driver = config('database.default');
        $connection = config("database.connections.{$driver}");
        $filename = "database-{$driver}-".now()->format('Y-m-d-His').'.sql';
        $path = self::DIRECTORY.'/'.$filename;
        $fullPath = Storage::disk('local')->path($path);

        Storage::disk('local')->makeDirectory(self::DIRECTORY);

        match ($driver) {
            // A portable SQL dump rather than VACUUM INTO's raw file copy:
            // VACUUM INTO cannot run inside a transaction (a real SQLite
            // limitation, not just a testing artifact — nothing prevents a
            // future code path from wrapping a request in one), so this
            // dumps via plain SELECTs instead, which work regardless.
            'sqlite' => $this->dumpSqlite($fullPath),
            'mysql', 'mariadb' => $this->runDump(
                'mysqldump',
                ['--single-transaction', '--no-tablespaces', '-h', $connection['host'], '-P', (string) $connection['port'], '-u', $connection['username'], $connection['database']],
                ['MYSQL_PWD' => $connection['password']],
                $fullPath,
            ),
            'pgsql' => $this->runDump(
                'pg_dump',
                ['-h', $connection['host'], '-p', (string) $connection['port'], '-U', $connection['username'], $connection['database']],
                ['PGPASSWORD' => $connection['password']],
                $fullPath,
            ),
            default => throw new RuntimeException("Backups are not supported for the \"{$driver}\" database driver."),
        };

        return [$path, $filename];
    }

    /**
     * Plain-SELECT SQL dump — schema (from sqlite_master) plus one INSERT
     * per row. Deliberately not VACUUM INTO (see dumpDatabase()'s comment).
     */
    private function dumpSqlite(string $outputPath): void
    {
        $handle = fopen($outputPath, 'w');

        $tables = DB::select("SELECT name, sql FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%'");

        foreach ($tables as $table) {
            fwrite($handle, $table->sql.";\n");

            foreach (DB::table($table->name)->get() as $row) {
                $row = (array) $row;
                $columns = implode(', ', array_map(fn ($c) => '"'.$c.'"', array_keys($row)));
                $values = implode(', ', array_map($this->quoteSqliteValue(...), array_values($row)));
                fwrite($handle, "INSERT INTO \"{$table->name}\" ({$columns}) VALUES ({$values});\n");
            }
        }

        fclose($handle);
    }

    private function quoteSqliteValue(mixed $value): string
    {
        return match (true) {
            $value === null => 'NULL',
            is_int($value), is_float($value) => (string) $value,
            default => DB::connection()->getPdo()->quote((string) $value),
        };
    }

    /**
     * Runs a dump binary with credentials passed via environment variable
     * (never a CLI argument) — avoids the password being visible via
     * `ps`/`/proc` on a shared host.
     */
    private function runDump(string $binary, array $args, array $env, string $outputPath): void
    {
        $result = Process::env($env)->run([$binary, ...$args])->throw();

        file_put_contents($outputPath, $result->output());
    }

    /**
     * @return array{0: string, 1: string} [disk-relative path, filename]
     */
    private function zipMedia(): array
    {
        $filename = 'media-'.now()->format('Y-m-d-His').'.zip';
        $path = self::DIRECTORY.'/'.$filename;
        $fullPath = Storage::disk('local')->path($path);

        Storage::disk('local')->makeDirectory(self::DIRECTORY);

        $zip = new ZipArchive;
        if ($zip->open($fullPath, ZipArchive::CREATE) !== true) {
            throw new RuntimeException("Couldn't create media backup archive.");
        }

        $this->addDiskToZip($zip, 'public', 'public');
        $this->addDiskToZip($zip, 'local', 'private', excludeDir: self::DIRECTORY);

        $zip->close();

        return [$path, $filename];
    }

    private function addDiskToZip(ZipArchive $zip, string $disk, string $zipPrefix, ?string $excludeDir = null): void
    {
        $root = rtrim(Storage::disk($disk)->path(''), '/\\');

        if (! is_dir($root)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            $relative = ltrim(str_replace('\\', '/', substr($file->getPathname(), strlen($root))), '/');

            if ($excludeDir !== null && str_starts_with($relative, $excludeDir.'/')) {
                continue;
            }

            if ($file->isFile()) {
                $zip->addFile($file->getPathname(), "{$zipPrefix}/{$relative}");
            }
        }
    }

    private function persist(string $type, string $path, string $filename, ?int $createdBy): Backup
    {
        $fullPath = Storage::disk('local')->path($path);

        return Backup::create([
            'type' => $type,
            'disk' => 'local',
            'path' => $path,
            'filename' => $filename,
            'file_size' => filesize($fullPath),
            'checksum_sha256' => hash_file('sha256', $fullPath),
            'created_by' => $createdBy,
        ]);
    }
}
