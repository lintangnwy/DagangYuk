<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Get profit/loss report
     */
    public function profit(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        // Default to this month if no dates provided
        if (!$startDate) {
            $startDate = now()->startOfMonth()->toDateString();
        }
        if (!$endDate) {
            $endDate = now()->endOfMonth()->toDateString();
        }

        // Summary (Total Revenue, Total Cost, Total Profit)
        $summary = Order::when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->selectRaw('
                COALESCE(SUM(order_items.subtotal), 0) as revenue,
                COALESCE(SUM(order_items.cost_price * order_items.quantity), 0) as cogs,
                COALESCE(SUM((order_items.unit_price - order_items.cost_price) * order_items.quantity), 0) as profit,
                COUNT(DISTINCT orders.id) as total_orders
            ')
            ->first();

        // Product Breakdown
        $productBreakdown = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->when($tenantId, fn($q) => $q->where('orders.tenant_id', $tenantId))
            ->whereBetween('orders.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->selectRaw('
                products.name,
                SUM(order_items.quantity) as qty,
                SUM(order_items.subtotal) as revenue,
                SUM(order_items.cost_price * order_items.quantity) as cogs,
                SUM((order_items.unit_price - order_items.cost_price) * order_items.quantity) as profit
            ')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('qty')
            ->get();

        return response()->json([
            'summary' => [
                'revenue'      => (float) $summary->revenue,
                'cogs'         => (float) $summary->cogs, // Cost of Goods Sold / Harga Pokok Penjualan
                'profit'       => (float) $summary->profit,
                'total_orders' => (int) $summary->total_orders,
            ],
            'breakdown' => $productBreakdown,
            'period' => [
                'start' => $startDate,
                'end'   => $endDate
            ]
        ]);
    }
}
