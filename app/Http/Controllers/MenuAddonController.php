<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\MenuAddon;
use Illuminate\Http\Request;

class MenuAddonController extends Controller
{
    // PUBLIC
    public function index($menuId)
    {
        $menu = Menu::findOrFail($menuId);

        $addons = $menu->addons()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return response()->json($addons);
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

        $addon = $menu->addons()->create($validated);

        return response()->json([
            'message' => 'Addon berhasil ditambahkan',
            'data' => $addon,
        ], 201);
    }

    // ADMIN
    public function update(Request $request, $id)
    {
        $admin = $request->user();

        $addon = MenuAddon::where('id', $id)
            ->whereHas('menu', function ($query) use ($admin) {
                $query->where('restaurant_id', $admin->restaurant_id);
            })
            ->first();

        if (!$addon) {
            return response()->json([
                'message' => 'Addon tidak ditemukan atau bukan milik restaurant Anda'
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $addon->update($validated);

        return response()->json([
            'message' => 'Addon berhasil diperbarui',
            'data' => $addon,
        ]);
    }

    // ADMIN
    public function destroy(Request $request, $id)
    {
        $admin = $request->user();

        $addon = MenuAddon::where('id', $id)
            ->whereHas('menu', function ($query) use ($admin) {
                $query->where('restaurant_id', $admin->restaurant_id);
            })
            ->first();

        if (!$addon) {
            return response()->json([
                'message' => 'Addon tidak ditemukan atau bukan milik restaurant Anda'
            ], 404);
        }

        $addon->delete();

        return response()->json([
            'message' => 'Addon berhasil dihapus',
        ]);
    }
}

