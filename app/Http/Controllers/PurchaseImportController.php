<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\StockMovement;

class PurchaseImportController extends Controller
{
    private function normalize($value): string
    {
        $value = (string) $value;
        $value = trim($value);
        $value = mb_strtolower($value, 'UTF-8');

        $replace = [
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'à' => 'a', 'â' => 'a', 'ä' => 'a',
            'ù' => 'u', 'û' => 'u', 'ü' => 'u',
            'î' => 'i', 'ï' => 'i',
            'ô' => 'o', 'ö' => 'o',
            'ç' => 'c',
        ];

        $value = strtr($value, $replace);
        $value = preg_replace('/\s+/', ' ', $value);

        return $value;
    }

    public function preview(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_date' => 'required|date',
            'status' => 'required|in:reçu,en attente,annulé',
        ]);

        $rows = Excel::toArray([], $request->file('file'))[0] ?? [];

        if (empty($rows)) {
            return back()->with('error', 'Le fichier est vide.');
        }

        // STEP 1: detect header row
        $headerIndex = null;

        foreach ($rows as $index => $row) {
            $score = 0;
        
            foreach ($row as $cell) {
                $col = $this->normalize($cell);
        
                if ($col === '') {
                    continue;
                }
        
                if (
                    str_contains($col, 'reference') ||
                    $col === 'ref'
                ) {
                    $score++;
                }
        
                if (
                    str_contains($col, 'designation') ||
                    str_contains($col, 'produit') ||
                    str_contains($col, 'article')
                ) {
                    $score++;
                }
        
                if (
                    str_contains($col, 'qte') ||
                    str_contains($col, 'qtes') ||
                    str_contains($col, 'quantite')
                ) {
                    $score++;
                }
        
                if (
                    str_contains($col, 'prix') ||
                    str_contains($col, 'price') ||
                    str_contains($col, 'pu')
                ) {
                    $score++;
                }
            }
        
            // خاص السطر يكون فيه على الأقل جوج/3 headers معروفين
            if ($score >= 2) {
                $headerIndex = $index;
                break;
            }
        }

        if ($headerIndex === null) {
            return back()->with('error', 'Table non détectée dans le fichier.');
        }

        $header = $rows[$headerIndex];
        $dataRows = array_slice($rows, $headerIndex + 1);

        // STEP 2: mapping
        $mapping = [];

        foreach ($header as $i => $col) {
            $col = $this->normalize($col);

            if (
                str_contains($col, 'reference') ||
                $col === 'ref'
            ) {
                $mapping['reference'] = $i;
            }

            if (
                str_contains($col, 'designation') ||
                str_contains($col, 'produit') ||
                str_contains($col, 'article')
            ) {
                $mapping['designation'] = $i;
            }

            if (
                str_contains($col, 'qte') ||
                str_contains($col, 'qtes') ||
                str_contains($col, 'quantite')
            ) {
                $mapping['quantity'] = $i;
            }

            if (
                str_contains($col, 'prix') ||
                str_contains($col, 'price') ||
                str_contains($col, 'pu')
            ) {
                $mapping['price'] = $i;
            }
        }

        if (!isset($mapping['designation'], $mapping['quantity'], $mapping['price'])) {
            return back()->with(
                'error',
                'Colonnes non reconnues. Colonnes détectées: ' . implode(' | ', array_map(fn($h) => (string) $h, $header))
            );
        }

        $preview = [];

        // STEP 3: parse rows
        foreach ($dataRows as $row) {
            if (count(array_filter($row, fn($v) => $v !== null && $v !== '')) === 0) {
                continue;
            }

            $reference = trim((string) ($row[$mapping['reference']] ?? ''));
            $designation = trim((string) ($row[$mapping['designation']] ?? ''));
            $quantity = (float) ($row[$mapping['quantity']] ?? 0);
            $price = (float) ($row[$mapping['price']] ?? 0);

            $designationNormalized = $this->normalize($designation);

            // وقف إلا وصلنا لشي سطر total
            if (
                $designation === '' ||
                str_contains($designationNormalized, 'total')
            ) {
                continue;
            }

            $product = null;

            if ($reference !== '') {
                $product = Product::where('Referonce', $reference)->first();
            }

            if (!$product && $designation !== '') {
                $product = Product::where('Designation', $designation)->first();
            }

            $oldPrice = $product->prace_bay ?? 0;

            if ($price > $oldPrice) {
                $status = 'Prix augmenté';
            } elseif ($price < $oldPrice) {
                $status = 'Prix diminué';
            } else {
                $status = 'Prix identique';
            }

            $preview[] = [
                'reference' => $reference,
                'designation' => $designation,
                'quantity' => $quantity,
                'price' => $price,
                'old_price' => $oldPrice,
                'status' => $status,
                'product_id' => $product->id ?? null,
            ];
        }

        if (empty($preview)) {
            return back()->with('error', 'Aucune ligne valide trouvée dans le fichier.');
        }

        session([
            'import_purchase' => $preview,
            'import_purchase_meta' => [
                'supplier_id' => $request->supplier_id,
                'purchase_date' => $request->purchase_date,
                'status' => $request->status,
            ]
        ]);
        return view('purchases.import_preview', compact('preview'));
    }

    public function confirm(Request $request)
{
    $data = session('import_purchase');
    $meta = session('import_purchase_meta');
    $selectedItems = $request->selected_items ?? [];

    if (!$data || !$meta) {
        return redirect()->back()->with('error', 'Aucune donnée à importer.');
    }

    if (empty($selectedItems)) {
        return redirect()->back()->with('error', 'Veuillez sélectionner au moins un produit.');
    }

    $purchase = \App\Models\Purchase::create([
        'supplier_id' => $meta['supplier_id'],
        'purchase_code' => 'ACH-' . time(),
        'purchase_date' => $meta['purchase_date'],
        'status' => $meta['status'],
        'total' => 0
    ]);

    $total = 0;

    foreach ($selectedItems as $index) {
        if (!isset($data[$index])) {
            continue;
        }

        $item = $data[$index];

        if (!$item['product_id']) {
            continue;
        }

        $lineTotal = $item['quantity'] * $item['price'];

        $purchase->items()->create([
            'product_id' => $item['product_id'],
            'quantity' => $item['quantity'],
            'buy_price' => $item['price'],
            'line_total' => $lineTotal
        ]);

        if ($meta['status'] === 'reçu') {
            $product = \App\Models\Product::find($item['product_id']);

            if ($product) {
                $product->increment('Quantite', $item['quantity']);

                \App\Models\StockMovement::create([
                    'product_id' => $product->id,
                    'type' => 'entree',
                    'quantity' => $item['quantity'],
                    'source' => 'import achat',
                    'reference' => $purchase->purchase_code,
                ]);
            }
        }

        $total += $lineTotal;
    }

    $purchase->update(['total' => $total]);

    session()->forget(['import_purchase', 'import_purchase_meta']);

return redirect()->route('purchases.show', $purchase->id)
    ->with('success', 'Achat importé avec succès ✅');}
}