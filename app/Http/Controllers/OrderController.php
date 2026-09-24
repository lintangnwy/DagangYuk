<?php

namespace App\Http\Controllers; 

use App\Models\CashShift;
use App\Models\Order;
use App\Models\Product;
use App\Models\Promotion;
use Illuminate\Http\JsonResponse;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(): JsonResponse
    {
        $user = Auth::user();
        $query = Order::with(['user:id,name', 'cashShift', 'items.product:id,name']);

        if ($user && $user->role_id !== 1) {
            $query->where('tenant_id', $user->tenant_id);
        }

        $orders = $query->latest()->get();

        return response()->json($orders);
    }

    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user || !$user->tenant_id) {
            return response()->json(['message' => 'Anda harus memiliki tenant aktif untuk membuat order.'], 403);
        }

        $data = $request->validate([
            'cash_shift_id'         => 'required|exists:cash_shifts,id',
            'payment_method'        => 'required|in:cash,qris,transfer',
            'discount_amount'      => 'nullable|numeric|min:0',
            'promo_code'           => 'nullable|string',
            'products'             => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity'   => 'required|integer|min:1',
        ]);

        $shift = CashShift::where('id', $data['cash_shift_id'])
            ->where('tenant_id', $user->tenant_id)
            ->first();

        if (!$shift) {
            return response()->json(['message' => 'Shift kasir tidak valid untuk tenant Anda.'], 422);
        }

        if ($shift->user_id !== $user->id) {
            return response()->json(['message' => 'Shift kasir tidak milik Anda.'], 403);
        }

        if ($shift->status !== 'open') {
            return response()->json(['message' => 'Shift kasir sudah ditutup.'], 422);
        }

        try {
            $order = DB::transaction(function () use ($data, $shift, $user) {
                $total = 0;
                $items = [];

                foreach ($data['products'] as $item) {
                    /** @var Product $product */
                    $product = Product::where('id', $item['product_id'])
                        ->where('tenant_id', $user->tenant_id)
                        ->lockForUpdate()
                        ->first();

                    if (!$product) {
                        abort(422, 'Produk tidak valid untuk tenant Anda.');
                    }

                    if ($product->stock < $item['quantity']) {
                        abort(422, "Stok produk \"{$product->name}\" tidak mencukupi.");
                    }

                    $subtotal = $product->price * $item['quantity'];
                    $total += $subtotal;

                    $items[] = [
                        'product'  => $product,
                        'quantity' => $item['quantity'],
                        'subtotal' => $subtotal,
                    ];
                }

                $discountAmount = min((float) ($data['discount_amount'] ?? 0), $total);

                if (!empty($data['promo_code'])) {
                    $promo = Promotion::where('code', $data['promo_code'])
                        ->where('tenant_id', $user->tenant_id)
                        ->lockForUpdate()
                        ->first();

                    if (!$promo || !$promo->isValid($total)) {
                        abort(422, 'Kode promo tidak valid atau syarat tidak terpenuhi.');
                    }

                    $discountAmount = $promo->calculateDiscount($total);
                    $promo->increment('used');
                }

                $finalTotal = max(0, $total - $discountAmount);

                $today = now()->format('Ymd');
                $lastInvoice = Order::withoutGlobalScopes()
                    ->where('tenant_id', $user->tenant_id)
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
                    'user_id'         => $user->id,
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
                        'cost_price'   => $item['product']->cost_price ?? 0,
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
        $user = Auth::user();
        $query = Order::with(['user:id,name', 'cashShift', 'items.product:id,name']);

        if ($user && $user->role_id !== 1) {
            $query->where('tenant_id', $user->tenant_id);
        }

        $order = $query->findOrFail($id);

        return response()->json($order);
    }
}
