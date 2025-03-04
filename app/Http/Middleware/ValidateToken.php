<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use Laravel\Passport\Token;

class ValidateToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the Authorization header is missing or token not included
        if (!$request->hasHeader('Authorization')) {
            Log::error('Unauthorized access: Missing Authorization header.');

            $response = [
                'data' => '',
                'meta' => [
                    'message' => 'Unauthorized user.',
                    'status_code' => Response::HTTP_UNAUTHORIZED
                ]
            ];
            return response()->json($response, Response::HTTP_UNAUTHORIZED);
        }

        // Authenticate the user using the token
        $user = Auth::guard('api')->user();

        if (!$user) {
            Log::error('Unauthorized access: Invalid or expired token.');

            $response = [
                'data' => '',
                'meta' => [
                    'message' => 'Unauthorized user.',
                    'status_code' => Response::HTTP_UNAUTHORIZED
                ]
            ];
            return response()->json($response, Response::HTTP_UNAUTHORIZED);
        }

        // Set the authenticated user
        Auth::setUser($user);

        return $next($request);
    }
}
