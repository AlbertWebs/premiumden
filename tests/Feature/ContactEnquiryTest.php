<?php

namespace Tests\Feature;

use App\Mail\ContactEnquiryReceived;
use App\Models\ContactEnquiry;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactEnquiryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_visitor_can_submit_contact_enquiry_to_admin_inbox(): void
    {
        Mail::fake();
        config(['services.contact.to_address' => 'office@example.test']);
        $this->get(route('contact'))->assertOk()->assertSee('Send enquiry');
        $this->post(route('contact.store'), [
            'name' => 'Visitor Example', 'email' => 'visitor@example.test', 'phone' => '+254700000005',
            'subject' => 'Membership question', 'message' => 'Please share details about the society.',
        ])->assertRedirect(route('contact'))->assertSessionHas('status');

        $enquiry = ContactEnquiry::firstOrFail();
        $this->assertSame('new', $enquiry->status);
        Mail::assertSent(ContactEnquiryReceived::class, fn (ContactEnquiryReceived $mail) => $mail->hasTo('office@example.test') && $mail->enquiry->is($enquiry));
        $admin = User::where('email', 'content@premiumden.test')->firstOrFail();
        $this->actingAs($admin)->get(route('admin.enquiries.index'))->assertOk()->assertSee('Membership question')->assertSee('Please share details about the society.');
        $this->actingAs($admin)->patch(route('admin.enquiries.update', $enquiry), ['status' => 'contacted'])->assertRedirect();
        $this->assertSame('contacted', $enquiry->fresh()->status);
        $this->assertNotNull($enquiry->fresh()->contacted_at);
    }

    public function test_honeypot_submissions_are_not_saved(): void
    {
        $this->post(route('contact.store'), [
            'name' => 'Automated sender', 'email' => 'bot@example.test', 'subject' => 'Spam', 'message' => 'Spam content.', 'company_website' => 'https://bot.example.test',
        ])->assertRedirect(route('contact'));
        $this->assertDatabaseCount('contact_enquiries', 0);
    }
}
