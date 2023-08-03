<?php

namespace App\Http\Actions\Admin\Brands\Pages;

use App\Services\Brand\BrandService;

class ShowBrandsListPageAction
{
    public function __invoke(BrandService $service)
    {
        return view('pages.admin.brands.list',[
            'brandsPaginated' => $service->getBrandsPaginated(),
        ]);
    }
}
