<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Notification;

class MidtransController extends Controller
{
    public function __construct()
    {
        Config::$serverKey    = config('midtrans.server_key');
        Config::$clientKey    = config('midtrans.client_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized  = config('midtrans.is_sanitized');
        Config::$is3ds        = config('midtrans.is_3ds');
    }

    /**
     * Buat Snap token untuk pembayaran QRIS / Transfer.
     */
    public function createToken(Request $request): JsonResponse
    {
        $request->validate([
            'order_id'   => 'required|string',
            'amount'     => 'required|integer|min:1',
            'customer'   => 'nullable|string',
        ]);

        $user = $request->user();

        $params = [
            'transaction_details' => [
                'order_id'     => $request->order_id,
                'gross_amount' => (int) $request->amount,
            ],
            'customer_details' => [
                'first_name' => $request->customer ?? $user->name,
                'email'      => $user->email,
            ],
            'enabled_payments' => ['qris', 'bank_transfer', 'echannel'],
            'qris' => [
                'acquirer' => 'gopay',
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);

            return response()->json([
                'snap_token' => $snapToken,
                'client_key' => config('midtrans.client_key'),
                'is_production' => config('midtrans.is_production'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal membuat token pembayaran: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Webhook dari Midtrans — update status order.
     */
    public function webhook(Request $request): JsonResponse
    {
        try {
            $notif = new Notification();

            $transactionStatus = $notif->transaction_status;
            $orderId           = $notif->order_id;
            $fraudStatus       = $notif->fraud_status;

            // Cari order berdasarkan invoice_number
            $order = Order::withoutGlobalScopes()
                ->where('invoice_number', $orderId)
                ->first();

            if (! $order) {
                return response()->json(['message' => 'Order not found'], 404);
            }

            // Update status berdasarkan notifikasi
            if ($transactionStatus === 'capture') {
                $order->payment_status = ($fraudStatus === 'accept') ? 'paid' : 'fraud';
            } elseif (in_array($transactionStatus, ['settlement', 'capture'])) {
                $order->payment_status = 'paid';
            } elseif (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
                $order->payment_status = 'cancelled';
            } elseif ($transactionStatus === 'pending') {
                $order->payment_status = 'pending';
            }

            $order->save();

            return response()->json(['message' => 'OK']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
