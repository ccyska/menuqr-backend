<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\MenuVariant;
use Illuminate\Http\Request;

class MenuVariantController extends Controller
{
    // Menampilkan semua variant
    public function index($menuId)
    {
        $menu = Menu::findOrFail($menuId);

        $variants = $menu->variants()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return response()->json($variants);
    }

    // Menambahkan variant
    public function store(Request $request, $menuId)
    {
        $menu = Menu::findOrFail($menuId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $variant = $menu->variants()->create($validated);

        return response()->json([
            'message' => 'Variant berhasil ditambahkan',
            'data' => $variant,
        ], 201);
    }

    // Mengubah variant
    public function update(Request $request, $id)
    {
        $variant = MenuVariant::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $variant->update($validated);

        return response()->json([
            'message' => 'Variant berhasil diperbarui',
            'data' => $variant,
        ]);
    }

    // Menghapus variant
    public function destroy($id)
    {
        $variant = MenuVariant::findOrFail($id);

        $variant->delete();

        return response()->json([
            'message' => 'Variant berhasil dihapus',
        ]);
    }
}