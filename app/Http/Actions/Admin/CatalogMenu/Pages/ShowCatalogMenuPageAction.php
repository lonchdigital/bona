<?php

namespace App\Http\Actions\Admin\CatalogMenu\Pages;

use App\Services\CatalogMenu\CatalogMenuService;

class ShowCatalogMenuPageAction
{
    public function __invoke(CatalogMenuService $service)
    {
        $productTypes = $service->getAdminProductTypes();

        return view('pages.admin.catalog-menu.index', [
            'menuProductTypes' => $productTypes,
            'footerMenus' => $service->getAdminFooterMenus($productTypes),
        ]);
    }
}
