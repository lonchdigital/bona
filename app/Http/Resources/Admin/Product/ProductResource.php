<?php

namespace App\Http\Resources\Admin\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Admin\Collection\CollectionResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource['id'],
            'text' => $this->resource['name'],
            'meta_title' => $this->resource->getTranslations('meta_title'),
            'meta_description' => $this->resource->getTranslations('meta_description'),
            'meta_keywords' => $this->resource->getTranslations('meta_keywords'),
            'special_offers' => $this->resource->special_offers,
            'price' => $this->resource['price'],
            'old_price' => $this->resource['old_price'],
            'price_in_currency' => $this->resource['price_in_currency'],
            'price_currency_id' => $this->resource['price_currency_id'],
            'country_id' => $this->resource['country_id'],
            'brand_id' => $this->resource['brand_id'],
            'collection' => CollectionResource::make($this->resource->collection),
            'categories' => $this->resource->categories->pluck('id'),
            'main_color_id' => $this->resource['main_color_id'],
            'all_color_ids' => $this->resource->colors->pluck('id'),
            'length' => $this->resource->length,
            'width' => $this->resource->width,
            'height' => $this->resource->height,
            'custom_fields' => CustomFieldValue::collection($this->resource->custom_fields),
        ];
    }
}
