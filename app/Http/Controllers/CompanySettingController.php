<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CompanySetting;

class CompanySettingController extends Controller
{
    public function edit()
    {
        $setting = CompanySetting::first();

        return view('settings.company', compact('setting'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'company_name' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'footer_note' => 'nullable|string',
            'footer_contact' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $setting = CompanySetting::first() ?? new CompanySetting();

        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('company', 'public');
            $setting->logo = $logoPath;
        }

        $setting->company_name = $request->company_name;
        $setting->city = $request->city;
        $setting->phone = $request->phone;
        $setting->email = $request->email;
        $setting->address = $request->address;
        $setting->footer_note = $request->footer_note;
        $setting->footer_contact = $request->footer_contact;
        $setting->save();

        return back()->with('success', 'Paramètres société enregistrés avec succès.');
    }
}