<?php

namespace App\Contracts;

use App\Models\Payment;
use Illuminate\Http\Request;

interface PaymentGatewayInterface
{
    public function name(): string;

    /** @return array<string, mixed> */
    public function initiate(Payment $payment, string $phone): array;

    /** @return array<string, mixed> */
    public function verifyCallback(Request $request): array;
}
