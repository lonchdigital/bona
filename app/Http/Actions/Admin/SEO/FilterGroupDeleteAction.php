<?php

namespace App\Http\Actions\Admin\SEO;

use App\Http\Actions\Admin\BaseAction;
use App\Models\FilterGroup;
use App\Services\FilterGroups\FilterGroupService;
use Illuminate\Http\Request;

class FilterGroupDeleteAction extends BaseAction
{
    public function __invoke(FilterGroup $filterGroup, Request $request, FilterGroupService $filterGroupService)
    {
        return $this->handleActionResult(route('admin.filter-groups.list.page'), $request, $filterGroupService->deleteFilterGroup($filterGroup));
    }
}
