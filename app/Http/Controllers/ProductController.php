<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\customer;

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
        // Validate the request data
        $formFields = $request->validate([
            'Category_ID' => 'required',
            'code' => 'required',
            'Referonce' => 'required',
            'Designation' => 'required',
            'prace_bay' => 'required|numeric',
            'prace_sell' => 'required|numeric',
            'Quantite' => 'required|integer',
        ]);

        // Create a new product using the validated fields
        Product::create($formFields);

        // Redirect with a success message
        return redirect('/product')->with('message', 'Product created successfully');
    }
   

    public function index(Request $request)
{
    $search = $request->search;
    $categories = Category::all(); // جلب كل الفئات
    $products = Product::when($search, function ($query, $search) {
        $query->where('Designation', 'like', '%' . $search . '%')
              ->orWhere('Referonce', 'like', '%' . $search . '%')
              ->orWhere('code', 'like', '%' . $search . '%');
    })->paginate(10);

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
        $customers = Customer::all(['id', 'name']); // Récupérer les clients avec leurs IDs et noms

        // Pass the products and customers to the view
        return view('facture', compact('products', 'customers'));
    }











}
