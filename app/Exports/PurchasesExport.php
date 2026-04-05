<?php

namespace App\Exports;

use App\Models\Purchase;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;

class PurchasesExport implements FromCollection, WithHeadings, WithEvents, WithCustomStartCell
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $search = strtolower($this->request->search ?? '');
        $status = $this->request->status ?? '';

        return Purchase::leftJoin('suppliers', 'purchases.supplier_id', '=', 'suppliers.id')
            ->select(
                'purchases.id',
                'purchases.purchase_code',
                'suppliers.name as supplier_name',
                'purchases.purchase_date',
                'purchases.total',
                'purchases.status'
            )
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->whereRaw('LOWER(purchases.purchase_code) LIKE ?', ["%{$search}%"])
                      ->orWhereRaw('LOWER(suppliers.name) LIKE ?', ["%{$search}%"]);
                });
            })
            ->when($status, function ($query) use ($status) {
                $query->where('purchases.status', $status);
            })
            ->latest('purchases.id')
            ->get();
    }

    public function startCell(): string
    {
        return 'A4';
    }

    public function headings(): array
    {
        return [
            'ID',
            'Code achat',
            'Fournisseur',
            'Date',
            'Total',
            'Statut',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function ($event) {
                $search = $this->request->search ?: 'Aucune';
                $status = $this->request->status ?: 'Tous';

                $lastRow = $event->sheet->getHighestRow();

                // Titre
                $event->sheet->setCellValue('A1', 'Liste des achats');
                $event->sheet->mergeCells('A1:F1');
                $event->sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
                $event->sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                // Sous-titre
                $event->sheet->setCellValue('A2', "Recherche: {$search} | Statut: {$status}");
                $event->sheet->mergeCells('A2:F2');
                $event->sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(10);
                $event->sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

                // Header style
                $event->sheet->getStyle('A4:F4')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                        'size' => 11,
                    ],
                    'fill' => [
                        'fillType' => 'solid',
                        'startColor' => ['rgb' => '059669'],
                    ],
                    'alignment' => [
                        'horizontal' => 'center',
                        'vertical' => 'center',
                    ],
                ]);

                // Borders
                $event->sheet->getStyle("A4:F{$lastRow}")->applyFromArray([
                    'borders' => [
                        'inside' => [
                            'borderStyle' => 'thin',
                            'color' => ['rgb' => '9CA3AF'],
                        ],
                        'outline' => [
                            'borderStyle' => 'medium',
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                // Alignement
                $event->sheet->getStyle("A4:F{$lastRow}")
                    ->getAlignment()
                    ->setHorizontal('center')
                    ->setVertical('center');

                // Zebra rows
                for ($i = 5; $i <= $lastRow; $i++) {
                    if ($i % 2 === 0) {
                        $event->sheet->getStyle("A{$i}:F{$i}")->applyFromArray([
                            'fill' => [
                                'fillType' => 'solid',
                                'startColor' => ['rgb' => 'F8FAFC'],
                            ],
                        ]);
                    }
                }

                // Auto-size
                foreach (range('A', 'F') as $column) {
                    $event->sheet->getColumnDimension($column)->setAutoSize(true);
                }

                // Couleur status
                for ($i = 5; $i <= $lastRow; $i++) {
                    $statusValue = strtolower((string) $event->sheet->getCell("F{$i}")->getValue());

                    if ($statusValue === 'reçu') {
                        $event->sheet->getStyle("F{$i}")->getFont()->getColor()->setRGB('16A34A');
                    } elseif ($statusValue === 'annulé') {
                        $event->sheet->getStyle("F{$i}")->getFont()->getColor()->setRGB('DC2626');
                    } elseif ($statusValue === 'en attente') {
                        $event->sheet->getStyle("F{$i}")->getFont()->getColor()->setRGB('D97706');
                    }
                }
            },
        ];
    }
}