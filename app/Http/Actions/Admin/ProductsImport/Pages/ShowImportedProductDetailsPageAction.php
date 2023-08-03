<?php

namespace App\Http\Actions\Admin\ProductsImport\Pages;

use App\Http\Actions\Admin\BaseAction;
use App\Models\ImportedProduct;

class ShowImportedProductDetailsPageAction extends BaseAction
{
    public function __invoke(ImportedProduct $importedProduct)
    {
        return view('pages.admin.products-import.imported-product-details', [
            'importedProduct' => $importedProduct,
        ]);
    }
}
