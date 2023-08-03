<?php

namespace App\Http\Actions\Admin\Brands;

use App\Http\Actions\Admin\BaseAction;
use App\Models\Brand;
use App\Services\Brand\BrandService;
use Illuminate\Http\Request;

class BrandDeleteAction extends BaseAction
{
    public function __invoke(Brand $brand, Request $request, BrandService $service)
    {
        $result = $service->deleteBrand($brand);

        return $this->handleActionResult(route('admin.brand.list.page'), $request, $result);
    }
}
