<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Menu;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    // Membuat pesanan baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'table_id' => 'nullable|exists:tables,id',
            'customer_name' => 'nullable|string|max:255',
            'note' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.menu_id' => 'required|exists:menus,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.note' => 'nullable|string',
        ]);

        $order = DB::transaction(function () use ($validated) {
            // Pastikan meja berasal dari restaurant yang sama
            if (!empty($validated['table_id'])) {
                $table = Table::findOrFail($validated['table_id']);

                if ($table->restaurant_id != $validated['restaurant_id']) {
                    abort(422, 'Meja tidak sesuai dengan restaurant');
                }

                if (!$table->is_active) {
                    abort(422, 'Meja sedang tidak aktif');
                }
            }

            $total = 0;
            $itemsData = [];

            foreach ($validated['items'] as $item) {
                $menu = Menu::findOrFail($item['menu_id']);

                // Pastikan menu berasal dari restaurant yang sama
                if ($menu->restaurant_id != $validated['restaurant_id']) {
                    abort(422, 'Menu tidak sesuai dengan restaurant');
                }

                // Menu harus tersedia
                if (!$menu->is_available) {
                    abort(422, "Menu {$menu->name} sedang tidak tersedia");
                }

                $price = $menu->price;
                $quantity = $item['quantity'];
                $subtotal = $price * $quantity;

                $total += $subtotal;

                $itemsData[] = [
                    'menu_id' => $menu->id,
                    'quantity' => $quantity,
                    'price' => $price,
                    'subtotal' => $subtotal,
                    'note' => $item['note'] ?? null,
                ];
            }

            $order = Order::create([
                'restaurant_id' => $validated['restaurant_id'],
                'table_id' => $validated['table_id'] ?? null,
                'order_code' => 'ORD-' . strtoupper(Str::random(8)),
                'customer_name' => $validated['customer_name'] ?? null,
                'note' => $validated['note'] ?? null,
                'total' => $total,
                'status' => 'pending',
            ]);

            $order->items()->createMany($itemsData);

            return $order;
        });

        $order->load([
            'restaurant',
            'table',
            'items.menu',
        ]);

        return response()->json([
            'message' => 'Pesanan berhasil dibuat',
            'data' => $order
        ], 201);
    }

    // Melihat detail pesanan
    public function show($id)
    {
        $order = Order::with([
            'restaurant',
            'table',
            'items.menu',
        ])->findOrFail($id);

        return response()->json([
            'data' => $order
        ]);
    }

    public function whatsapp($id)
{
    $order = Order::with([
        'restaurant',
        'table',
        'items.menu',
    ])->findOrFail($id);

    $message = "PESANAN BARU\n\n";

    $message .= "Kode Pesanan: " . $order->order_code . "\n";
    $message .= "Meja: " . ($order->table?->name ?? '-') . "\n";
    $message .= "Nama: " . ($order->customer_name ?? '-') . "\n\n";

    $message .= "Pesanan:\n";

    foreach ($order->items as $item) {
        $message .= $item->quantity . "x "
            . $item->menu->name
            . " — Rp" . number_format($item->subtotal, 0, ',', '.')
            . "\n";
    }

    $message .= "\nCatatan:\n";

    if ($order->note) {
        $message .= $order->note . "\n";
    } else {
        $message .= "-\n";
    }

    foreach ($order->items as $item) {
        if ($item->note) {
            $message .= $item->note . " pada " . $item->menu->name . "\n";
        }
    }

    $message .= "\nTotal: Rp"
        . number_format($order->total, 0, ',', '.')
        . "\n\n";

    $message .= "Terima kasih.";

    $whatsappNumber = $order->restaurant->whatsapp;

    $url = "https://wa.me/"
        . $whatsappNumber
        . "?text="
        . urlencode($message);

    return response()->json([
        'message' => 'Link WhatsApp berhasil dibuat',
        'whatsapp_url' => $url,
    ]);
}

    // Menampilkan semua pesanan
    public function index()
    {
        $orders = Order::with([
            'restaurant',
            'table',
            'items.menu',
        ])->latest()->get();

        return response()->json($orders);
    }
}