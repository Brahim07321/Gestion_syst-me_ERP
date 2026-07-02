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
use App\Models\CompanySetting;
use Illuminate\Validation\Rule;

class PurchaseController extends Controller
{
    use SoftDeletes;

    public function store(Request $request)
    {
        $companyId = auth()->user()->company_id;
    
        $request->validate([
            'supplier_id' => [
                'required',
                Rule::exists('suppliers', 'id')->where(fn ($q) => $q->where('company_id', $companyId)),
            ],
            'purchase_date' => 'required|date',
            'status' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => [
                'required',
                Rule::exists('products', 'id')->where(fn ($q) => $q->where('company_id', $companyId)),
            ],
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.buy_price' => 'required|numeric|min:0',
        ]);
    
        $total = 0;
    
        foreach ($request->items as $item) {
            $price = (float) ($item['buy_price'] ?? 0);
            $quantity = (int) ($item['quantity'] ?? 0);
            $total += $price * $quantity;
        }
    
        $purchase = Purchase::create([
            'company_id' => $companyId,
            'purchase_code' => 'ACH-' . now()->format('Ymd-His'),
            'supplier_id' => $request->supplier_id,
            'purchase_date' => $request->purchase_date,
            'status' => $request->status,
            'total' => $total,
        ]);
    
        foreach ($request->items as $item) {
            $price = (float) ($item['buy_price'] ?? 0);
            $quantity = (int) ($item['quantity'] ?? 0);
    
            $purchase->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $quantity,
                'buy_price' => $price,
                'line_total' => $price * $quantity,
            ]);
    
            if ($request->status === 'reçu') {
                $product = Product::where('company_id', $companyId)->find($item['product_id']);
    
                if ($product) {
                    $product->increment('Quantite', $quantity);
                    $product->prace_bay = $price;
                    $product->save();
    
                    StockMovement::create([
                        'company_id' => $companyId,
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
        $companyId = auth()->user()->company_id;
    
        $suppliers = Supplier::where('company_id', $companyId)->orderBy('name')->get();
        $products = Product::where('company_id', $companyId)->get();
        $categories = Category::where('company_id', $companyId)->get();
    
        return view('purchases.create', compact('suppliers', 'products', 'categories'));
    }

    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $search = strtolower($request->search ?? '');
        $status = $request->status ?? '';
    
        $suppliers = Supplier::where('company_id', $companyId)
            ->orderBy('name')
            ->get();
    
        $purchases = Purchase::with('supplier')
            ->where('company_id', $companyId)
            ->when($search, function ($query) use ($search, $companyId) {
                $query->where(function ($q) use ($search, $companyId) {
                    $q->whereRaw('LOWER(purchase_code) LIKE ?', ["%{$search}%"])
                      ->orWhereHas('supplier', function ($supplierQuery) use ($search, $companyId) {
                          $supplierQuery->where('company_id', $companyId)
                              ->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
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
        $company = CompanySetting::first();
    
        $purchase = Purchase::withTrashed()
            ->with(['items.product', 'supplier'])
            ->where('company_id', auth()->user()->company_id)
            ->findOrFail($id);
    
        return view('purchases.show', compact('purchase', 'company'));
    }




    public function changeStatus($id)
    {
        $companyId = auth()->user()->company_id;
    
        $purchase = Purchase::with('items')
            ->where('company_id', $companyId)
            ->findOrFail($id);
    
        if ($purchase->status === 'reçu') {
            return back()->with('error', 'Déjà reçu');
        }
    
        if ($purchase->status === 'annulé') {
            return back()->with('error', 'Cet achat est annulé');
        }
    
        foreach ($purchase->items as $item) {
            $product = Product::where('company_id', $companyId)->find($item->product_id);
    
            if ($product) {
                $product->increment('Quantite', $item->quantity);
    
                StockMovement::create([
                    'company_id' => $companyId,
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
        $purchase = Purchase::where('company_id', auth()->user()->company_id)
            ->findOrFail($id);
    
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
    
        $companyId = auth()->user()->company_id;
    
        $purchase = Purchase::with('items')
            ->where('company_id', $companyId)
            ->findOrFail($id);
    
        if ($purchase->status === 'reçu') {
            foreach ($purchase->items as $item) {
                $product = Product::where('company_id', $companyId)->find($item->product_id);
    
                if ($product) {
                    $product->decrement('Quantite', $item->quantity);
    
                    StockMovement::create([
                        'company_id' => $companyId,
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
    $companyId = auth()->user()->company_id;
    $search = strtolower($request->search ?? '');
    $status = $request->status ?? '';

    $purchases = Purchase::with('supplier')
        ->where('company_id', $companyId)
        ->when($search, function ($query) use ($search, $companyId) {
            $query->where(function ($q) use ($search, $companyId) {
                $q->whereRaw('LOWER(purchase_code) LIKE ?', ["%{$search}%"])
                  ->orWhereHas('supplier', function ($supplierQuery) use ($search, $companyId) {
                      $supplierQuery->where('company_id', $companyId)
                          ->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
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
    $companyId = auth()->user()->company_id;

    $purchase = Purchase::with('items')
        ->where('company_id', $companyId)
        ->findOrFail($id);

    if ($purchase->status === 'reçu') {
        return redirect()->back()->with('error', 'Cet achat est déjà marqué comme reçu.');
    }

    if ($purchase->status === 'annulé') {
        return redirect()->back()->with('error', 'Impossible de recevoir un achat annulé.');
    }

    foreach ($purchase->items as $item) {
        $product = Product::where('company_id', $companyId)->find($item->product_id);

        if ($product) {
            $product->increment('Quantite', $item->quantity);
            $product->prace_bay = $item->buy_price;
            $product->save();

            StockMovement::create([
                'company_id' => $companyId,
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