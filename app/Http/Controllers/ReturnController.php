<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReturnController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;

        $orders = Order::with(['items.product:id,name'])
            ->where('tenant_id', $tenantId)
            ->orderByDesc('created_at')
            ->take(100)
            ->get();

        return response()->json($orders);
    }

    /**
     * Process return order - refund dan kembalikan stok
     */
    public function processReturn(Request $request): JsonResponse
    {
        $data = $request->validate([
            'order_id'       => 'required|exists:orders,id',
            'reason'         => 'required|string|max:255',
            'item_condition' => 'required|in:sellable,damaged',
        ]);

        try {
            $order = Order::with(['items.product'])
                ->where('id', $data['order_id'])
                ->lockForUpdate()
                ->firstOrFail();

            if ($order->payment_status !== 'paid') {
                abort(422, 'Transaksi ini tidak bisa di-return karena status pembayaran belum lunas.');
            }

            DB::transaction(function () use ($order, $data) {
                foreach ($order->items as $item) {
                    $isDamaged = $data['item_condition'] === 'damaged';
                    
                    if ($isDamaged) {
                        // Masukkan ke Gudang Barang Rusak
                        $warehouse = \App\Models\Warehouse::firstOrCreate(
                            ['tenant_id' => $order->tenant_id, 'name' => 'Gudang Barang Rusak'],
                            ['is_active' => true]
                        );

                        $warehouseStock = \App\Models\WarehouseStock::firstOrCreate(
                            ['warehouse_id' => $warehouse->id, 'product_id' => $item->product_id],
                            ['quantity' => 0]
                        );
                        
                        $warehouseStock->increment('quantity', $item->quantity);
                        
                        // Opsional: log ke stock_adjustments dengan stok utama tidak berubah
                        $stockBefore = $item->product->stock;
                        \App\Models\StockAdjustment::create([
                            'tenant_id'    => $order->tenant_id,
                            'product_id'   => $item->product_id,
                            'user_id'      => auth()->id(),
                            'type'         => 'in',
                            'quantity'     => $item->quantity,
                            'stock_before' => $stockBefore,
                            'stock_after'  => $stockBefore, // Stok utama tidak berubah
                            'reason'       => 'Return Pesanan #' . $order->invoice_number . ' - ' . $data['reason'] . ' (Disimpan di Gudang Barang Rusak)',
                        ]);
                    } else {
                        // Layak jual -> kembalikan ke stok utama
                        $stockBefore = $item->product->stock;
                        $item->product->increment('stock', $item->quantity);
                        $stockAfter = $stockBefore + $item->quantity;
    
                        // Create stock adjustment record
                        \App\Models\StockAdjustment::create([
                            'tenant_id'    => $order->tenant_id,
                            'product_id'   => $item->product_id,
                            'user_id'      => auth()->id(),
                            'type'         => 'in',
                            'quantity'     => $item->quantity,
                            'stock_before' => $stockBefore,
                            'stock_after'  => $stockAfter,
                            'reason'       => 'Return Pesanan #' . $order->invoice_number . ' - ' . $data['reason'] . ' (Layak Jual)',
                        ]);
                    }
                }

                // Update status order jadi 'refunded'
                $order->update(['payment_status' => 'refunded']);
            });

            return response()->json([
                'message' => 'Return berhasil diproses. Stok telah dikembalikan ke inventory ' . ($data['item_condition'] === 'damaged' ? 'Barang Rusak.' : 'Utama.'),
            ], 200);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}