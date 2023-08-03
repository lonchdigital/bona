<?php

namespace App\Http\Actions\Admin\SEO;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Admin\Seo\EditFilterGroupRequest;
use App\Services\FilterGroups\FilterGroupService;

class FilterGroupCreateAction extends BaseAction
{
    public function __invoke(EditFilterGroupRequest $request, FilterGroupService $filterGroupService)
    {
        return $this->handleActionResult(route('admin.filter-groups.list.page'), $request, $filterGroupService->createFilterGroup($request->toDto(), $this->getAuthUser()));
    }
}
