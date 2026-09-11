<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_content_editor_is_denied_the_settings_section(): void
    {
        $editor = User::factory()->contentEditor()->create();

        $this->actingAs($editor)->get(route('admin.settings.edit', 'business'))->assertForbidden();
    }

    public function test_content_editor_is_denied_the_users_section(): void
    {
        $editor = User::factory()->contentEditor()->create();

        $this->actingAs($editor)->get(route('admin.users.index'))->assertForbidden();
    }

    public function test_owner_can_access_settings_and_users(): void
    {
        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)->get(route('admin.settings.edit', 'business'))->assertOk();
        $this->actingAs($owner)->get(route('admin.users.index'))->assertOk();
    }

    public function test_content_editor_can_access_the_apps_and_media_sections(): void
    {
        $editor = User::factory()->contentEditor()->create();

        $this->actingAs($editor)->get(route('admin.apps.index'))->assertOk();
        $this->actingAs($editor)->get(route('admin.media.index'))->assertOk();
    }
}
