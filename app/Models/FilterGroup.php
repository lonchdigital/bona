<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class FilterGroup extends Model
{
    use HasTranslations;

    public $translatable = [
        'name',
        'title_tag',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected $guarded = [];

    protected $casts = [
        'filters' => 'array',
    ];

    public function productType()
    {
        return $this->belongsTo(ProductType::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
