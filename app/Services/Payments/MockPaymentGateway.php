<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Payment;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class MockPaymentGateway implements PaymentGatewayInterface
{
    public function name(): string { return 'mock'; }

    public function initiate(Payment $payment, string $phone): array
    {
        if (app()->environment('production')) {
            throw new ServiceUnavailableHttpException(null, 'The mock payment gateway is disabled in production.');
        }

        return ['status' => 'processing', 'provider_reference' => $payment->provider_reference, 'message' => 'Mock payment request recorded. No money was collected.'];
    }

    public function verifyCallback(Request $request): array
    {
        if (app()->environment('production')) {
            throw new ServiceUnavailableHttpException(null, 'The mock payment gateway is disabled in production.');
        }

        $secret = (string) config('services.payment.callback_secret');
        if ($secret === '') {
            throw new ServiceUnavailableHttpException(null, 'Payment callback verification is not configured.');
        }

        $signature = (string) $request->header('X-Payment-Signature');
        $expected = hash_hmac('sha256', $request->getContent(), $secret);
        if ($signature === '' || ! hash_equals($expected, $signature)) {
            throw new UnauthorizedHttpException('Payment provider', 'Invalid payment callback signature.');
        }

        return $request->json()->all();
    }
}
