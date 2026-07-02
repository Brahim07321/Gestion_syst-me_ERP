<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockMovement;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Schema;

class StockMovementController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->stockMovementQuery($request);

        $movements = $query->latest()->paginate(15)->appends($request->query());

        return view('stock_movements', compact('movements'));
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(
            new \App\Exports\StockMovementsExport($request),
            'historique_stock.xlsx'
        );
    }

    public function exportPdf(Request $request)
    {
        $movements = $this->stockMovementQuery($request)
            ->latest()
            ->get();

        $pdf = Pdf::loadView('stockmovements_pdf', compact('movements'))
            ->setPaper('A4', 'landscape');

        return $pdf->download('historique_stock.pdf');
    }

    private function stockMovementQuery(Request $request)
    {
        $companyId = auth()->user()->company_id;
    
        $cancelledStatuses = [
            'annulé',
            'annule',
            'annulée',
            'annulee',
            'cancelled',
            'canceled',
            'supprimé',
            'supprime',
        ];
    
        $query = StockMovement::with(['product', 'facture', 'purchase'])
            ->where('company_id', $companyId)
            ->whereIn('source', [
                'achat',
                'facture',
                'restauration achat',
                'restauration facture',
            ])
            ->where(function ($q) use ($cancelledStatuses, $companyId) {
                $q->where(function ($sub) use ($cancelledStatuses, $companyId) {
                    $sub->whereIn('source', ['achat', 'restauration achat'])
                        ->whereHas('purchase', function ($purchase) use ($cancelledStatuses, $companyId) {
                            $purchase->where('company_id', $companyId);
    
                            if (Schema::hasColumn('purchases', 'status')) {
                                $purchase->whereNotIn('status', $cancelledStatuses);
                            }
                        });
                })
                ->orWhere(function ($sub) use ($cancelledStatuses, $companyId) {
                    $sub->whereIn('source', ['facture', 'restauration facture'])
                        ->whereHas('facture', function ($facture) use ($cancelledStatuses, $companyId) {
                            $facture->where('company_id', $companyId);
    
                            if (Schema::hasColumn('factures', 'status')) {
                                $facture->whereNotIn('status', $cancelledStatuses);
                            }
                        });
                });
            });
    
        if ($request->search) {
            $search = strtolower($request->search);
    
            $query->where(function ($q) use ($search, $companyId) {
                $q->whereRaw('LOWER(reference) LIKE ?', ["%{$search}%"])
                    ->orWhereHas('product', function ($p) use ($search, $companyId) {
                        $p->where('company_id', $companyId)
                            ->whereRaw('LOWER(Designation) LIKE ?', ["%{$search}%"]);
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
    
        return $query;
    }
}