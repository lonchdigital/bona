<?php

namespace App\Excel\Imports;

use App\Excel\Imports\Sheet\ProductsSheet;
use App\Models\ProductType;
use App\Services\Application\ApplicationConfigService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ProductImportImport implements WithMultipleSheets, SkipsOnFailure
{
    use Importable, SkipsFailures;

    protected array $schema = [];

    public function __construct(
        private readonly ProductType              $productType,
        private readonly ApplicationConfigService $applicationService,
        private readonly Collection               $countries,
        private readonly Collection               $categories,
        private readonly Collection               $brands,
        private readonly Collection               $currencies,
        private readonly Collection               $collections,
        private readonly Collection               $colors,
        private readonly Collection               $productFieldOptions,
    ) {
        $this->schema[0] = new ProductsSheet(
            $this->productType,
            $this->applicationService,
            $this->countries,
            $this->categories,
            $this->brands,
            $this->currencies,
            $this->collections,
            $this->colors,
            $this->productFieldOptions,
        );
    }


    public function sheets(): array
    {
        return $this->schema;
    }

    public function failures(): Collection
    {
        $failures = collect();
        foreach ($this->schema as $sheet) {
            $failures = $failures->merge($sheet->failures());
        }

        return $failures;
    }

    public function getRowsToSave(): array
    {
        return $this->schema[0]->getRowsToSave();
    }
}
