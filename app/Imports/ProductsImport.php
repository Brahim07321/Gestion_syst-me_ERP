<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductsImport implements ToModel, WithHeadingRow
{
    public $skipped = 0; // 🔥 compteur

    public function model(array $row)
    {
        $referonce = trim($row['referonce'] ?? '');

        if ($referonce === '') {
            return null;
        }

        // ✅ إلا كان موجود
        if (Product::where('Referonce', $referonce)->exists()) {
            $this->skipped++; // 🔥 زيد فالكاونتر
            return null;
        }

        return new Product([
            'Category_ID' => $row['category_id'] ?? null,
            'code' => $row['code'] ?? null,
            'Referonce' => $referonce,
            'Designation' => $row['designation'] ?? null,
            'prace_bay' => $row['prace_bay'] ?? 0,
            'prace_sell' => $row['prace_sell'] ?? 0,
            'Quantite' => 0,
        ]);
    }
}