<?php

namespace App\Excel\Exports;

use App\DataClasses\ProductFieldTypeOptionsDataClass;
use App\Excel\Exports\Sheets\AvailabilityStatusesSheet;
use App\Excel\Exports\Sheets\BrandsSheet;
use App\Excel\Exports\Sheets\CollectionSheet;
use App\Excel\Exports\Sheets\ColorsSheet;
use App\Excel\Exports\Sheets\CountriesSheet;
use App\Excel\Exports\Sheets\CurrenciesSheet;
use App\Excel\Exports\Sheets\CustomFieldsSheet;
use App\Excel\Exports\Sheets\ProductsSheet;
use App\Excel\Exports\Sheets\SpecialOffersSheet;
use App\Models\ProductType;
use App\Services\Application\ApplicationConfigService;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ProductImportExampleExcelExport implements WithMultipleSheets
{
    public function __construct(
        private readonly ProductType              $productType,
        private readonly ApplicationConfigService $applicationService,
    ) { }


    public function sheets(): array
    {
        $sheets = [
            new ProductsSheet($this->productType, $this->applicationService),
            new AvailabilityStatusesSheet(),
            new SpecialOffersSheet(),
            new CurrenciesSheet(),
            new CountriesSheet(),
            new ColorsSheet(),
        ];

        if ($this->productType->has_brand) {
            $sheets[] = new BrandsSheet();
        }

        if ($this->productType->has_collection) {
            $sheets[] = new CollectionSheet();
        }

        foreach ($this->productType->fields as $field) {
            if ($field->field_type_id === ProductFieldTypeOptionsDataClass::FIELD_TYPE_OPTION) {
                $sheets[] = new CustomFieldsSheet($field);
            }
        }

        return $sheets;
    }
}
