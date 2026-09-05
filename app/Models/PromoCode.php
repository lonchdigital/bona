<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PromoCode extends Model
{
    public const TYPE_PERCENT = 'percent';

    public const TYPE_FIXED = 'fixed';

    protected $guarded = [];

    protected $casts = [
        'is_used' => 'boolean',
        'is_active' => 'boolean',
        'all_products' => 'boolean',
        'discount_value' => 'decimal:2',
        'minimum_order_amount' => 'decimal:2',
        'maximum_discount_amount' => 'decimal:2',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'usage_limit' => 'integer',
        'usage_count' => 'integer',
        'max_discounted_items' => 'integer',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'promo_code_product');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function effectiveDiscountValue(): float
    {
        return (float) ($this->discount_value ?? $this->discount ?? 0);
    }

    public function effectiveUsageLimit(): ?int
    {
        // A row created by legacy code only has `discount`; those codes have
        // always been single-use and must remain so.
        if ($this->discount_value === null) {
            return 1;
        }

        return $this->usage_limit;
    }
}
