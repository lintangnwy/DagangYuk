<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockAdjustment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockAdjustmentController extends Controller
{
    /**
     * Daftar semua stock adjustment (dengan filter opsional per produk).
     */
    public function index(Request $request): JsonResponse
    {
        $query = StockAdjustment::with(['product:id,name,sku,image', 'user:id,name'])
            ->latest();

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $adjustments = $query->get();

        return response()->json($adjustments);
    }

    /**
     * Buat stock adjustment baru (tambah/kurang stok manual).
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'type'       => 'required|in:in,out',
            'quantity'   => 'required|integer|min:1',
            'reason'     => 'required|string|max:255',
            'notes'      => 'nullable|string|max:1000',
        ]);

        try {
            $adjustment = DB::transaction(function () use ($data) {
                /** @var Product $product */
                $product = Product::lockForUpdate()->findOrFail($data['product_id']);

                $stockBefore = $product->stock;

                if ($data['type'] === 'in') {
                    $stockAfter = $stockBefore + $data['quantity'];
                } else {
                    // Validasi stok cukup untuk dikurangi
                    if ($stockBefore < $data['quantity']) {
                        abort(422, "Stok tidak mencukupi. Stok saat ini: {$stockBefore}");
                    }
                    $stockAfter = $stockBefore - $data['quantity'];
                }

                // Update stok produk
                $product->update(['stock' => $stockAfter]);

                // Catat adjustment
                return StockAdjustment::create([
                    'tenant_id'    => $product->tenant_id,
                    'product_id'   => $product->id,
                    'user_id'      => Auth::id(),
                    'type'         => $data['type'],
                    'quantity'     => $data['quantity'],
                    'stock_before' => $stockBefore,
                    'stock_after'  => $stockAfter,
                    'reason'       => $data['reason'],
                    'notes'        => $data['notes'] ?? null,
                ]);
            });

            return response()->json([
                'message' => 'Penyesuaian stok berhasil dicatat.',
                'data'    => $adjustment->load(['product:id,name,sku,stock', 'user:id,name']),
            ], 201);

        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Ringkasan stok semua produk (untuk halaman inventaris).
     */
    public function summary(): JsonResponse
    {
        $products = Product::with('category:id,name')
            ->select('id', 'name', 'sku', 'image', 'stock', 'category_id', 'cost_price', 'price')
            ->orderBy('name')
            ->get()
            ->map(function ($product) {
                $lastAdjustment = StockAdjustment::where('product_id', $product->id)
                    ->latest()
                    ->first();

                return [
                    'id'              => $product->id,
                    'name'            => $product->name,
                    'sku'             => $product->sku,
                    'image'           => $product->image,
                    'stock'           => $product->stock,
                    'cost_price'      => $product->cost_price,
                    'price'           => $product->price,
                    'category'        => $product->category,
                    'stock_value'     => $product->stock * ($product->cost_price ?? $product->price),
                    'last_adjustment' => $lastAdjustment ? [
                        'type'       => $lastAdjustment->type,
                        'quantity'   => $lastAdjustment->quantity,
                        'reason'     => $lastAdjustment->reason,
                        'created_at' => $lastAdjustment->created_at,
                    ] : null,
                ];
            });

        $totalProducts  = $products->count();
        $totalStockValue = $products->sum('stock_value');
        $lowStock   = $products->filter(fn($p) => $p['stock'] > 0 && $p['stock'] < 5)->count();
        $outOfStock = $products->filter(fn($p) => $p['stock'] === 0)->count();

        return response()->json([
            'summary' => [
                'total_products'    => $totalProducts,
                'total_stock_value' => $totalStockValue,
                'low_stock'         => $lowStock,
                'out_of_stock'      => $outOfStock,
            ],
            'products' => $products,
        ]);
    }
}
