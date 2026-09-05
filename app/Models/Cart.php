<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'cart_products')
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

    public function promoCode()
    {
        return $this->belongsTo(PromoCode::class);
    }
}
