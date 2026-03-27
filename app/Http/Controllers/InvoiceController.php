<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function create()
    {
        $customers = \App\Models\Customer::all();
        $products = [
            ['Referonce' => 'PROD001', 'Designation' => 'Produit A', 'prace_sell' => 100],
            ['Referonce' => 'PROD002', 'Designation' => 'Produit B', 'prace_sell' => 200],
            // يمكنك جلب المنتجات من قاعدة البيانات بدلًا من هذا المثال الثابت
        ];

        return view('facture', compact('customers', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'invoice_number' => 'required|string|unique:invoices,invoice_number',
            'items' => 'required|array|min:1',
            'items.*.referonce' => 'required|string',
            'items.*.designation' => 'required|string',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $subtotal = 0;
        foreach ($request->items as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
        $tax = $subtotal * 0.2;
        $total = $subtotal + $tax;
        dd($request->all());


        DB::beginTransaction();
        try {
            $invoice = Invoice::create([
                'customer_id' => $request->customer_id,
                'invoice_date' => $request->invoice_date,
                'due_date' => $request->due_date,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
                'invoice_number' => $request->invoice_number,
            ]);
            

            foreach ($request->items as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'referonce' => $item['referonce'],
                    'designation' => $item['designation'],
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'total' => $item['price'] * $item['quantity'],
                ]);
            }

            DB::commit();
            return redirect()->back()->with('message', 'Facture enregistrée avec succès !');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Erreur lors de l\'enregistrement : ' . $e->getMessage());
        }
    }
}
