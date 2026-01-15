<?php

namespace App\Http\Middleware;

use App\Models\Plan;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Subscribed
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if (Plan::active()->count() == 0) {
            return $next($request);
        }

        $user = $request->user();

        if ($user && ! $user->hasValidPaidSubscription()) {
            if ($request->expectsJson()) {
                return new JsonResponse([
                    'message' => __('ui.payment_required'),
                ], 403);
            }

            return redirect()->route('billing.plans')->with('error', __('ui.you_need_to_subscribe'));
        }

        return $next($request);
    }
}
