<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class ApiAccess
{
    public const APP_TOKEN_PREFIX = 'x-app:';

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $appTokenHeader = $request->header('X-App-Authentication');

        if (! $appTokenHeader) {
            return $this->unauthorized('X-App-Authentication header is required.');
        }

        $plainToken = $this->extractPlainToken($appTokenHeader);

        $accessToken = PersonalAccessToken::findToken($plainToken);

        if (! $accessToken || ! $accessToken->tokenable) {
            return $this->unauthorized('Invalid or revoked app token.');
        }

        if (! str_starts_with($accessToken->name, self::APP_TOKEN_PREFIX)) {
            return $this->unauthorized('Invalid token type. Only app tokens are allowed in X-App-Authentication.');
        }

        return $next($request);
    }

    /**
     * Extracts a plain token from the provided header string by removing any
     * prefix like "Bearer " and trimming whitespace. This ensures the token
     * is in the correct format.
     *
     * @param string $header The authorization header containing the token.
     * @return string The extracted plain token.
     */
    private function extractPlainToken(string $header): string
    {
        $header = trim($header);

        if (str_starts_with(strtolower($header), 'bearer ')) {
            return $this->unauthorized('Bearer prefix is not allowed in X-App-Authentication header.');
        }

        return $header;
    }

    /**
     * Handles unauthorized access by returning a JSON response with a 401 status code.
     *
     * @param string $message The message to include in the response.
     * @return Response The JSON response indicating unauthorized access.
     */
    private function unauthorized(string $message): Response
    {
        return response([
            'message' => $message,
        ], 401)->header('Content-Type', 'application/json');
    }
}
