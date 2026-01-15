<?php

namespace App\Traits;

use App\Models\Plan;
use App\Models\User;
use App\Models\Order;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Enums\PaymentStatus;
use App\Notifications\BroadcastMessageNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use App\Notifications\SendPasswordNotification;
use App\Notifications\OrderCompletedNotification;
use App\Notifications\UserOrderCompletedNotification;
use Illuminate\Http\Response;
use Laravel\Cashier\Cashier;
use Stripe\StripeClient;

trait HandleStripeEvents
{
    public function handleProductUpdated(array $payload)
    {
        Log::channel('payments')->info('Webhook: product.updated received.', $payload);
        $product = $payload ?? null;
        if (!$product) {
            Log::channel('payments')->warning('Product updated webhook received but no product data found.');
            return new Response('No product data found', 400);
        }

        $stripe = Cashier::stripe();

        // Get the default price if exists
        $priceId = $product['default_price'] ?? null;
        $price = null;

        if ($priceId) {
            $price = $stripe->prices->retrieve($priceId, []);
        } else {
            // Try to get the first price of the product
            $prices = $stripe->prices->all([
                'product' => $product['id'],
                'limit'   => 1,
            ]);
            if (count($prices->data)) {
                $price = $prices->data[0];
            }
        }

        if (!$price) {
            Log::channel('payments')->warning('Product created but no price found.', [
                'product_id' => $product['id'],
                'name' => $product['name'],
            ]);

            return new Response('No price found for product', 400);
        }

        if ($product['active'] == true) {
            // Create or update Plan
            Plan::updateOrCreate(
                ['stripe_product_id' => $product['id']],
                [
                    'name'             => $product['name'],
                    'slug'             => Str::slug($product['name']),
                    'active'           => $product['active'] ?? true,
                    'currency'         => $price->currency,
                    'unit_amount'      => $price->unit_amount,
                    'interval'         => $price->recurring->interval ?? null,
                    'stripe_price_id'  => $price->id,
                    'features'         => $product['metadata']['features'] ? explode(',', $product['metadata']['features']) : null,
                ]
            );

            Log::channel('payments')->info('Plan created/updated from Stripe product.', [
                'product_id' => $product['id'],
                'price_id'   => $price->id,
            ]);
        }

        return new Response('Webhook handled', 200);
    }

    public function handleProductDeleted(array $payload)
    {
        $product = $payload ?? null;
        if (!$product) {
            return new Response('No product data found', 400);
        }

        $plan = Plan::where('stripe_product_id', $product['id'])->first();

        if ($plan) {
            $plan->update([
                'active' => false,
            ]);

            Log::channel('payments')->info('Plan marked inactive (product deleted on Stripe).', [
                'product_id' => $product['id'],
                'plan_id'    => $plan->id,
            ]);
        } else {
            Log::channel('payments')->warning('Received product.deleted for unknown product.', [
                'product_id' => $product['id'],
            ]);
        }

        return new Response('Product deleted handled', 200);
    }

    public function handleCheckoutSessionCompleted(array $payload)
    {
        $session = $payload ?? null;

        if (!$session) {
            return new Response('No session found', 400);
        }

        $user = User::where('stripe_id', $session['customer'])->first();

        if (!$user) {
            return new Response('User not found', 404);
        }

        // Get the plan from your DB by price
        $plan = Plan::find($session['metadata']['plan_id']) ?? null;

        if (!$plan) {
            return new Response('Plan not found', 404);
        }

        // Build the context for your notification
        $context = [
            'title' => __('ui.welcome_title'),
            'description' => __('ui.welcome_message', [
                'plan' => $plan->name,
            ]),
        ];

        // Send notification
        $user->notify(new BroadcastMessageNotification($context, true));

        return new Response('Webhook handled', 200);
    }

    public function handleCustomerSubscriptionDeleted(array $payload)
    {
        $session = $payload ?? null;

        if (!$session) {
            return new Response('No session found', 400);
        }

        $user = User::where('stripe_id', $session['customer'])->first();

        if (!$user) {
            return new Response('User not found', 404);
        }


        if ($user) {
            // Revoke all Sanctum tokens
            $user->tokens()->delete();

            Log::channel('payments')->info('Subscription ended, keys deleted', [
                'user' => $user->email ?? null,
                'customer' => $session['customer'],
            ]);
        }

        return new Response('Webhook handled', 200);
    }
}
