<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Product;
use App\Exports\ProductsExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\StockMovement;
use App\Models\Facture;

class StockControllerController extends Controller
{
    public function indexstock(Request $request)
    {
        $search = strtolower($request->search ?? '');
        $categoryId = $request->category_id ?? '';
        $lowStock = $request->low_stock ?? '';


        $lowStockProducts = Product::where('Quantite', '<', 5)->get();

        $categories = Category::all();

        $products = Product::leftJoin('categories', 'products.Category_ID', '=', 'categories.id')
        ->select('products.*', 'categories.category as category_name')
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
        ->latest('products.id')
        ->paginate(10)
        ->appends([
            'search' => $request->search,
            'category_id' => $categoryId,
            'low_stock' => $lowStock,
        ]);

        return view('stock', compact('categories', 'products', 'search', 'categoryId', 'lowStock', 'lowStockProducts'));
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $categories = Category::all();
        $products = Product::with('category')->latest()->paginate(10);

        return view('stock.edit', compact('product', 'categories', 'products'));
    }

    public function update_stock(Request $request, $id)
    {
        $request->validate([
            'Category_ID' => 'required',
            'code' => 'required',
            'Referonce' => 'required',
            'Designation' => 'required',
            'prace_bay' => 'required|numeric',
            'prace_sell' => 'required|numeric',
            'Quantite' => 'required|integer',
        ]);

        $product = Product::findOrFail($id);

        $product->Category_ID = $request->Category_ID;
        $product->code = $request->code;
        $product->Referonce = $request->Referonce;
        $product->Designation = $request->Designation;
        $product->prace_bay = $request->prace_bay;
        $product->prace_sell = $request->prace_sell;
        $product->Quantite = $request->Quantite;
        $product->save();

        return redirect()->route('stock.index')->with('success', 'Produit modifié avec succès');
    }



public function export(Request $request)
{
    return Excel::download(new ProductsExport($request), 'stock.xlsx');
}


public function movements()
{

    $movements = StockMovement::with(['product', 'facture', 'purchase'])
    ->latest()
    ->paginate(20);
    return view('stock_movements', compact('movements'));
}


}