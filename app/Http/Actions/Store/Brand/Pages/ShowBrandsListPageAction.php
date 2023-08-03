<?php

namespace App\Http\Actions\Store\Brand\Pages;

use App\Services\Brand\BrandService;
use App\Http\Actions\Admin\BaseAction;

class ShowBrandsListPageAction extends BaseAction
{
    public function __invoke(?string $letter = null)
    {
        $brandService = app()->make(BrandService::class);
        $brandLetters = $brandService->sortBrandsByFirstLetterByProductType($brandService->getBrands());
        return view('pages.store.brands', [
            'brandLetters' => $brandService->sortBrandsByFirstLetterByProductType($brandService->getBrands()),
            'selectedBrandLetter' => $letter,
            'brandsSorted' => $brandService->getBrandsByFirstLetter($letter, $brandLetters->keys()[0]),
        ]);
    }
}
