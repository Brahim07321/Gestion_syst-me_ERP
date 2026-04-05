<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $search = strtolower($request->search ?? '');

        $expenses = Expense::when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                      ->orWhereRaw('LOWER(description) LIKE ?', ["%{$search}%"]);
                });
            })
            ->latest()
            ->paginate(10);

        return view('expenses.index', compact('expenses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        Expense::create([
            'name' => $request->name,
            'amount' => $request->amount,
            'expense_date' => $request->expense_date,
            'description' => $request->description,
        ]);

        return redirect()->back()->with('success', 'Dépense ajoutée avec succès.');
    }

    public function destroy($id)
    {
        if (auth()->user()->role !== 'admin') {
            return back()->with('error', 'Accès refusé.');
        }
        $expense = Expense::findOrFail($id);
        $expense->delete();

        return redirect()->back()->with('success', 'Dépense supprimée avec succès.');
    }
}