<?php

namespace App\Http\Resources;

use App\Http\Resources\Store\Product\ProductSpecialOfferResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BaseProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'slug' => $this->resource->slug,
            'sku' => $this->resource->sku,
            'link' => route('store.product.page', ['productSlug' => $this->resource->slug]),
            'price' => $this->resource->price,
            'old_price' => $this->resource->old_price,
            'main_image_url' => $this->resource->preview_image_url,
            'product_points_name' => $this->resource->productType->product_point_name,
            'special_offers' => $this->resource->special_offers ? ProductSpecialOfferResource::collection($this->resource->special_offers) : [],
            'is_in_wish_list' => $this->resource->is_in_wish_list,
        ];
    }
}
