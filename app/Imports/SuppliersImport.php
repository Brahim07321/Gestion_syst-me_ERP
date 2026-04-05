<?php

namespace App\Imports;

use App\Models\Supplier;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SuppliersImport implements ToCollection, WithHeadingRow
{
    public $importedCount = 0;
    public $skippedCount = 0;
    public $skippedSuppliers = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $name = trim($row['nom'] ?? '');
            $phone = trim($row['telephone'] ?? '');
            $email = trim($row['email'] ?? '');
            $address = trim($row['adresse'] ?? '');

            // إلا كان الاسم أو الهاتف ناقص
            if (empty($name) || empty($phone)) {
                $this->skippedCount++;
                $this->skippedSuppliers[] = $name ?: 'Nom vide';
                continue;
            }

            // التحقق واش كاين من قبل بنفس الاسم والهاتف
            $exists = Supplier::whereRaw('LOWER(name) = ?', [strtolower($name)])
                ->where('phone', $phone)
                ->exists();

            if ($exists) {
                $this->skippedCount++;
                $this->skippedSuppliers[] = $name;
                continue;
            }

            Supplier::create([
                'name' => $name,
                'phone' => $phone,
                'email' => $email ?: null,
                'address' => $address ?: null,
            ]);

            $this->importedCount++;
        }
    }
}