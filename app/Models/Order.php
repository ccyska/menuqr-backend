<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'restaurant_id',
        'table_id',
        'order_code',
        'customer_name',
        'note',
        'total',
        'status',
    ];

    protected $casts = [
        'total' => 'decimal:2',
    ];

    // Order milik satu restaurant
    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    // Order berasal dari satu meja
    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    // Satu order memiliki banyak item
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}