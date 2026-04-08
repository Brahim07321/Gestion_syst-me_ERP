<?php

namespace App\Http\Controllers;

use App\Models\Facture;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function store(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'note' => 'nullable|string|max:255',
        ]);

        $facture = Facture::withTrashed()->findOrFail($id);

        if ($facture->trashed() || $facture->status === 'annulée') {
            return back()->with('error', 'Impossible d’ajouter un paiement à cette facture.');
        }

        if ($facture->status === 'payée') {
            return back()->with('error', 'Cette facture est déjà payée.');
        }

        $alreadyPaid = $facture->payments()->sum('amount');
        $remaining = $facture->total - $alreadyPaid;

        if ((float) $request->amount > $remaining) {
            return back()->with('error', 'Le montant dépasse le reste à payer.');
        }

        Payment::create([
            'facture_id' => $facture->id,
            'amount' => $request->amount,
            'payment_date' => $request->payment_date,
            'note' => $request->note,
        ]);

        $newTotalPaid = $facture->payments()->sum('amount');
        $newRemaining = $facture->total - $newTotalPaid;

        if ($newRemaining <= 0) {
            $facture->status = 'payée';
        } elseif ($newTotalPaid > 0) {
            $facture->status = 'partiellement payée';
        } else {
            $facture->status = 'non payée';
        }

        $facture->paid_amount = $newTotalPaid;
        $facture->remaining_amount = max($newRemaining, 0);
        $facture->save();

        return back()->with('success', 'Paiement ajouté avec succès.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'note' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            $payment = Payment::findOrFail($id);
            $facture = Facture::findOrFail($payment->facture_id);

            if ($facture->status === 'annulée') {
                DB::rollBack();
                return back()->with('error', 'Impossible de modifier un paiement d’une facture annulée.');
            }

            $otherPaymentsTotal = $facture->payments()
                ->where('id', '!=', $payment->id)
                ->sum('amount');

            $newTotalPaid = $otherPaymentsTotal + (float) $request->amount;

            if ($newTotalPaid > $facture->total) {
                DB::rollBack();
                return back()->with(
                    'error',
                    'Le montant total des paiements ne peut pas dépasser le total de la facture.'
                );
            }

            $payment->update([
                'amount' => $request->amount,
                'payment_date' => $request->payment_date,
                'note' => $request->note,
            ]);

            $newRemaining = $facture->total - $newTotalPaid;

            if ($newRemaining <= 0) {
                $facture->status = 'payée';
            } elseif ($newTotalPaid > 0) {
                $facture->status = 'partiellement payée';
            } else {
                $facture->status = 'non payée';
            }

            $facture->paid_amount = $newTotalPaid;
            $facture->remaining_amount = max($newRemaining, 0);
            $facture->save();

            DB::commit();

            return back()->with('success', 'Paiement modifié avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $payment = Payment::findOrFail($id);
            $facture = Facture::findOrFail($payment->facture_id);

            if ($facture->status === 'annulée') {
                DB::rollBack();
                return back()->with('error', 'Impossible de supprimer un paiement d’une facture annulée.');
            }

            $payment->delete();

            $totalPaid = $facture->payments()->sum('amount');
            $remaining = max($facture->total - $totalPaid, 0);

            if ($remaining <= 0) {
                $status = 'payée';
            } elseif ($totalPaid > 0) {
                $status = 'partiellement payée';
            } else {
                $status = 'non payée';
            }

            $facture->update([
                'paid_amount' => $totalPaid,
                'remaining_amount' => $remaining,
                'status' => $status,
            ]);

            DB::commit();

            return back()->with('success', 'Paiement supprimé avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }
}