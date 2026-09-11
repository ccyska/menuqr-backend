<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Menu;
use App\Models\Table;
use App\Models\Promo;
use App\Models\MenuVariant;
use App\Models\MenuAddon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    // =========================================================
    // MEMBUAT PESANAN
    // =========================================================
    public function store(Request $request)
    {
        $validated = $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'table_id' => 'nullable|exists:tables,id',

            'promo_id' => 'nullable|exists:promos,id',

            'customer_name' => 'nullable|string|max:255',
            'note' => 'nullable|string',

            'items' => 'required|array|min:1',
            'items.*.menu_id' => 'required|exists:menus,id',
            'items.*.variant_id' => 'nullable|exists:menu_variants,id',
            'items.*.addon_ids' => 'nullable|array',
            'items.*.addon_ids.*' => 'exists:menu_addons,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.note' => 'nullable|string',
        ]);

        $order = DB::transaction(function () use ($validated) {

            // =====================================================
            // VALIDASI MEJA
            // =====================================================
            if (!empty($validated['table_id'])) {

                $table = Table::findOrFail($validated['table_id']);

                if ($table->restaurant_id != $validated['restaurant_id']) {
                    abort(422, 'Meja tidak sesuai dengan restaurant');
                }

                if (!$table->is_active) {
                    abort(422, 'Meja sedang tidak aktif');
                }
            }

            $subtotalOrder = 0;
            $itemsData = [];

            // =====================================================
            // PROSES SETIAP ITEM
            // =====================================================
            foreach ($validated['items'] as $item) {

                $menu = Menu::findOrFail($item['menu_id']);

                // Menu harus milik restaurant yang sama
                if ($menu->restaurant_id != $validated['restaurant_id']) {
                    abort(
                        422,
                        "Menu {$menu->name} tidak sesuai dengan restaurant"
                    );
                }

                // Menu harus tersedia
                if (!$menu->is_available) {
                    abort(
                        422,
                        "Menu {$menu->name} sedang tidak tersedia"
                    );
                }

                // =================================================
                // HARGA DASAR MENU
                // =================================================

                $menuPrice = (float) $menu->price;

                // =================================================
                // VARIANT
                // =================================================

                $variant = null;
                $variantPrice = 0;

                if (!empty($item['variant_id'])) {

                    $variant = MenuVariant::findOrFail(
                        $item['variant_id']
                    );

                    // Variant harus milik menu yang dipilih
                    if ($variant->menu_id != $menu->id) {
                        abort(
                            422,
                            'Variant tidak sesuai dengan menu'
                        );
                    }

                    // Variant harus aktif
                    if (!$variant->is_active) {
                        abort(
                            422,
                            "Variant {$variant->name} sedang tidak tersedia"
                        );
                    }

                    $variantPrice = (float) $variant->price;
                }

                // =================================================
                // ADDON / TOPPING
                // =================================================

                $addons = [];
                $addonTotal = 0;

                if (!empty($item['addon_ids'])) {

                    $addonIds = array_unique(
                        $item['addon_ids']
                    );

                    foreach ($addonIds as $addonId) {

                        $addon = MenuAddon::findOrFail($addonId);

                        // Addon harus milik menu yang sama
                        if ($addon->menu_id != $menu->id) {
                            abort(
                                422,
                                "Addon {$addon->name} tidak sesuai dengan menu"
                            );
                        }

                        // Addon harus aktif
                        if (!$addon->is_active) {
                            abort(
                                422,
                                "Addon {$addon->name} sedang tidak tersedia"
                            );
                        }

                        $addonPrice = (float) $addon->price;

                        $addonTotal += $addonPrice;

                        $addons[] = [
                            'id' => $addon->id,
                            'name' => $addon->name,
                            'price' => $addonPrice,
                        ];
                    }
                }

                // =================================================
                // HITUNG HARGA ITEM
                // =================================================

                $quantity = (int) $item['quantity'];

                $unitPrice =
                    $menuPrice +
                    $variantPrice +
                    $addonTotal;

                $subtotal = $unitPrice * $quantity;

                $subtotalOrder += $subtotal;

                $itemsData[] = [
                    'menu_id' => $menu->id,
                    'variant_id' => $variant?->id,
                    'addons' => !empty($addons)
                        ? $addons
                        : null,
                    'quantity' => $quantity,
                    'price' => $unitPrice,
                    'subtotal' => $subtotal,
                    'note' => $item['note'] ?? null,
                ];
            }

            // =====================================================
            // PROMO
            // =====================================================

            $promo = null;
            $discount = 0;

            if (!empty($validated['promo_id'])) {

                $promo = Promo::findOrFail(
                    $validated['promo_id']
                );

                // Promo harus milik restaurant yang sama
                if ($promo->restaurant_id != $validated['restaurant_id']) {
                    abort(
                        422,
                        'Promo tidak sesuai dengan restaurant'
                    );
                }

                // Promo harus aktif
                if (!$promo->is_active) {
                    abort(
                        422,
                        'Promo sedang tidak aktif'
                    );
                }

                // Belum mulai
                if (
                    $promo->starts_at &&
                    now()->lt($promo->starts_at)
                ) {
                    abort(
                        422,
                        'Promo belum mulai'
                    );
                }

                // Sudah berakhir
                if (
                    $promo->ends_at &&
                    now()->gt($promo->ends_at)
                ) {
                    abort(
                        422,
                        'Promo sudah berakhir'
                    );
                }

                // Minimum pembelian
                if (
                    $subtotalOrder <
                    (float) $promo->min_order
                ) {
                    abort(
                        422,
                        'Minimum pembelian untuk promo ini adalah Rp' .
                        number_format(
                            $promo->min_order,
                            0,
                            ',',
                            '.'
                        )
                    );
                }

                // Hitung diskon
                if ($promo->type === 'percentage') {

                    if ((float) $promo->value > 100) {
                        abort(
                            422,
                            'Persentase diskon tidak boleh lebih dari 100%'
                        );
                    }

                    $discount =
                        $subtotalOrder *
                        (
                            (float) $promo->value / 100
                        );

                } else {

                    $discount =
                        (float) $promo->value;
                }

                // Diskon tidak boleh lebih dari subtotal
                $discount = min(
                    $discount,
                    $subtotalOrder
                );
            }

            // =====================================================
            // TOTAL AKHIR
            // =====================================================

            $total = $subtotalOrder - $discount;

            // =====================================================
            // BUAT ORDER
            // =====================================================

            $order = Order::create([
                'restaurant_id' => $validated['restaurant_id'],
                'table_id' => $validated['table_id'] ?? null,
                'promo_id' => $promo?->id,
                'order_code' =>
                    'ORD-' .
                    strtoupper(Str::random(8)),
                'customer_name' =>
                    $validated['customer_name'] ?? null,
                'note' =>
                    $validated['note'] ?? null,
                'total' => $total,
                'discount' => $discount,
                'status' => 'pending',
            ]);

            // =====================================================
            // SIMPAN ORDER ITEMS
            // =====================================================

            $order->items()->createMany(
                $itemsData
            );

            return $order;
        });

        // =========================================================
        // LOAD RELASI
        // =========================================================

        $order->load([
            'restaurant',
            'table',
            'promo',
            'items.menu',
            'items.variant',
        ]);

        return response()->json([
            'message' => 'Pesanan berhasil dibuat',
            'data' => $order,
        ], 201);
    }


    // =========================================================
    // DETAIL PESANAN
    // =========================================================
    public function show($id)
    {
        $order = Order::with([
            'restaurant',
            'table',
            'promo',
            'items.menu',
            'items.variant',
        ])->findOrFail($id);

        return response()->json([
            'data' => $order,
        ]);
    }


    // =========================================================
    // SEMUA PESANAN ADMIN
    // =========================================================
    public function index()
    {
        $orders = Order::with([
            'restaurant',
            'table',
            'promo',
            'items.menu',
            'items.variant',
        ])
        ->latest()
        ->get();

        return response()->json($orders);
    }


    // =========================================================
    // UPDATE STATUS PESANAN
    // =========================================================
    public function updateStatus(
        Request $request,
        $id
    ) {
        $validated = $request->validate([
            'status' =>
                'required|in:pending,confirmed,completed,cancelled',
        ]);

        $order = Order::findOrFail($id);

        $currentStatus = $order->status;
        $newStatus = $validated['status'];

        // Status sama
        if ($currentStatus === $newStatus) {

            return response()->json([
                'message' =>
                    'Status pesanan sudah ' .
                    $newStatus,
                'data' => $order,
            ]);
        }

        // Aturan perubahan status
        $allowedTransitions = [
            'pending' => [
                'confirmed',
                'cancelled'
            ],

            'confirmed' => [
                'completed',
                'cancelled'
            ],

            'completed' => [],

            'cancelled' => [],
        ];

        if (
            !in_array(
                $newStatus,
                $allowedTransitions[$currentStatus] ?? []
            )
        ) {

            return response()->json([
                'message' =>
                    "Status tidak dapat diubah dari " .
                    "{$currentStatus} menjadi {$newStatus}",
            ], 422);
        }

        $order->update([
            'status' => $newStatus,
        ]);

        $order->load([
            'restaurant',
            'table',
            'promo',
            'items.menu',
            'items.variant',
        ]);

        return response()->json([
            'message' =>
                'Status pesanan berhasil diperbarui',
            'data' => $order,
        ]);
    }


    // =========================================================
    // WHATSAPP
    // =========================================================
    public function whatsapp($id)
    {
        $order = Order::with([
            'restaurant',
            'table',
            'promo',
            'items.menu',
            'items.variant',
        ])->findOrFail($id);

        $message =
            "PESANAN BARU\n\n";

        $message .=
            "Kode Pesanan: " .
            $order->order_code .
            "\n";

        $message .=
            "Meja: " .
            ($order->table?->name ?? '-') .
            "\n";

        $message .=
            "Nama: " .
            ($order->customer_name ?? '-') .
            "\n\n";

        $message .=
            "Pesanan:\n";

        foreach ($order->items as $item) {

            $message .=
                $item->quantity .
                "x " .
                $item->menu->name;

            // Variant
            if ($item->variant) {

                $message .=
                    " - " .
                    $item->variant->name;
            }

            $message .=
                " — Rp" .
                number_format(
                    $item->subtotal,
                    0,
                    ',',
                    '.'
                ) .
                "\n";

            // Addon
            if (!empty($item->addons)) {

                $addonNames =
                    collect($item->addons)
                    ->pluck('name')
                    ->implode(', ');

                $message .=
                    "   Topping: " .
                    $addonNames .
                    "\n";
            }

            // Catatan item
            if ($item->note) {

                $message .=
                    "   Catatan: " .
                    $item->note .
                    "\n";
            }
        }

        // Catatan order
        $message .=
            "\nCatatan:\n";

        if ($order->note) {

            $message .=
                $order->note .
                "\n";

        } else {

            $message .=
                "-\n";
        }

        // Promo
        if ($order->promo) {

            $message .=
                "\nPromo: " .
                $order->promo->name .
                "\n";

            $message .=
                "Diskon: Rp" .
                number_format(
                    $order->discount,
                    0,
                    ',',
                    '.'
                ) .
                "\n";
        }

        // Total
        $message .=
            "\nTotal: Rp" .
            number_format(
                $order->total,
                0,
                ',',
                '.'
            ) .
            "\n\n";

        $message .=
            "Terima kasih.";

        $whatsappNumber =
            $order->restaurant->whatsapp;

        $url =
            "https://wa.me/" .
            $whatsappNumber .
            "?text=" .
            urlencode($message);

        return response()->json([
            'message' =>
                'Link WhatsApp berhasil dibuat',
            'whatsapp_url' => $url,
        ]);
    }
}