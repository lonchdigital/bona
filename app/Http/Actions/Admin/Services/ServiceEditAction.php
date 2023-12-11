<?php

namespace App\Http\Actions\Admin\Services;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Admin\ServicesPage\ServicesPageEditRequest;
use App\Services\ServicesPage\ServicesPageService;

class ServiceEditAction extends BaseAction
{
    public function __invoke(
        ServicesPageEditRequest $request,
        ServicesPageService $servicesPageService,
    )
    {
        $result = $servicesPageService->editServicesPage($request->toDTO());

        return $this->handleActionResult(route('admin.pages.list.page'), $request, $result);
    }
}
