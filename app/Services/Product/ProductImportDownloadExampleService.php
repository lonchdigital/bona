<?php

namespace App\Services\Product;

use App\Excel\Exports\ProductImportExampleExcelExport;
use App\Models\ProductType;
use App\Services\Application\ApplicationConfigService;
use App\Services\Base\BaseService;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProductImportDownloadExampleService extends BaseService
{
    public function __construct(
        private readonly ApplicationConfigService $applicationService
    ) {}

    public function downloadProductsImportExample(ProductType $productType): BinaryFileResponse
    {
        return Excel::download(new ProductImportExampleExcelExport($productType, $this->applicationService), $productType->name.'_example.xlsx');
    }
}
