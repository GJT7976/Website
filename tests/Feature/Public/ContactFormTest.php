<?php

namespace Tests\Feature\Public;

use App\Models\SupportRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_visitor_can_submit_the_contact_form(): void
    {
        $response = $this->post(route('contact.store'), [
            'name' => 'Jane Baker',
            'email' => 'jane@example.com',
            'subject' => 'Question',
            'message' => 'Does this work offline?',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('support_requests', [
            'name' => 'Jane Baker',
            'email' => 'jane@example.com',
            'status' => 'new',
        ]);
    }

    public function test_contact_form_requires_name_email_and_message(): void
    {
        $this->post(route('contact.store'), [])
            ->assertSessionHasErrors(['name', 'email', 'message']);

        $this->assertDatabaseCount('support_requests', 0);
    }

    public function test_honeypot_field_silently_blocks_bot_submissions(): void
    {
        $response = $this->post(route('contact.store'), [
            'name' => 'Bot',
            'email' => 'bot@example.com',
            'message' => 'Buy cheap watches',
            'website' => 'https://spam.example.com',
        ]);

        $response->assertSessionHasErrors('website');
        $this->assertDatabaseCount('support_requests', 0);
    }
}
