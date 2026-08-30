<?php

namespace App\Http\Actions\Admin\CatalogMenu;

use App\Http\Requests\Admin\CatalogMenu\UpdateCatalogMenuOverviewRequest;
use App\Services\CatalogMenu\CatalogMenuService;

class UpdateCatalogMenuOverviewAction
{
    public function __invoke(UpdateCatalogMenuOverviewRequest $request, CatalogMenuService $service)
    {
        $service->updateOverview($request->validated('configurations'));

        return redirect()
            ->route('admin.catalog-menu.page')
            ->with('success', trans('admin.catalog_menu_saved'));
    }
}
