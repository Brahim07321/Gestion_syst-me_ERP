<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
class CustomerController extends Controller
{
    public function Customer()
    {
        return view('customer'); // Ensure blade file is named 'customer.blade.php'
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

            // Vérifier la source pour rediriger correctement
            if ($request->input('source') === 'invoice') {
                return redirect()->back()->with('message', 'Customer created successfully and you are still on the Invoice page!');
            }

            return redirect('/Customer')->with('message', 'Customer created successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }








    public function ShowCustomers()
    {
        // Fetch all customers
        $customers = Customer::all();

        // Pass the data to the view
        return view('customer', ['customers' => $customers]);
    }
    public function getCustomers()
    {
        $customers = Customer::all(['id', 'name']); // Récupérer les clients avec leurs IDs et noms
        return view('facture', compact('customers'));
    }
    public function edit($id)
    {
        $customer = Customer::find($id);
        $customers = Customer::all();

        if (!$customer) {
            return redirect()->route('customers.update')->with('error', 'العميل غير موجود.');
        }

        return view('Customers.edit', compact('customer', 'customers'));
    }





    // Méthode pour mettre à jour le client
    public function update(Request $request, $id)
    {
        // البحث عن العميل حسب الـ ID
        $customer = Customer::find($id);

        // إذا لم يتم العثور على العميل، إعادة التوجيه مع رسالة خطأ
        if (!$customer) {
            return redirect()->route('customers.edit', $id)->with('error', 'العميل غير موجود.');
        }

        // التحقق من صحة البيانات قبل التحديث
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'contact' => 'required|digits:10', // تحقق من أن الرقم مكون من 10 أرقام
        ]);

        // تحديث بيانات العميل
        $customer->update($validatedData);

        // إعادة التوجيه إلى صفحة العملاء مع رسالة نجاح
        return redirect('/Customer')->with('message', 'Customer updated successfully.');
    }
    // for export PDF and excel 
    public function saveCustomers(Request $request)
    {
        try {
            $customers = $request->input('customers', []);
            
            foreach ($customers as $customer) {
                // Sauvegarder chaque client ou traiter les données
                Customer::updateOrCreate(
                    ['id' => $customer['id']],
                    ['name' => $customer['name']]
                );
            }
    
            return response()->json(['message' => 'Clients sauvegardés avec succès!'], 200);
        } catch (\Exception $e) {
            // Log l'erreur pour plus de détails
            \Log::error($e->getMessage());
            return response()->json(['error' => 'Une erreur est survenue.'], 500);
        }
    }
    
    





}
