<?php

namespace App\Http\Actions\Admin\Brands;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Admin\Brand\BrandCreateRequest;
use App\Services\Brand\BrandService;

class BrandCreateAction extends BaseAction
{
    public function __invoke(BrandCreateRequest $request, BrandService $service)
    {
        $creator = $this->getAuthUser();

        $result = $service->createBrand($creator, $request->toDTO());

        return $this->handleActionResult(route('admin.brand.list.page'), $request, $result);
    }
}
