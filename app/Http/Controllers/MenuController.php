<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MenuController extends Controller
{
    // Menampilkan semua menu yang tersedia + pencarian
    public function index(Request $request)
    {
        $query = Menu::with([
            'restaurant',
            'category',
            'variants',
            'addons'
        ])->where('is_available', true);

        // Pencarian berdasarkan nama atau deskripsi
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $menus = $query
            ->orderBy('sort_order')
            ->get()
            ->map(function ($menu) {
                $menu->image_url = $menu->image
                    ? asset('storage/' . $menu->image)
                    : null;

                return $menu;
            });

        return response()->json($menus);
    }

    // Menampilkan detail satu menu
    public function show($id)
    {
        $menu = Menu::with([
            'restaurant',
            'category',
            'variants' => function ($query) {
                $query->where('is_active', true)
                    ->orderBy('sort_order');
            },
            'addons' => function ($query) {
                $query->where('is_active', true)
                    ->orderBy('sort_order');
            },
        ])->findOrFail($id);

        $menu->image_url = $menu->image
            ? asset('storage/' . $menu->image)
            : null;

        return response()->json($menu);
    }

    // Menambahkan menu
    public function store(Request $request)
    {
        $validated = $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_available' => 'boolean',
            'sort_order' => 'integer',
        ]);

        // Pastikan category berasal dari restaurant yang sama
        $category = Category::findOrFail($validated['category_id']);

        if ($category->restaurant_id != $validated['restaurant_id']) {
            return response()->json([
                'message' => 'Category tidak sesuai dengan restaurant'
            ], 422);
        }

        // Upload gambar jika ada
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')
                ->store('menus', 'public');
        }

        $menu = Menu::create($validated);

        // Ambil relasi restaurant dan category
        $menu->load(['restaurant', 'category']);

        // Buat URL gambar
        $menu->image_url = $menu->image
            ? asset('storage/' . $menu->image)
            : null;

        return response()->json([
            'message' => 'Menu berhasil ditambahkan',
            'data' => $menu
        ], 201);
    }

    // Mengedit menu
    public function update(Request $request, $id)
    {
        $menu = Menu::findOrFail($id);

        $validated = $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_available' => 'boolean',
            'sort_order' => 'integer',
        ]);

        // Pastikan category berasal dari restaurant yang sama
        $category = Category::findOrFail($validated['category_id']);

        if ($category->restaurant_id != $validated['restaurant_id']) {
            return response()->json([
                'message' => 'Category tidak sesuai dengan restaurant'
            ], 422);
        }

        // Upload gambar baru jika ada
        if ($request->hasFile('image')) {

            // Hapus gambar lama
            if ($menu->image) {
                Storage::disk('public')->delete($menu->image);
            }

            // Simpan gambar baru
            $validated['image'] = $request->file('image')
                ->store('menus', 'public');
        }

        $menu->update($validated);

        // Ambil relasi terbaru
        $menu->load(['restaurant', 'category']);

        // Buat URL gambar
        $menu->image_url = $menu->image
            ? asset('storage/' . $menu->image)
            : null;

        return response()->json([
            'message' => 'Menu berhasil diperbarui',
            'data' => $menu
        ]);
    }

    // Menghapus menu
    public function destroy($id)
    {
        $menu = Menu::findOrFail($id);

        // Hapus gambar menu
        if ($menu->image) {
            Storage::disk('public')->delete($menu->image);
        }

        $menu->delete();

        return response()->json([
            'message' => 'Menu berhasil dihapus'
        ]);
    }
}