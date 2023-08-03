<?php

namespace App\Excel\Exports\Sheets;

use App\Models\Brand;
use App\Models\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Color;

class CollectionSheet implements FromCollection, WithTitle, WithEvents, ShouldAutoSize, WithHeadings
{
    public function collection(): \Illuminate\Support\Collection
    {
        return Collection::select(['name', 'brand_id'])->with(['brand'])->get()->map(function ($collection) {
            $collection->brand_name_string = $collection->brand->name;
            $collection->name_string = $collection->name;
            unset($collection->name);
            unset($collection->brand_id);
            return $collection;
        });
    }

    public function title(): string
    {
        return trans('admin.collections');
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->getDelegate()->getParent()->getDefaultStyle()->getFont()->setSize(14);

                $event->sheet->getStyle(1)->getFill()->applyFromArray(['fillType' => 'solid','rotation' => 0, 'color' => ['rgb' => 'FF000000'],]);
                $event->sheet->getStyle(1)->getFont()->setSize(16);
                $event->sheet->getStyle(1)->getFont()->getColor()->setRGB(Color::COLOR_WHITE);
            },
        ];
    }

    public function headings(): array
    {
        return [
            trans('admin.brand'),
            trans('admin.collection')
        ];
    }
}
