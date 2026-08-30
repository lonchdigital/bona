<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class ProductAttribute extends Model
{
    use HasTranslations;

    public $translatable = ['attribute_name'];

    protected $guarded = [];

    /*public function options(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProductFieldOption::class);
    }*/

    public function productAttributeOptions(): HasMany
    {
        return $this->hasMany(ProductAttributeOptions::class);
    }

    public function types(): BelongsToMany
    {
        return $this->belongsToMany(ProductType::class);
    }
}
