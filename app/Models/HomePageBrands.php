<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomePageBrands extends Model
{
    protected $guarded = [];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
}
