<?php

namespace App\Http\Actions\Admin\SEO;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Admin\Seo\EditRobotsTxtRequest;
use App\Services\Application\ApplicationConfigService;

class RobotsTxtEditAction extends BaseAction
{
    public function __invoke(EditRobotsTxtRequest $request, ApplicationConfigService $applicationConfigService)
    {
        $result = $applicationConfigService->setRobotsTxtContent($request->toDTO());

        return $this->handleActionResult(route('admin.seo.robots-txt.edit.page'), $request, $result);
    }
}
