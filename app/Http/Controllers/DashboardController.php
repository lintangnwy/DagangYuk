<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;

        // Penjualan & Profit hari ini
        $today = Order::when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->whereDate('orders.created_at', today())
            ->selectRaw('
                COUNT(DISTINCT orders.id) as count,
                COALESCE(SUM(order_items.subtotal), 0) as revenue,
                COALESCE(SUM((order_items.unit_price - order_items.cost_price) * order_items.quantity), 0) as profit
            ')
            ->first();

        // Penjualan & Profit bulan ini
        $thisMonth = Order::when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->whereMonth('orders.created_at', now()->month)
            ->whereYear('orders.created_at', now()->year)
            ->selectRaw('
                COUNT(DISTINCT orders.id) as count,
                COALESCE(SUM(order_items.subtotal), 0) as revenue,
                COALESCE(SUM((order_items.unit_price - order_items.cost_price) * order_items.quantity), 0) as profit
            ')
            ->first();

        // Total produk & stok menipis (< 5)
        $totalProducts  = Product::when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->count();
        $lowStockQuery  = Product::when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->where('stock', '>', 0)->where('stock', '<', 5);
        
        $lowStockCount = (clone $lowStockQuery)->count();
        $lowStockItems = (clone $lowStockQuery)->select('id', 'name', 'stock', 'sku')->limit(10)->get();

        $outOfStockCount = Product::when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->where('stock', 0)->count();

        // 7 hari terakhir (grafik)
        $last7Days = Order::when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->selectRaw("DATE(created_at) as date, COUNT(*) as count, COALESCE(SUM(total_amount), 0) as revenue")
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        // Isi hari yang kosong
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $chartData[] = [
                'date'    => $date,
                'label'   => now()->subDays($i)->locale('id')->isoFormat('ddd'),
                'count'   => (int) ($last7Days[$date]->count ?? 0),
                'revenue' => (float) ($last7Days[$date]->revenue ?? 0),
            ];
        }

        // 5 produk terlaris
        $topProducts = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->when($tenantId, fn($q) => $q->where('products.tenant_id', $tenantId))
            ->selectRaw('products.name, SUM(order_items.quantity) as total_qty, SUM(order_items.subtotal) as total_revenue')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        // Transaksi terbaru
        $recentOrders = Order::with('user:id,name')
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->latest()
            ->limit(5)
            ->get(['id', 'invoice_number', 'total_amount', 'payment_method', 'user_id', 'created_at']);

        return response()->json([
            'today' => [
                'revenue'    => (float) $today->revenue,
                'profit'     => (float) $today->profit,
                'orders'     => (int)   $today->count,
            ],
            'this_month' => [
                'revenue'    => (float) $thisMonth->revenue,
                'profit'     => (float) $thisMonth->profit,
                'orders'     => (int)   $thisMonth->count,
            ],
            'products' => [
                'total'     => $totalProducts,
                'low_stock' => $lowStockCount,
                'low_stock_items' => $lowStockItems,
                'out'       => $outOfStockCount,
            ],
            'tenants' => is_null($tenantId) ? [
                'total'  => \App\Models\Tenant::count(),
                'active' => \App\Models\Tenant::where('is_active', true)->count(),
            ] : null,
            'chart'         => $chartData,
            'top_products'  => $topProducts,
            'recent_orders' => $recentOrders,
        ]);
    }
}
