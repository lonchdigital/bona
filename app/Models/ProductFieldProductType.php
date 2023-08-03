<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Spatie\Translatable\HasTranslations;

class ProductFieldProductType extends Pivot
{
    use HasTranslations;

    public $translatable = ['filter_name'];

    protected $table = 'product_field_product_type';
}

