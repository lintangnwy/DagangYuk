<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Models\StockTransfer;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WarehouseController extends Controller
{
    /** List semua gudang milik tenant */
    public function index(): JsonResponse
    {
        $warehouses = Warehouse::with('branch')
            ->where('tenant_id', Auth::user()->tenant_id)
            ->orderBy('name')
            ->get();

        return response()->json($warehouses);
    }

    /** Buat gudang baru */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'      => 'required|string|max:100',
            'address'   => 'nullable|string|max:255',
            'branch_id' => 'nullable|integer|exists:branches,id',
        ]);

        $warehouse = Warehouse::create([
            ...$data,
            'tenant_id' => Auth::user()->tenant_id,
            'is_active' => true,
        ]);

        return response()->json($warehouse->load('branch'), 201);
    }

    /** Detail gudang + semua stok di dalamnya */
    public function show(int $id): JsonResponse
    {
        $warehouse = Warehouse::with(['branch', 'stocks.product:id,name,sku'])
            ->where('tenant_id', Auth::user()->tenant_id)
            ->findOrFail($id);

        return response()->json($warehouse);
    }

    /** Update gudang */
    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'name'      => 'sometimes|string|max:100',
            'address'   => 'nullable|string|max:255',
            'branch_id' => 'nullable|integer|exists:branches,id',
            'is_active' => 'sometimes|boolean',
        ]);

        $warehouse = Warehouse::where('tenant_id', Auth::user()->tenant_id)->findOrFail($id);
        $warehouse->update($data);

        return response()->json($warehouse->load('branch'));
    }

    /** Hapus gudang */
    public function destroy(int $id): JsonResponse
    {
        $warehouse = Warehouse::where('tenant_id', Auth::user()->tenant_id)->findOrFail($id);
        $warehouse->delete();

        return response()->json(['message' => 'Gudang berhasil dihapus.']);
    }

    /** Stok per produk di gudang tertentu */
    public function stocks(int $id): JsonResponse
    {
        $warehouse = Warehouse::where('tenant_id', Auth::user()->tenant_id)->findOrFail($id);

        $stocks = WarehouseStock::with('product:id,name,sku,price')
            ->where('warehouse_id', $warehouse->id)
            ->get();

        return response()->json($stocks);
    }

    /** Transfer stok antar gudang */
    public function transfer(Request $request): JsonResponse
    {
        $tenantId = Auth::user()->tenant_id;

        $data = $request->validate([
            'from_warehouse_id' => 'required|integer|exists:warehouses,id',
            'to_warehouse_id'   => 'required|integer|exists:warehouses,id|different:from_warehouse_id',
            'product_id'        => 'required|integer|exists:products,id',
            'quantity'          => 'required|integer|min:1',
            'note'              => 'nullable|string|max:255',
        ]);

        // Pastikan kedua gudang milik tenant ini
        $from = Warehouse::where('tenant_id', $tenantId)->findOrFail($data['from_warehouse_id']);
        $to   = Warehouse::where('tenant_id', $tenantId)->findOrFail($data['to_warehouse_id']);

        try {
            DB::transaction(function () use ($data, $from, $to, $tenantId) {
                // Cek stok di gudang asal
                $fromStock = WarehouseStock::where('warehouse_id', $from->id)
                    ->where('product_id', $data['product_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($fromStock->quantity < $data['quantity']) {
                    abort(422, 'Stok di gudang asal tidak mencukupi. Tersedia: ' . $fromStock->quantity);
                }

                // Kurangi stok di gudang asal
                $fromStock->decrement('quantity', $data['quantity']);

                // Tambah stok di gudang tujuan (buat record baru jika belum ada)
                WarehouseStock::updateOrCreate(
                    ['warehouse_id' => $to->id, 'product_id' => $data['product_id']],
                    ['quantity'     => 0]
                );
                WarehouseStock::where('warehouse_id', $to->id)
                    ->where('product_id', $data['product_id'])
                    ->increment('quantity', $data['quantity']);

                // Catat transfer
                StockTransfer::create([
                    'tenant_id'         => $tenantId,
                    'from_warehouse_id' => $from->id,
                    'to_warehouse_id'   => $to->id,
                    'product_id'        => $data['product_id'],
                    'user_id'           => Auth::id(),
                    'quantity'          => $data['quantity'],
                    'note'              => $data['note'] ?? null,
                    'status'            => 'completed',
                ]);
            });

            return response()->json(['message' => 'Transfer stok berhasil.']);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /** Riwayat transfer stok untuk tenant ini */
    public function transfers(): JsonResponse
    {
        $tenantId = Auth::user()->tenant_id;

        $transfers = StockTransfer::with([
            'fromWarehouse:id,name',
            'toWarehouse:id,name',
            'product:id,name',
            'user:id,name',
        ])
            ->where('tenant_id', $tenantId)
            ->orderByDesc('created_at')
            ->take(100)
            ->get();

        return response()->json($transfers);
    }
}
