<?php

namespace App\Http\Actions\Admin\SEO\Pages;

use App\Http\Actions\Admin\BaseAction;
use App\Services\FilterGroups\FilterGroupService;

class ShowFilterGroupListPageAction extends BaseAction
{
    public function __invoke(FilterGroupService $filterGroupService)
    {
        return view('pages.admin.seo_fields.filter-group-list', [
            'filterGroupsPaginated' => $filterGroupService->getFilterGroupsPaginated(),
        ]);
    }
}
