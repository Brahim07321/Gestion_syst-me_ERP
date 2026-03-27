<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;

class SaveCustomereController extends Controller
{
    public function saveSelectedCustomers(Request $request)
    {
        $selectedCustomers = $request->input('customers');

        foreach ($selectedCustomers as $customer) {
            // Insérez chaque client dans une table ou effectuez une autre action
            \DB::table('selected_customers')->insert([
                'customer_id' => $customer['id'],
                'customer_name' => $customer['name'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return response()->json(['message' => 'Données sauvegardées avec succès.'], 200);
    }
}
