<?php

namespace App\Http\Actions\Admin\PromoCodes\Pages;

use App\Models\Brand;
use App\Models\Category;
use App\Models\ProductType;

class ShowPromoCodeCreatePageAction
{
    public function __invoke()
    {
        return view('pages.admin.promo-codes.edit', [
            'selectedProducts' => collect(),
            'brands' => Brand::query()->orderBy('name->uk')->get(['id', 'name']),
            'categories' => Category::query()->with('productType:id,name')->orderBy('name->uk')->get(['id', 'name', 'product_type_id']),
            'productTypes' => ProductType::query()->orderBy('name->uk')->get(['id', 'name']),
        ]);
    }
}
