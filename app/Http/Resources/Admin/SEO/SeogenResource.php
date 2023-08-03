<?php

namespace App\Http\Resources\Admin\SEO;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SeogenResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'page_type' => $this->resource->page_type,
            'product_type_id' => $this->resource->product_type_id,
            'html_title_tag' => $this->resource->getTranslations('html_title_tag'),
            'html_h1_tag' => $this->resource->getTranslations('html_h1_tag'),
            'meta_title_tag' => $this->resource->getTranslations('meta_title_tag'),
            'meta_description_tag' => $this->resource->getTranslations('meta_description_tag'),
            'meta_keywords_tag' => $this->resource->getTranslations('meta_keywords_tag'),
        ];
    }
}
