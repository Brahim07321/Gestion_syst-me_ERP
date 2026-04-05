<?php

namespace App\Exports;

use App\Models\Facture;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;

class FacturesExport implements FromCollection, WithHeadings, WithEvents, WithCustomStartCell
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
        $dateFrom = $this->request->date_from ?? '';
        $dateTo = $this->request->date_to ?? '';

        return Facture::when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->whereRaw('LOWER(code_facture) LIKE ?', ["%{$search}%"])
                      ->orWhereRaw('LOWER(client_name) LIKE ?', ["%{$search}%"])
                      ->orWhereRaw('LOWER(status) LIKE ?', ["%{$search}%"]);
                });
            })
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($dateFrom, function ($query) use ($dateFrom) {
                $query->whereDate('date_facture', '>=', $dateFrom);
            })
            ->when($dateTo, function ($query) use ($dateTo) {
                $query->whereDate('date_facture', '<=', $dateTo);
            })
            ->select(
                'id',
                'code_facture',
                'client_name',
                'total',
                'status',
                'paid_amount',
                'remaining_amount',
                'date_facture'
            )
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
            'Code facture',
            'Client',
            'Montant total',
            'Statut',
            'Montant payé',
            'Reste à recevoir',
            'Date facture',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function ($event) {
                $search = $this->request->search ?: 'Aucune';
                $status = $this->request->status ?: 'Tous';
                $dateFrom = $this->request->date_from ?: '-';
                $dateTo = $this->request->date_to ?: '-';

                $lastRow = $event->sheet->getHighestRow();

                // Titre
                $event->sheet->setCellValue('A1', 'Archive des factures');
                $event->sheet->mergeCells('A1:H1');
                $event->sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
                $event->sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                // Sous-titre / filtres
                $event->sheet->setCellValue('A2', "Recherche: {$search} | Statut: {$status} | Du: {$dateFrom} | Au: {$dateTo}");
                $event->sheet->mergeCells('A2:H2');
                $event->sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(10);
                $event->sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

                // Header style
                $event->sheet->getStyle('A4:H4')->applyFromArray([
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

                // Borders
                $event->sheet->getStyle("A4:H{$lastRow}")->applyFromArray([
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
                $event->sheet->getStyle("A4:H{$lastRow}")
                    ->getAlignment()
                    ->setHorizontal('center')
                    ->setVertical('center');

                // Zebra rows
                for ($i = 5; $i <= $lastRow; $i++) {
                    if ($i % 2 === 0) {
                        $event->sheet->getStyle("A{$i}:H{$i}")->applyFromArray([
                            'fill' => [
                                'fillType' => 'solid',
                                'startColor' => ['rgb' => 'F8FAFC'],
                            ],
                        ]);
                    }
                }

                // Auto size
                foreach (range('A', 'H') as $column) {
                    $event->sheet->getColumnDimension($column)->setAutoSize(true);
                }

                // Couleur spéciale للستاتيو
                for ($i = 5; $i <= $lastRow; $i++) {
                    $statusValue = strtolower((string) $event->sheet->getCell("E{$i}")->getValue());

                    if ($statusValue === 'payée') {
                        $event->sheet->getStyle("E{$i}")->getFont()->getColor()->setRGB('16A34A');
                    } elseif ($statusValue === 'partiellement payée') {
                        $event->sheet->getStyle("E{$i}")->getFont()->getColor()->setRGB('D97706');
                    } elseif ($statusValue === 'non payée') {
                        $event->sheet->getStyle("E{$i}")->getFont()->getColor()->setRGB('DC2626');
                    }
                }
            },
        ];
    }
}