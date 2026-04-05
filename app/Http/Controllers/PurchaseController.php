<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Category;
use App\Models\StockMovement;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PurchasesExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseController extends Controller
{
    use SoftDeletes;

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required',
            'purchase_date' => 'required|date',
            'status' => 'required|string',
            'items' => 'required|array|min:1',
        ]);

        $total = 0;

        foreach ($request->items as $item) {
            $price = (float) ($item['buy_price'] ?? 0);
            $quantity = (int) ($item['quantity'] ?? 0);
            $total += $price * $quantity;
        }

        $purchase = Purchase::create([
            'purchase_code' => 'ACH-' . date('Ymd-His'),
            'supplier_id' => $request->supplier_id,
            'purchase_date' => $request->purchase_date,
            'status' => $request->status,
            'total' => $total,
        ]);

        foreach ($request->items as $item) {
            $price = (float) ($item['buy_price'] ?? 0);
            $quantity = (int) ($item['quantity'] ?? 0);

            if (empty($item['product_id'])) {
                continue;
            }

            $purchase->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $quantity,
                'buy_price' => $price,
                'line_total' => $price * $quantity,
            ]);

            // إلا كان reçu → زيد stock مباشرة
            if ($request->status === 'reçu') {
                $product = Product::find($item['product_id']);

                if ($product) {
                    $product->increment('Quantite', $quantity);

                    StockMovement::create([
                        'product_id' => $product->id,
                        'type' => 'entree',
                        'quantity' => $quantity,
                        'source' => 'achat',
                        'reference' => $purchase->purchase_code,
                    ]);
                }
            }
        }

        return redirect()->route('purchases.create')
            ->with('success', 'Achat ajouté avec succès');
    }

    public function create()
    {
        $suppliers = Supplier::all();
        $products = Product::all();
        $categories = Category::all();

        return view('purchases.create', compact('suppliers', 'products', 'categories'));
    }

    public function index(Request $request)
    {
        $search = strtolower($request->search ?? '');
        $status = $request->status ?? '';
        $suppliers = Supplier::orderBy('name')->get();


        $purchases = Purchase::with('supplier')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->whereRaw('LOWER(purchase_code) LIKE ?', ["%{$search}%"])
                      ->orWhereHas('supplier', function ($supplierQuery) use ($search) {
                          $supplierQuery->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
                      });
                });
            })
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(15);

        return view('purchases.index', compact('purchases', 'suppliers'));
    }

    public function show($id)
    {
   $purchase = Purchase::withTrashed()
        ->with(['items.product', 'supplier'])
        ->findOrFail($id);
        
        return view('purchases.show', compact('purchase'));
    }




public function changeStatus($id)
{
    $purchase = Purchase::with('items.product')->findOrFail($id);

    if ($purchase->status === 'reçu') {
        return back()->with('error', 'Déjà reçu');
    }

    if ($purchase->status === 'annulé') {
        return back()->with('error', 'Cet achat est annulé');
    }

    foreach ($purchase->items as $item) {
        $product = $item->product;

        if ($product) {
            $product->increment('Quantite', $item->quantity);

            StockMovement::create([
                'product_id' => $product->id,
                'type' => 'entree',
                'quantity' => $item->quantity,
                'source' => 'achat',
                'reference' => $purchase->purchase_code,
            ]);
        }
    }

    $purchase->status = 'reçu';
    $purchase->save();

    return back()->with('success', 'Achat marqué comme reçu');

}    
//function anulle

public function cancel($id)
{
    $purchase = Purchase::findOrFail($id);

    if ($purchase->status === 'reçu') {
        return back()->with('error', 'Impossible d’annuler un achat déjà reçu.');
    }

    if ($purchase->status === 'annulé') {
        return back()->with('error', 'Cet achat est déjà annulé.');
    }

    $purchase->status = 'annulé';
    $purchase->save();

    return back()->with('success', 'Achat annulé avec succès.');
}


 public function destroy($id)

    {
        if (auth()->user()->role !== 'admin') {
            return redirect()->back()->with('error', 'Accès refusé. Seul l’administrateur peut supprimer un achat.');
        }
        $purchase = Purchase::with('items.product')->findOrFail($id);

        // إلا كان reçu نقص stock
        if ($purchase->status === 'reçu') {
            foreach ($purchase->items as $item) {
                $product = $item->product;

                if ($product) {
                    $product->decrement('Quantite', $item->quantity);

                    StockMovement::create([
                        'product_id' => $product->id,
                        'type' => 'sortie',
                        'quantity' => $item->quantity,
                        'source' => 'suppression achat',
                        'reference' => $purchase->purchase_code,
                    ]);
                }
            }
        }

        $purchase->delete();

        return back()->with('success', 'Achat supprimé');
    }

    public function exportExcel(Request $request)
{
    return Excel::download(new PurchasesExport($request), 'liste_achats.xlsx');
}

public function exportPdf(Request $request)
{
    $search = strtolower($request->search ?? '');
    $status = $request->status ?? '';

    $purchases = Purchase::with('supplier')
        ->when($search, function ($query) use ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(purchase_code) LIKE ?', ["%{$search}%"])
                  ->orWhereHas('supplier', function ($supplierQuery) use ($search) {
                      $supplierQuery->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
                  });
            });
        })
        ->when($status, function ($query) use ($status) {
            $query->where('status', $status);
        })
        ->latest()
        ->get();

    $pdf = Pdf::loadView('purchases.pdf', compact('purchases', 'search', 'status'))
        ->setPaper('A4', 'landscape');

    return $pdf->download('liste_achats.pdf');
}

public function markAsReceived($id)
{
    $purchase = Purchase::with('items')->findOrFail($id);

    // إلا كانت ديجا reçu
    if ($purchase->status === 'reçu') {
        return redirect()->back()->with('error', 'Cet achat est déjà marqué comme reçu.');
    }

    // إلا كانت annuléة
    if ($purchase->status === 'annulé') {
        return redirect()->back()->with('error', 'Impossible de recevoir un achat annulé.');
    }

    foreach ($purchase->items as $item) {
        $product = Product::find($item->product_id);

        if ($product) {
            $product->increment('Quantite', $item->quantity);

            StockMovement::create([
                'product_id' => $product->id,
                'type' => 'entree',
                'quantity' => $item->quantity,
                'source' => 'achat',
                'reference' => $purchase->purchase_code,
            ]);
        }
    }

    $purchase->status = 'reçu';
    $purchase->save();

    return redirect()->back()->with('success', 'Achat marqué comme reçu avec succès.');
}
}