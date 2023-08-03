<?php

namespace App\Services\Product;

use App\Models\ProductType;
use App\Services\Application\ApplicationConfigService;
use App\Services\Base\BaseService;
use Maatwebsite\Excel\Facades\Excel;
use App\Excel\Exports\ProductImportExampleExcelExport;

class ProductImportDownloadExampleService extends BaseService
{
    public function __construct(
        private readonly ApplicationConfigService $applicationService
    ) {
    }

    public function downloadProductsImportExample(ProductType $productType): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        return Excel::download(new ProductImportExampleExcelExport($productType, $this->applicationService), $productType->name . '_example.xlsx');
    }
}
