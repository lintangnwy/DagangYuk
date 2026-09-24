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
        $user = $request->user();
        $canViewCost = $user->hasPermission('shop.cost.view');
        
        // Get date range filter from query params
        $rangeType = $request->query('range', 'today'); // today, this_week, this_month, last_month, custom
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        // Determine date range based on type
        if ($rangeType === 'custom' && $startDate && $endDate) {
            $start = \Carbon\Carbon::createFromFormat('Y-m-d', $startDate)->startOfDay();
            $end = \Carbon\Carbon::createFromFormat('Y-m-d', $endDate)->endOfDay();
        } elseif ($rangeType === 'today') {
            $start = now()->startOfDay();
            $end = now()->endOfDay();
        } elseif ($rangeType === 'last_7_days') {
            $start = now()->subDays(6)->startOfDay();
            $end = now()->endOfDay();
        } elseif ($rangeType === 'this_week') {
            $start = now()->startOfWeek();
            $end = now()->endOfDay();
        } elseif ($rangeType === 'this_month') {
            $start = now()->startOfMonth();
            $end = now()->endOfDay();
        } elseif ($rangeType === 'last_month') {
            $start = now()->subMonth()->startOfMonth();
            $end = now()->subMonth()->endOfMonth();
        } else {
            $start = now()->startOfDay();
            $end = now()->endOfDay();
        }

        // Penjualan & Profit dalam range
        $rangeData = Order::when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->whereBetween('orders.created_at', [$start, $end])
            ->selectRaw('
                COUNT(DISTINCT orders.id) as count,
                COALESCE(SUM(order_items.subtotal), 0) as revenue,
                COALESCE(SUM((order_items.unit_price - order_items.cost_price) * order_items.quantity), 0) as profit
            ')
            ->first();

        // Penjualan & Profit hari ini
        $today = Order::when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->whereDate('orders.created_at', now()->toDateString())
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

        // Calculate chart days based on range type
        $days = 7;
        if ($rangeType === 'last_7_days') {
            $days = 7;
        } elseif ($rangeType === 'this_week') {
            $days = now()->diffInDays(now()->startOfWeek()) + 1;
        } elseif ($rangeType === 'this_month') {
            $days = now()->day;
        } elseif ($rangeType === 'last_month') {
            $days = now()->subMonth()->daysInMonth;
        } elseif ($rangeType === 'custom' && $startDate && $endDate) {
            $days = \Carbon\Carbon::createFromFormat('Y-m-d', $startDate)
                ->diffInDays(\Carbon\Carbon::createFromFormat('Y-m-d', $endDate)) + 1;
        }

        // Chart data
        $chartQuery = Order::when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw("DATE(created_at) as date, COUNT(*) as count, COALESCE(SUM(total_amount), 0) as revenue")
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        // Isi hari yang kosong
        $chartData = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = $start->copy()->addDays($i)->toDateString();
            $dateObj = \Carbon\Carbon::createFromFormat('Y-m-d', $date);
            $chartData[] = [
                'date'    => $date,
                'label'   => $dateObj->locale('id')->isoFormat('ddd').', '.$dateObj->format('d M'),
                'count'   => (int) ($chartQuery[$date]->count ?? 0),
                'revenue' => (float) ($chartQuery[$date]->revenue ?? 0),
            ];
        }

        // 5 produk terlaris
        $topProducts = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->when($tenantId, fn($q) => $q->where('products.tenant_id', $tenantId))
            ->whereBetween('orders.created_at', [$start, $end])
            ->selectRaw('products.name, SUM(order_items.quantity) as total_qty, SUM(order_items.subtotal) as total_revenue')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        // Transaksi terbaru
        $recentOrders = Order::with('user:id,name')
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->whereBetween('created_at', [$start, $end])
            ->latest()
            ->limit(5)
            ->get(['id', 'invoice_number', 'total_amount', 'payment_method', 'user_id', 'created_at']);

        return response()->json([
            'period' => [
                'range'      => $rangeType,
                'start_date' => $start->toDateString(),
                'end_date'   => $end->toDateString(),
                'label'      => $this->getRangeLabel($rangeType, $start, $end),
            ],
            'current' => [
                'revenue'    => (float) $rangeData->revenue,
                'profit'     => $canViewCost ? (float) $rangeData->profit : null,
                'orders'     => (int)   $rangeData->count,
            ],
            'today' => [
                'revenue'    => (float) $today->revenue,
                'profit'     => $canViewCost ? (float) $today->profit : null,
                'orders'     => (int)   $today->count,
            ],
            'inventory' => [
                'total_products'    => $totalProducts,
                'low_stock_count'   => $lowStockCount,
                'out_of_stock_count'=> $outOfStockCount,
                'low_stock_items'   => $lowStockItems,
            ],
            'chart'        => $chartData,
            'top_products' => $topProducts,
            'recent_orders'=> $recentOrders,
        ]);
    }

    private function getRangeLabel(string $rangeType, \Carbon\Carbon $start, \Carbon\Carbon $end): string
    {
        return match ($rangeType) {
            'today'      => 'Hari Ini (' . $start->format('d M Y') . ')',
            'last_7_days'=> '7 Hari Terakhir',
            'this_week'  => 'Minggu Ini',
            'this_month' => 'Bulan Ini (' . $start->format('F Y') . ')',
            'last_month' => 'Bulan Lalu (' . $start->format('F Y') . ')',
            'custom'     => $start->format('d M Y') . ' s/d ' . $end->format('d M Y'),
            default      => 'Hari Ini',
        };
    }
}
