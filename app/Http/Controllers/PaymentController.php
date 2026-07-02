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
        $companyId = auth()->user()->company_id;

        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'note' => 'nullable|string|max:255',
        ]);

        $facture = Facture::withTrashed()
            ->where('company_id', $companyId)
            ->findOrFail($id);

        if ($facture->trashed() || $facture->status === 'annulée') {
            return back()->with('error', 'Impossible d’ajouter un paiement à cette facture.');
        }

        if ($facture->status === 'payée') {
            return back()->with('error', 'Cette facture est déjà payée.');
        }

        $amount = (float) $request->amount;
        $remaining = (float) $facture->remaining_amount;

        if ($amount > $remaining) {
            return back()
                ->withInput()
                ->with('error', 'Le montant dépasse le reste à payer.');
        }

        DB::beginTransaction();

        try {
            Payment::create([
                'facture_id' => $facture->id,
                'amount' => $amount,
                'payment_date' => $request->payment_date,
                'note' => $request->note,
            ]);

            $newTotalPaid = (float) $facture->paid_amount + $amount;
            $newRemaining = max((float) $facture->total - $newTotalPaid, 0);

            if ($newRemaining <= 0) {
                $status = 'payée';
            } elseif ($newTotalPaid > 0) {
                $status = 'partiellement payée';
            } else {
                $status = 'non payée';
            }

            $facture->update([
                'paid_amount' => $newTotalPaid,
                'remaining_amount' => $newRemaining,
                'status' => $status,
            ]);

            DB::commit();

            return back()->with('success', 'Paiement ajouté avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $companyId = auth()->user()->company_id;

        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'note' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            $payment = Payment::findOrFail($id);

            $facture = Facture::withTrashed()
                ->where('company_id', $companyId)
                ->findOrFail($payment->facture_id);

            if ($facture->trashed()) {
                DB::rollBack();
                return back()->with('error', 'Impossible de modifier un paiement d’une facture supprimée.');
            }

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
        $companyId = auth()->user()->company_id;

        DB::beginTransaction();

        try {
            $payment = Payment::findOrFail($id);

            $facture = Facture::withTrashed()
                ->where('company_id', $companyId)
                ->findOrFail($payment->facture_id);

            if ($facture->trashed()) {
                DB::rollBack();
                return back()->with('error', 'Impossible de supprimer un paiement d’une facture supprimée.');
            }

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