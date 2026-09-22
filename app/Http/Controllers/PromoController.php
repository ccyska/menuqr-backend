<?php

namespace App\Http\Controllers;

use App\Models\Promo;
use Illuminate\Http\Request;

class PromoController extends Controller
{
    // ADMIN
    public function index(Request $request)
    {
        $admin = $request->user();

        $promos = Promo::with('restaurant')
            ->where('restaurant_id', $admin->restaurant_id)
            ->latest()
            ->get();

        return response()->json($promos);
    }

    // ADMIN
    public function store(Request $request)
    {
        $admin = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'min_order' => 'nullable|numeric|min:0',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'is_active' => 'boolean',
        ]);

        $validated['restaurant_id'] = $admin->restaurant_id;

        $promo = Promo::create($validated);

        return response()->json([
            'message' => 'Promo berhasil ditambahkan',
            'data' => $promo,
        ], 201);
    }

    // ADMIN
    public function update(Request $request, $id)
    {
        $admin = $request->user();

        $promo = Promo::where('id', $id)
            ->where('restaurant_id', $admin->restaurant_id)
            ->first();

        if (!$promo) {
            return response()->json([
                'message' => 'Promo tidak ditemukan atau bukan milik restaurant Anda'
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'min_order' => 'nullable|numeric|min:0',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'is_active' => 'boolean',
        ]);

        $promo->update($validated);

        return response()->json([
            'message' => 'Promo berhasil diperbarui',
            'data' => $promo,
        ]);
    }

    // ADMIN
    public function destroy(Request $request, $id)
    {
        $admin = $request->user();

        $promo = Promo::where('id', $id)
            ->where('restaurant_id', $admin->restaurant_id)
            ->first();

        if (!$promo) {
            return response()->json([
                'message' => 'Promo tidak ditemukan atau bukan milik restaurant Anda'
            ], 404);
        }

        $promo->delete();

        return response()->json([
            'message' => 'Promo berhasil dihapus',
        ]);
    }
}
