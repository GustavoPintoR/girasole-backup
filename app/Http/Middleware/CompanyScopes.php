<?php

namespace App\Http\Middleware;

use App\Exceptions\CompanyNotFoundException;
use App\Interfaces\CompanyInterface;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CompanyScopes
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            app(CompanyInterface::class)->addGlobalCompanyScope();
            app(CompanyInterface::class)->addGlobalCompanyIndexScope();
        } catch (CompanyNotFoundException $e) {
            abort(406, __("Company doesn't exist"));
        }

        return $next($request);
    }
}
