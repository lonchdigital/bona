<?php

namespace App\Http\Actions\Admin\SEO\Pages;

use App\Http\Actions\Admin\BaseAction;
use App\Services\Application\ApplicationConfigService;

class ShowEditRobotsTxtEditPageAction extends BaseAction
{
    public function __invoke(ApplicationConfigService $applicationConfigService)
    {
        return view('pages.admin.seo_fields.robots-edit', [
            'content' => $applicationConfigService->getRobotsTxtContent(),
        ]);
    }
}
