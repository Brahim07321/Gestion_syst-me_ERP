<?php

namespace App\Imports;

use App\Models\Product;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductsImport implements ToModel, WithHeadingRow{
  
    public function model(array $row)
    {
        // 🔥 normalize
        $referonce = $row['referonce'] ?? null;
        $quantite = (int) ($row['quantite'] ?? 0);
    
        if (!$referonce) {
            return null;
        }
    
        $product = Product::where('Referonce', $referonce)->first();
    
        if ($product) {
    
            // ✅ زيد stock (ماشي replace)
            $product->Quantite += $quantite;
    
            // update باقي المعلومات
            $product->Category_ID = $row['category_id'] ?? $product->Category_ID;
            $product->code = $row['code'] ?? $product->code;
            $product->Designation = $row['designation'] ?? $product->Designation;
            $product->prace_bay = $row['prace_bay'] ?? $product->prace_bay;
            $product->prace_sell = $row['prace_sell'] ?? $product->prace_sell;
    
            $product->save();
    
            return null;
        }
    
        // create جديد
        return new Product([
            'Category_ID' => $row['category_id'],
            'code' => $row['code'],
            'Referonce' => $referonce,
            'Designation' => $row['designation'],
            'prace_bay' => $row['prace_bay'],
            'prace_sell' => $row['prace_sell'],
            'Quantite' => $quantite,
        ]);
    }
}
