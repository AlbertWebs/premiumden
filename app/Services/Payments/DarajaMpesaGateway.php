<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Illuminate\Validation\ValidationException;

class DarajaMpesaGateway implements PaymentGatewayInterface
{
    public function name(): string { return 'daraja'; }

    public function initiate(Payment $payment, string $phone): array
    {
        $this->assertConfigured();
        $phone = preg_replace('/\D+/', '', $phone) ?? '';
        if (str_starts_with($phone, '0')) $phone = '254'.substr($phone, 1);
        if (str_starts_with($phone, '+')) $phone = substr($phone, 1);
        if (! preg_match('/^254[17]\d{8}$/', $phone)) {
            throw ValidationException::withMessages(['phone' => 'Enter a valid Kenyan mobile number, such as 0712345678.']);
        }
        if (((int) round((float) $payment->amount * 100)) % 100 !== 0) {
            throw ValidationException::withMessages(['payment' => 'M-Pesa STK payments require a whole-shilling invoice amount.']);
        }
        if ($payment->status === 'processing' && ! empty($payment->metadata['checkout_request_id'])) {
            throw ValidationException::withMessages(['payment' => 'An M-Pesa prompt is already in progress for this invoice. Wait for the result before retrying.']);
        }

        $invoice = $payment->invoice()->with('application')->firstOrFail();
        $timestamp = now()->format('YmdHis');
        $shortCode = (string) config('services.payment.daraja.shortcode');
        $passkey = (string) config('services.payment.daraja.passkey');
        $token = $this->accessToken();
        $callbackUrl = route('payments.webhook', ['driver' => 'daraja']);
        if (app()->environment('production') && parse_url($callbackUrl, PHP_URL_SCHEME) !== 'https') {
            throw new ServiceUnavailableHttpException(null, 'The M-Pesa callback URL must use HTTPS.');
        }
        $response = Http::acceptJson()->withToken($token)->timeout(20)->post($this->baseUrl().'/mpesa/stkpush/v1/processrequest', [
            'BusinessShortCode' => $shortCode,
            'Password' => base64_encode($shortCode.$passkey.$timestamp),
            'Timestamp' => $timestamp,
            'TransactionType' => config('services.payment.daraja.transaction_type', 'CustomerPayBillOnline'),
            'Amount' => (int) $payment->amount,
            'PartyA' => $phone,
            'PartyB' => $shortCode,
            'PhoneNumber' => $phone,
            'CallBackURL' => $callbackUrl,
            'AccountReference' => substr(preg_replace('/[^A-Za-z0-9]/', '', $invoice->reference) ?: 'PBDMEMBERSHIP', 0, 12),
            'TransactionDesc' => 'Premium Business Den membership',
        ]);

        if (! $response->successful() || ! $response->json('CheckoutRequestID')) {
            report(new \RuntimeException('Daraja STK push initiation failed: '.$response->status()));
            throw ValidationException::withMessages(['payment' => 'The M-Pesa request could not be started. Check the phone number and try again.']);
        }

        return [
            'status' => 'processing',
            'message' => 'M-Pesa prompt sent. Enter your PIN on your phone to complete payment.',
            'provider_reference' => $payment->provider_reference,
            'metadata' => [
                'checkout_request_id' => $response->json('CheckoutRequestID'),
                'merchant_request_id' => $response->json('MerchantRequestID'),
                'phone' => $phone,
            ],
        ];
    }

    public function verifyCallback(Request $request): array
    {
        $this->assertConfigured();
        $allowedIps = (array) config('services.payment.daraja.callback_ips', []);
        if (app()->environment('production') && $allowedIps === []) {
            throw new ServiceUnavailableHttpException(null, 'The Daraja callback IP allowlist is not configured.');
        }
        if ($allowedIps !== [] && ! in_array($request->ip(), $allowedIps, true)) {
            throw new UnauthorizedHttpException('Daraja', 'Callback source address is not allowed.');
        }

        $callback = $request->input('Body.stkCallback');
        if (! is_array($callback) || empty($callback['CheckoutRequestID'])) {
            throw ValidationException::withMessages(['callback' => 'The M-Pesa callback payload is invalid.']);
        }

        $payment = Payment::where('provider', $this->name())
            ->where('metadata->checkout_request_id', $callback['CheckoutRequestID'])
            ->with('invoice')
            ->first();
        if (! $payment) throw ValidationException::withMessages(['payment' => 'The M-Pesa checkout request is not recognized.']);
        $expectedMerchantRequestId = $payment->metadata['merchant_request_id'] ?? null;
        if ($expectedMerchantRequestId && ($callback['MerchantRequestID'] ?? null) !== $expectedMerchantRequestId) {
            throw new UnauthorizedHttpException('Daraja', 'The callback merchant request does not match the recorded checkout.');
        }

        $query = $this->queryStkStatus((string) $callback['CheckoutRequestID']);
        if ((string) ($callback['ResultCode'] ?? '') !== '0' || (string) ($query['ResultCode'] ?? '') !== '0') {
            return [
                'event_id' => 'daraja-'.$callback['CheckoutRequestID'],
                'payment_reference' => $payment->provider_reference,
                'provider_reference' => null,
                'status' => 'failed',
                'amount' => $payment->amount,
                'currency' => $payment->currency,
            ];
        }

        $items = collect(data_get($callback, 'CallbackMetadata.Item', []))->keyBy('Name');
        $receipt = $items->get('MpesaReceiptNumber')['Value'] ?? null;
        $amount = $items->get('Amount')['Value'] ?? null;
        $callbackPhone = isset($items->get('PhoneNumber')['Value']) ? (string) $items->get('PhoneNumber')['Value'] : null;
        if (! $receipt || $amount === null) throw ValidationException::withMessages(['callback' => 'The successful M-Pesa callback omitted transaction details.']);
        if ($callbackPhone && $callbackPhone !== ($payment->metadata['phone'] ?? null)) {
            throw ValidationException::withMessages(['callback' => 'The M-Pesa phone number does not match this checkout request.']);
        }

        return [
            'event_id' => 'daraja-'.$callback['CheckoutRequestID'],
            'payment_reference' => $payment->provider_reference,
            'provider_reference' => (string) $receipt,
            'status' => 'paid',
            'amount' => number_format((float) $amount, 2, '.', ''),
            'currency' => $payment->currency,
        ];
    }

    private function queryStkStatus(string $checkoutRequestId): array
    {
        $shortCode = (string) config('services.payment.daraja.shortcode');
        $timestamp = now()->format('YmdHis');
        $password = base64_encode($shortCode.(string) config('services.payment.daraja.passkey').$timestamp);
        $response = Http::acceptJson()->withToken($this->accessToken())->timeout(20)->post($this->baseUrl().'/mpesa/stkpushquery/v1/query', [
            'BusinessShortCode' => $shortCode, 'Password' => $password, 'Timestamp' => $timestamp,
            'CheckoutRequestID' => $checkoutRequestId,
        ]);
        if (! $response->successful()) throw new ServiceUnavailableHttpException(null, 'M-Pesa payment verification is temporarily unavailable.');
        return $response->json() ?? [];
    }

    private function accessToken(): string
    {
        $environment = (string) config('services.payment.daraja.environment', 'sandbox');
        $token = cache()->remember('daraja_access_token_'.$environment, now()->addMinutes(50), function (): string {
            $response = Http::acceptJson()->withBasicAuth(
                (string) config('services.payment.daraja.consumer_key'),
                (string) config('services.payment.daraja.consumer_secret'),
            )->timeout(15)->get($this->baseUrl().'/oauth/v1/generate', ['grant_type' => 'client_credentials']);
            if (! $response->successful() || ! $response->json('access_token')) {
                throw new ServiceUnavailableHttpException(null, 'M-Pesa authorization is temporarily unavailable.');
            }
            return (string) $response->json('access_token');
        });
        return $token;
    }

    private function baseUrl(): string
    {
        return config('services.payment.daraja.environment') === 'production'
            ? 'https://api.safaricom.co.ke'
            : 'https://sandbox.safaricom.co.ke';
    }

    private function assertConfigured(): void
    {
        foreach (['consumer_key', 'consumer_secret', 'shortcode', 'passkey'] as $key) {
            if (! config('services.payment.daraja.'.$key)) {
                throw new ServiceUnavailableHttpException(null, 'M-Pesa payment settings are incomplete.');
            }
        }
        if (config('services.payment.daraja.environment') === 'production' && app()->environment() !== 'production') {
            throw new ServiceUnavailableHttpException(null, 'Production M-Pesa credentials cannot be used outside production.');
        }
    }
}
