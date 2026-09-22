<?php

namespace App\Exports;

use App\Models\Order;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SalesExport implements FromCollection, WithHeadings
{
    protected $restaurantId;
    protected $from;
    protected $to;

    public function __construct($restaurantId, $from = null, $to = null)
    {
        $this->restaurantId = $restaurantId;
        $this->from = $from;
        $this->to = $to;
    }

    public function collection(): Collection
    {
        $query = Order::with(['restaurant', 'table'])
            ->where('restaurant_id', $this->restaurantId)
            ->where('status', 'completed');

        if ($this->from) {
            $query->whereDate('created_at', '>=', $this->from);
        }

        if ($this->to) {
            $query->whereDate('created_at', '<=', $this->to);
        }

        return $query
            ->latest()
            ->get()
            ->map(function ($order) {
                return [
                    'Kode Pesanan' => $order->order_code,
                    'Tanggal' => $order->created_at->format('Y-m-d H:i:s'),
                    'Restaurant' => $order->restaurant?->name,
                    'Meja' => $order->table?->name,
                    'Nama Pelanggan' => $order->customer_name,
                    'Subtotal' => $order->total + $order->discount,
                    'Diskon' => $order->discount,
                    'Total' => $order->total,
                    'Status' => $order->status,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Kode Pesanan',
            'Tanggal',
            'Restaurant',
            'Meja',
            'Nama Pelanggan',
            'Subtotal',
            'Diskon',
            'Total',
            'Status',
        ];
    }
}
