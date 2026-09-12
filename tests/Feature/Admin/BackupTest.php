<?php

namespace Tests\Feature\Admin;

use App\Models\Backup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class BackupTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Storage::fake('public');
    }

    public function test_owner_can_create_a_database_backup(): void
    {
        $owner = User::factory()->owner()->create();

        $response = $this->actingAs($owner)->post(route('admin.backups.store'), ['type' => 'database']);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $backup = Backup::firstOrFail();
        $this->assertSame('database', $backup->type);
        $this->assertSame($owner->id, $backup->created_by);

        $fullPath = Storage::disk('local')->path($backup->path);
        $this->assertFileExists($fullPath);
        $this->assertSame(hash_file('sha256', $fullPath), $backup->checksum_sha256);
    }

    public function test_owner_can_create_a_full_backup(): void
    {
        $owner = User::factory()->owner()->create();
        Storage::disk('public')->put('media/test-icon.png', 'fake-image-bytes');

        $response = $this->actingAs($owner)->post(route('admin.backups.store'), ['type' => 'full']);

        $response->assertRedirect();
        $backup = Backup::where('type', 'full')->firstOrFail();
        $this->assertFileExists(Storage::disk('local')->path($backup->path));
    }

    public function test_content_editor_cannot_access_backups(): void
    {
        $editor = User::factory()->contentEditor()->create();

        $this->actingAs($editor)->get(route('admin.backups.index'))->assertForbidden();
        $this->actingAs($editor)->post(route('admin.backups.store'), ['type' => 'database'])->assertForbidden();
    }

    public function test_backup_archive_never_contains_the_env_file(): void
    {
        $owner = User::factory()->owner()->create();
        Storage::disk('public')->put('media/test-icon.png', 'fake-image-bytes');

        $this->actingAs($owner)->post(route('admin.backups.store'), ['type' => 'full']);

        $backup = Backup::where('type', 'full')->firstOrFail();
        $zip = new ZipArchive;
        $zip->open(Storage::disk('local')->path($backup->path));

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $this->assertStringNotContainsString('.env', $zip->getNameIndex($i));
        }
    }

    public function test_owner_can_download_a_backup(): void
    {
        $owner = User::factory()->owner()->create();
        $this->actingAs($owner)->post(route('admin.backups.store'), ['type' => 'database']);
        $backup = Backup::firstOrFail();

        $response = $this->actingAs($owner)->get(route('admin.backups.download', $backup));

        $response->assertOk();
    }

    public function test_owner_can_delete_a_backup_and_it_is_removed_from_disk(): void
    {
        $owner = User::factory()->owner()->create();
        $this->actingAs($owner)->post(route('admin.backups.store'), ['type' => 'database']);
        $backup = Backup::firstOrFail();
        $path = $backup->path;

        $this->actingAs($owner)->delete(route('admin.backups.destroy', $backup));

        $this->assertDatabaseMissing('backups', ['id' => $backup->id]);
        Storage::disk('local')->assertMissing($path);
    }

    public function test_creating_a_backup_writes_an_audit_log_entry(): void
    {
        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)->post(route('admin.backups.store'), ['type' => 'database']);

        $this->assertDatabaseHas('audit_logs', ['action' => 'backup.created', 'user_id' => $owner->id]);
    }

    public function test_backup_run_command_creates_a_backup_with_no_created_by(): void
    {
        Artisan::call('backup:run', ['--type' => 'database']);

        $backup = Backup::firstOrFail();
        $this->assertNull($backup->created_by);
    }
}
