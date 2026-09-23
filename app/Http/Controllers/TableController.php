<?php

namespace App\Http\Controllers;

use App\Models\Table;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TableController extends Controller
{
    // ====================
    // ADMIN - MENAMPILKAN MEJA
    // ====================

    public function index(Request $request)
    {
        $admin = $request->user();

        $tables = Table::with('restaurant')
            ->where('restaurant_id', $admin->restaurant_id)
            ->orderBy('name')
            ->get();

        return response()->json($tables);
    }


    // ====================
    // PUBLIC - VALIDASI MEJA
    // ====================

    public function validateTable($slug, $code)
    {
        $table = Table::with('restaurant')
            ->where('code', $code)
            ->where('is_active', true)
            ->whereHas('restaurant', function ($query) use ($slug) {
                $query->where('slug', $slug)
                    ->where('is_active', true);
            })
            ->first();

        if (!$table) {
            return response()->json([
                'message' => 'Meja tidak ditemukan atau tidak aktif'
            ], 404);
        }

        return response()->json([
            'restaurant' => [
                'id' => $table->restaurant->id,
                'name' => $table->restaurant->name,
                'slug' => $table->restaurant->slug,
            ],
            'table' => [
                'id' => $table->id,
                'name' => $table->name,
                'code' => $table->code,
                'is_active' => $table->is_active,
            ],
        ]);
    }


    // ====================
    // ADMIN - MENAMBAHKAN MEJA
    // ====================

    public function store(Request $request)
    {
        $admin = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255',
            'qr_code' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        // Restaurant otomatis mengikuti restaurant milik admin
        $validated['restaurant_id'] = $admin->restaurant_id;

        $table = Table::create($validated);

        return response()->json([
            'message' => 'Meja berhasil ditambahkan',
            'data' => $table
        ], 201);
    }


    // ====================
    // ADMIN - MENGEDIT MEJA
    // ====================

    public function update(Request $request, $id)
    {
        $admin = $request->user();

        // Hanya boleh mengambil meja milik restaurant admin
        $table = Table::where('id', $id)
            ->where('restaurant_id', $admin->restaurant_id)
            ->first();

        if (!$table) {
            return response()->json([
                'message' => 'Meja tidak ditemukan atau bukan milik restaurant Anda'
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255',
            'qr_code' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $table->update($validated);

        return response()->json([
            'message' => 'Meja berhasil diperbarui',
            'data' => $table
        ]);
    }


    // ====================
    // ADMIN - MENGHAPUS MEJA
    // ====================

    public function destroy(Request $request, $id)
    {
        $admin = $request->user();

        // Hanya boleh menghapus meja milik restaurant admin
        $table = Table::where('id', $id)
            ->where('restaurant_id', $admin->restaurant_id)
            ->first();

        if (!$table) {
            return response()->json([
                'message' => 'Meja tidak ditemukan atau bukan milik restaurant Anda'
            ], 404);
        }

        $table->delete();

        return response()->json([
            'message' => 'Meja berhasil dihapus'
        ]);
    }


    // ====================
    // ADMIN - GENERATE QR CODE
    // ====================

    public function generateQr(Request $request, $id)
    {
        $admin = $request->user();

        // Hanya bisa generate QR meja milik restaurant admin
        $table = Table::with('restaurant')
            ->where('id', $id)
            ->where('restaurant_id', $admin->restaurant_id)
            ->first();

        if (!$table) {
            return response()->json([
                'message' => 'Meja tidak ditemukan atau bukan milik restaurant Anda'
            ], 404);
        }

        // Ambil URL frontend dari .env
        $url = env('FRONTEND_URL') . '/menu/'
            . $table->restaurant->slug
            . '?table='
            . $table->code;

        // Simpan URL QR ke database
        $table->update([
            'qr_code' => $url
        ]);

        // Generate QR Code
        $qrCode = QrCode::format('svg')
            ->size(300)
            ->generate($url);

        return response($qrCode)
            ->header('Content-Type', 'image/svg+xml');
    }


    // ====================
    // ADMIN - DOWNLOAD QR CODE
    // ====================

    public function downloadQr(Request $request, $id)
    {
        $admin = $request->user();

        // Hanya bisa download QR meja milik restaurant admin
        $table = Table::with('restaurant')
            ->where('id', $id)
            ->where('restaurant_id', $admin->restaurant_id)
            ->first();

        if (!$table) {
            return response()->json([
                'message' => 'Meja tidak ditemukan atau bukan milik restaurant Anda'
            ], 404);
        }

        // Ambil URL frontend dari .env
        $url = env('FRONTEND_URL') . '/menu/'
            . $table->restaurant->slug
            . '?table='
            . $table->code;

        // Generate QR Code
        $qrCode = QrCode::format('svg')
            ->size(500)
            ->generate($url);

        return response($qrCode)
            ->header('Content-Type', 'image/svg+xml')
            ->header(
                'Content-Disposition',
                'attachment; filename="QR-' . $table->code . '.svg"'
            );
    }
}