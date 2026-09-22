<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        return response()->json(
            Category::all()
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);
        
        $data['tenant_id'] = $request->user()->tenant_id;

        $category = Category::create($data);

        return response()->json([
            'message' => 'Kategori berhasil ditambahkan',
            'data' => $category
        ], 201);
    }

    public function show($id)
    {
        return response()->json(
            Category::findOrFail($id)
        );
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $category->update($data);

        return response()->json([
            'message' => 'Kategori berhasil diubah',
            'data' => $category
        ]);
    }

    public function destroy($id)
{
    $category = Category::findOrFail($id);

    if ($category->products()->exists()) {
        return response()->json([
            'message' => 'Kategori tidak bisa dihapus karena masih memiliki produk'
        ], 422);
    }

    $category->delete();

    return response()->json([
        'message' => 'Kategori berhasil dihapus'
    ]);
}

}