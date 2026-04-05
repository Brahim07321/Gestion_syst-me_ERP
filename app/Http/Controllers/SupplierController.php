<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SuppliersExport;
use App\Imports\SuppliersImport;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::latest()->get();
        return view('suppliers.index', compact('suppliers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:255',
        ]);

        Supplier::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
        ]);

        return redirect()->back()->with('success', 'Fournisseur ajouté avec succès.');
    }

    public function destroy($id)
    {
        if (auth()->user()->role !== 'admin') {
            return back()->with('error', 'Accès refusé.');
        }
        $supplier = Supplier::findOrFail($id);
        $supplier->delete();

        return redirect()->back()->with('success', 'Fournisseur supprimé avec succès.');
    }

    public function exportExcel()
    {
        return Excel::download(new SuppliersExport, 'liste_fournisseurs.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls'
        ], [
            'file.required' => 'Veuillez sélectionner un fichier.',
            'file.mimes' => 'Le fichier doit être au format Excel (.xlsx, .xls).'
        ]);
    
        try {
            $import = new \App\Imports\SuppliersImport();
            \Maatwebsite\Excel\Facades\Excel::import($import, $request->file('file'));
    
            // إلا كانو تزادو وبعضهم تدار ليهم skip
            if ($import->importedCount > 0 && $import->skippedCount > 0) {
                return redirect()->back()->with([
                    'warning' => "Import partiel : {$import->importedCount} fournisseur(s) ajouté(s), {$import->skippedCount} ignoré(s).",
                    'skipped_suppliers' => $import->skippedSuppliers
                ]);
            }
    
            // إلا كلهم تزادو
            if ($import->importedCount > 0) {
                return redirect()->back()->with('success', "{$import->importedCount} fournisseur(s) importé(s) avec succès.");
            }
    
            // إلا كلهم skip
            return redirect()->back()->with([
                'error' => "Aucun fournisseur importé. Tous les fournisseurs existent déjà ou les données sont invalides.",
                'skipped_suppliers' => $import->skippedSuppliers
            ]);
    
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Une erreur est survenue lors de l’importation.');
        }
    }

    
    public function downloadTemplate()
    {
        return response()->download(public_path('templates/template_fournisseurs.xlsx'));
    }
}