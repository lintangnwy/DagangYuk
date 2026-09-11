<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTenant
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Jika user tidak login, biarkan melewati auth middleware yang akan menangani
        if (!$user) {
            return $next($request);
        }

        // Super Admin (role_id = 1) tidak dicek tenantnya
        if ($user->role_id === 1) {
            return $next($request);
        }

        // Pengecekan apakah user punya tenant dan apakah tenant tersebut aktif
        if (!$user->tenant_id || !$user->tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke tenant manapun.'
            ], 403);
        }

        if (!$user->tenant->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant Anda sedang dinonaktifkan.'
            ], 403);
        }

        return $next($request);
    }
}
