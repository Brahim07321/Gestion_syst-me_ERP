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
        // Validate input
        $formFields = $request->validate([
            'Category' => 'required|string|max:255', // Add constraints for better validation
        ]);
    
        // Add category
        try {
            Category::create($formFields);
            return redirect('/Category')->with('message', 'Category created successfully!');
        } catch (\Exception $e) {
            return redirect('/Category')->with('error', 'Error: ' . $e->getMessage());
        }
    }
    
    public function ShowCategory(Request $request)
    {
        $search = strtolower($request->search ?? '');
    
        $Categorys = Category::when($search, function ($query) use ($search) {
            $query->whereRaw('LOWER(category) LIKE ?', ["%{$search}%"]);
        })->latest()->paginate(10);
    
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
