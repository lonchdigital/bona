<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomePageProductOptions extends Model
{
    protected $guarded = [];

    public function option()
    {
        return $this->belongsTo(ProductFieldOption::class, 'product_field_option_id');
    }
}
