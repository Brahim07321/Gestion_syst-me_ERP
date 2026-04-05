<?php

namespace App\Http\Controllers;

use App\Models\Facture;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\product;
use App\Models\Customer;
use App\Models\Purchase;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use App\Models\StockMovement;
use App\Models\Expense;
use App\Models\FactureItem;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FacturesExport;
use Barryvdh\DomPDF\Facade\Pdf;


class FactureController extends Controller
{
   
    public function index(Request $request)
    {
        $search = strtolower($request->search ?? '');
        $status = $request->status ?? '';
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
    
        $factures = Facture::query()
        ->when($search, function ($query) use ($search) {
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
        ->latest()
        ->paginate(15)
        ->appends($request->all());

    
        // stats
        $totalFactures = Facture::where('status', '!=', 'annulée')->count();
        $totalAmount = Facture::where('status', '!=', 'annulée')->sum('total');
        $totalPaid = Facture::where('status', '!=', 'annulée')->sum('paid_amount');
        $totalRemaining = Facture::where('status', '!=', 'annulée')->sum('remaining_amount');
            
        return view('archife', compact(
            'factures',
            'search',
            'status',
            'dateFrom',
            'dateTo',
            'totalFactures',
            'totalAmount',
            'totalPaid',
            'totalRemaining'
        ));
    }
    public function store(Request $request)
{
    $request->validate([
        'invoice_number' => 'nullable|string|max:255|unique:factures,code_facture',
        'invoice_date' => 'required|date',
        'customer_search' => 'required|string|max:255',
        'items' => 'required|array|min:1',
    ]);

    DB::beginTransaction();

    try {

        $invoiceNumber = trim($request->invoice_number ?? '');

        if ($invoiceNumber === '') {
            $invoiceNumber = 'INV-' . now()->format('Ymd-His');
        }
        $total = 0;

        foreach ($request->items as $item) {
            $price = (float) ($item['price'] ?? 0);
            $quantity = (int) ($item['quantity'] ?? 0);
            $total += $price * $quantity;
        }

        $paid = (float) ($request->paid_amount ?? 0);

        if ($paid == 0) {
            $status = 'non payée';
        } elseif ($paid < $total) {
            $status = 'partiellement payée';
        } else {
            $status = 'payée';
        }
        
        $facture = Facture::create([
            'code_facture' => $invoiceNumber,
            'client_name' => $request->customer_search,
            'total' => $total,
            'date_facture' => $request->invoice_date,
            'status' => $status,
            'paid_amount' => 0,
            'remaining_amount' => $total,
        ]);
        
        if ($paid > 0) {
            Payment::create([
                'facture_id' => $facture->id,
                'amount' => $paid,
                'payment_date' => $request->invoice_date,
                'note' => 'Paiement initial',
            ]);
        }
        
        $newTotalPaid = $facture->payments()->sum('amount');
        $newRemaining = $facture->total - $newTotalPaid;
        
        if ($newRemaining <= 0) {
            $facture->status = 'payée';
        } elseif ($newTotalPaid > 0) {
            $facture->status = 'partiellement payée';
        } else {
            $facture->status = 'non payée';
        }
        
        $facture->paid_amount = $newTotalPaid;
        $facture->remaining_amount = $newRemaining;
        $facture->save();

        foreach ($request->items as $item) {
            $price = (float) ($item['price'] ?? 0);
            $quantity = (int) ($item['quantity'] ?? 0);

            if (
                empty($item['referonce']) &&
                empty($item['designation']) &&
                $price <= 0 &&
                $quantity <= 0
            ) {
                continue;
            }

            $product = Product::where('Referonce', $item['referonce'])->first();

            if (!$product) {
                throw new \Exception('Produit introuvable');
            }

            if ($product->Quantite < $quantity) {
                throw new \Exception('Stock insuffisant pour: ' . $product->Designation);
            }

            // إنشاء item
            $facture->items()->create([
                'referonce' => $item['referonce'],
                'designation' => $item['designation'],
                'price' => $price,
                'quantity' => $quantity,
                'line_total' => $price * $quantity,
            ]);

            // 🔥 نقص stock
            $product->decrement('Quantite', $quantity);


StockMovement::create([
    'product_id' => $product->id,
    'type' => 'sortie',
    'quantity' => $quantity,
    'source' => 'facture',
    'reference' => $invoiceNumber,
]);
        }

        DB::commit();

        return redirect()->route('factures.index')
            ->with('success', 'Facture enregistrée avec succès.');

    } catch (\Exception $e) {

        DB::rollBack();

        return redirect()->back()->with('error', $e->getMessage());
    }
}


    public function show($id)
{
    $facture = Facture::withTrashed()
    ->with(['items', 'payments'])
    ->findOrFail($id);
        return view('facture_show', compact('facture'));
}
public function dashboard()
{
    $facturesCount = Facture::count();
    $CategoryCount = Category::count();
    $productesCount = Product::count();
    $CustomeresCount = Customer::count();
    $PurchaseCount = Purchase::count();
    $lowStockCount = Product::where('Quantite', '<', 5)->count();
    $productsChart = Product::select('Designation', 'Quantite')->get();

    $categoryStats = Category::leftJoin('products', 'categories.id', '=', 'products.Category_ID')
        ->select(
            'categories.category as category_name',
            DB::raw('COUNT(products.id) as total_products'),
            DB::raw('SUM(CASE WHEN products.Quantite < 5 THEN 1 ELSE 0 END) as low_stock_products')
        )
        ->groupBy('categories.id', 'categories.category')
        ->get();

        //contabilte dashbord
        $totalSales = Facture::whereNull('deleted_at')
        ->where('status', '!=', 'annulée')
        ->sum('total');
    
    $totalPaid = Facture::whereNull('deleted_at')
        ->where('status', '!=', 'annulée')
        ->sum('paid_amount');
    
    $totalRemaining = Facture::whereNull('deleted_at')
        ->where('status', '!=', 'annulée')
        ->sum('remaining_amount');
        
        $totalPurchases = Purchase::sum('total');
        $totalExpenses = Expense::sum('amount');

        $netProfit = $totalSales - $totalPurchases - $totalExpenses;

        $topProducts = FactureItem::join('factures', 'factures.id', '=', 'facture_items.facture_id')
        ->whereNull('factures.deleted_at')
        ->where('factures.status', '!=', 'annulée')
        ->select(
            'facture_items.referonce',
            'facture_items.designation',
            DB::raw('SUM(facture_items.quantity) as total_sold')
        )
        ->groupBy('facture_items.referonce', 'facture_items.designation')
        ->orderByDesc('total_sold')
        ->limit(5)
        ->get();

        $topProfitProducts = FactureItem::join('factures', 'factures.id', '=', 'facture_items.facture_id')
        ->join('products', 'products.Referonce', '=', 'facture_items.referonce')
        ->where('factures.status', '!=', 'annulée')
        ->select(
            'facture_items.designation',
            DB::raw('SUM((facture_items.price - products.prace_bay) * facture_items.quantity) as total_profit')
        )
        ->groupBy('facture_items.designation')
        ->orderByDesc('total_profit')
        ->limit(5)
        ->get();

        
        
            return view('index', compact(
        'facturesCount',
        'CategoryCount',
        'productesCount',
        'CustomeresCount',
        'PurchaseCount',
        'lowStockCount',
        'categoryStats',
        'productsChart',
        'totalSales',
        'totalPaid',
        'totalRemaining',
        'totalPurchases',
        'totalExpenses',
        'netProfit',
        'topProducts',
        'topProfitProducts'

    ));
}


public function report(Request $request)
{
    $month = $request->month ?? date('Y-m');

    // 🔹 VENTES
$totalSales = Facture::whereNull('deleted_at')
    ->where('status', '!=', 'annulée')
    ->whereMonth('date_facture', date('m', strtotime($month)))
    ->whereYear('date_facture', date('Y', strtotime($month)))
    ->sum('total');

$totalPaid = Facture::whereNull('deleted_at')
    ->where('status', '!=', 'annulée')
    ->whereMonth('date_facture', date('m', strtotime($month)))
    ->whereYear('date_facture', date('Y', strtotime($month)))
    ->sum('paid_amount');

$totalRemaining = Facture::whereNull('deleted_at')
    ->where('status', '!=', 'annulée')
    ->whereMonth('date_facture', date('m', strtotime($month)))
    ->whereYear('date_facture', date('Y', strtotime($month)))
    ->sum('remaining_amount');
    // 🔹 ACHATS
    $totalPurchases = Purchase::whereMonth('purchase_date', date('m', strtotime($month)))
        ->whereYear('purchase_date', date('Y', strtotime($month)))
        ->sum('total');

    // 🔹 DEPENSES
    $totalExpenses = Expense::whereMonth('expense_date', date('m', strtotime($month)))
        ->whereYear('expense_date', date('Y', strtotime($month)))
        ->sum('amount');

    // 🔹 PROFIT
    $netProfit = $totalSales - $totalPurchases - $totalExpenses;

    return view('reports.index', compact(
        'month',
        'totalSales',
        'totalPaid',
        'totalRemaining',
        'totalPurchases',
        'totalExpenses',
        'netProfit'
    ));
}

public function exportExcel(Request $request)
{
    return Excel::download(new FacturesExport($request), 'archive_factures.xlsx');
}

public function exportPdf(Request $request)
{
    $search = strtolower($request->search ?? '');
    $status = $request->status ?? '';
    $dateFrom = $request->date_from ?? '';
    $dateTo = $request->date_to ?? '';

    $factures = Facture::when($search, function ($query) use ($search) {
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
        ->latest()
        ->get();

    $pdf = Pdf::loadView('factures.archive_pdf', compact(
        'factures',
        'status',
        'dateFrom',
        'dateTo',
        'search'
    ))->setPaper('A4', 'landscape');

    return $pdf->download('archive_factures.pdf');
}

///annull factura 

public function cancel($id)

{

    if (auth()->user()->role !== 'admin') {
        return back()->with('error', 'Accès refusé.');
    }
    DB::beginTransaction();

    try {
        $facture = Facture::with('items')->findOrFail($id);

        // إلا كانت ديجا annulée
        if ($facture->status === 'annulée') {
            return redirect()->back()->with('error', 'Cette facture est déjà annulée.');
        }

        // رجّع stock
        foreach ($facture->items as $item) {
            $product = Product::where('Referonce', $item->referonce)->first();

            if ($product) {
                $product->increment('Quantite', $item->quantity);

                StockMovement::create([
                    'product_id' => $product->id,
                    'type' => 'entree',
                    'quantity' => $item->quantity,
                    'source' => 'annulation facture',
                    'reference' => $facture->code_facture,
                ]);
            }
        }

        // حدّث الفاتورة
        $facture->status = 'annulée';
        $facture->paid_amount = 0;
        $facture->remaining_amount = 0;
        $facture->save();

        DB::commit();

        return redirect()->back()->with('success', 'Facture annulée avec succès.');
    } catch (\Exception $e) {
        DB::rollBack();

        return redirect()->back()->with('error', $e->getMessage());
    }
}

public function destroy($id)
{
    if (auth()->user()->role !== 'admin') {
        return back()->with('error', 'Accès refusé.');
    }

    $facture = Facture::findOrFail($id);
    $facture->delete();

    return back()->with('success', 'Facture supprimée avec succès.');
}
}