<?php

namespace Marble\Admin\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Marble\Admin\Models\ApiToken;
use Symfony\Component\HttpFoundation\Response;

class MarbleApiAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $authHeader = $request->header('Authorization', '');

        if (str_starts_with($authHeader, 'Bearer ')) {
            $plain = substr($authHeader, 7);
            $token = ApiToken::findByPlainToken($plain);

            if ($token) {
                $token->last_used_at = now();
                $token->save();

                $request->attributes->set('marble_api_token', $token);
            } else {
                // Token was provided but is invalid/expired
                return response()->json(['error' => 'Unauthorized', 'message' => 'Invalid or expired token.'], 401);
            }
        }
        // No token provided — let individual controllers decide if auth is required
        // (public blueprints allow unauthenticated access)

        return $next($request);
    }
}
