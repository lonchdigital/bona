<?php

namespace App\Excel\Imports;

use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeImport;

class NumberOfRowsImport implements WithEvents
{
    use Importable;

    private int $numberOfRows;


    public function registerEvents(): array
    {
        return [
            BeforeImport::class => function (BeforeImport $event) {
                $this->numberOfRows = $event->getReader()->getActiveSheet()->getHighestDataRow();
            }
        ];
    }

    public function getNumberOfRows()
    {
        return $this->numberOfRows;
    }
}
