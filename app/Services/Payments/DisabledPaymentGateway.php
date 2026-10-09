<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Payment;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

class DisabledPaymentGateway implements PaymentGatewayInterface
{
    public function name(): string { return 'disabled'; }

    public function initiate(Payment $payment, string $phone): array
    {
        throw new ServiceUnavailableHttpException(null, 'Payment collection has not been configured.');
    }

    public function verifyCallback(Request $request): array
    {
        throw new ServiceUnavailableHttpException(null, 'Payment collection has not been configured.');
    }
}
