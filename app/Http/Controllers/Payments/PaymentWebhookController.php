<?php

namespace App\Http\Controllers\Payments;

use App\Services\Payments\ConfirmPayment;
use App\Contracts\PaymentGatewayInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Validation\ValidationException;

class PaymentWebhookController
{
    public function __invoke(string $driver, Request $request, PaymentGatewayInterface $gateway, ConfirmPayment $confirm): JsonResponse
    {
        if ($driver !== $gateway->name()) {
            throw new NotFoundHttpException();
        }

        $event = $gateway->verifyCallback($request);
        try {
            $confirm->handle($gateway, $event);
        } catch (ValidationException $exception) {
            return response()->json(['received' => false, 'errors' => $exception->errors()], 422);
        }

        return response()->json(['received' => true]);
    }
}
