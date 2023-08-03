<?php

namespace App\Excel\Exports\Sheets;

use App\Models\Country;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Color;

class CountriesSheet implements FromCollection, WithTitle, WithEvents, ShouldAutoSize, WithHeadings
{
    public function collection()
    {
        return Country::select('name')->get()->map(function ($country) {
            $country->name_string = $country->name;
            unset($country->name);
            return $country;
        });
    }

    public function title(): string
    {
        return trans('admin.countries');
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
            trans('admin.name'),
        ];
    }
}
