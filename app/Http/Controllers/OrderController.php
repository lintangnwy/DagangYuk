<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with([
            'user',
            'cashShift',
            'items'
        ])
        ->latest()
        ->get();

        return response()->json($orders);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'cash_shift_id' => 'required|exists:cash_shifts,id',
            'payment_method' => 'required|in:cash,qris,transfer',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
        ]);

        $shift = \App\Models\CashShift::findOrFail(
            $data['cash_shift_id']
        );

        if ($shift->status !== 'open') {
            return response()->json([
                'message' => 'Shift sudah ditutup'
            ], 422);
        }

        try {
            $order = DB::transaction(function () use ($data, $shift) {

                $total = 0;
                $items = [];

                foreach ($data['products'] as $item) {

                    $product = Product::findOrFail(
                        $item['product_id']
                    );

                    if ($product->stock < $item['quantity']) {
                        abort(422, "Stok {$product->name} tidak mencukupi");
                    }

                    $subtotal =
                        $product->price * $item['quantity'];

                    $total += $subtotal;

                    $items[] = [
                        'product' => $product,
                        'quantity' => $item['quantity'],
                        'subtotal' => $subtotal,
                    ];
                }

                $order = Order::create([
                    'tenant_id' => $shift->tenant_id,
                    'user_id' => Auth::id(),
                    'cash_shift_id' => $shift->id,
                    'invoice_number' =>
                        'INV-' . now()->format('YmdHis') . '-' . rand(100, 999),
                    'total_amount' => $total,
                    'payment_method' => $data['payment_method'],
                ]);

                foreach ($items as $item) {

                    $product = $item['product'];

                    $order->items()->create([
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'quantity' => $item['quantity'],
                        'unit_price' => $product->price,
                        'subtotal' => $item['subtotal'],
                    ]);

                    $product->decrement(
                        'stock',
                        $item['quantity']
                    );
                }

                return $order;
            });

            return response()->json([
                'message' => 'Transaksi berhasil',
                'data' => $order->load('items')
            ], 201);

        } catch (\Throwable $e) {

            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function show($id)
    {
        $order = Order::with([
            'user',
            'cashShift',
            'items'
        ])->findOrFail($id);

        return response()->json($order);
    }
}