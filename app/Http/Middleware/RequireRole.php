<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireRole
{
    /**
     * Usage: middleware('role:admin') atau middleware('role:admin,super_admin')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! $user->role) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        if (! in_array($user->role->name, $roles, true)) {
            return response()->json(['message' => 'Akses ditolak. Role tidak sesuai.'], 403);
        }

        return $next($request);
    }
}
