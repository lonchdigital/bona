<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductSlugRedirect extends Model
{
    protected $guarded = [];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Where an unknown product slug should permanently point, or null for a
     * genuine 404. The path has no locale prefix.
     */
    public static function targetPathFor(string $slug): ?string
    {
        $redirect = static::query()->with('product')->where('slug', $slug)->first();

        if ($redirect?->product) {
            return '/product/'.$redirect->product->slug;
        }

        return $redirect?->target_path ?: null;
    }
}
