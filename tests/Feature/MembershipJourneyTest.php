<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Mail\ApplicantDocumentsRequested;
use App\Mail\ApplicantInvoiceIssued;
use App\Mail\MemberFirstAccess;
use App\Models\MembershipApplication;
use App\Models\MembershipPackage;
use App\Models\Conversation;
use App\Models\Payment;
use App\Models\User;
use App\Services\TransitionMembershipApplication;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Tests\TestCase;

class MembershipJourneyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        Storage::fake('local');
        config(['services.payment.driver' => 'mock', 'services.payment.callback_secret' => 'journey-test-secret']);
        Mail::fake();
        $this->withoutMiddleware(ThrottleRequests::class);
    }

    public function test_visitor_completes_application_to_member_messaging_journey(): void
    {
        $package = MembershipPackage::where('slug', 'platinum')->firstOrFail();
        $package->update(['price' => 18000]);
        $this->get(route('application.intro'))->assertOk();
        $this->post(route('application.store'), [
            'membership_package_id' => $package->id,
            'full_name' => 'Journey Applicant', 'email' => 'journey@example.test', 'phone' => '+254711222333',
            'company' => 'Journey Enterprises', 'job_title' => 'Managing Director', 'industry' => 'Trade', 'location' => 'Nairobi',
            'biography' => 'Interested in regional trade and leadership.',
            'references' => [
                ['full_name' => 'Diamond Member', 'email' => 'diamond@premiumden.test'],
                ['full_name' => 'Gold Member', 'email' => 'gold@premiumden.test'],
            ], 'consent' => '1',
        ])->assertRedirect();
        $application = MembershipApplication::where('email', 'journey@example.test')->firstOrFail();
        $this->get(route('application.received', $application->reference))->assertOk()->assertSee($application->reference);

        $transition = app(TransitionMembershipApplication::class);
        $risk = User::where('email', 'risk@premiumden.test')->firstOrFail();
        $membershipAdmin = User::where('email', 'membership@premiumden.test')->firstOrFail();
        $transition->handle($application, $risk, ApplicationStatus::UnderReview);
        $transition->handle($application->fresh(), $risk, ApplicationStatus::Vetting);
        $transition->handle($application->fresh(), $risk, ApplicationStatus::Approved);
        $transition->handle($application->fresh(), $membershipAdmin, ApplicationStatus::DocumentsRequired);
        $uploadMail = Mail::sent(ApplicantDocumentsRequested::class)->first();
        $this->get($uploadMail->uploadUrl)->assertOk();
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a7X8AAAAASUVORK5CYII=');
        $this->post($uploadMail->uploadUrl, [
            'photo' => UploadedFile::fake()->createWithContent('portrait.png', $png),
            'identity' => UploadedFile::fake()->createWithContent('passport.pdf', "%PDF-1.4\nidentity"),
            'identity_type' => 'passport',
        ])->assertRedirect();
        $this->assertDatabaseCount('applicant_documents', 2);

        foreach ($application->fresh()->documents as $document) {
            $this->actingAs($membershipAdmin)->patch(route('admin.documents.decide', $document), ['status' => 'approved'])->assertRedirect();
        }
        $this->post(route('logout'))->assertRedirect(route('home'));
        $transition->handle($application->fresh(), $membershipAdmin, ApplicationStatus::DocumentsReceived);
        $transition->handle($application->fresh(), $membershipAdmin, ApplicationStatus::InvoiceIssued);
        $invoiceMail = Mail::sent(ApplicantInvoiceIssued::class)->first();
        $this->get($invoiceMail->invoiceUrl)->assertOk()->assertSee('KES 18,000.00');
        $transition->handle($application->fresh(), $membershipAdmin, ApplicationStatus::AwaitingPayment);
        $payment = Payment::firstOrFail();

        $event = ['event_id' => 'journey-paid-001', 'payment_reference' => $payment->provider_reference, 'provider_reference' => 'gateway-transaction-001', 'status' => 'paid', 'amount' => '18000.00', 'currency' => 'KES'];
        $body = json_encode($event, JSON_THROW_ON_ERROR);
        $this->call('POST', route('payments.webhook', 'mock'), [], [], [], [
            'CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_PAYMENT_SIGNATURE' => hash_hmac('sha256', $body, 'journey-test-secret'),
        ], $body)->assertOk();
        $this->assertSame(ApplicationStatus::MembershipActivated, $application->fresh()->status);

        $firstAccess = Mail::sent(MemberFirstAccess::class)->first();
        $this->get($firstAccess->setupUrl)->assertOk();
        $this->post($firstAccess->setupUrl, ['password' => 'journey-member-password-2026', 'password_confirmation' => 'journey-member-password-2026'])->assertRedirect(route('login'));
        $member = User::where('email', 'journey@example.test')->firstOrFail();
        $this->assertSame('member', $member->role->value);
        $this->assertTrue($member->membership_active);
        $this->assertTrue(Hash::check('journey-member-password-2026', $member->password));
        $this->post(route('login.store'), ['email' => $member->email, 'password' => 'journey-member-password-2026'])->assertRedirect(route('member.dashboard'));
        $this->get(route('member.dashboard'))->assertOk()->assertSee('Welcome,');
        $this->get(route('member.membership.show'))->assertOk()->assertSee($member->membership->first()->number);
        $this->get(route('member.card.show'))->assertOk()->assertSee($member->membership->first()->number);
        $this->get(route('member.directory.index'))->assertOk()->assertSee('Diamond Member');

        $diamond = User::where('email', 'diamond@premiumden.test')->firstOrFail();
        $this->post(route('member.messages.start', $diamond))->assertRedirect();
        $conversation = Conversation::firstOrFail();
        $this->post(route('member.messages.store', $conversation), ['body' => 'Looking forward to a conversation about trade.'])->assertRedirect();
        $this->actingAs($diamond)->get(route('member.notifications.index'))->assertOk()->assertSee('New message from Journey Applicant');
    }
}
