<?php

namespace App\Http\Controllers;

use App\Models\Facture;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\product;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class FactureController extends Controller
{
   
    public function index(Request $request)
    {
        $search = strtolower($request->search ?? '');
        $status = $request->status ?? '';
    
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
            ->latest()
            ->paginate(10)
            ->appends([
                'search' => $request->search,
                'status' => $status,
            ]);
    
        return view('archife', compact('factures', 'search', 'status'));
    }

    public function store(Request $request)
{
    $request->validate([
        'invoice_number' => 'required|string|max:255',
        'invoice_date' => 'required|date',
        'customer_search' => 'required|string|max:255',
        'status' => 'required|string',
        'items' => 'required|array|min:1',
    ]);

    DB::beginTransaction();

    try {

        $total = 0;

        foreach ($request->items as $item) {
            $price = (float) ($item['price'] ?? 0);
            $quantity = (int) ($item['quantity'] ?? 0);
            $total += $price * $quantity;
        }

        $facture = Facture::create([
            'code_facture' => $request->invoice_number,
            'client_name' => $request->customer_search,
            'total' => $total,
            'date_facture' => $request->invoice_date,
            'status' => $request->status,
        ]);

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
    $facture = Facture::with('items')->findOrFail($id);
    return view('facture_show', compact('facture'));
}
public function dashboard()
{
    $facturesCount = Facture::count();
    $CategoryCount = Category::count();
    $productesCount = Product::count();
    $CustomeresCount = Customer::count();
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

    return view('index', compact(
        'facturesCount',
        'CategoryCount',
        'productesCount',
        'CustomeresCount',
        'lowStockCount',
        'categoryStats',
        'productsChart' 

    ));
}
}