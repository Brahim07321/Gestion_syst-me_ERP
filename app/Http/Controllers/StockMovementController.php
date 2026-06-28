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

            // مهم: حيدنا annulation facture
            ->whereIn('source', [
                'achat',
                'facture',
                'restauration achat',
                'restauration facture'
            ])

            // نخلي غير les mouvements اللي تابعين لفاتورة/شراء موجود وماشي annulé
            ->where(function ($q) use ($cancelledStatuses) {

                // Bon d'achat
                $q->where(function ($sub) use ($cancelledStatuses) {
                    $sub->where('source', 'achat')
                        ->whereHas('purchase', function ($purchase) use ($cancelledStatuses) {
                            if (Schema::hasColumn('purchases', 'status')) {
                                $purchase->whereNotIn('status', $cancelledStatuses);
                            }
                        });
                })

                // Facture
                ->orWhere(function ($sub) use ($cancelledStatuses) {
                    $sub->where('source', 'facture')
                        ->whereHas('facture', function ($facture) use ($cancelledStatuses) {
                            if (Schema::hasColumn('factures', 'status')) {
                                $facture->whereNotIn('status', $cancelledStatuses);
                            }
                        });
                });
            });

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

        return $query;
    }
}