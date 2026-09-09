<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        return response()->json(
            Product::with('category')->get()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'category_id' => 'nullable|exists:categories,id',
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100',
            'cost_price' => 'nullable|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
        ]);

        $product = Product::create($validated);

        return response()->json([
            'message' => 'Produk berhasil ditambahkan',
            'data' => $product
        ], 201);
    }

    public function show($id)
    {
        return response()->json(
            Product::with('category')->findOrFail($id)
        );
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'category_id' => 'nullable|exists:categories,id',
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100',
            'cost_price' => 'nullable|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
        ]);

        $product->update($validated);

        return response()->json([
            'message' => 'Produk berhasil diubah',
            'data' => $product
        ]);
    }

    public function destroy($id)
    {
        Product::findOrFail($id)->delete();

        return response()->json([
            'message' => 'Produk berhasil dihapus'
        ]);
    }
}