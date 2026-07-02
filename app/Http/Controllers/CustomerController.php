<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CustomersExport;
use Barryvdh\DomPDF\Facade\Pdf;



class CustomerController extends Controller
{
    public function Customer()
    {
        return view('customer');
    }

    public function createCustomer(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'address' => 'nullable',
            'contact' => 'nullable',
        ]);
    
        try {
            Customer::create([
                'company_id' => auth()->user()->company_id,
                'name' => $request->name,
                'address' => $request->address ?? '',
                'contact' => $request->contact ?? '',
            ]);
    
            if ($request->input('source') === 'invoice') {
                return redirect()->back()->with('success', 'Client ajouté avec succès!');
            }
    
            return redirect('/Customer')->with('message', 'Client ajouté avec succès!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    public function ShowCustomers(Request $request)
    {
        $search = $request->search;
        $companyId = auth()->user()->company_id;
    
        $customers = Customer::where('company_id', $companyId)
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('address', 'like', '%' . $search . '%')
                      ->orWhere('contact', 'like', '%' . $search . '%');
                });
            })
            ->latest()
            ->paginate(10)
            ->appends([
                'search' => $search
            ]);
    
        return view('customer', compact('customers'));
    }

    public function getCustomers()
    {
        $customers = Customer::all(['id', 'name']);
        return view('facture', compact('customers'));
    }

    public function edit($id)
    {
        $companyId = auth()->user()->company_id;
    
        $customer = Customer::where('company_id', $companyId)->find($id);
        $customers = Customer::where('company_id', $companyId)->paginate(10);
    
        if (!$customer) {
            return redirect('/Customer')->with('error', 'العميل غير موجود.');
        }
    
        return view('Customers.edit', compact('customer', 'customers'));
    }

    public function update(Request $request, $id)
    {
        $customer = Customer::where('company_id', auth()->user()->company_id)->find($id);
    
        if (!$customer) {
            return redirect('/Customer')->with('error', 'العميل غير موجود.');
        }
    
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'contact' => 'nullable|string|max:20',
        ]);
    
        $customer->update($validatedData);
    
        return redirect('/Customer')->with('message', 'Customer updated successfully.');
    }


    public function saveCustomers(Request $request)
    {
        try {
            $customers = $request->input('customers', []);

            foreach ($customers as $customer) {
                Customer::updateOrCreate(
                    ['id' => $customer['id']],
                    ['name' => $customer['name']]
                );
            }

            return response()->json(['message' => 'Clients sauvegardés avec succès!'], 200);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return response()->json(['error' => 'Une erreur est survenue.'], 500);
        }
    }
//for dellet
   public function destroy($id)
{
    if (auth()->user()->role !== 'admin') {
        return back()->with('error', 'Accès refusé.');
    }

    $customer = Customer::where('company_id', auth()->user()->company_id)->findOrFail($id);
    $customer->delete();

    return redirect('/Customer')->with('message', 'Client supprimé avec succès.');
}

//for export excill

public function exportExcel()
{
    return Excel::download(new CustomersExport, 'clients.xlsx');
}

public function exportPdf()
{
    $customers = Customer::where('company_id', auth()->user()->company_id)
        ->select('id', 'name', 'address', 'contact')
        ->get();

    $pdf = Pdf::loadView('customers.pdf', compact('customers'))
        ->setPaper('A4', 'portrait');

    return $pdf->download('liste_des_clients.pdf');
}
}