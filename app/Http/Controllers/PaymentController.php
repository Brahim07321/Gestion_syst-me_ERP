<?php

namespace App\Http\Controllers;

use App\Models\Facture;
use App\Models\Payment;
use Illuminate\Http\Request;

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

if ($facture->status === 'annulée') {
    return redirect()->back()->with('error', 'Cette facture est annulée. Aucun paiement ne peut être ajouté.');
}

if ($facture->status === 'payée') {
    return redirect()->back()->with('error', 'Cette facture est déjà payée.');
}
    
        $alreadyPaid = $facture->payments()->sum('amount');
        $remaining = $facture->total - $alreadyPaid;
    
        if ($request->amount > $remaining) {
            return redirect()->back()->with('error', 'Le montant dépasse le reste à payer.');
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
        $facture->remaining_amount = $newRemaining;
        $facture->save();
    
        return redirect()->back()->with('success', 'Paiement ajouté avec succès.');
    }
}