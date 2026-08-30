<?php

namespace App\Http\Actions\Admin\CatalogMenu\Pages;

use App\Models\ProductType;

class ShowCatalogMenuEditPageAction
{
    public function __invoke(ProductType $productType)
    {
        $productType->load(['catalogMenuConfiguration', 'categories']);

        return view('pages.admin.catalog-menu.edit', [
            'menuProductType' => $productType,
            'menuConfiguration' => $productType->catalogMenuConfiguration,
        ]);
    }
}
