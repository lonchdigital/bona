<?php

namespace App\Http\Actions\Admin\Collections\Pages;

use App\Models\Collection;
use App\Services\Brand\BrandService;

class ShowCollectionEditPageAction
{
    public function __invoke(Collection $collection, BrandService $brandService)
    {
        return view('pages.admin.collections.edit', [
            'collection' => $collection,
            'brands' => $brandService->getBrands(),
        ]);
    }
}
