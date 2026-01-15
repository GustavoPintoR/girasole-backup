<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiBasicAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        if (!str_contains($request->header('Accept'), 'text/html') && !$request->expectsJson()) {
            return $next($request);
        }

        $username = config('girasole.scribe.username');
        $password = config('girasole.scribe.password');

        $user = $request->getUser();
        $pass = $request->getPassword();

        if (!$user || !$pass || $user !== $username || $pass !== $password) {
            return $this->challenge();
        }

        return $next($request);
    }

    /**
     * Handles unauthorized access by returning a 401 response with a Basic authentication challenge header.
     */
    protected function challenge(): \Illuminate\Http\Response|ResponseFactory
    {
        return response('Access denied', 401)
            ->header('WWW-Authenticate', 'Basic realm="Staging Area"');
    }
}
