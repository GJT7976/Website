<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_admin_can_upload_a_valid_image(): void
    {
        $admin = User::factory()->owner()->create();
        $file = UploadedFile::fake()->image('icon.png', 512, 512);

        $this->actingAs($admin)->post(route('admin.media.store'), [
            'file' => $file,
            'title' => 'Test Icon',
        ])->assertRedirect();

        $this->assertDatabaseHas('media', ['title' => 'Test Icon']);
    }

    public function test_upload_rejects_a_non_image_file(): void
    {
        $admin = User::factory()->owner()->create();
        $file = UploadedFile::fake()->create('malware.exe', 100, 'application/x-msdownload');

        $this->actingAs($admin)
            ->post(route('admin.media.store'), ['file' => $file])
            ->assertSessionHasErrors('file');

        $this->assertDatabaseCount('media', 0);
    }

    public function test_upload_rejects_svg_to_avoid_stored_script_risk(): void
    {
        $admin = User::factory()->owner()->create();
        $file = UploadedFile::fake()->create('icon.svg', 10, 'image/svg+xml');

        $this->actingAs($admin)
            ->post(route('admin.media.store'), ['file' => $file])
            ->assertSessionHasErrors('file');
    }

    public function test_upload_rejects_a_file_over_the_size_limit(): void
    {
        $admin = User::factory()->owner()->create();
        $file = UploadedFile::fake()->create('big.jpg', 9000, 'image/jpeg'); // 9000 KB > 8192 KB limit

        $this->actingAs($admin)
            ->post(route('admin.media.store'), ['file' => $file])
            ->assertSessionHasErrors('file');
    }

    public function test_guest_cannot_upload_media(): void
    {
        $file = UploadedFile::fake()->image('icon.png');

        $this->post(route('admin.media.store'), ['file' => $file])
            ->assertRedirect(route('admin.login'));
    }
}
