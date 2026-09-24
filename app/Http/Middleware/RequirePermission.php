<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequirePermission
{
    /**
     * Pastikan user memiliki SALAH SATU dari permission yang diminta.
     *
     * Usage: middleware('permission:shop.product.manage')
     *        middleware('permission:platform.user.manage,staff.user.manage')
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if (! $user || ! $user->role) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Sesi pengguna tidak valid.',
            ], 403);
        }

        if (! $user->hasAnyPermission($permissions)) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Role Anda tidak memiliki hak akses untuk fitur ini.',
            ], 403);
        }

        return $next($request);
    }
}
