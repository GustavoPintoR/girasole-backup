<?php

namespace App\Services\Stripe;

use Stripe\Stripe;
use App\Models\Order;
use Stripe\Checkout\Session;
use App\Interfaces\PaymentInterface;
use Illuminate\Support\Facades\Auth;

class StripePaymentService implements PaymentInterface
{
    protected string $stripeSecretKey;

    public function __construct()
    {
        $this->stripeSecretKey = config('services.stripe.private_key');
        Stripe::setApiKey($this->stripeSecretKey);
    }

    /**
     * @param $subscription
     * @return array
     */
    public function getPaymentLink(): array
    {
        $user = Auth::user();
        
        try {
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'EUR',
                        'unit_amount' => 100,
                        'product_data' => [
                            'name' => "Booking for {$user->name}",
                            'metadata' => [
                                // 'orderId' => $order->id,
                            ],
                        ],
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                // 'success_url' => route('order.success') . "?order_id={$order->id}",
                // 'cancel_url' => route('order.cancel'),
                'metadata' => [
                    // 'orderId' => $order->id,
                ],
            ]);

            return [
                'success' => true,
                'checkout_url' => $session->url,
                'session_id' => $session->id,
            ];
        } catch (\Exception $exception) {
            return [
                'success' => false,
                'error' => $exception->getMessage(),
            ];
        }
    }
}
