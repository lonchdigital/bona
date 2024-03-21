<?php

namespace App\Excel\Exports\Sheets;

use App\DataClasses\ProductFieldTypeOptionsDataClass;
use App\DataClasses\ProductSpecialOfferOptionsDataClass;
use App\DataClasses\ProductStatusDataClass;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Country;
use App\Models\ProductType;
use App\Services\Application\ApplicationConfigService;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Color;

class ProductsSheet implements WithHeadings, WithTitle, ShouldAutoSize, WithEvents, FromArray
{

    public function __construct(
        private readonly ProductType              $productType,
        private readonly ApplicationConfigService $applicationService,
    ){ }

    public function headings(): array
    {
        $headings = [
            trans('admin.parent_sku'),
            trans('admin.sku'),
        ];

        foreach ($this->applicationService->getAvailableLanguages() as $languageCode) {
            $headings[] = trans('admin.name') . ' ' . mb_strtoupper($languageCode);
        }

        foreach ($this->applicationService->getAvailableLanguages() as $languageCode) {
            $headings[] = trans('admin.meta_title') . ' ' . mb_strtoupper($languageCode);
        }

        foreach ($this->applicationService->getAvailableLanguages() as $languageCode) {
            $headings[] = trans('admin.meta_description') . ' ' . mb_strtoupper($languageCode);
        }

        foreach ($this->applicationService->getAvailableLanguages() as $languageCode) {
            $headings[] =  trans('admin.meta_keywords') . ' ' . mb_strtoupper($languageCode);
        }

        $headings[] = trans('admin.availability_status');
        $headings[] = trans('admin.special_offer');
        $headings[] = trans('admin.price_in_currency');
        $headings[] = trans('admin.purchase_price_in_currency');
        $headings[] = trans('admin.old_price_in_currency');
        $headings[] = trans('admin.price_currency');
        $headings[] = trans('admin.country');

        if ($this->productType->has_brand) {
            $headings[] = trans('admin.brand');
        }

        if ($this->productType->has_collection) {
            $headings[] = trans('admin.collection');
        }

        if ($this->productType->has_category) {
            $headings[] = trans('admin.product_categories');
        }

        if ($this->productType->has_color) {
            $headings[] = trans('admin.color');
            $headings[] = trans('admin.all_colors');
        }

        if ($this->productType->has_size) {
            if ($this->productType->has_length) {
                if (!$this->productType->has_height) {
                    $headings[] = trans('admin.length') . ' / ' . trans('admin.height');
                } else {
                    $headings[] = trans('admin.length');
                }

            }
            if ($this->productType->has_width) {
                $headings[] = trans('admin.width');
            }
            if ($this->productType->has_height) {
                $headings[] = trans('admin.height');
            }
        }

        foreach ($this->productType->fields as $customField) {
            $headings[] = $customField->field_name;
        }

        return $headings;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->getDelegate()->getParent()->getDefaultStyle()->getFont()->setSize(14);
                $event->sheet->getDelegate()->getRowDimension(1)->setRowHeight(40);

                $event->sheet->getStyle(1)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                $event->sheet->getStyle(1)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $event->sheet->getStyle(1)->getFill()->applyFromArray(['fillType' => 'solid','rotation' => 0, 'color' => ['rgb' => 'FF000000'],]);
                $event->sheet->getStyle(1)->getFont()->setSize(16);
                $event->sheet->getStyle(1)->getFont()->getColor()->setRGB(Color::COLOR_WHITE);
            },
        ];
    }

    public function title(): string
    {
        return $this->productType->name;
    }

    public function array(): array
    {
        $brand = Brand::first();
        $country = Country::first();
        $collection = Collection::where('brand_id', $brand->id)->first();
        $colors = \App\Models\Color::limit(3)->get();
        $categories = Category::limit(4)->get();

        $row1 = $this->generateFakeRow(1, $brand, $country, $collection, $colors, $categories);
        $row2 = $this->generateFakeRow(2, $brand, $country, $collection, $colors, $categories);
        $row2[0] = $row1[1];
        return [$row1, $row2];
    }

    private function generateFakeRow(int $skuIterator, ?Brand $brand, ?Country $country, ?Collection $collection, ?\Illuminate\Support\Collection $colors, ?\Illuminate\Support\Collection $categories): array
    {
        $fakeData = [];

        $fakeData = [
            '',
            'ART000000F' . $skuIterator,
        ];

        foreach ($this->applicationService->getAvailableLanguages() as $languageCode) {
            $fakeData[] =  trans('admin.name') . ' ' . $this->productType->name . ' ' . mb_strtoupper($languageCode);
        }

        foreach ($this->applicationService->getAvailableLanguages() as $languageCode) {
            $fakeData[] =  trans('admin.buy_wallpapers') . ' ' . mb_strtoupper($languageCode);
        }

        foreach ($this->applicationService->getAvailableLanguages() as $languageCode) {
            $fakeData[] =  trans('admin.buy_wallpapers') . ' ' . mb_strtoupper($languageCode);
        }

        foreach ($this->applicationService->getAvailableLanguages() as $languageCode) {
            $fakeData[] =  trans('admin.buy_wallpapers') . ' ' . $this->productType->name . ' ' . mb_strtoupper($languageCode);
        }

        $fakeData[] = ProductStatusDataClass::get(ProductStatusDataClass::PRODUCT_STATUS_STOCK)['name'];
        $fakeData[] = ProductSpecialOfferOptionsDataClass::get(ProductSpecialOfferOptionsDataClass::EXCLUSIVE)['name'] . ', ' .  ProductSpecialOfferOptionsDataClass::get(ProductSpecialOfferOptionsDataClass::NEW)['name'];
        $fakeData[] = 8.43;
        $fakeData[] = 5.12;
        $fakeData[] = 10.43;
        $fakeData[] = 'USD';
        $fakeData[] = $country ? $country->name : trans('admin.country');

        if ($this->productType->has_brand) {
            $fakeData[] = $brand ? $brand->name : trans('admin.brand');
        }

        if ($this->productType->has_collection) {
            $fakeData[] = $collection ? $collection->name : trans('admin.collection');
        }

        if ($this->productType->has_category) {
            $fakeData[] = count($categories) ? $categories->pluck('name')->implode(', ') : trans('admin.category') . ', ' . trans('admin.category');
        }

        if ($this->productType->has_color) {
            $fakeData[] = count($colors) ? $colors[0]->name : trans('admin.color');
            $fakeData[] = count($colors) ? $colors->pluck('name')->implode(', ') : trans('admin.color') . ', ' . trans('admin.color');
        }

        if ($this->productType->has_size) {
            if ($this->productType->has_length) {
                $fakeData[] = rand(1, 99);
            }
            if ($this->productType->has_width) {
                $fakeData[] = rand(1, 99);
            }
            if ($this->productType->has_height) {
                $fakeData[] = rand(1, 99);
            }
        }

        foreach ($this->productType->fields as $field) {
            if ($field->field_type_id === ProductFieldTypeOptionsDataClass::FIELD_TYPE_STRING) {
                $fakeData = trans('admin.text');
            } else if ($field->field_type_id === ProductFieldTypeOptionsDataClass::FIELD_TYPE_NUMBER) {
                $fakeData[] = rand(1, 99);
            } else if ($field->field_type_id === ProductFieldTypeOptionsDataClass::FIELD_TYPE_SIZE) {
                $fakeData[] = rand(1, 99);
            } else if ($field->field_type_id === ProductFieldTypeOptionsDataClass::FIELD_TYPE_OPTION) {
                $options = $field->options;

                if ($field->is_multiselectable) {
                    $fakeData[] = count($options) >= 2 ? $options[0]->name . ', ' . $options[1]->name :
                        trans('admin.option') . ', ' . trans('admin.option');
                } else {
                    $fakeData[] = count($options) ? $options[0]->name : trans('admin.option');
                }
            }
        }

        return $fakeData;
    }
}
