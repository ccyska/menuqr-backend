<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'menu_id',
        'variant_id',
        'addons',
        'quantity',
        'price',
        'subtotal',
        'note',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'addons' => 'array',
    ];

    // Item milik satu order
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    // Item mengacu ke satu menu
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    // Item dapat memiliki satu variant
    public function variant(): BelongsTo
    {
        return $this->belongsTo(MenuVariant::class);
    }
}