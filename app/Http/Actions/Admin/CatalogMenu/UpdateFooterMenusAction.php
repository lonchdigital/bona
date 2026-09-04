<?php

namespace App\Http\Actions\Admin\CatalogMenu;

use App\Http\Requests\Admin\CatalogMenu\UpdateFooterMenusRequest;
use App\Services\CatalogMenu\CatalogMenuService;

class UpdateFooterMenusAction
{
    public function __invoke(UpdateFooterMenusRequest $request, CatalogMenuService $service)
    {
        $validated = $request->validated();

        $service->updateFooterMenus(
            $validated['navigation'] ?? [],
            $validated['categories'] ?? [],
        );

        return redirect()
            ->route('admin.catalog-menu.page', ['tab' => 'footer'])
            ->with('success', trans('admin.footer_menu_saved'));
    }
}
