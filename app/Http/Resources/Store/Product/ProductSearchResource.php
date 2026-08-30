<?php

namespace App\Http\Resources\Store\Product;

use App\Helpers\MultiLangRoute;
use App\Http\Resources\BaseProductResource;
use Illuminate\Http\Request;

class ProductSearchResource extends BaseProductResource
{
    public function toArray(Request $request): array
    {
        $fields = parent::toArray($request);
        $fields['link'] = MultiLangRoute::getMultiLangRoute('store.product.page', [
            'productSlug' => $this->resource->slug,
        ]);
        $fields['width'] = $this->resource->width;
        $fields['length'] = $this->resource->length;
        $fields['meta'] = collect([
            $this->resource->brand?->name,
            $this->resource->productType?->name,
        ])->filter()->join(' · ');
        $fields['price_formatted'] = number_format((float) $this->resource->price, 0, ',', ' ').' '.trans('base.uah');

        return $fields;
    }
}
