<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BranchController extends Controller
{
    public function index(): JsonResponse
    {
        $branches = Branch::with('warehouses')
            ->where('tenant_id', Auth::user()->tenant_id)
            ->orderBy('name')
            ->get();

        return response()->json($branches);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'    => 'required|string|max:100',
            'address' => 'nullable|string|max:255',
            'phone'   => 'nullable|string|max:20',
        ]);

        $branch = Branch::create([
            ...$data,
            'tenant_id' => Auth::user()->tenant_id,
            'is_active' => true,
        ]);

        return response()->json($branch->load('warehouses'), 201);
    }

    public function show(int $id): JsonResponse
    {
        $branch = Branch::with(['warehouses.stocks.product'])
            ->where('tenant_id', Auth::user()->tenant_id)
            ->findOrFail($id);

        return response()->json($branch);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'name'      => 'sometimes|string|max:100',
            'address'   => 'nullable|string|max:255',
            'phone'     => 'nullable|string|max:20',
            'is_active' => 'sometimes|boolean',
        ]);

        $branch = Branch::where('tenant_id', Auth::user()->tenant_id)->findOrFail($id);
        $branch->update($data);

        return response()->json($branch);
    }

    public function destroy(int $id): JsonResponse
    {
        $branch = Branch::where('tenant_id', Auth::user()->tenant_id)->findOrFail($id);
        $branch->delete();

        return response()->json(['message' => 'Cabang berhasil dihapus.']);
    }
}
