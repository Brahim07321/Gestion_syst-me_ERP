<?php

namespace App\Exports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;

class CustomersExport implements FromCollection, WithHeadings, WithEvents, WithCustomStartCell
{
    public function collection()
    {
        return Customer::select('id', 'name', 'address', 'contact')->get();
    }

    // باش نخليو العنوان فالسطر 1
    public function startCell(): string
    {
        return 'A3';
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nom du client',
            'Adresse',
            'Téléphone',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function ($event) {

                // ======================
                // 🎯 TITRE
                // ======================
                $event->sheet->setCellValue('A1', 'Liste des Clients');
                $event->sheet->mergeCells('A1:D1');

                $event->sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
                $event->sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                // ======================
                // 🎨 HEADINGS STYLE
                // ======================
                $event->sheet->getStyle('A3:D3')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                        'size' => 11,
                    ],
                    'fill' => [
                        'fillType' => 'solid',
                        'startColor' => ['rgb' => '059669'], // vert جميل
                    ],
                    'alignment' => [
                        'horizontal' => 'center',
                    ],
                ]);

                // ======================
                // 📦 TABLE STYLE
                // ======================
                $lastRow = $event->sheet->getHighestRow();

                $event->sheet->getStyle("A3:D$lastRow")->applyFromArray([
                    'borders' => [
                
                        // 🧱 borders داخلية
                        'inside' => [
                            'borderStyle' => 'medium',
                            'color' => ['rgb' => '000000'],
                        ],
                
                        // 🔳 border خارجية قوية
                        'outline' => [
                            'borderStyle' => 'medium',
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                // ======================
                // 📏 AUTO SIZE
                // ======================
                foreach (range('A', 'D') as $column) {
                    $event->sheet->getColumnDimension($column)->setAutoSize(true);
                }

                // ======================
                // 🎯 CENTER TEXT
                // ======================
                $event->sheet->getStyle("A3:D$lastRow")
                    ->getAlignment()
                    ->setVertical('center')
                    ->setHorizontal('center');
            },
        ];
    }
}