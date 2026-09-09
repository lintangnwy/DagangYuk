<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
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

        return response()->json([
            'message' => 'Tenant berhasil diubah',
            'data' => $tenant
        ]);
    }

    public function destroy($id)
    {
        Tenant::findOrFail($id)->delete();

        return response()->json([
            'message' => 'Tenant berhasil dihapus'
        ]);
    }
}