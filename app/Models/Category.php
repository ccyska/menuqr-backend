<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = [
        'restaurant_id',
        'name',
        'slug',
        'description',
        'is_active',
        'sort_order',
    ];

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function menus(): HasMany
    {
        return $this->hasMany(Menu::class);
    }
}