<?php

namespace App\Http\Actions\Admin\Collections\Pages;

use App\Services\Brand\BrandService;

class ShowCollectionCreatePageAction
{
    public function __invoke(BrandService $brandService)
    {
        return view('pages.admin.collections.edit', [
            'brands' => $brandService->getBrands(),
        ]);
    }
}
