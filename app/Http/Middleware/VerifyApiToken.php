<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyApiToken
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check for Bearer token in Authorization header
        $authHeader = $request->header('Authorization');
        
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.'
            ], 401);
        }
        
        $token = substr($authHeader, 7); // Remove "Bearer " prefix
        
        // Use Laravel Sanctum to verify token
        $user = auth()->guard('sanctum')->setUserUsing(function () use ($token) {
            return auth()->guard('sanctum')->provider()
                ->getProvider()
                ->retrieveByToken($token);
        });
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid token.'
            ], 401);
        }
        
        $request->attributes->set('user', $user);
        
        return $next($request);
    }
}