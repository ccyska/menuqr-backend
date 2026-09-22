<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // =========================================================
    // PUBLIC
    // Menampilkan kategori aktif
    // Bisa difilter berdasarkan restaurant_id
    // =========================================================
    public function index(Request $request)
    {
        $query = Category::where('is_active', true);

        if ($request->filled('restaurant_id')) {
            $query->where('restaurant_id', $request->restaurant_id);
        }

        $categories = $query
            ->orderBy('sort_order')
            ->get();

        return response()->json($categories);
    }

    // =========================================================
    // ADMIN
    // Menampilkan SEMUA kategori milik restoran admin
    // =========================================================
    public function adminIndex(Request $request)
    {
        $admin = $request->user();

        $categories = Category::where(
                'restaurant_id',
                $admin->restaurant_id
            )
            ->orderBy('sort_order')
            ->get();

        return response()->json($categories);
    }

    // =========================================================
    // ADMIN
    // Menambahkan kategori
    // =========================================================
    public function store(Request $request)
    {
        $admin = $request->user();

        $validated = $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        // Admin hanya boleh membuat kategori
        // untuk restorannya sendiri
        if ((int) $validated['restaurant_id'] !== (int) $admin->restaurant_id) {
            return response()->json([
                'message' => 'Anda tidak memiliki akses ke restoran ini.'
            ], 403);
        }

        $category = Category::create($validated);

        return response()->json([
            'message' => 'Category berhasil ditambahkan',
            'data' => $category
        ], 201);
    }

    // =========================================================
    // ADMIN
    // Mengubah kategori
    // =========================================================
    public function update(Request $request, $id)
    {
        $admin = $request->user();

        // Cari kategori hanya di restoran milik admin
        $category = Category::where('restaurant_id', $admin->restaurant_id)
            ->findOrFail($id);

        $validated = $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        // Jangan izinkan memindahkan kategori
        // ke restoran lain
        if ((int) $validated['restaurant_id'] !== (int) $admin->restaurant_id) {
            return response()->json([
                'message' => 'Anda tidak memiliki akses ke restoran ini.'
            ], 403);
        }

        $category->update($validated);

        return response()->json([
            'message' => 'Category berhasil diperbarui',
            'data' => $category
        ]);
    }

    // =========================================================
    // ADMIN
    // Menghapus kategori
    // =========================================================
    public function destroy(Request $request, $id)
    {
        $admin = $request->user();

        // Hanya bisa menghapus kategori restoran sendiri
        $category = Category::where('restaurant_id', $admin->restaurant_id)
            ->findOrFail($id);

        $category->delete();

        return response()->json([
            'message' => 'Category berhasil dihapus'
        ]);
    }
}