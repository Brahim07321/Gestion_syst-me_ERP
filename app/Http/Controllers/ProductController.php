<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\customer;
use App\Imports\ProductsImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TemplateExport;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\CompanySetting;

// You need this for category model
class ProductController extends Controller
{


    public function product()
    {
        return view(view: 'product'); // Ensure blade file is named 'customer.blade.php'
    }


    public function FormCategory()
    {
        // Fetch all category
        $Categorys = category::all();

        // Pass the data to the view
        return view('product', ['Categorys' => $Categorys]);
    }


    public function createproduct(Request $request)
    {
        $formFields = $request->validate([
            'Category_ID' => 'required',
            'code' => 'required',
            'Referonce' => 'required|unique:products,Referonce',
            'Designation' => 'required',
            'prace_bay' => 'required|numeric',
            'prace_sell' => 'required|numeric',
            'Quantite' => 'required|integer',
        ], [
            'Referonce.unique' => '⚠️Référence déjà existante.',
            'Referonce.required' => '⚠️ خاصك تدخل référence.',
        ]);
    
        Product::create($formFields);
    
        return redirect()->back()->with('success', 'Product created successfully');
    }

    
   

    public function index(Request $request)
{
    $search = $request->search;
    $categories = Category::all(); // جلب كل الفئات
    $products = Product::when($search, function ($query, $search) {
        $query->where('Designation', 'like', '%' . $search . '%')
              ->orWhere('Referonce', 'like', '%' . $search . '%')
              ->orWhere('code', 'like', '%' . $search . '%');
    })->paginate(25);

    return view('product', compact('products', 'search', 'categories'));
}

    public function getProductByReference(Request $request)
    {
        $searchQuery = $request->input('query');  // Get the search query from the request

        // Query the products table for products matching the reference or designation
        $products = Product::where('Referonce', 'like', "%{$searchQuery}%")
            ->orWhere('Designation', 'like', "%{$searchQuery}%")
            ->limit(10)
            ->get();

        // Return the products as JSON to be used by JavaScript
        return response()->json($products);
    }

    public function showInvoice()
    {
        // Fetch products and customers
        $products = Product::all();
        $customers = Customer::all(['id', 'name', 'address']); // Récupérer les clients avec leurs IDs et noms
        $company = CompanySetting::first();

        return view('facture', compact('products', 'customers', 'company'));
    }





    public function update(Request $request, $id)
{
    $product = Product::findOrFail($id);

    $formFields = $request->validate([
        'Category_ID' => 'required',
        'code' => 'required',
        'Referonce' => 'required|unique:products,Referonce,' . $id,
        'Designation' => 'required',
        'prace_bay' => 'required|numeric',
        'prace_sell' => 'required|numeric',
        'Quantite' => 'required|integer',
    ], [
        'Referonce.unique' => 'La référence que vous avez saisie est déjà utilisée. Merci de choisir une référence unique.',
    ]);

    $product->update($formFields);

    return redirect()->back()->with('success', 'Produit modifié avec succès.');
}


    public function destroy($id)

{
    if (auth()->user()->role !== 'admin') {
        return back()->with('error', 'Accès refusé.');
    }
    $product = Product::findOrFail($id);
    $product->delete();

    return redirect()->back()->with('success', 'Produit supprimé avec succès');
}

//exprt excil
public function export(Request $request)
{
    return Excel::download(new ProductsExport($request), 'stock.xlsx');
}

//imper fix excil

public function import(Request $request)
{
    $request->validate([
        'file' => 'required|file|mimes:xlsx,xls'
    ], [
        'file.required' => 'Veuillez sélectionner un fichier.',
        'file.mimes' => 'Le fichier doit être au format Excel (.xlsx, .xls).'
    ]);

    try {
        $file = $request->file('file');

        $spreadsheet = IOFactory::load($file->getPathname());
        $sheet = $spreadsheet->getActiveSheet();

        // غير 6 colonnes
        $headerRow = $sheet->rangeToArray('A1:F1', null, true, true, true)[1];

        $headers = array_map(function ($value) {
            return trim((string) $value);
        }, array_values($headerRow));

        $expectedHeaders = [
            'Category_ID',
            'code',
            'Referonce',
            'Designation',
            'prace_bay',
            'prace_sell',
        ];

        if ($headers !== $expectedHeaders) {
            return back()->with('error', 'Le fichier Excel ne correspond pas au modèle requis.');
        }

        $import = new ProductsImport();
        Excel::import($import, $file);
        if ($import->skipped > 0) {
            return back()->with('warning', $import->skipped . ' produits déjà existants n’ont pas été importés.');
        }
        
        return back()->with('success', 'Le fichier Excel a été importé avec succès.');


    } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
        return back()->with('warning', 'Certaines lignes contiennent des erreurs et n’ont pas été importées.');

    } catch (\Exception $e) {
        Log::error($e);

        return back()->with('error', 'Une erreur est survenue pendant l’importation.');
    }
}
///for dowload templet exile    

public function downloadTemplate()
{
    return Excel::download(new TemplateExport, 'template_products.xlsx');
}








}
