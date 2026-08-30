<?php

namespace App\Models;

use App\Services\Pricing\PricingService;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Order extends Model
{
    use HasTranslations;

    public $translatable = [
        'np_city',
        'np_department',
        'sat_city',
        'sat_department',
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
        return Attribute::make(
            get: fn () => app(PricingService::class)->forOrder($this)['total'],
        );
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
