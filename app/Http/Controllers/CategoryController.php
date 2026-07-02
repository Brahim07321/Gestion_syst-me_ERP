<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function Category()
    {
        return view('Category'); // Ensure blade file is named 'customer.blade.php'
    }

    public function CreateCategory(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            return back()->with('error', 'Accès refusé.');
        }
    
        $formFields = $request->validate([
            'Category' => 'required|string|max:255|unique:categories,Category',
        ]);
    
        $formFields['company_id'] = auth()->user()->company_id;
    
        try {
            Category::create($formFields);
    
            return redirect('/Category')->with('message', 'Category created successfully!');
        } catch (\Exception $e) {
            return redirect('/Category')->with('error', 'Error: ' . $e->getMessage());
        }
    }
 

    public function ShowCategory(Request $request)
    {
        $search = $request->search;
        $companyId = auth()->user()->company_id;
    
        $Categorys = Category::where('company_id', $companyId)
            ->when($search, function ($query) use ($search) {
                $query->where('Category', 'like', '%' . $search . '%');
            })
            ->latest()
            ->paginate(10);
    
        return view('Category', compact('Categorys', 'search'));
    }
//delet 
public function destroy($id)
{
    if (auth()->user()->role !== 'admin') {
        return back()->with('error', 'Accès refusé.');
    }
    $category = Category::findOrFail($id);
    $category->delete();

    return back()->with('success', 'Catégorie supprimée avec succès.');
}

   
}
