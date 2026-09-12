<?php

namespace Tests\Feature;

use App\Models\App;
use App\Models\AppRelease;
use App\Models\CustomerEntitlement;
use App\Models\Order;
use App\Models\Platform;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class MyDownloadsTest extends TestCase
{
    use RefreshDatabase;

    private function androidEntitlement(string $email = 'jane@example.com', string $status = 'active', bool $downloadable = true): array
    {
        $app = App::factory()->create();
        $platform = Platform::create(['code' => 'android', 'name' => 'Android', 'sort_order' => 0]);

        Storage::fake('local');
        $file = UploadedFile::fake()->create('app.apk', 100);
        $path = $file->storeAs("releases/{$app->id}/android", 'test.apk', 'local');

        $release = AppRelease::create([
            'app_id' => $app->id,
            'platform_id' => $platform->id,
            'version' => '1.0.0',
            'released_at' => now()->toDateString(),
            'is_current' => true,
            'disk' => 'local',
            'path' => $path,
            'original_filename' => 'app.apk',
            'file_size' => 100,
            'customer_downloadable' => $downloadable,
        ]);

        $order = Order::factory()->create(['customer_email' => $email, 'payment_status' => 'paid']);

        $entitlement = CustomerEntitlement::create([
            'order_id' => $order->id,
            'app_id' => $app->id,
            'platform_id' => $platform->id,
            'access_type' => 'download',
            'customer_email' => $email,
            'status' => $status,
            'source' => 'purchase',
        ]);

        return [$app, $entitlement, $release, $order];
    }

    public function test_requesting_a_link_sends_mail_only_when_the_email_has_purchases(): void
    {
        Mail::fake();
        [, , , $order] = $this->androidEntitlement('jane@example.com');

        $this->post(route('my-downloads.send-link'), ['email' => 'jane@example.com'])->assertRedirect();
        Mail::assertSent(\App\Mail\MyDownloadsLink::class);

        Mail::fake();
        $this->post(route('my-downloads.send-link'), ['email' => 'nobody@example.com'])->assertRedirect();
        Mail::assertNothingSent();
    }

    public function test_signed_my_downloads_page_lists_the_entitlement(): void
    {
        [$app] = $this->androidEntitlement('jane@example.com');

        $url = URL::signedRoute('my-downloads.show', ['email' => 'jane@example.com']);

        $this->get($url)->assertOk()->assertSee($app->name)->assertSee('Android');
    }

    public function test_my_downloads_page_requires_a_valid_signature(): void
    {
        $this->get(route('my-downloads.show', ['email' => 'jane@example.com']))->assertForbidden();
    }

    public function test_active_entitlement_can_download_the_current_release(): void
    {
        [, $entitlement] = $this->androidEntitlement();

        $url = URL::temporarySignedRoute('downloads.show', now()->addMinutes(10), ['entitlement' => $entitlement->id]);

        $this->get($url)->assertOk();
    }

    public function test_revoked_entitlement_cannot_download(): void
    {
        [, $entitlement] = $this->androidEntitlement(status: 'revoked');

        $url = URL::temporarySignedRoute('downloads.show', now()->addMinutes(10), ['entitlement' => $entitlement->id]);

        $this->get($url)->assertForbidden();
    }

    public function test_non_customer_downloadable_release_cannot_be_downloaded(): void
    {
        [, $entitlement] = $this->androidEntitlement(downloadable: false);

        $url = URL::temporarySignedRoute('downloads.show', now()->addMinutes(10), ['entitlement' => $entitlement->id]);

        $this->get($url)->assertNotFound();
    }

    public function test_download_requires_a_valid_signature(): void
    {
        [, $entitlement] = $this->androidEntitlement();

        $this->get(route('downloads.show', ['entitlement' => $entitlement->id]))->assertForbidden();
    }
}
