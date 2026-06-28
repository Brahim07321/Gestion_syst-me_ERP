<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Purchase;
use App\Models\Facture;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class ArchiveDocumentController extends Controller
{
    public function index(Request $request)
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

        /*
        |--------------------------------------------------------------------------
        | Bons d'achat annulés / supprimés
        |--------------------------------------------------------------------------
        */
        $purchaseQuery = $this->queryWithTrashedIfAvailable(Purchase::class)
            ->with('supplier')
            ->where(function ($q) use ($cancelledStatuses) {
                $q->whereIn('status', $cancelledStatuses);

                if ($this->usesSoftDeletes(Purchase::class)) {
                    $q->orWhereNotNull('deleted_at');
                }
            });

        /*
        |--------------------------------------------------------------------------
        | Factures annulées / supprimées
        |--------------------------------------------------------------------------
        */
        $factureQuery = $this->queryWithTrashedIfAvailable(Facture::class)
            ->with('customer')
            ->where(function ($q) use ($cancelledStatuses) {
                $q->whereIn('status', $cancelledStatuses);

                if ($this->usesSoftDeletes(Facture::class)) {
                    $q->orWhereNotNull('deleted_at');
                }
            });

        /*
        |--------------------------------------------------------------------------
        | Recherche
        |--------------------------------------------------------------------------
        */
        if ($request->filled('search')) {
            $search = strtolower($request->search);

            $purchaseQuery->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(purchase_code) LIKE ?', ["%{$search}%"])
                    ->orWhereHas('supplier', function ($s) use ($search) {
                        $s->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
                    });
            });

            $factureQuery->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(code_facture) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(client_name) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(status) LIKE ?', ["%{$search}%"]);
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Date filters
        |--------------------------------------------------------------------------
        */
        if ($request->filled('date_from')) {
            $purchaseQuery->whereDate('created_at', '>=', $request->date_from);
            $factureQuery->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $purchaseQuery->whereDate('created_at', '<=', $request->date_to);
            $factureQuery->whereDate('created_at', '<=', $request->date_to);
        }

        /*
        |--------------------------------------------------------------------------
        | Type filter
        |--------------------------------------------------------------------------
        */
        $type = $request->get('type');

        $purchases = collect();
        $factures = collect();

        if (!$type || $type === 'achat') {
            $purchases = $purchaseQuery->latest()->get();
        }

        if (!$type || $type === 'facture') {
            $factures = $factureQuery->latest()->get();
        }

        return view('documents_archives', compact('purchases', 'factures'));
    }

    private function queryWithTrashedIfAvailable(string $modelClass)
    {
        if ($this->usesSoftDeletes($modelClass)) {
            return $modelClass::withTrashed();
        }

        return $modelClass::query();
    }

    private function usesSoftDeletes(string $modelClass): bool
    {
        return in_array(SoftDeletes::class, class_uses_recursive($modelClass));
    }

    public function restoreFacture($id)
    {
        if (auth()->user()->role !== 'admin') {
            return back()->with('error', 'Accès refusé.');
        }
    
        try {
            return DB::transaction(function () use ($id) {
    
                $facture = Facture::withTrashed()
                    ->with(['items', 'payments'])
                    ->findOrFail($id);
    
                $status = mb_strtolower(trim($facture->status ?? ''));
    
                $isCancelled = in_array($status, [
                    'annulé',
                    'annule',
                    'annulée',
                    'annulee',
                ]);
    
                /*
                 مهم:
                 كنشوفو واش stock كان ترجع من قبل
                 إما بسبب annulation facture
                 أو مستقبلاً بسبب suppression facture
                */
                $stockWasReturned = StockMovement::where('reference', $facture->code_facture)
                    ->whereIn('source', [
                        'annulation facture',
                        'suppression facture',
                    ])
                    ->exists();
    
                // رجع facture إذا كانت supprimée
                if ($facture->trashed()) {
                    $facture->restore();
                }
    
                /*
                 إذا كانت annulée أو stock كان ترجع من قبل،
                 خاصنا ننقصو stock من جديد باش facture ترجع active.
                */
                if ($isCancelled || $stockWasReturned) {
                    foreach ($facture->items as $item) {
                        $product = Product::where('Referonce', $item->referonce)->first();
    
                        if (!$product) {
                            throw new \Exception('Produit introuvable: ' . $item->referonce);
                        }
    
                        $quantity = (float) $item->quantity;
    
                        if ($product->Quantite < $quantity) {
                            throw new \Exception('Stock insuffisant pour restaurer: ' . $product->Designation);
                        }
    
                        $product->decrement('Quantite', $quantity);
    
                        StockMovement::create([
                            'product_id' => $product->id,
                            'type' => 'sortie',
                            'quantity' => $quantity,
                            'source' => 'restauration facture',
                            'reference' => $facture->code_facture,
                        ]);
                    }
    
                    $paid = $facture->payments()->sum('amount');
                    $remaining = max($facture->total - $paid, 0);
    
                    if ($remaining <= 0) {
                        $newStatus = 'payée';
                    } elseif ($paid > 0) {
                        $newStatus = 'partiellement payée';
                    } else {
                        $newStatus = 'non payée';
                    }
    
                    $facture->update([
                        'status' => $newStatus,
                        'paid_amount' => $paid,
                        'remaining_amount' => $remaining,
                    ]);
                }
    
                return redirect()
                    ->route('documents.archives')
                    ->with('success', 'Facture restaurée avec succès et stock mis à jour.');
            });
    
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }


    public function restorePurchase($id)
    {
        if (auth()->user()->role !== 'admin') {
            return back()->with('error', 'Accès refusé.');
        }
    
        try {
            return DB::transaction(function () use ($id) {
    
                $purchase = Purchase::withTrashed()
                    ->with(['items.product'])
                    ->findOrFail($id);
    
                if (!$purchase->trashed()) {
                    return back()->with('error', 'Ce bon d’achat n’est pas supprimé.');
                }
    
                // 1. رجّع bon d'achat من soft delete
                $purchase->restore();
    
                // 2. زيد stock ديال المنتجات
                foreach ($purchase->items as $item) {
                    $product = $item->product;
    
                    if (!$product) {
                        throw new \Exception('Produit introuvable dans ce bon d’achat.');
                    }
    
                    $quantity = (float) $item->quantity;
    
                    $product->increment('Quantite', $quantity);
    
                    StockMovement::create([
                        'product_id' => $product->id,
                        'type' => 'entree',
                        'quantity' => $quantity,
                        'source' => 'restauration achat',
                        'reference' => $purchase->purchase_code,
                    ]);
                }
    
                // 3. مهم: بدّل status من annulé إلى reçu
                $purchase->update([
                    'status' => 'reçu',
                ]);
    
                return redirect()
                    ->route('purchases.index')
                    ->with('success', 'Bon d’achat restauré avec succès, stock ajouté et statut marqué comme reçu.');
            });
    
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}