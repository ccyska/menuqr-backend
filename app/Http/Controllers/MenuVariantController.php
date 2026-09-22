<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\MenuVariant;
use Illuminate\Http\Request;

class MenuVariantController extends Controller
{
    // PUBLIC
    public function index($menuId)
    {
        $menu = Menu::findOrFail($menuId);

        $variants = $menu->variants()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return response()->json($variants);
    }

    // ADMIN
    public function store(Request $request, $menuId)
    {
        $admin = $request->user();

        $menu = Menu::where('id', $menuId)
            ->where('restaurant_id', $admin->restaurant_id)
            ->first();

        if (!$menu) {
            return response()->json([
                'message' => 'Menu tidak ditemukan atau bukan milik restaurant Anda'
            ], 404);
        }

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

    // ADMIN
    public function update(Request $request, $id)
    {
        $admin = $request->user();

        $variant = MenuVariant::where('id', $id)
            ->whereHas('menu', function ($query) use ($admin) {
                $query->where('restaurant_id', $admin->restaurant_id);
            })
            ->first();

        if (!$variant) {
            return response()->json([
                'message' => 'Variant tidak ditemukan atau bukan milik restaurant Anda'
            ], 404);
        }

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

    // ADMIN
    public function destroy(Request $request, $id)
    {
        $admin = $request->user();

        $variant = MenuVariant::where('id', $id)
            ->whereHas('menu', function ($query) use ($admin) {
                $query->where('restaurant_id', $admin->restaurant_id);
            })
            ->first();

        if (!$variant) {
            return response()->json([
                'message' => 'Variant tidak ditemukan atau bukan milik restaurant Anda'
            ], 404);
        }

        $variant->delete();

        return response()->json([
            'message' => 'Variant berhasil dihapus',
        ]);
    }
}
