<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use Illuminate\Http\Request;

class RestaurantController extends Controller
{
    // Menampilkan semua restoran
    public function index()
    {
        $restaurants = Restaurant::where('is_active', true)->get();

        return response()->json($restaurants);
    }

    // Menampilkan menu berdasarkan slug restoran
    public function menu($slug)
    {
        $restaurant = Restaurant::where('slug', $slug)
            ->where('is_active', true)
            ->with([
                'categories' => function ($query) {
                    $query->where('is_active', true)
                        ->orderBy('sort_order');
                },
               'menus' => function ($query) {
    $query->where('is_available', true)
        ->with([
            'variants' => function ($query) {
                $query->where('is_active', true)
                    ->orderBy('sort_order');
            },
            'addons' => function ($query) {
                $query->where('is_active', true)
                    ->orderBy('sort_order');
            },
        ])
        ->orderBy('sort_order');
}
            ])
            ->firstOrFail();

        $restaurant->menus->each(function ($menu) {
            $menu->image_url = $menu->image
                ? asset('storage/' . $menu->image)
                : null;
        });

        return response()->json([
            'restaurant' => $restaurant,
            'categories' => $restaurant->categories,
            'menus' => $restaurant->menus,
        ]);
    }

    // Menambahkan restoran
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:restaurants,slug',
            'logo' => 'nullable|string',
            'description' => 'nullable|string',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'whatsapp' => 'required|string|max:20',
            'is_active' => 'boolean',
        ]);

        $restaurant = Restaurant::create($validated);

        return response()->json([
            'message' => 'Restaurant berhasil ditambahkan',
            'data' => $restaurant
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $restaurant = Restaurant::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:restaurants,slug,' . $id,
            'logo' => 'nullable|string',
            'description' => 'nullable|string',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'whatsapp' => 'required|string|max:20',
            'is_active' => 'boolean',
        ]);

        $restaurant->update($validated);

        return response()->json([
            'message' => 'Restaurant berhasil diperbarui',
            'data' => $restaurant
        ]);
    }

    public function destroy($id)
    {
        $restaurant = Restaurant::findOrFail($id);

        $restaurant->delete();

        return response()->json([
            'message' => 'Restaurant berhasil dihapus'
        ]);
    }
}