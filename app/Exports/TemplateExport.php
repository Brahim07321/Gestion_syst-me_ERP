<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;

class TemplateExport implements FromArray
{
    public function array(): array
    {
        return [
            ['Category_ID', 'code', 'Referonce', 'Designation', 'prace_bay', 'prace_sell', 'Quantite'],
        ];
    }
}
