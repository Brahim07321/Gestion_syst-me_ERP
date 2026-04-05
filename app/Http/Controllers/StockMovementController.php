<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockMovement;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StockMovementsExport;
use Barryvdh\DomPDF\Facade\Pdf;

class StockMovementController extends Controller
{
    public function index(Request $request)
    {
        $query = StockMovement::with(['product', 'facture', 'purchase'])
        ->whereIn('source', ['achat', 'facture', 'annulation facture']);

        // 🔍 Search
        if ($request->search) {
            $search = strtolower($request->search);

            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(reference) LIKE ?', ["%{$search}%"])
                  ->orWhereHas('product', function ($p) use ($search) {
                      $p->whereRaw('LOWER(Designation) LIKE ?', ["%{$search}%"]);
                  });
            });
        }

        // 📦 Type
        if ($request->type) {
            $query->where('type', $request->type);
        }

        // 🔗 Source
        if ($request->source) {
            $query->where('source', $request->source);
        }

        // 📅 Date from
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        // 📅 Date to
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $movements = $query->latest()->paginate(15)->appends($request->query());

        return view('stock_movements', compact('movements'));
    }

    public function exportExcel(Request $request)
{
    return Excel::download(new \App\Exports\StockMovementsExport($request), 'historique_stock.xlsx');
}

public function exportPdf(Request $request)
{
$query = StockMovement::with(['product', 'facture', 'purchase'])
    ->whereIn('source', ['achat', 'facture', 'annulation facture']);
    
    if ($request->search) {
        $search = strtolower($request->search);

        $query->where(function ($q) use ($search) {
            $q->whereRaw('LOWER(reference) LIKE ?', ["%{$search}%"])
              ->orWhereHas('product', function ($p) use ($search) {
                  $p->whereRaw('LOWER(Designation) LIKE ?', ["%{$search}%"]);
              });
        });
    }

    if ($request->type) {
        $query->where('type', $request->type);
    }

    if ($request->source) {
        $query->where('source', $request->source);
    }

    if ($request->date_from) {
        $query->whereDate('created_at', '>=', $request->date_from);
    }

    if ($request->date_to) {
        $query->whereDate('created_at', '<=', $request->date_to);
    }

    $movements = $query->latest()->get();

    $pdf = Pdf::loadView('stockmovements_pdf', compact('movements'))
        ->setPaper('A4', 'landscape');

    return $pdf->download('historique_stock.pdf');
}


}