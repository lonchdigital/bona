<?php

namespace App\Http\Actions\Admin\SEO;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Admin\Seo\EditFilterGroupRequest;
use App\Models\FilterGroup;
use App\Services\FilterGroups\FilterGroupService;

class FilterGroupEditAction extends BaseAction
{
    public function __invoke(FilterGroup $filterGroup, EditFilterGroupRequest $request, FilterGroupService $filterGroupService)
    {
        return $this->handleActionResult(route('admin.filter-groups.list.page'), $request, $filterGroupService->editFilterGroup($filterGroup, $request->toDto()));
    }
}
