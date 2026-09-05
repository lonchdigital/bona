<?php

namespace App\Http\Actions\Admin\PromoCodes\Pages;

use App\Models\Brand;
use App\Models\Category;
use App\Models\ProductType;
use App\Models\PromoCode;

class ShowPromoCodeEditPageAction
{
    public function __invoke(PromoCode $promoCode)
    {
        return view('pages.admin.promo-codes.edit', [
            'promoCode' => $promoCode->load(['products', 'brands:id', 'categories:id', 'productTypes:id']),
            'selectedProducts' => $promoCode->products,
            'brands' => Brand::query()->orderBy('name->uk')->get(['id', 'name']),
            'categories' => Category::query()->with('productType:id,name')->orderBy('name->uk')->get(['id', 'name', 'product_type_id']),
            'productTypes' => ProductType::query()->orderBy('name->uk')->get(['id', 'name']),
        ]);
    }
}
