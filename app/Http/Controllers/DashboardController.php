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

        // Penjualan hari ini
        $today = Order::when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->whereDate('created_at', today())
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(total_amount), 0) as revenue')
            ->first();

        // Penjualan bulan ini
        $thisMonth = Order::when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(total_amount), 0) as revenue')
            ->first();

        // Total produk & stok menipis (< 5)
        $totalProducts  = Product::when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))->count();
        $lowStockCount  = Product::when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->where('stock', '>', 0)->where('stock', '<', 5)->count();
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
                'orders'     => (int)   $today->count,
            ],
            'this_month' => [
                'revenue'    => (float) $thisMonth->revenue,
                'orders'     => (int)   $thisMonth->count,
            ],
            'products' => [
                'total'     => $totalProducts,
                'low_stock' => $lowStockCount,
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
