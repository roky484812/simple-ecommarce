<?php

namespace App\Models;

use Database\Factories\CartFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'session_id'])]
class Cart extends Model
{
    /** @use HasFactory<CartFactory> */
    use HasFactory;

    /**
     * The user this cart belongs to, if authenticated.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The items in this cart.
     *
     * @return HasMany<CartItem>
     */
    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * The total quantity of all items in the cart.
     */
    public function totalQty(): int
    {
        return $this->items->sum('qty');
    }

    /**
     * The subtotal (sum of price_snapshot * qty) across all items.
     */
    public function subtotal(): float
    {
        return (float) $this->items->sum(fn (CartItem $item) => $item->price_snapshot * $item->qty);
    }
}
