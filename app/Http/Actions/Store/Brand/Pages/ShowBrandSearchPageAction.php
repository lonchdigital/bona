<?php

namespace App\Http\Actions\Store\Brand\Pages;

use App\Services\Brand\BrandService;
use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Store\Brand\BrandSearchRequest;

class ShowBrandSearchPageAction extends BaseAction
{
    public function __invoke(
        BrandSearchRequest $request,
        BrandService $brandService,
    )
    {
        $dto = $request->toDTO();

        return view('pages.store.brand-search', [
            'brandsFound' => $brandService->searchBrandsByName($dto),
            'searchText' => $dto->search,
        ]);
    }
}
