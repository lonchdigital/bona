<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\Sitemap\Contracts\Sitemapable;
use Spatie\Sitemap\Tags\Url;
use Spatie\Translatable\HasTranslations;

class ProductType extends Model implements Sitemapable
{
    use HasTranslations;

    public $translatable = [
        'name',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'meta_product_title',
        'meta_product_description',
        'size_points',
        'product_size_length_filter_name',
        'product_size_width_filter_name',
        'product_size_height_filter_name',
        'product_point_name',
    ];

    protected $guarded = [];

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function fields(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(ProductField::class)
            ->withPivot(['show_as_filter', 'filter_name', 'show_on_main_filters_list', 'filter_full_position_id'])
            ->using(ProductFieldProductType::class);
    }

    /*public function fields()
    {
        return $this->belongsToMany(ProductField::class, 'product_field_product_type')
            ->withPivot(['show_as_filter', 'filter_name', 'show_on_main_filters_list', 'filter_full_position_id'])
            ->using(ProductFieldProductType::class)
            ->whereHas('products', function ($query) {
                $query->whereNotNull('products.id');
            });
    }*/

    public function attributes(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(ProductAttribute::class);
    }

    public function products(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_type_products');
    }

    public function sizeFilterOptions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProductTypeSizeOption::class);
    }

    public function categories(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function imageUrl(): Attribute
    {
        return Attribute::make(function () {
            if ($this->image_path) {
                return Storage::url($this->image_path);
            }
            return null;
        });
    }

    public function toArray(): array
    {
        $array = parent::toArray();

        $array['image_url'] = $this->image_url;

        return $array;
    }

    public function toSitemapTag(): Url | string | array
    {
        $urls = [];
        $urls[] = route('store.catalog.page', ['productTypeSlug' => $this->slug]);
        $urls[] = '/ru' . route('store.catalog.page', ['productTypeSlug' => $this->slug], false);

        return $urls;
    }
}
