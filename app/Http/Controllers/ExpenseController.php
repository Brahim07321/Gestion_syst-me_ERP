<?php

namespace App\Http\Controllers;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    public function index(Request $request)
{
    $search = $request->search;
    $companyId = auth()->user()->company_id;

    $expenses = Expense::where('company_id', $companyId)
        ->when($search, function ($query) use ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        })
        ->latest()
        ->paginate(10);

    return view('expenses.index', compact('expenses', 'search'));
}

public function store(Request $request)
{
    if (auth()->user()->role !== 'admin') {
        return back()->with('error', 'Accès refusé.');
    }

    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'amount' => 'required|numeric|min:0',
        'expense_date' => 'required|date',
        'description' => 'nullable|string',
    ]);

    $validated['company_id'] = auth()->user()->company_id;

    Expense::create($validated);

    return back()->with('success', 'Dépense ajoutée avec succès.');
}

 
    public function destroy($id)
{
    if (auth()->user()->role !== 'admin') {
        return back()->with('error', 'Accès refusé.');
    }

    $expense = Expense::where('company_id', auth()->user()->company_id)
        ->findOrFail($id);

    $expense->delete();

    return back()->with('success', 'Dépense supprimée avec succès.');
}
}