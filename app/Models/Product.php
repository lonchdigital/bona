<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL as FacadeURL;
use Spatie\Sitemap\Contracts\Sitemapable;
use Spatie\Sitemap\Tags\Url;
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

    public function scopeOrderByAvailabilityStatus($query)
    {
        return $query->orderByRaw('CASE WHEN availability_status_id = 4 THEN 1 ELSE 0 END');
    }

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

    public function galleries(): HasMany
    {
        return $this->hasMany(ProductGalleries::class, 'product_id');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'product_categories');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function productType()
    {
        return $this->belongsTo(ProductType::class);
    }

    public function productTypes(): BelongsToMany
    {
        return $this->belongsToMany(ProductType::class, 'product_type_products');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'price_currency_id');
    }

    public function getCustomFieldValue(int $fieldId): array|int|string|null
    {
        $field = $this->productType->fields->where('id', $fieldId)->first();
        $custom_fields = (! is_null($this->custom_fields)) ? $this->custom_fields : [];

        if (! array_key_exists($fieldId, $custom_fields)) {
            return null;
        }

        if ($field) {
            if ($field->is_multiselectable && ! is_array($this->custom_fields[$fieldId])) {
                return [$this->custom_fields[$fieldId]];
            } elseif (! $field->is_multiselectable && is_array($this->custom_fields[$fieldId])) {
                return $this->custom_fields[$fieldId][0];
            }
        }

        //        dd($this->custom_fields);
        return $this->custom_fields[$fieldId];
    }

    public function previewImageUrl(): Attribute
    {
        if ($this->product_type_id != config('constants.SUB_PRODUCTS_ID')) {
            return Attribute::make(function () {
                if ($this->main_image_path) {
                    return Storage::url($this->preview_image_path);
                } else {
                    return '/assets/images/no-image.png';
                }
            });
        } else {
            return Attribute::make(function () {
                $categoryImagePath = $this->categories->first()?->image_path;

                if ($this->main_image_path) {
                    return Storage::url($this->preview_image_path);
                } elseif ($categoryImagePath) {
                    return Storage::url($categoryImagePath);
                } else {
                    return '/assets/images/no-image.png';
                }
            });
        }
    }

    public function previewImageFullUrl(): Attribute
    {
        return Attribute::make(function () {
            if ($this->main_image_path) {

                //                return storage_path('app\public\\' . $this->preview_image_path);

                return FacadeURL::to('').Storage::url($this->preview_image_path);
            } else {
                return FacadeURL::to('').'/assets/images/no-image.png';
            }
        });
    }

    public function mainImageUrl(): Attribute
    {
        if ($this->product_type_id != config('constants.SUB_PRODUCTS_ID')) {
            return Attribute::make(function () {
                if ($this->main_image_path) {
                    return Storage::url($this->main_image_path);
                }

                return null;
            });
        } else {
            return Attribute::make(function () {
                $categoryImagePath = $this->categories->first()?->image_path;

                if ($this->main_image_path) {
                    return Storage::url($this->preview_image_path);
                } elseif ($categoryImagePath) {
                    return Storage::url($categoryImagePath);
                } else {
                    return '/assets/images/no-image.png';
                }
            });
        }
    }

    public function reviews()
    {
        return $this->hasMany(ProductReview::class);
    }

    public function approvedReviews()
    {
        return $this->hasMany(ProductReview::class)
            ->approved()
            ->orderByDesc('published_at')
            ->orderByDesc('id');
    }

    public function toArray(): array
    {
        $array = parent::toArray();

        $array['main_image_url'] = $this->main_image_url;
        $array['preview_image_url'] = $this->preview_image_url;

        return $array;
    }

    public function toSitemapTag(): Url|string|array
    {
        $urls = [];
        $urls[] = route('store.product.page', ['productSlug' => $this->slug]);
        $urls[] = '/ru'.route('store.product.page', ['productSlug' => $this->slug], false);

        return $urls;
    }
}
