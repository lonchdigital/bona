<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Order extends Model
{
    use HasTranslations;

    public $translatable = [
        'np_city',
        'np_department',
        'meest_city',
        'meest_department',
    ];

    protected $guarded = [];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'order_products')
            ->withTimestamps()
            ->withPivot(['count', 'price', 'attributes', 'attributes_price']);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function summary(): Attribute
    {
        return Attribute::make(function () {
            $summary = 0;

            foreach ($this->products as $product) {
                $summary += round(($product->pivot->price + $product->pivot->attributes_price) * $product->pivot->count);
            }

            return $summary;
        });
    }

    public function promoCode()
    {
        return $this->belongsTo(PromoCode::class);
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }
}
