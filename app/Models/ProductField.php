<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class ProductField extends Model
{
    use HasTranslations;

    public $translatable = ['field_name', 'field_size_name'];

    protected $guarded = [];

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function options(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProductFieldOption::class);
    }

    public function fieldFilterOptions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProductFieldFilterOption::class);
    }

    public function types(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(ProductType::class)
            ->withPivot(['show_as_filter', 'filter_name', 'show_on_main_filters_list', 'filter_full_position_id'])
            ->using(ProductFieldProductType::class);
    }
}
