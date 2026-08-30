<?php

namespace App\Http\Actions\Admin\CatalogMenu;

use App\Http\Requests\Admin\CatalogMenu\UpdateCatalogMenuContentRequest;
use App\Models\ProductType;
use App\Services\CatalogMenu\CatalogMenuService;

class UpdateCatalogMenuContentAction
{
    public function __invoke(ProductType $productType, UpdateCatalogMenuContentRequest $request, CatalogMenuService $service)
    {
        $service->updateContent(
            $productType,
            $request->validated('cards', []),
            $request->validated('columns', []),
        );

        return redirect()
            ->route('admin.catalog-menu.edit.page', $productType)
            ->with('success', trans('admin.catalog_menu_saved'));
    }
}
