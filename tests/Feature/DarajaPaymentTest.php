<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Models\Invoice;
use App\Models\MembershipApplication;
use App\Models\MembershipPackage;
use App\Models\Payment;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class DarajaPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        config(['services.payment.driver' => 'daraja']);
        config(['services.payment.daraja' => [
            'environment' => 'sandbox', 'consumer_key' => 'consumer-key', 'consumer_secret' => 'consumer-secret',
            'shortcode' => '174379', 'passkey' => 'sandbox-passkey', 'transaction_type' => 'CustomerPayBillOnline',
            'callback_ips' => ['127.0.0.1'],
        ]]);
    }

    public function test_invoice_starts_an_mpesa_prompt_and_records_checkout_request(): void
    {
        [$invoice, $payment] = $this->openInvoice();
        Http::fake([
            '*oauth/v1/generate*' => Http::response(['access_token' => 'sandbox-token', 'expires_in' => 3600]),
            '*mpesa/stkpush/v1/processrequest' => Http::response([
                'ResponseCode' => '0', 'CheckoutRequestID' => 'ws_CO_123', 'MerchantRequestID' => 'merchant_123',
            ]),
        ]);
        $url = URL::temporarySignedRoute('application.invoice.pay', now()->addMinutes(10), ['reference' => $invoice->reference]);

        $this->post($url, ['phone' => '0712 345 678'])->assertRedirect();

        $payment->refresh();
        $this->assertSame('processing', $payment->status);
        $this->assertSame('ws_CO_123', $payment->metadata['checkout_request_id']);
        $this->assertSame('254712345678', $payment->metadata['phone']);
        $this->assertDatabaseHas('payment_attempts', ['payment_id' => $payment->id, 'status' => 'processing']);
        Http::assertSent(fn ($request) => str_contains($request->url(), 'processrequest')
            && $request['PhoneNumber'] === '254712345678'
            && $request['Amount'] === 2500);
    }

    public function test_callback_is_correlated_and_confirmed_with_daraja_status_query(): void
    {
        [$invoice, $payment, $application] = $this->openInvoice();
        $payment->update(['metadata' => ['checkout_request_id' => 'ws_CO_456', 'phone' => '254700000001']]);
        Http::fake([
            '*oauth/v1/generate*' => Http::response(['access_token' => 'sandbox-token', 'expires_in' => 3600]),
            '*mpesa/stkpushquery/v1/query' => Http::response(['ResponseCode' => '0', 'ResultCode' => '0', 'ResultDesc' => 'The service request is processed successfully.']),
        ]);

        $this->postJson(route('payments.webhook', 'daraja'), [
            'Body' => ['stkCallback' => [
                'MerchantRequestID' => 'merchant_456', 'CheckoutRequestID' => 'ws_CO_456', 'ResultCode' => 0,
                'CallbackMetadata' => ['Item' => [
                    ['Name' => 'Amount', 'Value' => 2500], ['Name' => 'MpesaReceiptNumber', 'Value' => 'QAB123XYZ'],
                    ['Name' => 'PhoneNumber', 'Value' => 254700000001],
                ]],
            ]],
        ])->assertOk()->assertJson(['received' => true]);

        $this->assertSame(ApplicationStatus::MembershipActivated, $application->fresh()->status);
        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertSame('QAB123XYZ', $payment->fresh()->metadata['gateway_transaction_reference']);
        Http::assertSent(fn ($request) => str_contains($request->url(), 'stkpushquery') && $request['CheckoutRequestID'] === 'ws_CO_456');
    }

    private function openInvoice(): array
    {
        $package = MembershipPackage::where('slug', 'gold')->firstOrFail();
        $package->update(['price' => 2500]);
        $application = MembershipApplication::create([
            'reference' => 'PBD-2610-MPESA1', 'membership_package_id' => $package->id,
            'status' => ApplicationStatus::AwaitingPayment, 'full_name' => 'M-Pesa Member', 'email' => 'mpesa@example.test', 'phone' => '+254700000001',
            'company' => 'Example Ltd', 'job_title' => 'Director', 'industry' => 'Trade', 'location' => 'Nairobi', 'consent_at' => now(), 'submitted_at' => now(),
        ]);
        $invoice = Invoice::create(['membership_application_id' => $application->id, 'reference' => 'PBD-INV-MPESA1', 'status' => 'awaiting_payment', 'currency' => 'KES', 'subtotal' => 2500, 'total' => 2500, 'issued_at' => now()]);
        $invoice->items()->create(['description' => 'Gold membership', 'quantity' => 1, 'unit_amount' => 2500, 'amount' => 2500]);
        $payment = Payment::create(['invoice_id' => $invoice->id, 'provider' => 'daraja', 'provider_reference' => 'PBD-PAY-MPESA1', 'status' => 'pending', 'currency' => 'KES', 'amount' => 2500]);

        return [$invoice, $payment, $application];
    }
}
