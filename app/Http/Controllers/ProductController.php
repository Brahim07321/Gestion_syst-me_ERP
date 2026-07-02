<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Imports\ProductsImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TemplateExport;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\CompanySetting;
use Illuminate\Validation\Rule;
use App\Models\Customer;

use App\Models\PurchaseItem;

// You need this for category model
class ProductController extends Controller
{


    public function product()
    {
        return view(view: 'product'); // Ensure blade file is named 'customer.blade.php'
    }


    public function FormCategory()
    {
        $Categorys = Category::where('company_id', auth()->user()->company_id)->get();
    
        return view('product', ['Categorys' => $Categorys]);
    }


    public function createproduct(Request $request)
    {
        $companyId = auth()->user()->company_id;
    
        $formFields = $request->validate([
            'Category_ID' => [
                'required',
                Rule::exists('categories', 'id')->where(function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId);
                }),
            ],
            'code' => 'required',
            'Referonce' => [
                'required',
                Rule::unique('products', 'Referonce')->where(function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId);
                }),
            ],
            'Designation' => 'required',
            'prace_bay' => 'required|numeric|min:0',
            'prace_sell' => 'required|numeric|min:0',
            'Quantite' => 'required|integer|min:0',
        ], [
            'Referonce.unique' => '⚠️Référence déjà existante.',
            'Referonce.required' => '⚠️ خاصك تدخل référence.',
        ]);
    
        $formFields['company_id'] = $companyId;
    
        Product::create($formFields);
    
        return redirect()->back()->with('success', 'Product created successfully');
    }
    
   

    public function index(Request $request)
    {
        $search = $request->search;
        $companyId = auth()->user()->company_id;
    
        $categories = Category::where('company_id', $companyId)->get();
    
        $products = Product::where('company_id', $companyId)
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('Designation', 'like', '%' . $search . '%')
                      ->orWhere('Referonce', 'like', '%' . $search . '%')
                      ->orWhere('code', 'like', '%' . $search . '%');
                });
            })
            ->latest()
            ->paginate(25);
    
        return view('product', compact('products', 'search', 'categories'));
    }

    public function getProductByReference(Request $request)
    {
        $searchQuery = $request->input('query');
        $companyId = auth()->user()->company_id;
    
        $products = Product::where('company_id', $companyId)
            ->where(function ($query) use ($searchQuery) {
                $query->where('Referonce', 'like', "%{$searchQuery}%")
                      ->orWhere('Designation', 'like', "%{$searchQuery}%");
            })
            ->limit(10)
            ->get();
    
        return response()->json($products);
    }


public function showInvoice()
{
    $companyId = auth()->user()->company_id;

    $products = Product::where('company_id', $companyId)->get();
    $customers = Customer::where('company_id', $companyId)
        ->get(['id', 'name', 'address']);

    $company = CompanySetting::first();

    return view('facture', compact('products', 'customers', 'company'));
}




public function update(Request $request, $id)
{
    $companyId = auth()->user()->company_id;

    $product = Product::where('company_id', $companyId)->findOrFail($id);

    $formFields = $request->validate([
        'Category_ID' => [
            'required',
            Rule::exists('categories', 'id')->where(function ($query) use ($companyId) {
                return $query->where('company_id', $companyId);
            }),
        ],
        'code' => 'required',
        'Referonce' => [
            'required',
            Rule::unique('products', 'Referonce')
                ->ignore($id)
                ->where(function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId);
                }),
        ],
        'Designation' => 'required',
        'prace_bay' => 'required|numeric|min:0',
        'prace_sell' => 'required|numeric|min:0',
        'Quantite' => 'required|integer|min:0',
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

    $product = Product::where('company_id', auth()->user()->company_id)->findOrFail($id);
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


public function details($id)
{
    $product = Product::where('company_id', auth()->user()->company_id)->findOrFail($id);

    $purchases = PurchaseItem::with('purchase.supplier')
        ->where('product_id', $id)
        ->latest()
        ->get();

    return view('products.details', compact('product', 'purchases'));
}







}
