<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Mail\ApplicantInvoiceIssued;
use App\Mail\MembershipRenewalReminder;
use App\Models\Membership;
use App\Models\MembershipApplication;
use App\Models\MembershipCard;
use App\Models\MembershipPackage;
use App\Models\Payment;
use App\Services\SendMembershipRenewalReminders;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MembershipRenewalTest extends TestCase
{
    use RefreshDatabase;

    public function test_membership_admin_can_issue_and_verify_a_renewal(): void
    {
        $this->seed(DatabaseSeeder::class);
        Mail::fake();
        config(['services.payment.driver' => 'mock', 'services.payment.callback_secret' => 'renew-secret']);
        $member = User::where('email', 'gold@premiumden.test')->firstOrFail();
        $package = MembershipPackage::where('slug', 'gold')->firstOrFail();
        $package->update(['price' => 2500]);
        $application = MembershipApplication::create([
            'reference' => 'PBD-2610-RENEW01', 'membership_package_id' => $package->id, 'status' => ApplicationStatus::MembershipActivated,
            'full_name' => $member->name, 'email' => $member->email, 'phone' => '+254700000001', 'company' => 'Example',
            'job_title' => 'Director', 'industry' => 'Trade', 'location' => 'Nairobi', 'consent_at' => now(), 'submitted_at' => now(),
        ]);
        $membership = Membership::create([
            'user_id' => $member->id, 'membership_application_id' => $application->id, 'membership_package_id' => $package->id,
            'number' => 'PBD-26-RENEW01', 'status' => 'active', 'started_at' => today()->subMonths(10), 'expires_at' => today()->addMonths(2),
        ]);
        MembershipCard::create(['membership_id' => $membership->id, 'identifier' => (string) str()->uuid(), 'status' => 'active']);
        $admin = User::where('email', 'membership@premiumden.test')->firstOrFail();
        $this->actingAs($admin)->post(route('admin.memberships.renewals.store', $membership))->assertRedirect();
        $invoice = $application->fresh()->invoice;
        $this->assertSame('renewal', $invoice->kind);
        $payment = Payment::where('invoice_id', $invoice->id)->firstOrFail();
        Mail::assertSent(ApplicantInvoiceIssued::class, fn ($mail) => $mail->hasTo($member->email));

        $event = ['event_id' => 'renewal-event-1', 'payment_reference' => $payment->provider_reference, 'status' => 'paid', 'amount' => '2500.00', 'currency' => 'KES'];
        $body = json_encode($event, JSON_THROW_ON_ERROR);
        $response = $this->call('POST', route('payments.webhook', 'mock'), [], [], [], [
            'CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_PAYMENT_SIGNATURE' => hash_hmac('sha256', $body, 'renew-secret'),
        ], $body);
        $response->assertOk();
        $this->assertSame(ApplicationStatus::MembershipActivated, $application->fresh()->status);
        $this->assertTrue($member->fresh()->membership_active);
        $this->assertSame(today()->addMonths(14)->toDateString(), $membership->fresh()->expires_at->toDateString());
        $this->assertDatabaseHas('audit_events', ['action' => 'membership.renewed', 'resource_id' => (string) $membership->id]);
    }

    public function test_renewal_reminder_is_sent_once_for_each_expiry_date(): void
    {
        $this->seed(DatabaseSeeder::class);
        Mail::fake();
        $member = User::where('email', 'gold@premiumden.test')->firstOrFail();
        $package = MembershipPackage::where('slug', 'gold')->firstOrFail();
        $application = MembershipApplication::create([
            'reference' => 'PBD-2610-RENEW02', 'membership_package_id' => $package->id, 'status' => ApplicationStatus::MembershipActivated,
            'full_name' => $member->name, 'email' => $member->email, 'phone' => '0700000000', 'company' => 'Example',
            'job_title' => 'Director', 'industry' => 'Trade', 'location' => 'Nairobi', 'consent_at' => now(), 'submitted_at' => now(),
        ]);
        Membership::create([
            'user_id' => $member->id, 'membership_application_id' => $application->id, 'membership_package_id' => $package->id,
            'number' => 'PBD-26-RENEW02', 'status' => 'active', 'started_at' => today()->subMonths(11), 'expires_at' => today()->addDays(30),
        ]);
        $reminders = app(SendMembershipRenewalReminders::class);
        $this->assertSame(1, $reminders->handle());
        $this->assertSame(0, $reminders->handle());
        Mail::assertSentTimes(MembershipRenewalReminder::class, 1);
    }
}
