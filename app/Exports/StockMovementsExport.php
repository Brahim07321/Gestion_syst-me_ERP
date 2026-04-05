<?php

namespace App\Exports;

use App\Models\StockMovement;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;

class StockMovementsExport implements FromCollection, WithHeadings, WithEvents, WithCustomStartCell
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $query = StockMovement::with('product');

        if ($this->request->search) {
            $search = strtolower($this->request->search);

            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(reference) LIKE ?', ["%{$search}%"])
                  ->orWhereHas('product', function ($p) use ($search) {
                      $p->whereRaw('LOWER(Designation) LIKE ?', ["%{$search}%"]);
                  });
            });
        }

        if ($this->request->type) {
            $query->where('type', $this->request->type);
        }

        if ($this->request->source) {
            $query->where('source', $this->request->source);
        }

        if ($this->request->date_from) {
            $query->whereDate('created_at', '>=', $this->request->date_from);
        }

        if ($this->request->date_to) {
            $query->whereDate('created_at', '<=', $this->request->date_to);
        }

        return $query->latest()->get()->map(function ($movement) {
            return [
                'id' => $movement->id,
                'date' => optional($movement->created_at)->format('d/m/Y H:i'),
                'produit' => $movement->product->Designation ?? '-',
                'type' => $movement->type === 'entree' ? 'Entrée' : 'Sortie',
                'quantite' => $movement->quantity,
                'source' => $movement->source,
                'reference' => $movement->reference,
            ];
        });
    }

    public function startCell(): string
    {
        return 'A4';
    }

    public function headings(): array
    {
        return [
            'ID',
            'Date',
            'Produit',
            'Type',
            'Quantité',
            'Source',
            'Référence',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function ($event) {
                $lastRow = $event->sheet->getHighestRow();

                $event->sheet->setCellValue('A1', 'Historique des mouvements de stock');
                $event->sheet->mergeCells('A1:G1');
                $event->sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
                $event->sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                $event->sheet->getStyle('A4:G4')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                        'size' => 11,
                    ],
                    'fill' => [
                        'fillType' => 'solid',
                        'startColor' => ['rgb' => '2563EB'],
                    ],
                    'alignment' => [
                        'horizontal' => 'center',
                        'vertical' => 'center',
                    ],
                ]);

                $event->sheet->getStyle("A4:G{$lastRow}")->applyFromArray([
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

                $event->sheet->getStyle("A4:G{$lastRow}")
                    ->getAlignment()
                    ->setHorizontal('center')
                    ->setVertical('center');

                foreach (range('A', 'G') as $column) {
                    $event->sheet->getColumnDimension($column)->setAutoSize(true);
                }

                for ($i = 5; $i <= $lastRow; $i++) {
                    if ($i % 2 === 0) {
                        $event->sheet->getStyle("A{$i}:G{$i}")->applyFromArray([
                            'fill' => [
                                'fillType' => 'solid',
                                'startColor' => ['rgb' => 'F8FAFC'],
                            ],
                        ]);
                    }

                    $typeValue = strtolower((string) $event->sheet->getCell("D{$i}")->getValue());

                    if ($typeValue === 'entrée') {
                        $event->sheet->getStyle("D{$i}")->getFont()->getColor()->setRGB('16A34A');
                    } elseif ($typeValue === 'sortie') {
                        $event->sheet->getStyle("D{$i}")->getFont()->getColor()->setRGB('DC2626');
                    }
                }
            },
        ];
    }
}