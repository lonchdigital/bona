<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WishList extends Model
{
    protected $guarded = [];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
    public function products()
    {
        return $this->belongsToMany(Product::class, 'wish_list_products')->withTimestamps();
    }
}
