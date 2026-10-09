<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Mail\MemberFirstAccess;
use App\Mail\MemberWelcome;
use App\Mail\MemberPaymentConfirmed;
use App\Mail\ApplicantInvoiceIssued;
use App\Models\Invoice;
use App\Models\MembershipApplication;
use App\Models\MembershipPackage;
use App\Models\Payment;
use App\Models\User;
use App\Models\ApplicantDocument;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use App\Services\TransitionMembershipApplication;
use Tests\TestCase;

class PaymentActivationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        config(['services.payment.driver' => 'mock', 'services.payment.callback_secret' => 'testing-callback-secret']);
        Mail::fake();
    }

    public function test_signed_verified_payment_activates_membership_once_and_issues_first_access(): void
    {
        [$application, $payment] = $this->awaitingPayment();
        $event = ['event_id' => 'evt-pbd-001', 'payment_reference' => $payment->provider_reference, 'provider_reference' => 'gw-tx-441', 'status' => 'paid', 'amount' => '2500.00', 'currency' => 'KES'];

        $this->sendEvent($event)->assertOk()->assertJson(['received' => true]);
        $this->sendEvent($event)->assertOk();

        $this->assertSame(ApplicationStatus::MembershipActivated, $application->fresh()->status);
        $this->assertDatabaseCount('memberships', 1);
        $this->assertDatabaseCount('membership_cards', 1);
        $this->assertDatabaseCount('welcome_packages', 1);
        $this->assertDatabaseCount('payment_attempts', 1);
        $member = User::where('email', 'newmember@example.test')->firstOrFail();
        $this->assertTrue($member->membership_active);
        $this->assertNotNull($member->first_access_token_hash);

        Mail::assertSent(MemberFirstAccess::class, fn (MemberFirstAccess $mail) => $mail->hasTo($member->email) && str_contains($mail->setupUrl, 'signature='));
        Mail::assertSent(MemberWelcome::class, fn (MemberWelcome $mail) => $mail->hasTo($member->email) && str_contains($mail->membership->number, 'PBD-'));
        Mail::assertSent(MemberPaymentConfirmed::class, fn (MemberPaymentConfirmed $mail) => $mail->hasTo($member->email) && $mail->payment->status === 'paid');
        $setupMail = Mail::sent(MemberFirstAccess::class)->first();
        $this->get($setupMail->setupUrl)->assertOk()->assertSee('Set your password');
        parse_str(parse_url($setupMail->setupUrl, PHP_URL_QUERY), $query);
        $this->post($setupMail->setupUrl, ['password' => 'member-password-strong-2026', 'password_confirmation' => 'member-password-strong-2026', '_token' => csrf_token()])
            ->assertRedirect(route('login'));
        $this->assertNull($member->fresh()->first_access_token_hash);
        $this->get($setupMail->setupUrl)->assertNotFound();
        $this->post(route('login.store'), ['email' => $member->email, 'password' => 'member-password-strong-2026'])->assertRedirect(route('member.dashboard'));
        $this->get(route('member.card.show'))->assertOk()->assertSee($member->membership->first()->number);
    }

    public function test_unsigned_callback_cannot_confirm_payment(): void
    {
        [$application, $payment] = $this->awaitingPayment();
        $this->postJson(route('payments.webhook', 'mock'), ['event_id' => 'evt-invalid', 'payment_reference' => $payment->provider_reference, 'status' => 'paid', 'amount' => '2500.00', 'currency' => 'KES'])
            ->assertUnauthorized();
        $this->assertSame(ApplicationStatus::AwaitingPayment, $application->fresh()->status);
        $this->assertDatabaseCount('memberships', 0);
    }

    public function test_callback_amount_must_match_invoice(): void
    {
        [$application, $payment] = $this->awaitingPayment();
        $this->sendEvent(['event_id' => 'evt-wrong-amount', 'payment_reference' => $payment->provider_reference, 'status' => 'paid', 'amount' => '1.00', 'currency' => 'KES'])
            ->assertUnprocessable();
        $this->assertSame(ApplicationStatus::AwaitingPayment, $application->fresh()->status);
        $this->assertDatabaseCount('memberships', 0);
        $this->assertDatabaseHas('payment_attempts', ['provider_event_id' => 'evt-wrong-amount', 'status' => 'rejected']);
    }

    public function test_membership_team_issues_configured_invoice_after_document_approval(): void
    {
        config(['services.payment.driver' => 'mock']);
        Mail::fake();
        $package = MembershipPackage::where('slug', 'gold')->firstOrFail();
        $package->update(['price' => 2500]);
        $application = MembershipApplication::create([
            'reference' => 'PBD-2610-INVOICE1', 'membership_package_id' => $package->id,
            'status' => ApplicationStatus::DocumentsReceived, 'full_name' => 'Invoice Applicant', 'email' => 'invoice@example.test', 'phone' => '+254700000001',
            'company' => 'Example Ltd', 'job_title' => 'Director', 'industry' => 'Trade', 'location' => 'Nairobi', 'consent_at' => now(), 'submitted_at' => now(),
        ]);
        $admin = User::where('email', 'membership@premiumden.test')->firstOrFail();
        $transition = app(TransitionMembershipApplication::class);

        $transition->handle($application, $admin, ApplicationStatus::InvoiceIssued);
        $invoice = $application->fresh()->invoice;
        $this->assertSame('2500.00', $invoice->total);
        $this->assertSame('pending', $invoice->status);
        $this->assertDatabaseCount('invoice_items', 1);
        Mail::assertSent(ApplicantInvoiceIssued::class, fn (ApplicantInvoiceIssued $mail) => $mail->hasTo('invoice@example.test') && str_contains($mail->invoiceUrl, 'signature='));
        $invoiceMail = Mail::sent(ApplicantInvoiceIssued::class)->first();
        $this->get($invoiceMail->invoiceUrl)->assertOk()->assertSee('KES 2,500.00')->assertSee($invoice->reference);
        $this->get(str_replace('signature=', 'signature=x', $invoiceMail->invoiceUrl))->assertForbidden();

        $transition->handle($application->fresh(), $admin, ApplicationStatus::AwaitingPayment);
        $this->assertDatabaseHas('payments', ['invoice_id' => $invoice->id, 'provider' => 'mock', 'status' => 'pending']);
    }

    private function awaitingPayment(): array
    {
        $package = MembershipPackage::where('slug', 'gold')->firstOrFail();
        $package->update(['price' => 2500]);
        $application = MembershipApplication::create([
            'reference' => 'PBD-2610-PAY123', 'membership_package_id' => $package->id,
            'status' => ApplicationStatus::AwaitingPayment, 'full_name' => 'New Member', 'email' => 'newmember@example.test', 'phone' => '+254700000001',
            'company' => 'Example Ltd', 'job_title' => 'Director', 'industry' => 'Trade', 'location' => 'Nairobi', 'consent_at' => now(), 'submitted_at' => now(),
        ]);
        $invoice = Invoice::create(['membership_application_id' => $application->id, 'reference' => 'PBD-INV-TEST123', 'status' => 'awaiting_payment', 'currency' => 'KES', 'subtotal' => 2500, 'total' => 2500, 'issued_at' => now()]);
        $invoice->items()->create(['description' => 'Gold membership', 'quantity' => 1, 'unit_amount' => 2500, 'amount' => 2500]);
        $payment = Payment::create(['invoice_id' => $invoice->id, 'provider' => 'mock', 'provider_reference' => 'PBD-PAY-TEST123', 'status' => 'pending', 'currency' => 'KES', 'amount' => 2500]);

        return [$application, $payment];
    }

    private function sendEvent(array $event): \Illuminate\Testing\TestResponse
    {
        $body = json_encode($event, JSON_THROW_ON_ERROR);

        return $this->call('POST', route('payments.webhook', 'mock'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_PAYMENT_SIGNATURE' => hash_hmac('sha256', $body, 'testing-callback-secret'),
        ], $body);
    }
}
