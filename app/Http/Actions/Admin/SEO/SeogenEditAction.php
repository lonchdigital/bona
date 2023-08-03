<?php

namespace App\Http\Actions\Admin\SEO;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Admin\Seo\EditSeogenRequest;
use App\Services\Seogen\SeogenService;

class SeogenEditAction extends BaseAction
{
    public function __invoke(
        EditSeogenRequest $request,
        SeogenService $seogenService,
    )
    {
        return $this->handleActionResult(route('admin.seo-gen.edit.page'), $request, $seogenService->editSeogen($request->toDTO()));
    }
}
