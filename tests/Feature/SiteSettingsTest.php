<?php

namespace Tests\Feature;

use App\Mail\ContactEnquiryReceived;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SiteSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_content_admin_can_change_contact_and_default_seo_settings(): void
    {
        $admin = User::where('email', 'content@premiumden.test')->firstOrFail();
        $this->actingAs($admin)->get(route('admin.settings.edit'))->assertOk()->assertSee('Website settings');
        $this->actingAs($admin)->put(route('admin.settings.update'), [
            'public_contact_email' => 'hello@example.test', 'public_contact_phone' => '+254 700 000 010',
            'office_location' => 'Nairobi, Kenya', 'contact_recipient_email' => 'office@example.test',
            'default_seo_title' => 'Premium Business Den Kenya', 'default_seo_description' => 'A private society for business leaders in Kenya.',
            'home_hero_title' => "A new hero\nfor the Den.", 'home_hero_intro' => 'A configurable hero message.',
            'home_intro_heading' => "A shared\ndirection.", 'home_intro_body' => 'Editable introduction text.',
            'about_heading' => "A new\nabout heading.", 'about_body' => 'A private society.\n\nBuilt around trusted business relationships.',
            'how_to_join_heading' => "A simple\njoining process.", 'how_to_join_body' => 'Updated application guidance.',
            'faq_items' => 'How do I apply? | Submit an application for review.',
        ])->assertRedirect();
        $this->get(route('contact'))->assertOk()->assertSee('hello@example.test')->assertSee('+254 700 000 010');
        $this->get(route('about'))->assertOk()->assertSee('A private society.')->assertSee('Built around trusted business relationships.');
        $this->get(route('home'))->assertOk()->assertSee('A configurable hero message.')->assertSee('How do I apply?')->assertSee('Submit an application for review.');

        Mail::fake();
        $this->post(route('contact.store'), ['name' => 'Visitor', 'email' => 'visitor@example.test', 'subject' => 'Question', 'message' => 'Hello.'])->assertRedirect();
        Mail::assertSent(ContactEnquiryReceived::class, fn (ContactEnquiryReceived $mail) => $mail->hasTo('office@example.test'));
    }

    public function test_member_cannot_change_public_site_settings(): void
    {
        $this->actingAs(User::where('email', 'gold@premiumden.test')->firstOrFail())->get(route('admin.settings.edit'))->assertForbidden();
    }
}
