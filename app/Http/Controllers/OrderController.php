<?php

namespace App\Http\Controllers;

use App\Models\CashShift;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(): JsonResponse
    {
        $orders = Order::with(['user:id,name', 'cashShift', 'items.product:id,name'])
            ->latest()
            ->get();

        return response()->json($orders);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'cash_shift_id'              => 'required|exists:cash_shifts,id',
            'payment_method'             => 'required|in:cash,qris,transfer',
            'discount_amount'            => 'nullable|numeric|min:0',
            'products'                   => 'required|array|min:1',
            'products.*.product_id'      => 'required|exists:products,id',
            'products.*.quantity'        => 'required|integer|min:1',
        ]);

        $shift = CashShift::findOrFail($data['cash_shift_id']);

        if ($shift->status !== 'open') {
            return response()->json(['message' => 'Shift kasir sudah ditutup.'], 422);
        }

        try {
            $order = DB::transaction(function () use ($data, $shift) {
                $total = 0;
                $items = [];

                foreach ($data['products'] as $item) {
                    /** @var Product $product */
                    $product = Product::lockForUpdate()->findOrFail($item['product_id']);

                    if ($product->stock < $item['quantity']) {
                        abort(422, "Stok produk \"{$product->name}\" tidak mencukupi.");
                    }

                    $subtotal = $product->price * $item['quantity'];
                    $total   += $subtotal;

                    $items[] = [
                        'product'  => $product,
                        'quantity' => $item['quantity'],
                        'subtotal' => $subtotal,
                    ];
                }

                $discountAmount = min((float) ($data['discount_amount'] ?? 0), $total);
                $finalTotal = max(0, $total - $discountAmount);

                // Invoice number unik — ambil sequence terakhir hari ini (GLOBAL, tanpa tenant scope)
                $today = now()->format('Ymd');
                $lastInvoice = Order::withoutGlobalScopes()
                    ->where('invoice_number', 'like', "INV-{$today}-%")
                    ->orderByDesc('invoice_number')
                    ->lockForUpdate()
                    ->value('invoice_number');

                $nextSeq = 1;
                if ($lastInvoice) {
                    $lastSeq = (int) substr($lastInvoice, -4);
                    $nextSeq = $lastSeq + 1;
                }

                $invoiceNumber = "INV-{$today}-" . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);

                $order = Order::create([
                    'tenant_id'       => $shift->tenant_id,
                    'user_id'         => Auth::id(),
                    'cash_shift_id'   => $shift->id,
                    'invoice_number'  => $invoiceNumber,
                    'total_amount'    => $finalTotal,
                    'discount_amount' => $discountAmount,
                    'payment_method'  => $data['payment_method'],
                ]);

                foreach ($items as $item) {
                    $order->items()->create([
                        'product_id'   => $item['product']->id,
                        'product_name' => $item['product']->name,
                        'quantity'     => $item['quantity'],
                        'unit_price'   => $item['product']->price,
                        'subtotal'     => $item['subtotal'],
                    ]);

                    $item['product']->decrement('stock', $item['quantity']);
                }

                return $order->load(['items.product:id,name', 'user:id,name', 'cashShift']);
            });

            return response()->json([
                'message' => 'Transaksi berhasil.',
                'data'    => $order,
            ], 201);

        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function show(int $id): JsonResponse
    {
        $order = Order::with(['user:id,name', 'cashShift', 'items.product:id,name'])
            ->findOrFail($id);

        return response()->json($order);
    }
}
