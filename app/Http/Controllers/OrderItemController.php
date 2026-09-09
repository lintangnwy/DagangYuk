<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;

class OrderItemController extends Controller
{
    public function index()
    {
        $items = OrderItem::with([
            'order',
            'product'
        ])->get();

        return response()->json($items);
    }

    public function show($id)
    {
        $item = OrderItem::with([
            'order',
            'product'
        ])->findOrFail($id);

        return response()->json($item);
    }

    public function destroy($id)
    {
        $item = OrderItem::findOrFail($id);

        $item->delete();

        return response()->json([
            'message' => 'Detail transaksi berhasil dihapus'
        ]);
    }
}