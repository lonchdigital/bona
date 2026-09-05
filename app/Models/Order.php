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

    protected function casts(): array
    {
        return [
            'installment_period' => 'integer',
            'installment_surcharge_percent' => 'decimal:2',
            'installment_surcharge_amount' => 'decimal:2',
        ];
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'order_products')
            ->withTimestamps()
            ->withPivot([
                'id',
                'count',
                'price',
                'attributes',
                'attributes_price',
                'current_image_path',
                'bundle_key',
                'bundle_role',
                'bundle_category',
            ])
            ->orderByPivot('id');
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
