<?php

namespace App\Exports;

use Illuminate\Http\Request;
use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;

class ProductsExport implements FromCollection, WithHeadings, WithEvents, WithCustomStartCell
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $search = strtolower($this->request->search ?? '');
        $categoryId = $this->request->category_id ?? '';
        $lowStock = $this->request->low_stock ?? '';

        return Product::leftJoin('categories', 'products.Category_ID', '=', 'categories.id')
            ->select(
                'products.id',
                'products.Referonce',
                'products.code',
                'categories.Category as category_name',
                'products.Designation',
                'products.prace_bay',
                'products.prace_sell',
                'products.Quantite'
            )
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->whereRaw('LOWER(Designation) LIKE ?', ["%{$search}%"])
                      ->orWhereRaw('LOWER(Referonce) LIKE ?', ["%{$search}%"])
                      ->orWhereRaw('LOWER(code) LIKE ?', ["%{$search}%"]);
                });
            })
            ->when($categoryId, function ($query) use ($categoryId) {
                $query->where('Category_ID', $categoryId);
            })
            ->when($lowStock === '1', function ($query) {
                $query->where('Quantite', '<', 5);
            })
            ->get();
    }

    // باش نخليو العنوان فوق
    public function startCell(): string
    {
        return 'A3';
    }

    public function headings(): array
    {
        return [
            'ID',
            'Référence',
            'Code',
            'Catégorie',
            'Désignation',
            'Prix Achat',
            'Prix Vente',
            'Quantité',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function ($event) {

                // ======================
                // 🎯 TITRE
                // ======================
                $event->sheet->setCellValue('A1', 'Liste des Produits');
                $event->sheet->mergeCells('A1:H1');

                $event->sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
                $event->sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                // ======================
                // 🎨 HEADINGS STYLE
                // ======================
                $event->sheet->getStyle('A3:H3')->applyFromArray([
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

                $event->sheet->getStyle("A3:H$lastRow")->applyFromArray([
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
                foreach (range('A', 'H') as $column) {
                    $event->sheet->getColumnDimension($column)->setAutoSize(true);
                }

                // ======================
                // 🎯 ALIGNMENT
                // ======================
                $event->sheet->getStyle("A3:H$lastRow")
                    ->getAlignment()
                    ->setVertical('center')
                    ->setHorizontal('center');
            },
        ];
    }
}