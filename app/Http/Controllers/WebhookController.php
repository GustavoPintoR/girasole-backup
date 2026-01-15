<?php

namespace App\Http\Controllers;

use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Traits\HandleStripeEvents;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class WebhookController extends Controller
{
    use HandleStripeEvents;
    
    public function handleWebhook(Request $request): void
    {
        $response = $request->all();
        $responseType = Arr::get($response, 'type');
        $responseDataObject = Arr::get($response, 'data.object');

        Log::channel('payments')->info("Stripe Webhook Type {$responseType}");

        match ($responseType) {
            'product.updated' => $this->handleProductUpdated($responseDataObject),
            'product.deleted' => $this->handleProductDeleted($responseDataObject),
            'checkout.session.completed' => $this->handleCheckoutSessionCompleted($responseDataObject),
            // 'charge.failed' => $this->chargeFailed($responseDataObject),
            default => Log::debug("Unhandled event: {$responseType}"),
        };
    }
}
