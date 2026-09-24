<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    public function index()
    {
        return response()->json(Tenant::all());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        $tenant = Tenant::create($data);

        ActivityLogger::log('TENANT_CREATE', "Menambahkan tenant baru: {$tenant->name} (ID: {$tenant->id})");

        return response()->json([
            'message' => 'Tenant berhasil ditambahkan',
            'data' => $tenant
        ], 201);
    }

    public function show($id)
    {
        return response()->json(
            Tenant::findOrFail($id)
        );
    }

    public function update(Request $request, $id)
    {
        $tenant = Tenant::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        $tenant->update($data);

        ActivityLogger::log('TENANT_UPDATE', "Mengubah data tenant: {$tenant->name} (ID: {$tenant->id})");

        return response()->json([
            'message' => 'Tenant berhasil diubah',
            'data' => $tenant
        ]);
    }

    public function destroy($id)
    {
        $tenant = Tenant::findOrFail($id);
        $name = $tenant->name;
        $tenant->delete();

        ActivityLogger::log('TENANT_DELETE', "Menghapus tenant: {$name} (ID: {$id})");

        return response()->json([
            'message' => 'Tenant berhasil dihapus'
        ]);
    }
}