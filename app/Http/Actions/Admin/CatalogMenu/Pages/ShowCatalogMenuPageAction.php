<?php

namespace App\Http\Actions\Admin\CatalogMenu\Pages;

use App\Services\CatalogMenu\CatalogMenuService;

class ShowCatalogMenuPageAction
{
    public function __invoke(CatalogMenuService $service)
    {
        return view('pages.admin.catalog-menu.index', [
            'menuProductTypes' => $service->getAdminProductTypes(),
        ]);
    }
}
