<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\MenuAddon;
use Illuminate\Http\Request;

class MenuAddonController extends Controller
{
    // Menampilkan semua addon
    public function index($menuId)
    {
        $menu = Menu::findOrFail($menuId);

        $addons = $menu->addons()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return response()->json($addons);
    }

    // Menambahkan addon
    public function store(Request $request, $menuId)
    {
        $menu = Menu::findOrFail($menuId);

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

    // Mengubah addon
    public function update(Request $request, $id)
    {
        $addon = MenuAddon::findOrFail($id);

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

    // Menghapus addon
    public function destroy($id)
    {
        $addon = MenuAddon::findOrFail($id);

        $addon->delete();

        return response()->json([
            'message' => 'Addon berhasil dihapus',
        ]);
    }
}