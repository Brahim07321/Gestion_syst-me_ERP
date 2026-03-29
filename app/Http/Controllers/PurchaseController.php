<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Category;
use App\Models\StockMovement;

class PurchaseController extends Controller
{
    public function store(Request $request)
{
    $request->validate([
        'supplier_id' => 'required|exists:suppliers,id',
        'purchase_date' => 'required|date',
        'items' => 'required|array|min:1',
    ]);

    $total = 0;

    foreach ($request->items as $item) {
        $qty = (int) ($item['quantity'] ?? 0);
        $price = (float) ($item['buy_price'] ?? 0);
        $total += $qty * $price;
    }

    $purchase = Purchase::create([
        'purchase_code' => 'ACH-' . time(),
        'supplier_id' => $request->supplier_id,
        'purchase_date' => $request->purchase_date,
        'total' => $total,
        'status' => 'reçu',
    ]);

    foreach ($request->items as $item) {
        $product = Product::findOrFail($item['product_id']);
        $qty = (int) $item['quantity'];
        $price = (float) $item['buy_price'];

        $purchase->items()->create([
            'product_id' => $product->id,
            'quantity' => $qty,
            'buy_price' => $price,
            'line_total' => $qty * $price,
        ]);

        // ✅ stock يزيد
        $product->Quantite += $qty;
        $product->prace_bay = $price; // اختياري: آخر prix achat
        $product->save();

        $product->Quantite += $qty;
$product->save();

StockMovement::create([
    'product_id' => $product->id,
    'type' => 'entree',
    'quantity' => $qty,
    'source' => 'achat',
    'reference' => $purchase->purchase_code,
]);
    }

    return redirect()->route('purchases.index')->with('success', 'Achat enregistré avec succès.');
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

    return view('purchases.index', compact('purchases'));
}
public function show($id)
{
    $purchase = Purchase::with(['supplier', 'items.product'])->findOrFail($id);

    return view('purchases.show', compact('purchase'));
}

}
