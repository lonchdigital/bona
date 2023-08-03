<?php

namespace App\Http\Resources\Admin\SEO;

use App\Http\Resources\Admin\Collection\CollectionResource;
use App\Http\Resources\Admin\Color\ColorResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Admin\Brand\BrandResource;

class ProductTypeWithFilters extends JsonResource
{
    public function toArray(Request $request)
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'has_brand' => $this->resource->has_brand,
            'has_color' => $this->resource->has_color,
            'has_collection' => $this->resource->has_collection,
            'has_category' => $this->resource->has_category,
            'categories' => ListResource::collection($this->resource->categories),
            'has_length' => $this->resource->has_size && $this->resource->has_length && $this->resource->filter_by_length,
            'length_options' => ListResource::collection($this->resource->length_options),
            'has_width' => $this->resource->has_size && $this->resource->has_width && $this->resource->filter_by_width,
            'width_options' => ListResource::collection($this->resource->width_options),
            'has_height' => $this->resource->has_size && $this->resource->has_height && $this->resource->filter_by_height,
            'height_options' => ListResource::collection($this->resource->height_options),
            'custom_fields' => CustomFieldResource::collection($this->resource->fields()->having('pivot_show_as_filter', true)->get()),
        ];
    }
}
