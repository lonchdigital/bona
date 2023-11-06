<?php

namespace App\Models;

use Spatie\Sitemap\Tags\Url;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\Sitemap\Contracts\Sitemapable;
use Spatie\Translatable\HasTranslations;

class Product extends Model implements Sitemapable
{
    use HasTranslations;

    public $translatable = [
        'name',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected $guarded = [];

    protected $casts = [
        'gallery_images' => 'array',
        'custom_fields' => 'array',
        'special_offers' => 'array',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function color()
    {
        return $this->belongsTo(Color::class, 'main_color_id');
    }

    public function colors()
    {
        return $this->belongsToMany(Color::class, 'product_colors')->withPivot(['price']);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'product_categories');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function collection()
    {
        return $this->belongsTo(Collection::class);
    }

    public function parent()
    {
        return $this->belongsTo(Product::class, 'parent_product_id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }


    public function children()
    {
        return $this->hasMany(Product::class, 'parent_product_id');
    }

    public function productType()
    {
        return $this->belongsTo(ProductType::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'price_currency_id');
    }

    public function getCustomFieldValue(int $fieldId): array | int | string | null
    {
        $field = $this->productType->fields->where('id', $fieldId)->first();
        $custom_fields = (!is_null($this->custom_fields)) ? $this->custom_fields : [];

        if (!array_key_exists($fieldId, $custom_fields)) {
            return null;
        }

        if ($field) {
            if ($field->is_multiselectable && !is_array($this->custom_fields[$fieldId])) {
                return [$this->custom_fields[$fieldId]];
            } elseif (!$field->is_multiselectable && is_array($this->custom_fields[$fieldId])) {
                return $this->custom_fields[$fieldId][0];
            }
        }
        return $this->custom_fields[$fieldId];
    }

    public function previewImageUrl(): Attribute
    {
        return Attribute::make(function () {
            if ($this->main_image_path) {
                return Storage::url($this->preview_image_path);
            }
            return null;
        });
    }

    public function mainImageUrl(): Attribute
    {
        return Attribute::make(function () {
            if ($this->main_image_path) {
                return Storage::url($this->main_image_path);
            }
            return null;
        });
    }

    public function patternImageUrl(): Attribute
    {
        return Attribute::make(function () {
            if ($this->pattern_image_path) {
                return Storage::url($this->pattern_image_path);
            }
            return null;
        });
    }

    //gallery_image_1_url
    public function galleryImage1Url(): Attribute
    {
        return Attribute::make(function () {
            if (isset($this->gallery_images['image_1'])) {
                return Storage::url($this->gallery_images['image_1']);
            }
            return null;
        });
    }

    public function galleryImage2Url(): Attribute
    {
        return Attribute::make(function () {
            if (isset($this->gallery_images['image_2'])) {
                return Storage::url($this->gallery_images['image_2']);
            }
            return null;
        });
    }

    public function galleryImage3Url(): Attribute
    {
        return Attribute::make(function () {
            if (isset($this->gallery_images['image_3'])) {
                return Storage::url($this->gallery_images['image_3']);
            }
            return null;
        });
    }

    public function galleryImage4Url(): Attribute
    {
        return Attribute::make(function () {
            if (isset($this->gallery_images['image_4'])) {
                return Storage::url($this->gallery_images['image_4']);
            }
            return null;
        });
    }

    public function galleryImage5Url(): Attribute
    {
        return Attribute::make(function () {
            if (isset($this->gallery_images['image_5'])) {
                return Storage::url($this->gallery_images['image_5']);
            }
            return null;
        });
    }

    public function galleryImagesCount(): Attribute
    {
        return Attribute::make(function () {
            $countOfImages = 0;

            if ($this->main_image_path) {
                $countOfImages++;
            }

            if ($this->gallery_image1_url) {
                $countOfImages++;
            }

            if ($this->gallery_image2_url) {
                $countOfImages++;
            }

            if ($this->gallery_image3_url) {
                $countOfImages++;
            }

            if ($this->gallery_image4_url) {
                $countOfImages++;
            }

            if ($this->gallery_image5_url) {
                $countOfImages++;
            }

            return $countOfImages;
        });
    }

    public function toArray(): array
    {
        $array = parent::toArray();

        $array['main_image_url'] = $this->main_image_url;
        $array['preview_image_url'] = $this->preview_image_url;

        return $array;
    }

    public function toSitemapTag(): Url | string | array
    {
        return route('store.product.page', ['productSlug' => $this->slug]);
    }
}
