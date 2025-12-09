<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = [
        'user_id',
        'session_id',
        'subtotal',
        'discount',
        'total',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    // Convenience helpers
    public function recalculateTotals(): void
    {
        $this->subtotal = $this->items->sum('total_price');
        // if kono discount thake ekhane logic boshabe
        $this->total = $this->subtotal - $this->discount;
        $this->save();
    }

    public function totalQuantity(): int
    {
        return (int) $this->items->sum('quantity');
    }
}
