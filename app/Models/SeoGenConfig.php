<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class SeoGenConfig extends Model
{
    const PAGE_TYPE_PRODUCT_CATEGORY = 'PRODUCT_CATEGORY';
    const PAGE_TYPE_PRODUCT = 'PRODUCT';
    const PAGE_TYPE_BRAND = 'BRAND';

    use HasTranslations;

    public $translatable = [
        'html_title_tag',
        'html_h1_tag',
        'meta_title_tag',
        'meta_description_tag',
        'meta_keywords_tag',
    ];

    protected $guarded = [];
}
