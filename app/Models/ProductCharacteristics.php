<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class ProductCharacteristics extends Model
{
    use HasTranslations;

    protected $guarded = [];

    public $translatable = ['name', 'value'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
