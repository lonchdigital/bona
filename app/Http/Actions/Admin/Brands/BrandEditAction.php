<?php

namespace App\Http\Actions\Admin\Brands;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Admin\Brand\BrandEditRequest;
use App\Models\Brand;
use App\Services\Brand\BrandService;

class BrandEditAction extends BaseAction
{
    public function __invoke(Brand $brand, BrandEditRequest $request, BrandService $service)
    {
        $result = $service->editBrand($brand, $request->toDTO());

        return $this->handleActionResult(route('admin.brand.list.page'), $request, $result);
    }
}
