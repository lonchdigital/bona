<?php

namespace App\Http\Actions\Admin\SEO\Pages;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Resources\Admin\ProductType\ProductTypeResource;
use App\Http\Resources\Admin\SEO\SeogenResource;
use App\Models\SeoGenConfig;
use App\Services\Admin\ProductType\ProductTypeService;
use App\Services\Seogen\SeogenService;

class ShowSeoGenPageAction extends BaseAction
{
    public function __invoke(
        ProductTypeService $productTypeService,
        SeogenService $seogenService,
    )
    {
        $seogenData = $seogenService->getSeogen();


        return view('pages.admin.seo_fields.seogen-edit', [
            'productTypes' => ProductTypeResource::collection($productTypeService->getProductTypes()),
            'seogenCategories' => SeogenResource::collection($seogenData->where('page_type', SeoGenConfig::PAGE_TYPE_PRODUCT_CATEGORY)),
            'seogenProducts' => SeogenResource::collection($seogenData->where('page_type', SeoGenConfig::PAGE_TYPE_PRODUCT)),
            'seogenBrand' => SeogenResource::make($seogenData->where('page_type', SeoGenConfig::PAGE_TYPE_BRAND)->first()),
        ]);
    }
}
