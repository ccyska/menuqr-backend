<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // Menampilkan semua kategori aktif
    public function index()
    {
        $categories = Category::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return response()->json($categories);
    }

    // Menambahkan kategori
    public function store(Request $request)
    {
        $validated = $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $category = Category::create($validated);

        return response()->json([
            'message' => 'Category berhasil ditambahkan',
            'data' => $category
        ], 201);
    }

    // Mengubah kategori
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        // Pastikan restaurant tujuan benar-benar ada
        $category->update($validated);

        return response()->json([
            'message' => 'Category berhasil diperbarui',
            'data' => $category
        ]);
    }

    // Menghapus kategori
    public function destroy($id)
    {
        $category = Category::findOrFail($id);

        $category->delete();

        return response()->json([
            'message' => 'Category berhasil dihapus'
        ]);
    }
}