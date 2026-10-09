<?php

namespace App\Providers;

use App\Contracts\PaymentGatewayInterface;
use App\Services\Payments\MockPaymentGateway;
use App\Services\Payments\DisabledPaymentGateway;
use App\Services\Payments\DarajaMpesaGateway;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\SiteSetting;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PaymentGatewayInterface::class, function (): PaymentGatewayInterface {
            return match (config('services.payment.driver', 'disabled')) {
                'mock' => new MockPaymentGateway(),
                'daraja' => new DarajaMpesaGateway(),
                'disabled' => new DisabledPaymentGateway(),
                default => throw new \InvalidArgumentException('Configured payment gateway is not registered.'),
            };
        });
    }

    public function boot(): void
    {
        View::composer('*', function ($view): void {
            $view->with('siteSettings', SiteSetting::allValues());
        });
    }
}
