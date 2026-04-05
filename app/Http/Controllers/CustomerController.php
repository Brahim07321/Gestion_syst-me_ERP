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
        $formFields = $request->validate([
            'name' => 'required',
            'address' => 'required',
            'contact' => 'required',
        ]);

        try {
            Customer::create($formFields);

            if ($request->input('source') === 'invoice') {
                return redirect()->back()->with('message', 'Customer created successfully and you are still on the Invoice page!');
            }

            return redirect('/Customer')->with('message', 'Customer created successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function ShowCustomers(Request $request)
    {
        $search = $request->search;

        $customers = Customer::when($search, function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                      ->orWhere('address', 'like', '%' . $search . '%')
                      ->orWhere('contact', 'like', '%' . $search . '%');
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
        $customer = Customer::find($id);
        $customers = Customer::paginate(10);

        if (!$customer) {
            return redirect()->route('customers.update')->with('error', 'العميل غير موجود.');
        }

        return view('Customers.edit', compact('customer', 'customers'));
    }

    public function update(Request $request, $id)
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return redirect()->route('customers.edit', $id)->with('error', 'العميل غير موجود.');
        }

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'contact' => 'required|digits:10',
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
    
    $customer = Customer::findOrFail($id);
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
    $customers = Customer::select('id', 'name', 'address', 'contact')->get();

    $pdf = Pdf::loadView('customers.pdf', compact('customers'))
        ->setPaper('A4', 'portrait');

    return $pdf->download('liste_des_clients.pdf');
}
}