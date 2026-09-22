<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingController extends Controller
{
    /**
     * Get current tenant settings
     */
    public function show(): JsonResponse
    {
        $tenantId = Auth::user()->tenant_id;
        if (!$tenantId) {
            return response()->json(['message' => 'Super Admin tidak memiliki pengaturan toko.'], 403);
        }

        $tenant = Tenant::findOrFail($tenantId);
        return response()->json($tenant);
    }

    /**
     * Update current tenant settings
     */
    public function update(Request $request): JsonResponse
    {
        $tenantId = Auth::user()->tenant_id;
        if (!$tenantId) {
            return response()->json(['message' => 'Super Admin tidak memiliki pengaturan toko.'], 403);
        }

        $tenant = Tenant::findOrFail($tenantId);

        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'address'        => 'nullable|string',
            'phone'          => 'nullable|string|max:20',
            'receipt_footer' => 'nullable|string',
        ]);

        $tenant->update($data);

        return response()->json([
            'message' => 'Pengaturan toko berhasil disimpan.',
            'data'    => $tenant,
        ]);
    }
}
