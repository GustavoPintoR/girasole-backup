<?php

namespace App\Http\Middleware;

use App\Models\TermsAndConditions;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HasAcceptedLatestTerms
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()->hasAcceptedTerms() || ! TermsAndConditions::where('is_active', true)->exists()) {
            return $next($request);
        }

        return to_route('terms-and-conditions.accept');
    }
}
