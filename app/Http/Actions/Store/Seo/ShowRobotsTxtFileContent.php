<?php

namespace App\Http\Actions\Store\Seo;

use App\Http\Actions\Admin\BaseAction;
use App\Services\Application\ApplicationConfigService;

class ShowRobotsTxtFileContent extends BaseAction
{
    public function __invoke(ApplicationConfigService $applicationConfigService)
    {
        return response($applicationConfigService->getRobotsTxtContent())->header('Content-Type:', 'text/plain; charset=UTF-8');
    }
}
