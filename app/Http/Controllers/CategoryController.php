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
        // Validate input
        $formFields = $request->validate([
            'category' => 'required|string|max:255', // Add constraints for better validation
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


   
}
