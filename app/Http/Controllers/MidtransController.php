<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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

        $cacertPath = realpath(base_path('vendor/midtrans/midtrans-php/data/cacert.pem'));
        $curlOptions = [
            CURLOPT_HTTPHEADER => [],
        ];
        if ($cacertPath && file_exists($cacertPath)) {
            $curlOptions[CURLOPT_CAINFO] = $cacertPath;
        }
        Config::$curlOptions = $curlOptions;
    }

    /**
     * Buat Snap token untuk pembayaran QRIS / Transfer.
     */
    public function createToken(Request $request): JsonResponse
    {
        $data = $request->validate([
            'order_id' => 'required|string|exists:orders,invoice_number',
            'customer' => 'nullable|string|max:100',
            'payment_channel' => 'nullable|string|in:qris,bca_va,bni_va,bri_va,mandiri_bill,permata_va',
        ]);

        $user = $request->user();
        $order = Order::where('invoice_number', $data['order_id'])
            ->where('user_id', $user->id)
            ->where('payment_method', '!=', 'cash')
            ->firstOrFail();

        if ($order->payment_status === 'paid') {
            return response()->json(['message' => 'Order sudah dibayar.'], 422);
        }

        $order->update(['payment_status' => 'pending']);

        $params = [
            'transaction_details' => [
                'order_id'     => $order->invoice_number,
                'gross_amount' => (int) $order->total_amount,
            ],
            'customer_details' => [
                'first_name' => $data['customer'] ?? $user->name,
                'email'      => $user->email,
            ],
            'enabled_payments' => match ($data['payment_channel'] ?? null) {
                'qris' => ['qris'],
                'bca_va' => ['bca_va'],
                'bni_va' => ['bni_va'],
                'bri_va' => ['bri_va'],
                'mandiri_bill' => ['echannel'],
                'permata_va' => ['permata_va'],
                default => ['qris', 'bca_va', 'bni_va', 'bri_va', 'permata_va', 'echannel'],
            },
            'qris' => [
                'acquirer' => 'gopay',
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
            $order->update(['midtrans_token' => $snapToken]);

            return response()->json([
                'snap_token' => $snapToken,
                'client_key' => config('midtrans.client_key'),
                'is_production' => config('midtrans.is_production'),
            ]);
        } catch (\Throwable $e) {
            Log::error('Midtrans token gagal dibuat', [
                'order_id' => $order->invoice_number,
                'payment_channel' => $data['payment_channel'] ?? null,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Payment gateway belum dapat membuat pembayaran. Periksa konfigurasi Midtrans Sandbox.',
            ], 422);
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
