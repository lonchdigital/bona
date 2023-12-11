<?php

namespace App\Http\Actions\Admin\ApplicationConfig;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Admin\ApplicationConfigs\ApplicationConfigsEditRequest;
use App\Services\Application\ApplicationConfigService;

class ApplicationConfigEditAction extends BaseAction
{
    public function __invoke(
        ApplicationConfigsEditRequest $request,
        ApplicationConfigService $applicationConfigService,
    )
    {
        $result = $applicationConfigService->editApplicationConfig($request->toDTO());

        return $this->handleActionResult(route('admin.application-config.edit.page'), $request, $result);
    }
}

