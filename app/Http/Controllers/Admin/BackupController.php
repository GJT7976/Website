<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Backup;
use App\Services\AuditLogger;
use App\Services\BackupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupController extends Controller
{
    public function index(): View
    {
        return view('admin.backups.index', [
            'backups' => Backup::with('creator')->latest()->get(),
        ]);
    }

    public function store(Request $request, BackupService $backups): RedirectResponse
    {
        $data = $request->validate(['type' => ['required', 'in:database,media,full']]);

        try {
            $backup = match ($data['type']) {
                'database' => $backups->createDatabaseDump(Auth::id()),
                'media' => $backups->createMediaArchive(Auth::id()),
                'full' => $backups->createFullBackup(Auth::id()),
            };
        } catch (\Throwable $e) {
            return back()->withErrors(['backup' => 'Backup failed: '.$e->getMessage()]);
        }

        AuditLogger::record('backup.created', $backup, null, ['type' => $backup->type, 'file_size' => $backup->file_size]);

        return back()->with('status', 'Backup created.');
    }

    public function download(Backup $backup): StreamedResponse
    {
        return Storage::disk($backup->disk)->download($backup->path, $backup->filename);
    }

    public function destroy(Backup $backup, BackupService $backups): RedirectResponse
    {
        AuditLogger::record('backup.deleted', null, $backup->only(['type', 'filename', 'file_size']), null, $backup->filename);

        $backups->delete($backup);

        return back()->with('status', 'Backup deleted.');
    }
}
