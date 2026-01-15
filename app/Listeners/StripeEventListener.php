<?php

namespace App\Listeners;

use Illuminate\Support\Arr;
use App\Traits\HandleStripeEvents;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Events\WebhookReceived;
 
class StripeEventListener
{
    use HandleStripeEvents;

    /**
     * Handle received Stripe webhooks.
     */
    public function handle(WebhookReceived $event): void
    {
        $responseDataObject = Arr::get($event, 'payload');
        $responseType = Arr::get($responseDataObject, 'type');

        Log::channel('payments')->info("Stripe Webhook Type {$responseType}");

        match ($responseType) {
            'product.updated' => $this->handleProductUpdated($responseDataObject),
            'product.deleted' => $this->handleProductDeleted($responseDataObject),
            'checkout.session.completed' => $this->handleCheckoutSessionCompleted($responseDataObject),
            'customer.subscription.deleted' => $this->handleCustomerSubscriptionDeleted($responseDataObject),
            // 'charge.failed' => $this->chargeFailed($responseDataObject),
            default => Log::debug("Unhandled event: {$responseType}"),
        };

    }
}
