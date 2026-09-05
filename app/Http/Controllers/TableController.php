<?php

namespace App\Http\Controllers;

use App\Models\Table;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TableController extends Controller
{
    // Menampilkan semua meja
    public function index()
    {
        $tables = Table::with('restaurant')
            ->orderBy('name')
            ->get();

        return response()->json($tables);
    }

    // Menambahkan meja
    public function store(Request $request)
    {
        $validated = $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255',
            'qr_code' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $table = Table::create($validated);

        return response()->json([
            'message' => 'Meja berhasil ditambahkan',
            'data' => $table
        ], 201);
    }

    // Mengedit meja
    public function update(Request $request, $id)
    {
        $table = Table::findOrFail($id);

        $validated = $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
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

    // Menghapus meja
    public function destroy($id)
    {
        $table = Table::findOrFail($id);

        $table->delete();

        return response()->json([
            'message' => 'Meja berhasil dihapus'
        ]);
    }

    // Generate QR Code
    public function generateQr($id)
    {
        $table = Table::with('restaurant')->findOrFail($id);

        // Ambil URL frontend dari file .env
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

    // Download QR Code
    public function downloadQr($id)
    {
        $table = Table::with('restaurant')->findOrFail($id);

        // Ambil URL frontend dari file .env
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

