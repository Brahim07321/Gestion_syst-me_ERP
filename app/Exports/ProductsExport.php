<?php

namespace App\Exports;

use Illuminate\Http\Request;
use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductsExport implements FromCollection, WithHeadings
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
                'categories.Category as category_name', // ولا category إذا عندك صغيرة
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
}